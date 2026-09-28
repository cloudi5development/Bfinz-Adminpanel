<?php

namespace Tests\Unit\Services\Alerts;

use App\Contracts\Providers\PushSender;
use App\Models\Alert;
use App\Models\ForexRate;
use App\Models\MetalRate;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserFcmToken;
use App\Services\Alerts\AlertEvaluationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AlertEvaluationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function serviceWithNoopPush(): AlertEvaluationService
    {
        $push = new class implements PushSender
        {
            public function send(array $fcmTokens, string $title, string $body, array $data = []): void {}
        };

        return new AlertEvaluationService($push);
    }

    public function test_above_alert_fires_once_when_crossing_up_and_rearms_after_crossing_back(): void
    {
        $user = User::factory()->create();
        MetalRate::factory()->create(['metal' => 'gold', 'purity' => '24k', 'city_id' => null, 'rate_per_gram_paise' => 700000]);
        $alert = Alert::factory()->create([
            'user_id' => $user->id, 'type' => 'gold', 'asset' => '24k', 'condition' => 'above', 'target_value' => 6900, 'armed' => true,
        ]);

        $service = $this->serviceWithNoopPush();
        $service->evaluate('gold');

        $alert->refresh();
        $this->assertFalse($alert->armed);
        $this->assertNotNull($alert->last_triggered_at);
        $this->assertSame(1, Notification::where('user_id', $user->id)->count());

        // Still above target, still disarmed: must NOT fire again.
        $service->evaluate('gold');
        $this->assertSame(1, Notification::where('user_id', $user->id)->count());

        // Crosses back below target: re-arms, no new notification yet.
        MetalRate::where('metal', 'gold')->where('purity', '24k')->update(['rate_per_gram_paise' => 650000]);
        $service->evaluate('gold');
        $this->assertTrue($alert->refresh()->armed);
        $this->assertSame(1, Notification::where('user_id', $user->id)->count());

        // Crosses up again: fires a second time.
        MetalRate::where('metal', 'gold')->where('purity', '24k')->update(['rate_per_gram_paise' => 710000]);
        $service->evaluate('gold');
        $this->assertSame(2, Notification::where('user_id', $user->id)->count());
    }

    public function test_below_alert_fires_when_value_drops_to_or_under_target(): void
    {
        $user = User::factory()->create();
        ForexRate::factory()->create(['base' => 'USD', 'rate' => 83.5]);
        Alert::factory()->create([
            'user_id' => $user->id, 'type' => 'forex', 'asset' => 'USD', 'condition' => 'below', 'target_value' => 84, 'armed' => true,
        ]);

        $this->serviceWithNoopPush()->evaluate('forex');

        $this->assertSame(1, Notification::where('user_id', $user->id)->count());
    }

    public function test_any_change_fires_at_most_once_per_day(): void
    {
        Carbon::setTestNow(now());
        $user = User::factory()->create();
        MetalRate::factory()->create(['metal' => 'silver', 'purity' => '999', 'city_id' => null]);
        Alert::factory()->create([
            'user_id' => $user->id, 'type' => 'silver', 'asset' => '999', 'condition' => 'any_change', 'target_value' => null,
        ]);

        $service = $this->serviceWithNoopPush();
        $service->evaluate('silver');
        $service->evaluate('silver');

        $this->assertSame(1, Notification::where('user_id', $user->id)->count());

        Carbon::setTestNow(now()->addDay());
        $service->evaluate('silver');

        $this->assertSame(2, Notification::where('user_id', $user->id)->count());
    }

    public function test_inactive_alerts_are_never_evaluated(): void
    {
        $user = User::factory()->create();
        MetalRate::factory()->create(['metal' => 'gold', 'purity' => '24k', 'rate_per_gram_paise' => 700000]);
        Alert::factory()->create([
            'user_id' => $user->id, 'type' => 'gold', 'asset' => '24k', 'condition' => 'above', 'target_value' => 100, 'is_active' => false,
        ]);

        $this->serviceWithNoopPush()->evaluate('gold');

        $this->assertSame(0, Notification::count());
    }

    public function test_fd_and_rd_alerts_are_skipped_without_error(): void
    {
        $user = User::factory()->create();
        Alert::factory()->create(['user_id' => $user->id, 'type' => 'fd', 'asset' => 'bank:1:12m', 'condition' => 'above', 'target_value' => 7]);

        $this->serviceWithNoopPush()->evaluate('fd');

        $this->assertSame(0, Notification::count());
    }

    public function test_alert_with_no_market_data_yet_is_skipped(): void
    {
        $user = User::factory()->create();
        Alert::factory()->create(['user_id' => $user->id, 'type' => 'gold', 'asset' => '24k', 'condition' => 'above', 'target_value' => 100]);

        $this->serviceWithNoopPush()->evaluate('gold');

        $this->assertSame(0, Notification::count());
    }

    public function test_push_is_sent_to_the_users_registered_devices(): void
    {
        $user = User::factory()->create();
        UserFcmToken::create(['user_id' => $user->id, 'fcm_token' => 'token-abc']);
        MetalRate::factory()->create(['metal' => 'gold', 'purity' => '24k', 'rate_per_gram_paise' => 700000]);
        Alert::factory()->create(['user_id' => $user->id, 'type' => 'gold', 'asset' => '24k', 'condition' => 'above', 'target_value' => 100]);

        $push = new class implements PushSender
        {
            public array $sent = [];

            public function send(array $fcmTokens, string $title, string $body, array $data = []): void
            {
                $this->sent[] = $fcmTokens;
            }
        };

        (new AlertEvaluationService($push))->evaluate('gold');

        $this->assertCount(1, $push->sent);
        $this->assertSame(['token-abc'], $push->sent[0]);
    }
}
