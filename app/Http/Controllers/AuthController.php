<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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
     *
     * Reads top-down: honeypot → validate → lookup → check password →
     * log → auth + regenerate → redirect.
     */
    public function login(Request $request): RedirectResponse
    {
        $honeypotResponse = $this->enforceHoneypot($request);
        if ($honeypotResponse) {
            return $honeypotResponse;
        }

        $credentials = $this->validateLoginRequest($request);
        $user = $this->findUserByLogin($credentials['login']);

        if (! $this->credentialsMatch($user, $credentials['password'])) {
            $this->logFailedLogin($request, $credentials['login']);

            return back()
                ->withErrors(['login' => 'Invalid credentials.'])
                ->withInput($request->only('login'));
        }

        $this->logSuccessfulLogin($request, $user);
        $this->authenticateAndStartSession($request, $user, $request->boolean('remember'));

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
        // Honeypot: reject bots that fill the hidden "website" field.
        // Generic error message — don't reveal that the honeypot is the trigger.
        if ($request->filled('website')) {
            return back()
                ->withErrors(['email' => 'Terjadi kesalahan. Silakan coba lagi.'])
                ->withInput();
        }

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

    // ───── login() helpers ─────

    /**
     * Bot detection: reject requests where the hidden "website" field is filled.
     * Returns a redirect with a generic error if bot detected, null if human.
     */
    private function enforceHoneypot(Request $request): ?RedirectResponse
    {
        if (! $request->filled('website')) {
            return null;
        }

        return back()
            ->withErrors(['login' => 'Invalid credentials.'])
            ->withInput($request->only('login'));
    }

    /**
     * Validate the login form. Returns the validated credentials.
     */
    private function validateLoginRequest(Request $request): array
    {
        return $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);
    }

    /**
     * Find a user by email (case-insensitive, normalized) or username.
     *
     * Emails are normalized to lowercase before lookup so that
     * "LOGIN@EXAMPLE.COM" and "login@example.com" both find the same user,
     * regardless of DB collation (SQLite is case-sensitive by default,
     * MySQL utf8mb4_unicode_ci is case-insensitive).
     */
    private function findUserByLogin(string $login): ?User
    {
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            return User::where('email', strtolower($login))->first();
        }

        return User::where('username', $login)->first();
    }

    /**
     * Constant-time credential check that doesn't leak whether the user
     * exists via timing differences.
     *
     * SECURITY: BUG FIX — old code did `if (! $user || ! Hash::check(...))`
     * which short-circuited on missing user (no hash work done), letting
     * attackers enumerate valid emails by measuring response time.
     * Now we always run Hash::check against a dummy hash when the user
     * is missing, so timing is identical for "user not found" and
     * "wrong password".
     */
    private function credentialsMatch(?User $user, string $password): bool
    {
        // Pre-computed bcrypt hash of a random string — used as a dummy
        // when the user is missing so Hash::check always runs.
        static $dummyHash = null;
        if ($dummyHash === null) {
            $dummyHash = Hash::make(Str::random(32));
        }

        if (! $user) {
            // Run Hash::check against dummy so timing matches known-user path.
            Hash::check($password, $dummyHash);

            return false;
        }

        return Hash::check($password, $user->password);
    }

    /**
     * Log a failed login attempt with context for security monitoring.
     */
    private function logFailedLogin(Request $request, string $loginAttempted): void
    {
        Log::warning('auth.login.failed', [
            'event' => 'login.failed',
            'login_attempted' => $loginAttempted,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'request_id' => app()->bound('request_id') ? app('request_id') : null,
        ]);
    }

    /**
     * Log a successful login for security audit trail.
     */
    private function logSuccessfulLogin(Request $request, User $user): void
    {
        Log::info('auth.login.succeeded', [
            'event' => 'login.succeeded',
            'user_id' => $user->id,
            'ip' => $request->ip(),
            'request_id' => app()->bound('request_id') ? app('request_id') : null,
        ]);
    }

    /**
     * Authenticate the user and regenerate the session ID to prevent
     * session fixation attacks.
     */
    private function authenticateAndStartSession(Request $request, User $user, bool $remember): void
    {
        Auth::login($user, $remember);
        $request->session()->regenerate();
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
