@extends('layouts.guest')

@section('title', $posting->posting_title)

@section('content')
<style>
    :root {
        --cx-ink: #1B2130;
        --cx-muted: #6B7280;
        --cx-line: #E6E8ED;
        --cx-brand: #1B6BE0;
        --cx-brand-deep: #143A78;
    }

    body { background: #F4F5F7 !important; }

    .cd-wrap { max-width: 900px; margin: 0 auto; padding: 22px 4px 90px; }

    .cd-back {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-size: 13px;
        font-weight: 600;
        color: var(--cx-muted);
        text-decoration: none;
        margin-bottom: 16px;
    }

    .cd-back:hover { color: var(--cx-brand); }

    .cd-head {
        background: linear-gradient(135deg, var(--cx-brand-deep) 0%, var(--cx-brand) 60%, #2F92E8 100%);
        border-radius: 20px 20px 0 0;
        padding: 34px 36px 30px;
        color: #fff;
    }

    .cd-head h1 {
        font-size: 27px;
        font-weight: 800;
        letter-spacing: -.4px;
        margin: 0 0 10px;
        line-height: 1.25;
    }

    .cd-head-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        font-size: 12.5px;
        opacity: .92;
    }

    .cd-head-meta span { display: inline-flex; align-items: center; gap: 6px; }

    .cd-body {
        background: #fff;
        border: 1px solid var(--cx-line);
        border-top: none;
        border-radius: 0 0 20px 20px;
        padding: 32px 36px 36px;
    }

    /* The ad is hand-written copy with emoji and hard line breaks.
       pre-line keeps the author's line structure without a <pre> font. */
    .cd-ad {
        white-space: pre-line;
        font-size: 15.5px;
        line-height: 1.85;
        color: #2C3342;
        margin: 0;
        word-break: break-word;
    }

    .cd-apply-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        margin-top: 32px;
        padding-top: 26px;
        border-top: 1px solid #F1F2F5;
    }

    .cd-apply-note { font-size: 12.5px; color: var(--cx-muted); max-width: 40ch; margin: 0; }

    .cd-apply-btn {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        background: linear-gradient(135deg, #2E8FE8, var(--cx-brand));
        color: #fff;
        border: none;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 700;
        padding: 14px 30px;
        text-decoration: none;
        box-shadow: 0 6px 18px rgba(27, 107, 224, .3);
        transition: filter .15s ease, transform .15s ease;
    }

    .cd-apply-btn:hover { filter: brightness(1.07); transform: translateY(-1px); color: #fff; }

    .cx-alert { border-radius: 12px; border: none; font-size: 13.5px; }

    .cd-apply-actions {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 8px;
    }

    .cd-login-hint {
        font-size: 12.5px;
        color: var(--cx-muted);
    }

    .cd-login-hint a {
        color: var(--cx-brand);
        font-weight: 700;
        text-decoration: none;
    }

    .cd-login-hint a:hover { text-decoration: underline; }

    /* Sticky apply on small screens — the CTA is the whole point of the page */
    .cd-sticky {
        display: none;
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        background: #fff;
        border-top: 1px solid var(--cx-line);
        box-shadow: 0 -6px 18px rgba(31, 36, 48, .09);
        padding: 12px 16px;
        z-index: 40;
    }

    .cd-sticky .cd-apply-btn { width: 100%; justify-content: center; }

    @media (max-width: 700px) {
        .cd-head, .cd-body { padding-left: 22px; padding-right: 22px; }
        .cd-head h1 { font-size: 23px; }
        .cd-sticky { display: block; }
        .cd-wrap { padding-bottom: 110px; }
    }
</style>

@php
    // stdClass from the query builder — ?? also covers the column not
    // existing yet (migration not run).
    $ad = trim(($posting->public_description ?? null) ?: ($posting->posting_description ?? ''));
    $posted = $posting->posted_at ? \Illuminate\Support\Carbon::parse($posting->posted_at) : null;
    $applyUrl = auth()->check() ? null : route('register', ['job' => $posting->id]);
    // Carries the posting through login so an existing applicant is not
    // dropped on their profile with the posting forgotten.
    $loginUrl = auth()->check() ? null : route('login', ['job' => $posting->id]);
@endphp

<div class="cd-wrap">
    <a href="{{ route('careers.index') }}" class="cd-back">
        <i class="bi bi-arrow-left"></i> Back to all openings
    </a>

    @if (session('success'))
        <div class="alert alert-success cx-alert">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger cx-alert">{{ session('error') }}</div>
    @endif

    <div class="cd-head">
        <h1>{{ $posting->posting_title }}</h1>
        <div class="cd-head-meta">
            @if ($posted)
                <span><i class="bi bi-calendar3"></i> Posted {{ $posted->format('M d, Y') }}</span>
            @endif
            <span><i class="bi bi-patch-check"></i> Now accepting applications</span>
        </div>
    </div>

    <div class="cd-body">
        <div class="cd-ad">{{ $ad }}</div>

        <div class="cd-apply-bar">
            <p class="cd-apply-note">
                @auth
                    Applying takes one click. You can track progress under My Applications.
                @else
                    You'll create a short profile first — it takes a few minutes and you can apply to other roles with
                    it later.
                @endauth
            </p>

            @auth
                <form method="POST" action="{{ route('careers.apply', $posting->id) }}" class="m-0">
                    @csrf
                    <button type="submit" class="cd-apply-btn">
                        Apply Now <i class="bi bi-arrow-right"></i>
                    </button>
                </form>
            @else
                <div class="cd-apply-actions">
                    <a href="{{ $applyUrl }}" class="cd-apply-btn">Apply Now <i class="bi bi-arrow-right"></i></a>
                    <span class="cd-login-hint">Already have an account?
                        <a href="{{ $loginUrl }}">Log in</a></span>
                </div>
            @endauth
        </div>
    </div>
</div>

<div class="cd-sticky">
    @auth
        <form method="POST" action="{{ route('careers.apply', $posting->id) }}" class="m-0">
            @csrf
            <button type="submit" class="cd-apply-btn">Apply Now <i class="bi bi-arrow-right"></i></button>
        </form>
    @else
        <a href="{{ $applyUrl }}" class="cd-apply-btn">Apply Now <i class="bi bi-arrow-right"></i></a>
    @endauth
</div>
@endsection
