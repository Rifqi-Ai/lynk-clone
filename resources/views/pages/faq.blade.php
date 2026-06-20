@extends('layouts.app')

@section('title', 'FAQ')
@section('content')
<section class="py-16 px-4">
    <div class="max-w-3xl mx-auto">
        <h1 class="text-4xl font-black text-center mb-12">Frequently Asked Questions</h1>

        @php
            $faqs = [
                ['q' => 'What is ' . config('app.name') . '?', 'a' => config('app.name') . ' is a place for people to share their expertise, advice, and knowledge through our link. You can sell digital products, courses, appointments, and more.'],
                ['q' => 'How does it work?', 'a' => 'Create your page, add your products or services, and share your unique link with your audience. They can buy directly from your page.'],
                ['q' => 'Who is this for?', 'a' => 'Public speakers, experts, coaches, advisors, artists, celebrities, writers, consultants, counselors, developers, entrepreneurs, investors, influencers, doctors, mentors, teachers — anyone with knowledge to share.'],
                ['q' => 'When and how will I get paid?', 'a' => 'Your earnings are sent monthly (day 25) to your PayPal or Bank Transfer. Pro plan members can request payout anytime.'],
                ['q' => 'Any platform fees?', 'a' => 'Yes. Starter plan: 10% admin fee. Pro plan: 5%. Brandpreneur: up to 0% (negotiated).'],
                ['q' => 'Can I use a custom domain?', 'a' => 'Yes, on the Pro plan and above. You can use your own domain like yourname.com instead of the default link.'],
                ['q' => 'How do I tell my audience?', 'a' => 'Add your profile link to your social media bio. Share it in DMs and posts. The link is shareable anywhere.'],
                ['q' => 'Can I use this as an alternative to Linktree?', 'a' => 'Absolutely! All Linktree features are free here, plus you can sell products directly.'],
            ];
        @endphp

        <div class="space-y-4">
            @foreach ($faqs as $i => $faq)
                <details class="card p-5 group" {{ $i === 0 ? 'open' : '' }}>
                    <summary class="font-bold cursor-pointer flex items-center justify-between">
                        {{ $faq['q'] }}
                        <svg class="w-4 h-4 transition group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </summary>
                    <p class="mt-3 text-sm text-ink-700 leading-relaxed">{{ $faq['a'] }}</p>
                </details>
            @endforeach
        </div>
    </div>
</section>
@endsection