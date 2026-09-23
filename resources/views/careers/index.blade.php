@extends('layouts.guest')

@section('title', 'Careers')

@section('content')

{{--
    Browsing open positions: the list on the left, the posting itself on the
    right. Choosing one loads it beside the list instead of leaving the page,
    and the address bar still follows (/careers/{id}), so links, Back and
    refresh all work. On a phone the posting takes the whole screen with a
    back link, since there is no room for two panes.
--}}

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
    <div class="zn-jobs-split" id="jobs-split">
        <div class="zn-jobs-list-pane">
            <div class="zn-searchbar">
                <input type="search" id="job-search" placeholder="Search by role, e.g. technician" aria-label="Search roles">
            </div>

            <div class="zn-joblist" id="job-list" role="list">
                @foreach ($postings as $posting)
                    @php
                        // The public ad is the composed one; fall back to the internal
                        // description only if a posting somehow has no public copy.
                        $ad = trim(($posting->public_description ?? null) ?: ($posting->posting_description ?? ''));
                        // The short description is written for exactly this; the ad's
                        // opening is the fallback for a posting without one.
                        $teaser = trim((string) ($posting->short_description ?? '')) !== ''
                            ? Str::limit($posting->short_description, 200)
                            : Str::limit(preg_replace('/\s+/', ' ', strip_tags($ad)), 150);
                        $posted = $posting->posted_at ? \Illuminate\Support\Carbon::parse($posting->posted_at) : null;
                    @endphp

                    <a class="zn-job zn-job-card" role="listitem" href="{{ route('careers.show', $posting->id) }}"
                       data-posting="{{ $posting->id }}" data-search="{{ Str::lower($posting->posting_title . ' ' . $teaser) }}">
                        <h4>{{ $posting->posting_title }}</h4>
                        @if ($posted)
                            <div class="zn-job-tags">
                                <span class="zn-pill zn-pill-opt">Posted {{ $posted->format('M j, Y') }}</span>
                            </div>
                        @endif
                        @if ($teaser)
                            <p class="zn-job-excerpt">{{ $teaser }}</p>
                        @endif
                        <span class="zn-job-open">View details &rarr;</span>
                    </a>
                @endforeach
            </div>

            <div class="zn-empty mt-2" id="no-matches" hidden>
                <b>No roles match that search</b>
                Try a shorter word, or clear the box to see everything.
            </div>
        </div>

        <div class="zn-jobs-detail-pane" id="job-detail" aria-live="polite">
            <button type="button" class="zn-link zn-jobs-back" id="job-back" hidden>&larr; All positions</button>
            <div id="job-detail-body">
                <div class="zn-jobs-placeholder">
                    <i class="bi bi-briefcase"></i>
                    <b>Choose a position</b>
                    <span>Pick one on the left to read it here.</span>
                </div>
            </div>
        </div>
    </div>
@endif

@include('careers.partials.help-bubble', ['raised' => true])

@endsection

@push('scripts')
<script>
    (function () {
        const split = document.getElementById('jobs-split');
        if (!split) return;

        const list = document.getElementById('job-list');
        const pane = document.getElementById('job-detail');
        const body = document.getElementById('job-detail-body');
        const back = document.getElementById('job-back');
        const search = document.getElementById('job-search');
        const empty = document.getElementById('no-matches');
        const title = document.title;

        function card(id) { return list.querySelector('.zn-job-card[data-posting="' + id + '"]'); }

        async function open(id, push) {
            const chosen = card(id);
            list.querySelectorAll('.zn-job-card').forEach(c => c.classList.toggle('is-open', c === chosen));
            body.innerHTML = '<div class="zn-jobs-placeholder"><span>Loading…</span></div>';
            split.classList.add('is-reading');
            back.hidden = false;

            try {
                const res = await fetch('{{ url('/careers') }}/' + id + '/panel', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (!res.ok) throw new Error(res.status);
                body.innerHTML = await res.text();
            } catch (e) {
                // Anything unexpected: fall back to the posting's own page.
                window.location = '{{ url('/careers') }}/' + id;
                return;
            }

            if (push) history.pushState({ posting: id }, '', '{{ url('/careers') }}/' + id);
            document.title = (chosen?.querySelector('h4')?.textContent || 'Careers') + ' · ' + title;
            pane.scrollIntoView({ block: 'start', behavior: 'smooth' });
        }

        function close(push) {
            split.classList.remove('is-reading');
            back.hidden = true;
            body.innerHTML = '<div class="zn-jobs-placeholder"><i class="bi bi-briefcase"></i><b>Choose a position</b><span>Pick one on the left to read it here.</span></div>';
            list.querySelectorAll('.zn-job-card').forEach(c => c.classList.remove('is-open'));
            document.title = title;
            if (push) history.pushState({}, '', '{{ route('careers.index') }}');
        }

        list.addEventListener('click', function (e) {
            const chosen = e.target.closest('.zn-job-card');
            // Let a new tab / middle click open the posting's own page.
            if (!chosen || e.metaKey || e.ctrlKey || e.shiftKey || e.button !== 0) return;
            e.preventDefault();
            open(chosen.dataset.posting, true);
        });

        back.addEventListener('click', () => close(true));
        window.addEventListener('popstate', function (e) {
            if (e.state && e.state.posting) open(e.state.posting, false); else close(false);
        });

        // Search filters the list; the open posting stays open.
        search?.addEventListener('input', function () {
            const q = search.value.trim().toLowerCase();
            let shown = 0;
            list.querySelectorAll('.zn-job-card').forEach(function (c) {
                const match = !q || (c.dataset.search || '').includes(q);
                c.hidden = !match;
                if (match) shown++;
            });
            empty.hidden = shown > 0;
        });
    })();
</script>
@endpush
