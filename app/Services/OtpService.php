<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\OtpVerification;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

/**
 * Generates, sends, and verifies the 4-digit OTP used for mobile login.
 * Two independent throttle layers protect this: the route-level
 * throttle:X,1 middleware (routes/api.php) and the rolling-window /
 * attempt-count checks below, keyed by mobile number rather than IP.
 */
class OtpService
{
    private const MAX_SENDS_PER_WINDOW = 3;

    private const WINDOW_MINUTES = 10;

    private const OTP_EXPIRY_MINUTES = 10;

    private const MAX_VERIFY_ATTEMPTS = 5;

    public function __construct(private readonly SendSmsService $sms) {}

    /**
     * Generate a fresh OTP for $mobile and send it via SMS.
     *
     * @throws ApiException on rate limit or SMS gateway failure
     */
    public function send(string $mobile): void
    {
        if ($this->isRateLimited($mobile)) {
            throw new ApiException(
                'Too many OTP requests. Please wait '.self::WINDOW_MINUTES.' minutes and try again.',
                null,
                429
            );
        }

        $otp = str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT);

        OtpVerification::where('mobile', $mobile)->delete();

        OtpVerification::create([
            'mobile' => $mobile,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(self::OTP_EXPIRY_MINUTES),
            'attempts' => 0,
            'last_sent_at' => now(),
        ]);

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

        if (! $this->otpMatches($mobile, $otp, $record->otp)) {
            $record->increment('attempts');

            Log::warning('OTP verification failed', ['mobile' => $this->maskMobile($mobile)]);

            throw new ApiException('Invalid OTP. Please try again.', null, 422);
        }

        return $record;
    }

    private function isRateLimited(string $mobile): bool
    {
        return OtpVerification::where('mobile', $mobile)
            ->where('last_sent_at', '>=', now()->subMinutes(self::WINDOW_MINUTES))
            ->count() >= self::MAX_SENDS_PER_WINDOW;
    }

    /**
     * QA/app-store review bypass: a fixed test mobile number always accepts a
     * fixed test OTP, both configured via the admin Settings table so nothing
     * is hardcoded into source control.
     */
    private function otpMatches(string $mobile, string $submitted, string $actual): bool
    {
        $testMobile = Setting::get('sms_test_mobile');
        $testOtp = Setting::get('sms_test_otp');

        if ($testMobile && $testOtp && $mobile === $testMobile && $submitted === $testOtp) {
            return true;
        }

        return hash_equals($actual, $submitted);
    }

    private function maskMobile(string $mobile): string
    {
        return substr($mobile, 0, 4).'******';
    }
}
