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
            <p class="zn-hero-note">
                Already have a profile? <a class="zn-link" href="{{ route('login') }}">Sign in</a>
            </p>
        </div>
    </section>

    <section class="zn-canvas">
        <div class="zn-narrow">
            {{-- Three doors, matching the three things a visitor can actually
                 do here. Deliberately not a job list or a process explainer —
                 both live on their own pages. --}}
            <div class="zn-doors">
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

                <a class="zn-door" href="{{ route('login') }}">
                    <span class="zn-door-ico"><i class="bi bi-box-arrow-in-right"></i></span>
                    <b>Sign in</b>
                    <span>Continue an application or check where yours stands.</span>
                    <span class="zn-door-go">Sign in &rarr;</span>
                </a>
            </div>

            <p class="zn-landing-foot">
                We handle the information you give us in line with the
                <a class="zn-link" href="{{ route('privacy') }}">Privacy Notice</a>.
            </p>
        </div>
    </section>

</main>
@endsection
