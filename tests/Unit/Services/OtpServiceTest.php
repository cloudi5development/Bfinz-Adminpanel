<?php

namespace Tests\Unit\Services;

use App\Exceptions\ApiException;
use App\Models\OtpVerification;
use App\Models\Setting;
use App\Services\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OtpServiceTest extends TestCase
{
    use RefreshDatabase;

    private const MOBILE = '9876543210';

    protected function setUp(): void
    {
        parent::setUp();

        // Bypasses the real SMS gateway (SendSmsService) for this mobile,
        // so these tests exercise OtpService's own logic, not the gateway.
        Setting::put('sms_test_mobile', self::MOBILE);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_otp_is_hashed_at_rest_not_stored_plaintext(): void
    {
        app(OtpService::class)->send(self::MOBILE);

        $record = OtpVerification::sole();

        $this->assertStringStartsWith('$2y$', $record->otp_hash);
    }

    public function test_otp_expires_five_minutes_from_now(): void
    {
        Carbon::setTestNow(now());

        app(OtpService::class)->send(self::MOBILE);

        $record = OtpVerification::sole();

        // A few seconds' delta, not because of clock drift (testNow is
        // frozen) but because SQLite's DATETIME column truncates the
        // microseconds that a frozen-but-precise now() still carries.
        $this->assertEqualsWithDelta(5, now()->diffInMinutes($record->expires_at, true), 0.05);
    }

    public function test_resend_before_cooldown_is_rejected(): void
    {
        $service = app(OtpService::class);
        $service->send(self::MOBILE);

        try {
            $service->send(self::MOBILE);
            $this->fail('Expected an ApiException for resending within the cooldown window.');
        } catch (ApiException $e) {
            $this->assertSame(429, $e->getStatusCode());
        }

        $this->assertSame(1, OtpVerification::count());
    }

    public function test_resend_after_cooldown_succeeds(): void
    {
        Carbon::setTestNow(now());
        $service = app(OtpService::class);

        $service->send(self::MOBILE);
        Carbon::setTestNow(now()->addSeconds(31));
        $service->send(self::MOBILE);

        $this->assertSame(1, OtpVerification::count());
    }

    public function test_hourly_send_cap_is_enforced_per_mobile(): void
    {
        Carbon::setTestNow(now());
        $service = app(OtpService::class);

        for ($i = 0; $i < 5; $i++) {
            $service->send(self::MOBILE);
            Carbon::setTestNow(now()->addSeconds(31));
        }

        try {
            $service->send(self::MOBILE);
            $this->fail('Expected an ApiException once the hourly cap is reached.');
        } catch (ApiException $e) {
            $this->assertSame(429, $e->getStatusCode());
        }
    }

    public function test_hourly_send_cap_is_enforced_per_ip_across_different_mobiles(): void
    {
        Carbon::setTestNow(now());
        $service = app(OtpService::class);
        $ip = '203.0.113.10';

        // Different mobiles won't trigger the SMS bypass, so configure the
        // shared gateway settings the real SendSmsService checks and let it
        // hit Illuminate's HTTP fake instead.
        Http::fake(['*' => Http::response(['ErrorCode' => '000'])]);
        Setting::put('sms_nettyfish_api_key', 'test-key');
        Setting::put('sms_nettyfish_sender_id', 'TESTID');

        for ($i = 0; $i < 5; $i++) {
            $service->send('90000000'.str_pad((string) $i, 2, '0', STR_PAD_LEFT), $ip);
            Carbon::setTestNow(now()->addSeconds(31));
        }

        try {
            $service->send('9000000099', $ip);
            $this->fail('Expected an ApiException once the per-IP hourly cap is reached.');
        } catch (ApiException $e) {
            $this->assertSame(429, $e->getStatusCode());
        }
    }
}
