@extends('layouts.app')

@section('title', 'Careers at ZenHub')

@section('body')
<main class="zn-landing">

    {{-- The brand is in the header; the hero leads with the message. Its two
         buttons are the page's actions — nothing below repeats them. --}}
    <section class="zn-hero">
        <div class="zn-hero-inner">
            <p class="zn-hero-eyebrow">ZenHub Careers Portal</p>
            <h1>Build your career with us.</h1>
            <p class="zn-hero-lede">
                This is where you apply to work with ZenHub. Create one applicant profile, use it for
                every position you're interested in, and follow your application from here.
            </p>
            <div class="zn-hero-actions">
                <a class="zn-btn" href="{{ route('register') }}">Create applicant profile</a>
                <a class="zn-btn zn-btn-out" href="{{ route('careers.index') }}">View open positions</a>
            </div>
        </div>
    </section>

    <section class="zn-canvas">
        <div class="zn-narrow">
            <p class="zn-landing-foot">
                Using this portal means agreeing to the
                <a class="zn-link" href="{{ route('terms') }}">Terms of Use</a>.
                We handle the information you give us in line with the
                <a class="zn-link" href="{{ route('privacy') }}">Privacy Notice</a>.
            </p>
        </div>
    </section>

</main>
@endsection
