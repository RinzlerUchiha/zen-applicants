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

{{-- Read the job, then apply. The posting is the page; the apply bar follows
     it, and there is no process explainer in between. --}}
<article class="zn-jd">
    <header class="zn-jd-head">
        <h1>{{ $posting->posting_title }}</h1>
        @if ($posted)
            <div class="zn-job-tags" style="margin-bottom:0">
                <span class="zn-pill zn-pill-opt">Posted {{ $posted->format('M j, Y') }}</span>
            </div>
        @endif
    </header>

    @if ($ad !== '')
        <div class="zn-jd-body">{{ $ad }}</div>
    @else
        <div class="zn-empty">
            <b>No description available</b>
            This posting has no published details yet. Please check back, or contact HR.
        </div>
    @endif

    {{-- The apply action belongs at the end of the posting. It is sticky: while
         the posting is on screen it stays pinned to the bottom of the window,
         and it settles into this place once the reader reaches the end — so it
         never covers the last of the content. Same flow as before: signed-in
         applicants submit straight away, visitors carry this job through
         sign-up or sign-in. --}}
    <div class="zn-apply-bar">
        <div class="zn-apply-bar-inner">
            <div class="zn-apply-bar-text">
                <b>{{ $posting->posting_title }}</b>
                @auth
                    @if ($reapplyOn)
                        <span>You can apply for this position again on {{ $reapplyOn->format('F j, Y') }}.
                            Other positions are open to you now.</span>
                    @else
                        <span>Your saved profile will be used</span>
                    @endif
                @else
                    <span>Already applied before?
                        <a class="zn-link" href="{{ route('login', ['job' => $posting->id]) }}">Sign in</a></span>
                @endauth
            </div>

            @auth
                @if ($reapplyOn)
                    <a class="zn-btn zn-btn-out" href="{{ route('careers.index') }}">See other positions</a>
                @else
                    <form method="POST" action="{{ route('careers.apply', $posting->id) }}" class="m-0">
                        @csrf
                        <button type="submit" class="zn-btn">Apply for this position</button>
                    </form>
                @endif
            @else
                <a class="zn-btn" href="{{ route('register', ['job' => $posting->id]) }}">Apply for this position</a>
            @endauth
        </div>
    </div>
</article>

@endsection
