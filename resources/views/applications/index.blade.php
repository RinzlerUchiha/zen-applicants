@extends('layouts.app')

@section('title', 'My Applications')

@section('body')
<main class="zn-canvas">
    <div class="zn-narrow">

        @if (session('success'))
            <div class="zn-toast"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="zn-toast error"><i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}</div>
        @endif

        <div class="mb-3">
            <p class="zn-page-title" style="margin-bottom:2px">My applications</p>
            <p class="zn-page-sub" style="margin-bottom:0">Everything you've applied to, and where each one stands.</p>
        </div>

        @forelse ($applications as $application)
            @php
                // The applicant-facing stage. Only "Applied" and the current
                // screening step are known from data today — the later stages
                // are shown as upcoming, not claimed as reached.
                $status = strtolower((string) $application->status);
                $isClosed = in_array($status, ['closed', 'withdrawn', 'rejected', 'filled'], true);
            @endphp

            <div class="zn-card mb-2">
                <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
                    <div>
                        <h4 style="font-size:15.5px;font-weight:700;margin:0;letter-spacing:-.2px">{{ $application->job_title }}</h4>
                        <p class="zn-count" style="font-weight:400;margin:3px 0 0">
                            {{ $application->mr_no }} · Applied {{ $application->applied_at?->format('F j, Y') }}
                        </p>
                    </div>
                    <span class="zn-pill {{ $isClosed ? 'zn-pill-opt' : 'zn-pill-acc' }}">{{ $application->status }}</span>
                </div>

                <div class="zn-track">
                    <div class="zn-step done"><span>Applied</span></div>
                    <div class="zn-step {{ $isClosed ? '' : 'now' }}"><span>Forms &amp; materials</span></div>
                    <div class="zn-step"><span>Initial interview</span></div>
                    <div class="zn-step"><span>Assessments</span></div>
                </div>

                <p class="zn-note">
                    @if ($isClosed)
                        This application is no longer active. Your profile stays on file — you can apply to other
                        roles without re-entering anything.
                    @else
                        HR reviews your application once your form and documents are complete, then contacts you
                        to schedule an initial interview.
                        <a class="zn-link" href="{{ route('home') }}">See what's left</a>
                    @endif
                </p>
            </div>
        @empty
            <div class="zn-empty">
                <b>You haven't applied to any positions yet</b>
                Browse our open roles — your profile carries over to every application, so you only fill it in once.
                <div class="mt-3">
                    <a class="zn-btn zn-btn-sm" href="{{ route('careers.index') }}">View open positions</a>
                </div>
            </div>
        @endforelse

    </div>
</main>
@endsection
