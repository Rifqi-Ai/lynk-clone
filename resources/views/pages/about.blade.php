@extends('layouts.app')

@section('title', 'About')

@section('content')
<section class="py-16 px-4">
    <div class="max-w-3xl mx-auto prose">
        <h1 class="text-4xl font-black">About {{ config('app.name') }}</h1>
        <p class="lead mt-4 text-lg text-ink-700">
            We empower creators to monetize their knowledge and build sustainable businesses online.
        </p>

        <h2 class="text-2xl font-black mt-8">Our Mission</h2>
        <p class="text-ink-700">
            {{ config('app.name') }} was built to give creators a single platform to share, sell, and grow. We believe everyone has something valuable to teach, share, or sell — and we make it easy to do so.
        </p>

        <h2 class="text-2xl font-black mt-8">What We Offer</h2>
        <ul class="text-ink-700 space-y-2">
            <li>📦 Digital products (ebooks, presets, templates)</li>
            <li>🎓 Online courses</li>
            <li>📅 Appointment booking</li>
            <li>🎟️ Event ticketing</li>
            <li>☕ Donations & support</li>
            <li>🛍️ Physical product sales</li>
        </ul>

        <h2 class="text-2xl font-black mt-8">Why {{ config('app.name') }}?</h2>
        <p class="text-ink-700">
            Unlike other platforms that charge 15-30% fees, we keep things fair: 10% on Starter, 5% on Pro, and up to 0% on Brandpreneur. We believe creators should keep more of what they earn.
        </p>
    </div>
</section>
@endsection