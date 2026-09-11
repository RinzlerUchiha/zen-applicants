@extends('layouts.app')

@section('title', 'Careers at ZenHub')

@section('body')
<main class="zn-landing">

    <section class="zn-hero">
        <div class="zn-hero-inner">
            <img class="zn-hero-mark" src="https://teamtngc.com/zen/assets/img/coffi.png"
                 width="64" height="64" alt="">
            <p class="zn-hero-eyebrow">ZenHub Careers Portal</p>
            <h1>Build your career with us.</h1>
            <p class="zn-hero-lede">
                This is where you apply to work with ZenHub. Create one applicant profile, use it for
                every position you're interested in, and follow your application from here.
            </p>
            <div class="zn-hero-actions">
                <a class="zn-btn" href="{{ route('register') }}">Create applicant profile</a>
                <a class="zn-btn zn-btn-out" href="{{ route('careers.index') }}">Browse opportunities</a>
            </div>
            {{-- No "Sign in" link here. The header carries the one Sign in
                 action on every guest page, and it is visible in this same
                 viewport — a second one competes with the primary action
                 without adding a route. --}}
        </div>
    </section>

    <section class="zn-canvas">
        <div class="zn-narrow">
            {{-- Two doors, matching the two things a visitor who is not signed
                 in comes here to start. Signing in is not one of them — that
                 is a returning action and lives in the header, once.
                 Deliberately not a job list or a process explainer; both live
                 on their own pages. --}}
            <div class="zn-doors two">
                <a class="zn-door" href="{{ route('careers.index') }}">
                    <span class="zn-door-ico"><i class="bi bi-search"></i></span>
                    <b>Browse opportunities</b>
                    <span>See the positions we're hiring for right now.</span>
                    <span class="zn-door-go">View openings &rarr;</span>
                </a>

                <a class="zn-door" href="{{ route('register') }}">
                    <span class="zn-door-ico"><i class="bi bi-person-plus"></i></span>
                    <b>Create your profile</b>
                    <span>Fill it in once. It carries over to every application you make.</span>
                    <span class="zn-door-go">Get started &rarr;</span>
                </a>

            </div>

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
