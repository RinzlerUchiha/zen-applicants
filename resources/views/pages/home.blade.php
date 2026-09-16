@extends('layouts.app')

@section('title', 'Home')

@section('body')
<main class="zn-canvas">
    <div class="zn-narrow">

        @if (session('success'))
            <div class="zn-toast"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="zn-toast error"><i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}</div>
        @endif

        @php
            $hour = (int) now()->format('H');
            $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
            $docsDone = $docsSubmitted >= $docsRequired && $docsAttention->isEmpty();
        @endphp

        <p class="zn-page-title">{{ $greeting }}, {{ auth()->user()->app_fname ?: 'there' }}</p>
        <p class="zn-page-sub">
            @if ($applications->count())
                You're being considered for {{ $applications->count() }}
                {{ Str::plural('position', $applications->count()) }}.
            @else
                You haven't applied to a position yet.
            @endif
        </p>

        {{-- One primary action, chosen for them. An HR request comes first — it
             is the one thing someone is actively waiting on — then documents,
             then the form. The checklist below is status, not a second copy of
             this action. --}}
        @if ($docsAttention->isNotEmpty())
            @php $first = $docsAttention->first(); @endphp
            <div class="zn-nextstep">
                <div>
                    <p class="zn-nextstep-label">HR needs something from you</p>
                    <h3>
                        {{ $first['document']?->review_status === 'rejected' ? 'Replace your' : 'Send your' }}
                        {{ strtolower($first['label']) }}
                        @if ($docsAttention->count() > 1)
                            and {{ $docsAttention->count() - 1 }} more
                        @endif
                    </h3>
                    <p>
                        @if ($first['document']?->review_status === 'rejected')
                            {{ $first['document']->review_reason_text }}
                        @else
                            HR has asked you to upload this document.
                        @endif
                    </p>
                </div>
                <a class="zn-btn" href="{{ route('documents.index') }}">Go to documents</a>
            </div>
        @elseif (!$docsDone)
            <div class="zn-nextstep">
                <div>
                    <p class="zn-nextstep-label">Your next step</p>
                    <h3>{{ $docsSubmitted === 0 ? 'Send us your résumé and 2x2 picture' : 'Upload your remaining document' }}</h3>
                    <p>{{ $docsRequired - $docsSubmitted }} of {{ $docsRequired }} still needed. PDF, JPG or PNG, up to
                        {{ round(config('documents.max_size_kb') / 1024) }} MB.</p>
                </div>
                <a class="zn-btn" href="{{ route('documents.index') }}">Upload documents</a>
            </div>
        @elseif ($nextSection)
            <div class="zn-nextstep">
                <div>
                    <p class="zn-nextstep-label">Your next step</p>
                    <h3>Finish {{ strtolower($nextSection['label']) }}</h3>
                    <p>
                        @if (count($nextSection['missing']))
                            Still needed: {{ implode(', ', array_slice($nextSection['missing'], 0, 3)) }}{{ count($nextSection['missing']) > 3 ? '…' : '' }}
                        @else
                            Add at least one entry to complete this section.
                        @endif
                    </p>
                </div>
                <a class="zn-btn" href="{{ Route::has($nextSection['route']) ? route($nextSection['route']) : '#' }}">Continue</a>
            </div>
        @else
            <div class="zn-nextstep">
                <div>
                    <p class="zn-nextstep-label">All done</p>
                    <h3>Your application is complete</h3>
                    <p>Nothing further is needed from you. HR reviews your application and will contact you to
                        schedule an initial interview.</p>
                </div>
                <a class="zn-btn zn-btn-out" href="{{ route('careers.index') }}">View open positions</a>
            </div>
        @endif

        <div class="row g-3">
            <div class="col-lg-7">
                <div class="zn-card h-100">
                    <div class="zn-card-head">
                        <p class="zn-card-title">Your {{ Str::plural('application', max($applications->count(), 1)) }}</p>
                        @if ($applications->count())
                            <a class="zn-link" style="font-size:12px" href="{{ route('applications.index') }}">View all</a>
                        @endif
                    </div>

                    @forelse ($applications->take(2) as $application)
                        <div class="{{ !$loop->first ? 'pt-3 mt-3 border-top' : '' }}">
                            <div class="d-flex align-items-baseline justify-content-between gap-2">
                                <span style="font-size:16px;font-weight:700">{{ $application->job_title }}</span>
                                <span class="zn-count">{{ $application->mr_no }}</span>
                            </div>
                            {{-- A summary only. The stage tracker lives on My
                                 Applications, and what to do next is the card
                                 above — neither is repeated here. --}}
                            <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                                <span class="zn-count" style="font-weight:400">
                                    Applied {{ $application->applied_at?->format('F j, Y') }}
                                </span>
                                <span class="zn-pill zn-pill-acc">{{ $application->status }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="zn-empty">
                            <b>No applications yet</b>
                            Browse our open positions and apply — your profile carries over to every one.
                            <div class="mt-3"><a class="zn-btn zn-btn-sm" href="{{ route('careers.index') }}">View open positions</a></div>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="col-lg-5">
                <div class="zn-card h-100">
                    <div class="zn-card-head"><p class="zn-card-title">What's left</p></div>

                    <div class="zn-tasks">
                        <a class="zn-task" href="{{ route('documents.index') }}">
                            <div class="zn-task-ico"><i class="bi bi-file-earmark-text"></i></div>
                            <div>
                                <div class="zn-task-name">
                                    Documents
                                    @if ($docsAttention->isNotEmpty())
                                        <span class="zn-pill zn-pill-req">HR request</span>
                                    @elseif (!$docsDone)
                                        <span class="zn-pill zn-pill-req">{{ $docsRequired - $docsSubmitted }} left</span>
                                    @endif
                                </div>
                                <div class="zn-task-sub">{{ $docsSubmitted }} of {{ $docsRequired }} required items sent</div>
                                <div class="zn-minibar"><i style="width: {{ $docsRequired ? round($docsSubmitted / $docsRequired * 100) : 100 }}%"></i></div>
                            </div>
                            <div class="zn-task-pct">{{ $docsRequired ? round($docsSubmitted / $docsRequired * 100) : 100 }}%</div>
                        </a>

                        <a class="zn-task" href="{{ route('personal.show') }}">
                            <div class="zn-task-ico"><i class="bi bi-pencil-square"></i></div>
                            <div>
                                <div class="zn-task-name">Application Form</div>
                                <div class="zn-task-sub">{{ $counts['done'] }} of {{ $counts['total'] }} required sections complete</div>
                                <div class="zn-minibar"><i style="width: {{ $percent }}%"></i></div>
                            </div>
                            <div class="zn-task-pct">{{ $percent }}%</div>
                        </a>

                        {{-- Not "Locked": nothing locks them yet (that gate is a
                             later milestone), and the link opens. This says when
                             they happen in the process; the Assessments page
                             carries the full explanation. --}}
                        <a class="zn-task" href="{{ route('assessments.index') }}">
                            <div class="zn-task-ico"><i class="bi bi-ui-checks"></i></div>
                            <div>
                                <div class="zn-task-name">
                                    Assessments <span class="zn-pill zn-pill-later">After interview</span>
                                </div>
                                <div class="zn-task-sub">Provided by HR after your initial interview</div>
                            </div>
                            <div class="zn-task-pct zn-muted">—</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>
@endsection
