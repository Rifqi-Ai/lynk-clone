@extends('layouts.app')

@section('title', 'Terms of Service')
@section('content')
<section class="py-16 px-4">
    <div class="max-w-3xl mx-auto prose prose-sm">
        <h1 class="text-3xl font-black">Terms of Service</h1>
        <p class="text-ink-500">Last updated: {{ now()->format('F j, Y') }}</p>

        <h2 class="text-xl font-black mt-8">1. Acceptance</h2>
        <p>By accessing {{ config('app.name') }}, you agree to these Terms.</p>

        <h2 class="text-xl font-black mt-8">2. Account</h2>
        <p>You're responsible for your account credentials and all activity under your account.</p>

        <h2 class="text-xl font-black mt-8">3. Content</h2>
        <p>You retain ownership of content you upload. You grant us a license to display and distribute it via the platform.</p>

        <h2 class="text-xl font-black mt-8">4. Payments</h2>
        <p>Sales are subject to transaction fees based on your plan. Payouts are processed monthly on the 25th.</p>

        <h2 class="text-xl font-black mt-8">5. Prohibited Use</h2>
        <p>No illegal content, no spam, no fraud, no harassment. We may suspend accounts that violate these rules.</p>

        <h2 class="text-xl font-black mt-8">6. Refunds</h2>
        <p>Refund policies are set by individual creators. We facilitate but don't mandate refunds.</p>

        <h2 class="text-xl font-black mt-8">7. Limitation of Liability</h2>
        <p>{{ config('app.name') }} is provided "as is" without warranties. We're not liable for indirect damages.</p>

        <h2 class="text-xl font-black mt-8">8. Changes</h2>
        <p>We may update these terms. Continued use means acceptance of updated terms.</p>
    </div>
</section>
@endsection