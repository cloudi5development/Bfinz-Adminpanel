<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\OtpVerification;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Generates, sends, and verifies the 6-digit OTP used for mobile login.
 *
 * Three independent throttle layers protect this: the route-level
 * throttle:X,1 middleware (routes/api.php, per IP, per minute), the
 * resend-cooldown / hourly-cap checks below (per mobile AND per IP, via
 * Laravel's RateLimiter — a cache-backed counter, not a DB row, so it can't
 * be reset by the delete-and-recreate cycle below), and the per-record
 * verify-attempt cap.
 */
class OtpService
{
    private const OTP_LENGTH = 6;

    private const OTP_EXPIRY_MINUTES = 5;

    private const MAX_VERIFY_ATTEMPTS = 5;

    private const RESEND_COOLDOWN_SECONDS = 30;

    private const MAX_SENDS_PER_HOUR = 5;

    public function __construct(private readonly SendSmsService $sms) {}

    /**
     * Generate a fresh OTP for $mobile and send it via SMS.
     *
     * @throws ApiException on rate limit or SMS gateway failure
     */
    public function send(string $mobile, ?string $ip = null): void
    {
        $this->guardResendCooldown($mobile);
        $this->guardHourlyCap($mobile, $ip);

        $otp = str_pad((string) random_int(0, 10 ** self::OTP_LENGTH - 1), self::OTP_LENGTH, '0', STR_PAD_LEFT);

        OtpVerification::where('mobile', $mobile)->delete();

        OtpVerification::create([
            'mobile' => $mobile,
            'otp_hash' => Hash::make($otp),
            'ip' => $ip,
            'expires_at' => now()->addMinutes(self::OTP_EXPIRY_MINUTES),
            'attempts' => 0,
            'last_sent_at' => now(),
        ]);

        RateLimiter::hit($this->mobileRateLimitKey($mobile), 3600);

        if ($ip) {
            RateLimiter::hit($this->ipRateLimitKey($ip), 3600);
        }

        if (! $this->sms->sendOtp($mobile, $otp)) {
            throw new ApiException('Failed to send OTP. Please try again.', null, 500);
        }
    }

    /**
     * Verify $otp for $mobile. Returns the matched, still-valid OTP record
     * (caller is responsible for deleting it once login has been issued).
     *
     * @throws ApiException on missing/expired/exhausted/mismatched OTP
     */
    public function verify(string $mobile, string $otp): OtpVerification
    {
        $record = OtpVerification::where('mobile', $mobile)->latest()->first();

        if (! $record) {
            throw new ApiException('No OTP found. Please request a new OTP.', null, 422);
        }

        if ($record->isExpired()) {
            $record->delete();

            throw new ApiException('OTP has expired. Please request a new one.', null, 422);
        }

        if ($record->attempts >= self::MAX_VERIFY_ATTEMPTS) {
            $record->delete();

            throw new ApiException('Too many failed attempts. Please request a new OTP.', null, 429);
        }

        if (! $this->otpMatches($mobile, $otp, $record->otp_hash)) {
            $record->increment('attempts');

            Log::warning('OTP verification failed', ['mobile' => $this->maskMobile($mobile)]);

            throw new ApiException('Invalid OTP. Please try again.', null, 422);
        }

        return $record;
    }

    private function guardResendCooldown(string $mobile): void
    {
        $lastSentAt = OtpVerification::where('mobile', $mobile)->value('last_sent_at');

        // Carbon 3's diffInSeconds() is signed by default (a breaking change
        // from Carbon 2) — force absolute since we only care about elapsed
        // time here, not which side is later.
        $elapsed = $lastSentAt ? now()->diffInSeconds($lastSentAt, true) : null;

        if ($elapsed !== null && $elapsed < self::RESEND_COOLDOWN_SECONDS) {
            $wait = self::RESEND_COOLDOWN_SECONDS - $elapsed;

            throw new ApiException("Please wait {$wait} seconds before requesting another OTP.", null, 429);
        }
    }

    private function guardHourlyCap(string $mobile, ?string $ip): void
    {
        if (RateLimiter::tooManyAttempts($this->mobileRateLimitKey($mobile), self::MAX_SENDS_PER_HOUR)) {
            throw new ApiException('Too many OTP requests for this number. Please try again in an hour.', null, 429);
        }

        if ($ip && RateLimiter::tooManyAttempts($this->ipRateLimitKey($ip), self::MAX_SENDS_PER_HOUR)) {
            throw new ApiException('Too many OTP requests from this device. Please try again in an hour.', null, 429);
        }
    }

    private function mobileRateLimitKey(string $mobile): string
    {
        return "otp-send-mobile:{$mobile}";
    }

    private function ipRateLimitKey(string $ip): string
    {
        return "otp-send-ip:{$ip}";
    }

    /**
     * QA/app-store review bypass: a fixed test mobile number always accepts a
     * fixed test OTP, both configured via the admin Settings table so nothing
     * is hardcoded into source control.
     */
    private function otpMatches(string $mobile, string $submitted, string $actualHash): bool
    {
        $testMobile = Setting::get('sms_test_mobile');
        $testOtp = Setting::get('sms_test_otp');

        if ($testMobile && $testOtp && $mobile === $testMobile && $submitted === $testOtp) {
            return true;
        }

        return Hash::check($submitted, $actualHash);
    }

    private function maskMobile(string $mobile): string
    {
        return substr($mobile, 0, 4).'******';
    }
}
