@extends('layouts.dashboard')

@section('title', 'Profile Settings')
@section('header', 'Profile')

@section('content')
<form method="POST" action="{{ route('settings.profile.update') }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @method('PATCH')

    {{-- Avatar --}}
    <div class="card p-6">
        <h2 class="font-black text-lg mb-4">Avatar</h2>
        <div class="flex items-center gap-4">
            <img src="{{ $user->avatar_url }}" class="w-20 h-20 rounded-full">
            <div class="flex-1">
                <input type="file" name="avatar" accept="image/*"
                       class="input file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-brand-50 file:text-brand-700 file:font-bold">
                <div class="help">JPG/PNG, max 2MB</div>
            </div>
        </div>
    </div>

    {{-- Basic info --}}
    <div class="card p-6 space-y-4">
        <h2 class="font-black text-lg">Basic Info</h2>
        <div>
            <label class="label" for="name">Name</label>
            <input id="name" name="name" type="text" required value="{{ old('name', $user->name) }}" class="input">
        </div>
        <div>
            <label class="label" for="username">Username</label>
            <div class="flex items-center gap-2">
                <span class="text-sm text-ink-500 font-bold whitespace-nowrap">{{ url('/') }}/</span>
                <input id="username" type="text" value="{{ $user->username }}" readonly class="input bg-ink-50 cursor-not-allowed">
            </div>
            <div class="help">Username cannot be changed (MVP).</div>
        </div>
        <div>
            <label class="label" for="title">Title</label>
            <input id="title" name="title" type="text" maxlength="100" value="{{ old('title', $user->title) }}" class="input" placeholder="Storyteller | Lightroom Presets">
            <div class="help">Subtitle shown under your name on your public page.</div>
        </div>
        <div>
            <label class="label" for="bio">Bio</label>
            <textarea id="bio" name="bio" rows="4" maxlength="1000" class="input" placeholder="Tell your audience about yourself...">{{ old('bio', $user->bio) }}</textarea>
        </div>
    </div>

    {{-- WhatsApp notifications --}}
    <div class="card p-6 space-y-4">
        <h2 class="font-black text-lg">📱 WhatsApp Notifications</h2>
        <p class="text-sm text-ink-500">Dapatkan notifikasi penjualan via WhatsApp.</p>
        <div>
            <label class="label" for="phone">Nomor WhatsApp</label>
            <input id="phone" name="phone" type="tel" pattern="[0-9+\-\s]+" maxlength="20"
                   value="{{ old('phone', $user->phone) }}" class="input"
                   placeholder="081234567890 atau +6281234567890">
            <div class="help">Format: 08xx atau +62xxx. Hanya untuk notifikasi, tidak ditampilkan ke publik.</div>
        </div>
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="hidden" name="whatsapp_opt_in" value="0">
            <input type="checkbox" name="whatsapp_opt_in" value="1" {{ old('whatsapp_opt_in', $user->whatsapp_opt_in ?? true) ? 'checked' : '' }} class="w-4 h-4 rounded">
            <span class="text-sm">Kirim notifikasi penjualan via WhatsApp</span>
        </label>
    </div>

    {{-- Public page preview --}}
    <div class="card p-6 bg-gradient-to-br from-brand-50 to-brand-100/40 border-brand-200">
        <h2 class="font-black text-lg">Your Public Page</h2>
        <p class="text-sm text-ink-700 mt-1">Your URL:</p>
        <div class="mt-2 flex items-center gap-2">
            <input type="text" readonly value="{{ $user->profile_url }}" class="input flex-1 font-mono text-sm bg-white" onclick="this.select()">
            <a href="{{ $user->profile_url }}" target="_blank" class="btn-secondary btn-sm">Open</a>
        </div>
    </div>

    <div class="flex justify-end">
        <button type="submit" class="btn-primary">Save Changes</button>
    </div>
</form>
@endsection