{{--
    Shell for the eleven assessments.

    They are NOT part of the Application Form, so they deliberately do not get
    its rail or stepper — showing an application-progress bar on an assessment
    would misrepresent where the applicant is. They get their own chrome:
    what this is, roughly how long it takes, and whether it's already done.

    The gate that decides when these open is a separate design item (the
    anti-cheating / OTP workflow) and is deliberately not implemented here.
    Nothing in this layout blocks access.

    Children keep @section('content') — no per-view change was needed beyond
    re-parenting.
--}}
@extends('layouts.app')

@php
    $all = collect(config('application_form.assessments.list'));
    $meta = $all->first(fn ($a) => request()->routeIs($a['route']));
    $position = $all->search(fn ($a) => request()->routeIs($a['route']));
    $done = isset($answer) && $answer;
@endphp

@section('title', $meta['label'] ?? 'Assessment')

@section('body')
<main class="zn-canvas">
    <div class="zn-narrow">

        @if (session('success'))
            <div class="zn-toast"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="zn-toast error"><i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}</div>
        @endif

        <p style="margin:0 0 12px">
            <a class="zn-link" href="{{ route('assessments.index') }}">&larr; All assessments</a>
        </p>

        <div class="zn-assess-head">
            <div>
                <p class="zn-crumb">
                    Assessment
                    @if ($position !== false)
                        · {{ $position + 1 }} of {{ $all->count() }}
                    @endif
                </p>
                <p class="zn-page-title" style="margin-bottom:0">
                    {{ $meta['label'] ?? 'Assessment' }}
                    @if ($done)
                        <span class="zn-pill zn-pill-ok"><i class="bi bi-check-lg"></i> Completed</span>
                    @endif
                </p>
            </div>

            <div class="zn-assess-meta">
                @if (!empty($meta['minutes']))
                    <span class="zn-assess-time"><i class="bi bi-clock"></i> About {{ $meta['minutes'] }} minutes</span>
                @endif
            </div>
        </div>

        @if ($done)
            <div class="zn-assess-note done">
                <i class="bi bi-check-circle-fill"></i>
                <div>
                    <b>You've already completed this one</b>
                    <span>Your answers are saved. There's nothing more to do here.</span>
                </div>
            </div>
        @else
            <div class="zn-assess-note">
                <i class="bi bi-info-circle-fill"></i>
                <div>
                    <b>Answer honestly — there are no right or wrong answers</b>
                    <span>Work through it in one sitting if you can. Answers are submitted only when you
                        press the button at the end.</span>
                </div>
            </div>
        @endif

        <div class="zn-assess-body">
            @yield('content')
        </div>

    </div>
</main>
@endsection
