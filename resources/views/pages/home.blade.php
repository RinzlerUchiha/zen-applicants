@extends('layouts.app')

@section('title', 'My Application')

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
            $docsDone = $docsSubmitted >= $docsRequired;
            $allDone  = $completeness->isComplete() && $docsDone;
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

        {{-- One action, chosen for them. Documents come before the form because
             they are the shorter task and the one HR is actually waiting on. --}}
        @if (!$docsDone)
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
                <a class="zn-btn zn-btn-out" href="{{ route('careers.index') }}">Browse more jobs</a>
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
                            <div class="zn-count" style="font-weight:400">
                                Applied {{ $application->applied_at?->format('F j, Y') }}
                            </div>

                            {{-- Stages reflect the real process: apply, forms and
                                 materials, initial interview, then exams. --}}
                            <div class="zn-track">
                                <div class="zn-step done"><span>Applied</span></div>
                                <div class="zn-step {{ $allDone ? 'done' : 'now' }}"><span>Forms &amp; materials</span></div>
                                <div class="zn-step {{ $allDone ? 'now' : '' }}"><span>Initial interview</span></div>
                                <div class="zn-step"><span>Assessments</span></div>
                            </div>

                            @if ($loop->first)
                                <p class="zn-note">
                                    @if ($allDone)
                                        <strong>Everything is in.</strong> HR reviews your application and will
                                        contact you to schedule an initial interview.
                                    @else
                                        <strong>Almost there.</strong> Once your documents and application form are
                                        finished, HR reviews everything and contacts you about an interview.
                                    @endif
                                </p>
                            @endif
                        </div>
                    @empty
                        <div class="zn-empty">
                            <b>No applications yet</b>
                            Browse our open positions and apply — your profile carries over to every one.
                            <div class="mt-3"><a class="zn-btn zn-btn-sm" href="{{ route('careers.index') }}">See open positions</a></div>
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
                                    @if (!$docsDone)
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

                        {{-- Locked, with no mechanism invented. HR provides these
                             after the initial interview; until that workflow is
                             designed, saying so is the honest state. --}}
                        <a class="zn-task locked" href="{{ route('assessments.index') }}" style="pointer-events:auto">
                            <div class="zn-task-ico"><i class="bi bi-lock-fill"></i></div>
                            <div>
                                <div class="zn-task-name zn-muted">
                                    Assessments <span class="zn-pill zn-pill-later">Locked</span>
                                </div>
                                <div class="zn-task-sub">{{ config('application_form.assessments.gate_message') }}</div>
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
