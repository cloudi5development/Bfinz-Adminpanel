<?php

namespace App\Services;

use App\Models\LoginHistory;
use App\Models\User;
use App\Models\UserFcmToken;
use Illuminate\Http\Request;

/**
 * Issues the Sanctum bearer token for a mobile app user once their OTP has
 * been verified, and records the device/session in login_histories.
 */
class UserAuthService
{
    public function loginUser(User $user, array $data, ?Request $request = null): string
    {
        $deviceName = $data['device_name'] ?? 'mobile-token';
        $token = $user->createToken($deviceName);

        $user->update([
            'last_login_at' => now(),
            'last_active_at' => now(),
            'mobile_verified_at' => now(),
            'is_mobile_verified' => true,
        ]);

        LoginHistory::create([
            'user_id' => $user->id,
            'token_id' => $token->accessToken->id,
            'device_name' => $data['device_name'] ?? null,
            'platform' => $data['platform'] ?? null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'logged_in_at' => now(),
        ]);

        if (! empty($data['fcm_token'])) {
            $this->saveFcmToken($user, $data['fcm_token'], $data['device_name'] ?? null, $data['platform'] ?? null);
        }

        return $token->plainTextToken;
    }

    public function saveFcmToken(User $user, string $fcmToken, ?string $deviceName, ?string $platform): void
    {
        UserFcmToken::updateOrCreate(
            ['user_id' => $user->id, 'fcm_token' => $fcmToken],
            ['device_name' => $deviceName, 'platform' => $platform]
        );
    }
}
