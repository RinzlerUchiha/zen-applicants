@extends('layouts.guest')

@section('title', $posting->posting_title)

@section('content')

@php
    $ad = trim(($posting->public_description ?? null) ?: ($posting->posting_description ?? ''));
    $posted = $posting->posted_at ? \Illuminate\Support\Carbon::parse($posting->posted_at) : null;
@endphp

<p style="margin:0 0 12px">
    <a class="zn-link" href="{{ route('careers.index') }}">&larr; All positions</a>
</p>

<div class="zn-jd-head">
    <h1>{{ $posting->posting_title }}</h1>

    @if ($posted)
        <div class="zn-job-tags" style="margin-bottom:12px">
            <span class="zn-pill zn-pill-opt">Posted {{ $posted->format('M j, Y') }}</span>
        </div>
    @endif

    <div class="d-flex gap-2 align-items-center flex-wrap">
        @auth
            <form method="POST" action="{{ route('careers.apply', $posting->id) }}" class="m-0">
                @csrf
                <button type="submit" class="zn-btn">Apply for this position</button>
            </form>
            <span class="zn-count">Your saved profile will be used</span>
        @else
            <a class="zn-btn" href="{{ route('register', ['job' => $posting->id]) }}">Apply for this position</a>
            <span class="zn-count">Already applied before? <a class="zn-link" href="{{ route('login', ['job' => $posting->id]) }}">Sign in</a></span>
        @endauth
    </div>

    {{-- What the previous page never said: what happens after you apply.
         Setting this expectation up front is the cheapest reassurance in the
         whole flow, and it matches the real process rather than a guess. --}}
    <div class="zn-whatnext">
        <div class="zn-wn"><b>1 · Apply</b><span>Create an account and submit.</span></div>
        <div class="zn-wn"><b>2 · Forms</b><span>Application form, résumé and 2x2 picture.</span></div>
        <div class="zn-wn"><b>3 · Interview</b><span>HR contacts you to schedule.</span></div>
        <div class="zn-wn"><b>4 · Assessments</b><span>Provided after your interview.</span></div>
    </div>
</div>

@if ($ad !== '')
    <div class="zn-jd-body">{{ $ad }}</div>
@else
    <div class="zn-empty">
        <b>No description available</b>
        This posting has no published details yet. Please check back, or contact HR.
    </div>
@endif

@endsection
