<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Http\Requests\Api\SendOtpRequest;
use App\Http\Requests\Api\VerifyOtpRequest;
use App\Http\Resources\Api\Auth\LoginHistoryResource;
use App\Http\Resources\Api\Auth\UserResource;
use App\Models\LoginHistory;
use App\Models\User;
use App\Models\UserFcmToken;
use App\Services\OtpService;
use App\Services\UserAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Mobile OTP login for the consumer app: send-otp / verify-otp are public,
 * the rest requires a Sanctum bearer token (routes/api.php).
 */
class AuthController extends Controller
{
    public function __construct(
        private readonly OtpService $otp,
        private readonly UserAuthService $auth,
    ) {}

    public function sendOtp(SendOtpRequest $request): JsonResponse
    {
        $this->otp->send($request->mobile);

        return $this->success(null, 'OTP sent successfully.');
    }

    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $record = $this->otp->verify($request->mobile, $request->otp);

        // Mass-assignment is limited to the mobile-login fields on $fillable;
        // is_super_admin / modules / is_active are never set here, so a
        // mobile app account can never gain admin-panel access.
        $user = User::firstOrCreate(
            ['mobile' => $request->mobile],
            ['name' => $request->name ?: 'Guest']
        );

        $record->delete();

        $token = $this->auth->loginUser($user, $request->validated(), $request);

        return $this->success(['token' => $token], 'Login successful.');
    }

    public function profile(Request $request): JsonResponse
    {
        return $this->success(new UserResource($request->user()));
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
        ]);

        $request->user()->update($data);

        return $this->success(new UserResource($request->user()->fresh()), 'Profile updated.');
    }

    public function loginHistory(Request $request): JsonResponse
    {
        $currentTokenId = (int) $request->user()->currentAccessToken()->id;

        $history = LoginHistory::where('user_id', $request->user()->id)
            ->latest('logged_in_at')
            ->limit(50)
            ->get()
            ->map(fn (LoginHistory $entry) => new LoginHistoryResource($entry, $currentTokenId));

        return $this->success($history);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        $tokenId = (int) $user->currentAccessToken()->id;

        if ($request->filled('device_name')) {
            UserFcmToken::where('user_id', $user->id)->where('device_name', $request->device_name)->delete();
        } elseif ($request->filled('fcm_token')) {
            UserFcmToken::where('user_id', $user->id)->where('fcm_token', $request->fcm_token)->delete();
        }

        LoginHistory::where('token_id', $tokenId)->whereNull('logged_out_at')->update(['logged_out_at' => now()]);

        $user->currentAccessToken()->delete();

        return $this->success(null, 'Logged out successfully.');
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $user = $request->user();
        $tokenIds = $user->tokens()->pluck('id')->all();

        if ($tokenIds !== []) {
            LoginHistory::whereIn('token_id', $tokenIds)->whereNull('logged_out_at')->update(['logged_out_at' => now()]);
        }

        UserFcmToken::where('user_id', $user->id)->delete();
        $user->tokens()->delete();

        return $this->success(null, 'All sessions logged out successfully.');
    }
}
