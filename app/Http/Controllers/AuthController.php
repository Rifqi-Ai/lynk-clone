<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /**
     * Show login form.
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Handle login with username OR email + password.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Rate limit handled by 'throttle:login' middleware in routes/web.php.
        // Defense: throttle is per (IP + login field) + per-IP-only secondary limit.

        $login = $credentials['login'];
        $password = $credentials['password'];

        // Allow login by username OR email (lynk.id style)
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $user = User::where($field, $login)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return back()
                ->withErrors(['login' => 'Invalid credentials.'])
                ->withInput($request->only('login'));
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard.index'));
    }

    /**
     * Show register form.
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Handle registration.
     */
    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'username' => [
                'required', 'string', 'min:3', 'max:30',
                'regex:/^[a-zA-Z0-9._-]+$/',
                Rule::unique('users', 'username'),
            ],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'plan_tier' => 'starter',
            'transaction_fee_pct' => 10.00,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard.index')
            ->with('success', 'Welcome! Your creator page is ready.');
    }

    /**
     * Logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    /**
     * Redirect to Google OAuth.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback.
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->withErrors(['login' => 'Google authentication failed: '.$e->getMessage()]);
        }

        // Find or create user
        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if (! $user) {
            $user = User::create([
                'name' => $googleUser->getName() ?? 'User',
                'email' => $googleUser->getEmail(),
                'username' => $this->generateUniqueUsername($googleUser),
                'google_id' => $googleUser->getId(),
                'avatar_path' => null, // we use Google avatar URL instead
                'email_verified_at' => now(),
                'plan_tier' => 'starter',
                'transaction_fee_pct' => 10.00,
                'appearance' => ['avatar_url' => $googleUser->getAvatar()],
            ]);
        } else {
            // Link Google ID if not set
            if (! $user->google_id) {
                $user->update(['google_id' => $googleUser->getId()]);
            }
            // Store Google avatar in appearance if user has no avatar
            if (! $user->avatar_path && $googleUser->getAvatar()) {
                $user->update([
                    'appearance' => array_merge($user->appearance ?? [], ['avatar_url' => $googleUser->getAvatar()]),
                ]);
            }
        }

        Auth::login($user, true);

        return redirect()->intended(route('dashboard.index'));
    }

    /**
     * Generate a unique username from Google user data.
     */
    protected function generateUniqueUsername($googleUser): string
    {
        $base = $googleUser->getNickname() ?: Str::slug(explode(' ', $googleUser->getName())[0] ?? 'user');
        $base = preg_replace('/[^a-zA-Z0-9._-]/', '', $base);
        $base = substr($base, 0, 25) ?: 'user';

        $username = $base;
        $i = 1;
        while (User::where('username', $username)->exists()) {
            $username = $base.$i;
            $i++;
        }

        return $username;
    }
}
