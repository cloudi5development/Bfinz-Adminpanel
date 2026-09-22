<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AdminAuth;
use App\Support\AdminRemember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Sign-in / sign-out for the admin panel.
 *
 * The panel deliberately does not use Laravel's auth guard: AdminAuth,
 * AdminRemember and the AdminAuthenticate middleware all key off a session flag
 * instead, so this controller is the single place that decides whether an
 * account may open the panel. Credentials live in the users table and the
 * password column is hashed by the model's "hashed" cast — nothing is hard-coded
 * here or in the views.
 */
class AuthController extends Controller
{
    /** Failed attempts allowed per email + IP before the form locks. */
    private const MAX_ATTEMPTS = 5;

    /** Seconds the lock lasts once MAX_ATTEMPTS is reached. */
    private const DECAY_SECONDS = 60;

    /**
     * Display the admin login page.
     */
    public function login(Request $request): View|RedirectResponse
    {
        // Already signed in — no reason to show the form again.
        if ($request->session()->get('admin_logged_in')) {
            return redirect()->route('backend.dashboard');
        }

        return view('backend.auth.login');
    }

    /**
     * Verify the posted credentials and open a session.
     */
    public function authenticate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email'    => ['required', 'string', 'email', 'max:180'],
            'password' => ['required', 'string', 'max:72'],
            'remember' => ['nullable', 'boolean'],
        ], [
            'email.required'    => 'Enter your email address.',
            'email.email'       => 'Enter a valid email address.',
            'password.required' => 'Enter your password.',
        ]);

        $key = $this->throttleKey($request, $data['email']);

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            throw ValidationException::withMessages([
                'email' => 'Too many login attempts. Try again in '
                    . RateLimiter::availableIn($key) . ' seconds.',
            ]);
        }

        $user = User::where('email', $data['email'])->first();

        // One message for "no such account", "wrong password" and "deactivated",
        // so the form never reveals which addresses have an account.
        if (! $user || ! $user->is_active || ! Hash::check($data['password'], $user->password)) {
            RateLimiter::hit($key, self::DECAY_SECONDS);

            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        RateLimiter::clear($key);

        self::openSession($request, $user);

        // "Remember me" issues the panel's own long-lived cookie; signing in
        // without it drops any cookie left over from a previous session.
        if ($request->boolean('remember')) {
            AdminRemember::issue($user);
        } else {
            AdminRemember::forget();
        }

        return redirect()->intended(route('backend.dashboard'));
    }

    /**
     * Mark this session as signed in as $user.
     *
     * Static because AdminAuthenticate calls it too, when a valid "remember me"
     * cookie reopens a session that had expired.
     */
    public static function openSession(Request $request, User $user): void
    {
        // New session id on privilege change — closes session-fixation attacks.
        $request->session()->regenerate();

        $request->session()->put([
            'admin_logged_in' => true,
            'admin_id'        => $user->id,
            'admin_name'      => $user->name,
            'admin_email'     => $user->email,
        ]);

        $user->forceFill(['last_login_at' => now()])->save();

        AdminAuth::forget();
    }

    /**
     * End the session and drop the "remember me" cookie.
     */
    public function logout(Request $request): RedirectResponse
    {
        AdminRemember::forget(AdminAuth::user());

        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        AdminAuth::forget();

        return redirect()->route('backend.auth.login')
            ->with('success', 'You have been signed out.');
    }

    /** Rate-limiter bucket: this address from this IP. */
    private function throttleKey(Request $request, string $email): string
    {
        return 'admin-login|' . Str::lower($email) . '|' . $request->ip();
    }
}
