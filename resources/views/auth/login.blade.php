@extends('layouts.app')

@section('title', 'Sign In')

@section('content')
<div class="min-h-[calc(100vh-200px)] flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">
        <div class="card p-8">
            <h1 class="text-2xl font-black text-ink-900">Welcome back</h1>
            <p class="text-sm text-ink-500 mt-1">Sign in to manage your page.</p>

            {{-- Google OAuth --}}
            <a href="{{ route('auth.google') }}" class="mt-6 flex items-center justify-center gap-3 w-full px-5 py-3 rounded-lg border border-ink-200 bg-white hover:bg-ink-50 transition font-bold text-ink-900">
                <svg class="w-5 h-5" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                Continue with Google
            </a>

            <div class="flex items-center gap-3 my-6">
                <div class="flex-1 h-px bg-ink-200"></div>
                <span class="text-xs text-ink-500 font-bold uppercase">Or</span>
                <div class="flex-1 h-px bg-ink-200"></div>
            </div>

            {{-- Email/Password --}}
            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="label" for="login">Username or Email</label>
                    <input id="login" name="login" type="text" required autofocus
                           value="{{ old('login') }}"
                           class="input @error('login') input-error @enderror"
                           placeholder="yourname or you@email.com">
                    @error('login')<div class="error">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="label" for="password">Password</label>
                    <input id="password" name="password" type="password" required
                           class="input @error('password') input-error @enderror"
                           placeholder="Enter your password">
                    @error('password')<div class="error">{{ $message }}</div>@enderror
                </div>
                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-ink-200 text-brand-500 focus:ring-brand-500">
                        <span class="text-ink-700">Remember me</span>
                    </label>
                </div>
                <button type="submit" class="btn-primary btn-block">Sign In</button>
            </form>

            <p class="text-center text-sm text-ink-500 mt-6">
                Don't have an account?
                <a href="{{ route('register') }}" class="text-brand-500 font-bold hover:underline">Register</a>
            </p>
        </div>
    </div>
</div>
@endsection