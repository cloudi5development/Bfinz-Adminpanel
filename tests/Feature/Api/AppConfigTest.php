<?php

namespace Tests\Feature\Api;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppConfigTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_sensible_defaults_when_unconfigured(): void
    {
        $response = $this->getJson('/api/v1/app/config');

        $response->assertOk()->assertJson([
            'status' => true,
            'data' => [
                'min_version' => '1.0.0',
                'force_update' => false,
                'quick_actions' => [],
                'banners' => [],
            ],
        ]);
    }

    public function test_reflects_admin_configured_values(): void
    {
        Setting::put('app.min_version', '2.3.0');
        Setting::put('app.force_update', true);
        Setting::put('app.quick_actions', json_encode([['label' => 'Gold Rate', 'route' => 'gold']]));

        $response = $this->getJson('/api/v1/app/config');

        $response->assertOk()
            ->assertJsonPath('data.min_version', '2.3.0')
            ->assertJsonPath('data.force_update', true)
            ->assertJsonPath('data.quick_actions.0.label', 'Gold Rate');
    }
}
