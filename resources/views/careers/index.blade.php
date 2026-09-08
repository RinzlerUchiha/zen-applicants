@extends('layouts.guest')

@section('title', 'Careers')

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

    .cx-wrap { max-width: 1080px; margin: 0 auto; padding: 26px 4px 60px; }

    .cx-hero {
        background: linear-gradient(135deg, var(--cx-brand-deep) 0%, var(--cx-brand) 58%, #2F92E8 100%);
        border-radius: 20px;
        padding: 40px 38px;
        color: #fff;
        box-shadow: 0 14px 34px rgba(20, 58, 120, .22);
    }

    .cx-hero h1 {
        font-size: 30px;
        font-weight: 800;
        letter-spacing: -.4px;
        margin: 0 0 8px;
    }

    .cx-hero p {
        margin: 0;
        font-size: 14.5px;
        opacity: .9;
        max-width: 52ch;
    }

    .cx-hero-count {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-top: 18px;
        background: rgba(255, 255, 255, .16);
        border: 1px solid rgba(255, 255, 255, .26);
        border-radius: 30px;
        padding: 6px 15px;
        font-size: 12.5px;
        font-weight: 700;
    }

    .cx-toolbar { margin: 24px 0 16px; display: flex; gap: 12px; flex-wrap: wrap; align-items: center; }

    .cx-search {
        position: relative;
        flex: 1;
        min-width: 240px;
    }

    .cx-search i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #A8AEBA;
        font-size: 14px;
    }

    .cx-search input {
        width: 100%;
        border: 1px solid var(--cx-line);
        border-radius: 12px;
        background: #fff;
        padding: 12px 16px 12px 40px;
        font-size: 14px;
        color: var(--cx-ink);
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .cx-search input:focus {
        outline: none;
        border-color: #9FB8ED;
        box-shadow: 0 0 0 4px #E8F0FE;
    }

    .cx-result-note { font-size: 12.5px; color: var(--cx-muted); }

    .cx-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }

    @media (max-width: 820px) { .cx-grid { grid-template-columns: 1fr; } }

    .cx-card {
        display: flex;
        flex-direction: column;
        height: 100%;
        background: #fff;
        border: 1px solid var(--cx-line);
        border-radius: 16px;
        padding: 22px 24px;
        text-decoration: none;
        color: inherit;
        transition: border-color .16s ease, box-shadow .16s ease, transform .16s ease;
    }

    .cx-card:hover {
        border-color: #BFD4F5;
        box-shadow: 0 12px 26px rgba(27, 79, 176, .13);
        transform: translateY(-3px);
        color: inherit;
    }

    .cx-card-top { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 10px; }

    .cx-card-title {
        font-size: 17px;
        font-weight: 800;
        color: var(--cx-ink);
        letter-spacing: -.2px;
        margin: 0;
        flex: 1;
        line-height: 1.3;
    }

    .cx-badge-new {
        flex-shrink: 0;
        font-size: 9.5px;
        font-weight: 800;
        letter-spacing: .05em;
        text-transform: uppercase;
        background: #E7F6EC;
        color: #1E9E4C;
        border-radius: 20px;
        padding: 3px 9px;
        margin-top: 3px;
    }

    .cx-card-meta {
        font-size: 11.5px;
        color: #98A0AE;
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 12px;
    }

    .cx-card-teaser {
        font-size: 13.5px;
        line-height: 1.65;
        color: #5B6474;
        margin: 0 0 18px;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .cx-card-cta {
        margin-top: auto;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-size: 13px;
        font-weight: 700;
        color: var(--cx-brand);
    }

    .cx-card:hover .cx-card-cta i { transform: translateX(3px); }

    .cx-card-cta i { transition: transform .16s ease; font-size: 12px; }

    .cx-empty {
        background: #fff;
        border: 1px dashed var(--cx-line);
        border-radius: 16px;
        padding: 60px 30px;
        text-align: center;
        color: #98A0AE;
    }

    .cx-empty i { font-size: 30px; display: block; margin-bottom: 12px; color: #C7CBD3; }

    .cx-alert { border-radius: 12px; border: none; font-size: 13.5px; }
</style>

<div class="cx-wrap">
    <div class="cx-hero">
        <h1>Career Opportunities</h1>
        <p>Browse our current openings and apply online. Every application is reviewed by our recruitment team.</p>
        <div class="cx-hero-count">
            <i class="bi bi-briefcase-fill"></i>
            {{ $postings->count() }} {{ \Illuminate\Support\Str::plural('open role', $postings->count()) }}
        </div>
    </div>

    @auth
        <div class="d-flex gap-2 mt-3">
            <a href="{{ route('applications.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">My
                Applications</a>
            <a href="{{ route('personal.show') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">My
                Profile</a>
        </div>
    @endauth

    @if (session('success'))
        <div class="alert alert-success cx-alert mt-3">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger cx-alert mt-3">{{ session('error') }}</div>
    @endif

    @if ($postings->isEmpty())
        <div class="cx-empty mt-4">
            <i class="bi bi-inbox"></i>
            No open positions at the moment. Please check back soon.
        </div>
    @else
        <div class="cx-toolbar">
            <div class="cx-search">
                <i class="bi bi-search"></i>
                <input type="search" id="cx-search-input" placeholder="Search by job title or keyword…"
                    autocomplete="off">
            </div>
            <span class="cx-result-note" id="cx-result-note"></span>
        </div>

        <div class="cx-grid" id="cx-grid">
            @foreach ($postings as $posting)
                @php
                    // Public ad if written; fall back to the internal draft only
                    // when HR has not supplied one. Rows come from the query
                    // builder as stdClass, so ?? also covers the column not
                    // existing yet (migration not run).
                    $ad = trim(($posting->public_description ?? null) ?: ($posting->posting_description ?? ''));
                    $teaser = \Illuminate\Support\Str::limit(
                        preg_replace('/\s+/u', ' ', strip_tags($ad)),
                        170
                    );
                    $posted = $posting->posted_at
                        ? \Illuminate\Support\Carbon::parse($posting->posted_at)
                        : null;
                    $isNew = $posted && $posted->greaterThan(now()->subDays(7));
                @endphp
                <a href="{{ route('careers.show', $posting->id) }}" class="cx-card"
                    data-search="{{ \Illuminate\Support\Str::lower($posting->posting_title . ' ' . $teaser) }}">
                    <div class="cx-card-top">
                        <h2 class="cx-card-title">{{ $posting->posting_title }}</h2>
                        @if ($isNew)
                            <span class="cx-badge-new">New</span>
                        @endif
                    </div>

                    @if ($posted)
                        <div class="cx-card-meta">
                            <i class="bi bi-calendar3"></i> Posted {{ $posted->format('M d, Y') }}
                        </div>
                    @endif

                    <p class="cx-card-teaser">{{ $teaser }}</p>

                    <span class="cx-card-cta">View details <i class="bi bi-arrow-right"></i></span>
                </a>
            @endforeach
        </div>

        <div class="cx-empty mt-3 d-none" id="cx-no-results">
            <i class="bi bi-search"></i>
            No openings match your search.
        </div>
    @endif
</div>

<script>
    (function () {
        const input = document.getElementById('cx-search-input');
        if (!input) return;

        const cards = Array.from(document.querySelectorAll('#cx-grid .cx-card'));
        const note = document.getElementById('cx-result-note');
        const noResults = document.getElementById('cx-no-results');

        function filter() {
            const q = input.value.trim().toLowerCase();
            let shown = 0;

            cards.forEach(card => {
                const hit = !q || card.dataset.search.includes(q);
                card.style.display = hit ? '' : 'none';
                if (hit) shown++;
            });

            noResults.classList.toggle('d-none', shown > 0);
            note.textContent = q ? `${shown} of ${cards.length} shown` : '';
        }

        input.addEventListener('input', filter);
    })();
</script>
@endsection
