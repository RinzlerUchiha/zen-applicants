@extends('layouts.guest')

@section('title', 'Careers')

@section('content')

<div class="mb-3">
    <p class="zn-page-title" style="margin-bottom:2px">Open positions</p>
    <p class="zn-page-sub" style="margin-bottom:0">
        {{ $postings->count() }} {{ Str::plural('role', $postings->count()) }} currently hiring.
    </p>
</div>

@if ($postings->isEmpty())
    <div class="zn-empty">
        <b>No open positions right now</b>
        Nothing is being advertised at the moment. Check back soon — new roles are posted as they open.
    </div>
@else
    <div class="zn-searchbar">
        <input type="search" id="job-search" placeholder="Search by role, e.g. technician" aria-label="Search roles">
    </div>

    <div class="zn-joblist" id="job-list">
        @foreach ($postings as $posting)
            @php
                // The public ad is the composed one; fall back to the internal
                // description only if a posting somehow has no public copy.
                $ad = trim(($posting->public_description ?? null) ?: ($posting->posting_description ?? ''));
                $teaser = Str::limit(preg_replace('/\s+/', ' ', strip_tags($ad)), 150);
                $posted = $posting->posted_at ? \Illuminate\Support\Carbon::parse($posting->posted_at) : null;
            @endphp

            <div class="zn-job" data-search="{{ Str::lower($posting->posting_title . ' ' . $teaser) }}">
                <div>
                    <h4>{{ $posting->posting_title }}</h4>
                    @if ($posted)
                        <div class="zn-job-tags">
                            <span class="zn-pill zn-pill-opt">Posted {{ $posted->format('M j, Y') }}</span>
                        </div>
                    @endif
                    @if ($teaser)
                        <p class="zn-job-excerpt">{{ $teaser }}</p>
                    @endif
                </div>
                <a class="zn-btn zn-btn-out zn-btn-sm" href="{{ route('careers.show', $posting->id) }}">View details</a>
            </div>
        @endforeach
    </div>

    <div class="zn-empty mt-2" id="no-matches" hidden>
        <b>No roles match that search</b>
        Try a shorter word, or clear the box to see everything.
    </div>
@endif

@endsection

@push('scripts')
<script>
    (function () {
        const search = document.getElementById('job-search');
        if (!search) return;

        const cards = Array.from(document.querySelectorAll('#job-list .zn-job'));
        const empty = document.getElementById('no-matches');

        search.addEventListener('input', function () {
            const term = this.value.trim().toLowerCase();
            let shown = 0;

            cards.forEach(card => {
                const match = !term || card.dataset.search.includes(term);
                card.hidden = !match;
                if (match) shown++;
            });

            empty.hidden = shown > 0;
        });
    })();
</script>
@endpush
