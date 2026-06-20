@extends('layouts.app')

@section('title', 'Privacy Policy')
@section('content')
<section class="py-16 px-4">
    <div class="max-w-3xl mx-auto prose prose-sm">
        <h1 class="text-3xl font-black">Privacy Policy</h1>
        <p class="text-ink-500">Last updated: {{ now()->format('F j, Y') }}</p>

        <h2 class="text-xl font-black mt-8">Information We Collect</h2>
        <p>We collect information you provide (name, email, username) and usage data (pages viewed, purchases made).</p>

        <h2 class="text-xl font-black mt-8">How We Use It</h2>
        <p>To provide our services, process payments, send notifications, and improve the platform.</p>

        <h2 class="text-xl font-black mt-8">Cookies</h2>
        <p>We use cookies for authentication and analytics. You can disable them in browser settings, but some features may not work.</p>

        <h2 class="text-xl font-black mt-8">Third Parties</h2>
        <p>We share data with: Google (OAuth, Analytics), Duitku (payments), Cloudflare (hosting/CDN).</p>

        <h2 class="text-xl font-black mt-8">Your Rights</h2>
        <p>You can access, update, or delete your data anytime from your account settings.</p>

        <h2 class="text-xl font-black mt-8">Security</h2>
        <p>Passwords are hashed. Payments are encrypted (RSA). HTTPS enforced.</p>

        <h2 class="text-xl font-black mt-8">Contact</h2>
        <p>Questions? Contact us at privacy{{ '@' . config('app.url') }}.</p>
    </div>
</section>
@endsection