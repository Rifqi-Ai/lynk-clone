@extends('layouts.dashboard')

@section('title', 'Profile Settings')
@section('header', 'Profile Settings')
@section('subheader', 'Manage how your public page looks and your notification preferences.')

@section('content')
<form method="POST" action="{{ route('settings.profile.update') }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @method('PATCH')

    {{-- Flash messages --}}
    @if (session('success'))
        <div class="alert alert-success">
            <svg class="w-5 h-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Avatar --}}
    <div class="card-warm p-6">
        <div class="flex items-center gap-2 pb-3 mb-5 border-b border-ink-100">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand-500 to-brand-700 text-white flex items-center justify-center">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
            </div>
            <h2 class="font-black text-lg text-ink-900">Avatar</h2>
        </div>
        <div class="flex items-center gap-5">
            <img src="{{ $user->avatar_url }}" class="w-20 h-20 rounded-2xl ring-2 ring-ink-200 shadow-sm" alt="Your avatar">
            <div class="flex-1">
                <input type="file" name="avatar" accept="image/*" class="file-input file-input-bordered w-full max-w-md">
                <div class="mt-1.5 text-xs text-ink-500">JPG/PNG, max 2MB. Square image works best.</div>
            </div>
        </div>
    </div>

    {{-- Basic info --}}
    <div class="card-warm p-6 space-y-5">
        <div class="flex items-center gap-2 pb-3 border-b border-ink-100">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand-500 to-brand-700 text-white flex items-center justify-center">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
            </div>
            <h2 class="font-black text-lg text-ink-900">Basic Info</h2>
        </div>

        <div>
            <label class="block text-sm font-bold text-ink-900 mb-1.5" for="name">Display Name</label>
            <input id="name" name="name" type="text" required value="{{ old('name', $user->name) }}" class="input input-bordered w-full">
        </div>
        <div>
            <label class="block text-sm font-bold text-ink-900 mb-1.5" for="username">Username</label>
            <div class="flex items-center gap-2 max-w-md">
                <span class="text-sm text-ink-500 font-bold whitespace-nowrap px-3 py-2 rounded-lg bg-ink-100">{{ url('/') }}/</span>
                <input id="username" type="text" value="{{ $user->username }}" readonly class="input input-bordered flex-1 bg-ink-50 cursor-not-allowed font-mono">
            </div>
            <div class="mt-1.5 text-xs text-ink-500">Username cannot be changed (MVP).</div>
        </div>
        <div>
            <label class="block text-sm font-bold text-ink-900 mb-1.5" for="title">Title</label>
            <input id="title" name="title" type="text" maxlength="100" value="{{ old('title', $user->title) }}" class="input input-bordered w-full" placeholder="Storyteller | Lightroom Presets">
            <div class="mt-1.5 text-xs text-ink-500">Subtitle shown under your name on your public page.</div>
        </div>
        <div>
            <label class="block text-sm font-bold text-ink-900 mb-1.5" for="bio">Bio</label>
            <textarea id="bio" name="bio" rows="4" maxlength="1000" class="textarea textarea-bordered w-full" placeholder="Tell your audience about yourself...">{{ old('bio', $user->bio) }}</textarea>
        </div>
    </div>

    {{-- WhatsApp notifications --}}
    <div class="card-warm p-6 space-y-5">
        <div class="flex items-center gap-2 pb-3 border-b border-ink-100">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-green-500 to-green-700 text-white flex items-center justify-center">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0 0 20.885 3.488"/></svg>
            </div>
            <div>
                <h2 class="font-black text-lg text-ink-900">WhatsApp Notifications</h2>
                <p class="text-xs text-ink-500 mt-0.5">Get instant alerts when you make a sale.</p>
            </div>
        </div>

        <div>
            <label class="block text-sm font-bold text-ink-900 mb-1.5" for="phone">Nomor WhatsApp</label>
            <input id="phone" name="phone" type="tel" pattern="[0-9+\-\s]+" maxlength="20"
                   value="{{ old('phone', $user->phone) }}" class="input input-bordered w-full max-w-md font-mono"
                   placeholder="081234567890 atau +628****7890">
            <div class="mt-1.5 text-xs text-ink-500">Format: 08xx atau +62xxx. Private — tidak ditampilkan ke publik.</div>
        </div>
        <label class="flex items-start gap-3 cursor-pointer p-4 rounded-xl bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 hover:border-green-300 transition-colors">
            <input type="hidden" name="whatsapp_opt_in" value="0">
            <input type="checkbox" name="whatsapp_opt_in" value="1" {{ old('whatsapp_opt_in', $user->whatsapp_opt_in ?? true) ? 'checked' : '' }} class="checkbox checkbox-success mt-0.5">
            <div class="flex-1">
                <div class="font-bold text-sm">Kirim notifikasi penjualan via WhatsApp</div>
                <div class="text-xs text-ink-600 mt-0.5">Kami kirim detail order baru ke nomor kamu.</div>
            </div>
        </label>
    </div>

    {{-- Public page preview --}}
    <div class="card-warm p-6 bg-gradient-to-br from-brand-50 via-amber-50 to-brand-50 border-brand-200">
        <div class="flex items-center gap-2 pb-3 mb-4 border-b border-brand-200/50">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand-500 to-brand-700 text-white flex items-center justify-center">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/></svg>
            </div>
            <div>
                <h2 class="font-black text-lg text-ink-900">Your Public Page</h2>
                <p class="text-xs text-ink-600 mt-0.5">Bagikan link ini ke audiens kamu.</p>
            </div>
        </div>
        <div class="flex items-center gap-2 max-w-2xl">
            <input type="text" readonly value="{{ $user->profile_url }}" class="input input-bordered flex-1 font-mono text-sm bg-white" onclick="this.select()">
            <a href="{{ $user->profile_url }}" target="_blank" class="btn-secondary flex-shrink-0">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                Open
            </a>
        </div>
    </div>

    <div class="flex justify-end gap-3">
        <a href="{{ route('dashboard.index') }}" class="btn-ghost-ink">Cancel</a>
        <button type="submit" class="btn-cta">
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
            Save Changes
        </button>
    </div>
</form>
@endsection