<?php

namespace Tests\Feature\Api;

use App\Models\OtpVerification;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserFcmToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AuthOtpTest extends TestCase
{
    use RefreshDatabase;

    private const MOBILE = '9876543210';

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function bypassSms(string $mobile = self::MOBILE): void
    {
        Setting::put('sms_test_mobile', $mobile);
    }

    public function test_send_otp_creates_a_hashed_record(): void
    {
        $this->bypassSms();

        $response = $this->postJson('/api/v1/auth/send-otp', ['mobile' => self::MOBILE]);

        $response->assertOk()->assertJson(['status' => true]);

        $record = OtpVerification::sole();
        $this->assertSame(self::MOBILE, $record->mobile);
        $this->assertStringStartsWith('$2y$', $record->otp_hash);
    }

    #[DataProvider('invalidMobileProvider')]
    public function test_send_otp_rejects_invalid_mobile_numbers(string $mobile): void
    {
        $response = $this->postJson('/api/v1/auth/send-otp', ['mobile' => $mobile]);

        $response->assertStatus(422);
    }

    public static function invalidMobileProvider(): array
    {
        return [
            'starts with 0' => ['0123456789'],
            'starts with 4' => ['4123456789'],
            'too short' => ['98765'],
            'contains letters' => ['98765abcde'],
        ];
    }

    public function test_resend_within_cooldown_returns_429(): void
    {
        $this->bypassSms();

        $this->postJson('/api/v1/auth/send-otp', ['mobile' => self::MOBILE])->assertOk();
        $this->postJson('/api/v1/auth/send-otp', ['mobile' => self::MOBILE])->assertStatus(429);
    }

    public function test_verify_otp_success_issues_a_token_and_consumes_the_otp(): void
    {
        Setting::put('sms_test_mobile', self::MOBILE);
        Setting::put('sms_test_otp', '123456');

        $this->postJson('/api/v1/auth/send-otp', ['mobile' => self::MOBILE])->assertOk();

        $response = $this->postJson('/api/v1/auth/verify-otp', [
            'mobile' => self::MOBILE,
            'otp' => '123456',
        ]);

        $response->assertOk()->assertJsonPath('status', true);
        $this->assertNotEmpty($response->json('data.token'));
        $this->assertSame(0, OtpVerification::count());

        $user = User::where('mobile', self::MOBILE)->sole();
        $this->assertTrue($user->is_mobile_verified);
    }

    public function test_verify_otp_with_wrong_code_increments_attempts_and_fails(): void
    {
        $this->bypassSms();
        $this->postJson('/api/v1/auth/send-otp', ['mobile' => self::MOBILE])->assertOk();

        $record = OtpVerification::sole();
        $record->update(['otp_hash' => Hash::make('222222')]);

        $response = $this->postJson('/api/v1/auth/verify-otp', [
            'mobile' => self::MOBILE,
            'otp' => '111111',
        ]);

        $response->assertStatus(422);
        $this->assertSame(1, $record->fresh()->attempts);
    }

    public function test_verify_otp_locks_out_after_max_attempts(): void
    {
        $this->bypassSms();
        $this->postJson('/api/v1/auth/send-otp', ['mobile' => self::MOBILE])->assertOk();

        $record = OtpVerification::sole();
        $record->update(['otp_hash' => Hash::make('222222')]);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/verify-otp', [
                'mobile' => self::MOBILE,
                'otp' => '111111',
            ])->assertStatus(422);
        }

        $this->postJson('/api/v1/auth/verify-otp', [
            'mobile' => self::MOBILE,
            'otp' => '111111',
        ])->assertStatus(429);

        $this->assertSame(0, OtpVerification::count());
    }

    public function test_expired_otp_is_rejected(): void
    {
        Carbon::setTestNow(now());
        $this->bypassSms();
        $this->postJson('/api/v1/auth/send-otp', ['mobile' => self::MOBILE])->assertOk();

        Carbon::setTestNow(now()->addMinutes(6));

        $this->postJson('/api/v1/auth/verify-otp', [
            'mobile' => self::MOBILE,
            'otp' => '111111',
        ])->assertStatus(422);

        $this->assertSame(0, OtpVerification::count());
    }

    private function loginAndGetToken(): array
    {
        Setting::put('sms_test_mobile', self::MOBILE);
        Setting::put('sms_test_otp', '123456');

        $this->postJson('/api/v1/auth/send-otp', ['mobile' => self::MOBILE])->assertOk();

        $response = $this->postJson('/api/v1/auth/verify-otp', [
            'mobile' => self::MOBILE,
            'otp' => '123456',
        ]);

        $token = $response->json('data.token');
        $user = User::where('mobile', self::MOBILE)->sole();

        return [$token, $user];
    }

    public function test_delete_profile_soft_deletes_anonymises_and_revokes_the_token(): void
    {
        [$token, $user] = $this->loginAndGetToken();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson('/api/v1/auth/profile')
            ->assertOk();

        $fresh = User::withTrashed()->find($user->id);
        $this->assertTrue($fresh->trashed());
        $this->assertNull($fresh->mobile);
        $this->assertSame('Deleted User', $fresh->name);

        // Asserted at the token-table level rather than with a second HTTP
        // call: Sanctum's guard caches the resolved user for the lifetime of
        // the test's $app instance, so a second call in the same test would
        // pass against the cached user even though the token row is gone —
        // a test-only artifact, not real request-to-request behaviour.
        $this->assertSame(0, PersonalAccessToken::where('tokenable_id', $user->id)->count());
    }

    public function test_register_device_upserts_the_fcm_token(): void
    {
        [$token, $user] = $this->loginAndGetToken();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/devices', [
                'fcm_token' => 'fcm-token-abc',
                'platform' => 'android',
                'app_version' => '1.2.3',
            ])
            ->assertOk();

        $device = UserFcmToken::where('user_id', $user->id)->sole();
        $this->assertSame('android', $device->platform);
        $this->assertSame('1.2.3', $device->app_version);
        $this->assertNotNull($device->last_seen_at);
    }
}
