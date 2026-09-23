{{--
    One job posting, as applicants read it. Used by the careers page's detail
    pane and by the posting's own page (/careers/{id}), so both show exactly
    the same thing.

    $posting, $photos, $reapplyOn — as JobListingController provides them.
--}}
@php
    $ad = trim(($posting->public_description ?? null) ?: ($posting->posting_description ?? ''));
    $posted = $posting->posted_at ? \Illuminate\Support\Carbon::parse($posting->posted_at) : null;
    // The Job Specification's photos (added by HR in zen-admin): alternately
    // left and right beside the posting on a wide screen, a strip above it
    // on a narrow one.
    $photoUrls = collect($photos ?? [])->map(fn ($name) => route('careers.photo', [$posting->id, $name]));
    $sides = [$photoUrls->filter(fn ($u, $i) => $i % 2 === 0), $photoUrls->filter(fn ($u, $i) => $i % 2 === 1)];
@endphp

<div class="zn-jd-layout {{ $photoUrls->isNotEmpty() ? 'has-photos' : '' }}">
    @if ($photoUrls->isNotEmpty())
        <aside class="zn-jd-side" aria-hidden="true">
            @foreach ($sides[0] as $url)
                <img src="{{ $url }}" alt="" loading="lazy">
            @endforeach
        </aside>
    @endif

    <article class="zn-jd">
        <header class="zn-jd-head">
            <h1>{{ $posting->posting_title }}</h1>
            @if ($posted)
                <div class="zn-job-tags" style="margin-bottom:0">
                    <span class="zn-pill zn-pill-opt">Posted {{ $posted->format('M j, Y') }}</span>
                </div>
            @endif
        </header>

        @if ($photoUrls->isNotEmpty())
            <div class="zn-jd-strip" aria-hidden="true">
                @foreach ($photoUrls as $url)
                    <img src="{{ $url }}" alt="" loading="lazy">
                @endforeach
            </div>
        @endif

        @if ($ad !== '')
            <div class="zn-jd-body">{{ $ad }}</div>
        @else
            <div class="zn-empty">
                <b>No description available</b>
                This posting has no published details yet. Please check back, or contact HR.
            </div>
        @endif

        {{-- The apply action belongs at the end of the posting. It is sticky:
             while the posting is on screen it stays pinned to the bottom of the
             window, and it settles into this place once the reader reaches the
             end. Signed-in applicants go to the apply step (CV, 2x2 picture,
             optional cover letter); visitors carry this job through sign-up or
             sign-in. --}}
        <div class="zn-apply-bar">
            <div class="zn-apply-bar-inner">
                <div class="zn-apply-bar-text">
                    <b>{{ $posting->posting_title }}</b>
                    @auth
                        @if ($reapplyOn ?? null)
                            <span>You can apply for this position again on {{ $reapplyOn->format('F j, Y') }}.
                                Other positions are open to you now.</span>
                        @else
                            <span>Next: your CV and 2x2 picture</span>
                        @endif
                    @else
                        <span>Already applied before?
                            <a class="zn-link" href="{{ route('login', ['job' => $posting->id]) }}">Sign in</a></span>
                    @endauth
                </div>

                @auth
                    @if ($reapplyOn ?? null)
                        <a class="zn-btn zn-btn-out" href="{{ route('careers.index') }}">See other positions</a>
                    @else
                        <a class="zn-btn" href="{{ route('careers.apply.form', $posting->id) }}">Apply for this position</a>
                    @endif
                @else
                    <a class="zn-btn" href="{{ route('register', ['job' => $posting->id]) }}">Apply for this position</a>
                @endauth
            </div>
        </div>
    </article>

    @if ($photoUrls->isNotEmpty())
        <aside class="zn-jd-side" aria-hidden="true">
            @foreach ($sides[1] as $url)
                <img src="{{ $url }}" alt="" loading="lazy">
            @endforeach
        </aside>
    @endif
</div>
