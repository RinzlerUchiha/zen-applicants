{{--
    Shell for the eleven assessments.

    They are NOT part of the Application Form, so they deliberately do not get
    its rail or stepper. They get their own chrome: what this is, how long it
    takes, and where the applicant stands.

    What the page shows depends on the attempt ($exam, from
    App\Services\AssessmentAttempts::view()):

      locked       no access yet — enter HR's code
      ready        the rules, and a Start button. No questions on the page.
      active       the exam: time left, progress, question map, autosave.
      elsewhere    running in another browser — continue here
      interrupted  stopped; a new HR code resumes it with the time left
      submitted    finished
      timed_out    finished by the clock

    The questions (@yield('content')) are only sent to the browser while the
    attempt is running in this window — or, after the fact, for a questionnaire
    whose answers the applicant may review, as before.
--}}
@extends('layouts.app')

@php
    $meta = $exam->definition;
    $keys = array_keys(config('application_form.assessments.list'));
    $position = array_search($exam->key, $keys, true);
    $aptitude = $meta['kind'] === 'aptitude';
    $minutes = $meta['minutes'];
    $left = intdiv($exam->remaining + 59, 60);
    $graceMinutes = intdiv(config('application_form.assessments.attempts.grace_seconds'), 60);
    $running = $exam->status === 'active' && $exam->token;
    // A started aptitude test is finished before anything else
    // (App\Http\Middleware\AptitudeLock), so while it is unfinished this page
    // offers no way back to the list.
    $aptitudeHeld = $aptitude && in_array($exam->status, ['active', 'elsewhere', 'interrupted'], true);
@endphp

@section('title', $meta['label'])

@section('body')
<main class="zn-canvas {{ $running ? 'zn-exam-running' : '' }}">
    <div class="zn-narrow">

        @if (session('success'))
            <div class="zn-toast"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="zn-toast error"><i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}</div>
        @endif

        @unless ($running || $aptitudeHeld)
            <p style="margin:0 0 12px">
                <a class="zn-link" href="{{ route('assessments.index') }}">&larr; All assessments</a>
            </p>
        @endunless

        <div class="zn-assess-head">
            <div>
                <p class="zn-crumb">
                    {{ $aptitude ? 'Aptitude test' : 'Questionnaire' }} · {{ $position + 1 }} of {{ count($keys) }}
                </p>
                <p class="zn-page-title" style="margin-bottom:0">
                    {{ $meta['label'] }}
                    @if ($exam->status === 'submitted')
                        <span class="zn-pill zn-pill-ok"><i class="bi bi-check-lg"></i> Completed</span>
                    @elseif ($exam->status === 'timed_out')
                        <span class="zn-pill zn-pill-caution"><i class="bi bi-hourglass-bottom"></i> Time ran out</span>
                    @elseif ($exam->status === 'interrupted')
                        <span class="zn-pill zn-pill-caution"><i class="bi bi-pause-fill"></i> Paused</span>
                    @endif
                </p>
            </div>
            <div class="zn-assess-meta">
                <span class="zn-assess-time"><i class="bi bi-clock"></i> {{ $minutes }}-minute limit</span>
                <span class="zn-assess-time"><i class="bi bi-list-ol"></i> {{ $meta['size'] }}</span>
            </div>
        </div>

        @switch($exam->status)

            @case('locked')
                <section class="zn-assess-panel">
                    <div class="zn-assess-panel-icon"><i class="bi bi-lock-fill"></i></div>
                    <div class="zn-assess-panel-main">
                        <p class="zn-assess-panel-title">Enter your access code to open this assessment</p>
                        <p class="zn-assess-panel-text">{{ config('application_form.assessments.gate_message') }}</p>
                        @include('pages.partials.assessment-code', ['assessmentKey' => $exam->key])
                    </div>
                </section>
                @break

            @case('ready')
                <section class="zn-assess-rules">
                    <p class="zn-assess-rules-title">Before you start</p>
                    <div class="zn-assess-rules-grid">
                        <div>
                            <i class="bi bi-stopwatch"></i>
                            <b>{{ $minutes }} minutes</b>
                            <span>
                                The timer starts when you press Start and keeps running until you submit.
                                @unless ($aptitude)
                                    Most people finish in about {{ $meta['typical'] }} minutes.
                                @endunless
                                When time runs out, what you have answered is submitted for you.
                            </span>
                        </div>
                        <div>
                            <i class="bi bi-{{ $aptitude ? 'mortarboard' : 'person' }}"></i>
                            @if ($aptitude)
                                <b>Graded — work on your own</b>
                                <span>No help, notes or other websites.
                                    @if (!empty($meta['shuffle']['questions']))
                                        Questions and choices are in a different order for each applicant.
                                    @endif
                                    You can leave a question and come back to it. Once you start, finish and
                                    submit this test before moving on to another assessment.</span>
                            @else
                                <b>No right or wrong answers</b>
                                <span>Answer as you really are, not as you think you should be. Every item needs an answer.</span>
                            @endif
                        </div>
                        <div>
                            <i class="bi bi-arrows-fullscreen"></i>
                            <b>One window, fullscreen</b>
                            <span>The assessment opens in fullscreen where your browser allows it. If you leave
                                fullscreen, the questions are hidden until you return — the timer keeps running.</span>
                        </div>
                        <div>
                            <i class="bi bi-cloud-check"></i>
                            <b>Saved as you go</b>
                            <span>If your connection drops, keep going — your answers save when it's back.
                                If the page closes (a power cut, say), come back within {{ $graceMinutes }} minutes to carry on;
                                after that it pauses, and HR can give you a code to continue with the time you had left.</span>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('assessments.start', $exam->key) }}" class="zn-assess-start">
                        @csrf
                        <button type="submit" class="zn-btn zn-btn-lg"><i class="bi bi-play-fill"></i> Start {{ $meta['label'] }}</button>
                        <span class="zn-muted">One attempt. Once you submit, your answers are final.</span>
                    </form>
                    @if ($exam->unlockedUntil)
                        <p class="zn-assess-rules-foot">Your access is open until {{ $exam->unlockedUntil->format('g:i A') }}.</p>
                    @endif
                </section>
                @break

            @case('elsewhere')
                <section class="zn-assess-panel">
                    <div class="zn-assess-panel-icon"><i class="bi bi-window-stack"></i></div>
                    <div class="zn-assess-panel-main">
                        <p class="zn-assess-panel-title">This assessment is open in another browser</p>
                        <p class="zn-assess-panel-text">You have {{ $left }} {{ Str::plural('minute', $left) }} left and your answers are saved.
                            Continue where it's open, or continue here —
                            {{ $exam->canTakeOver ? 'the other browser will stop.' : 'for that you need an access code from HR.' }}</p>
                        @if ($exam->canTakeOver)
                            <form method="POST" action="{{ route('assessments.start', $exam->key) }}" class="zn-assess-start">
                                @csrf
                                <button type="submit" class="zn-btn">Continue here</button>
                            </form>
                        @else
                            @include('pages.partials.assessment-code', ['assessmentKey' => $exam->key])
                        @endif
                    </div>
                </section>
                @break

            @case('interrupted')
                <section class="zn-assess-panel">
                    <div class="zn-assess-panel-icon is-caution"><i class="bi bi-pause-circle-fill"></i></div>
                    <div class="zn-assess-panel-main">
                        <p class="zn-assess-panel-title">Paused — your answers are safe</p>
                        <p class="zn-assess-panel-text">The page was closed for more than {{ $graceMinutes }} minutes, so the timer stopped at
                            {{ $exam->attempt->interrupted_at->format('g:i A') }}. Nothing was lost: you have
                            <b>{{ $left }} {{ Str::plural('minute', $left) }}</b> left and every answer you saved is kept.</p>
                        @if ($exam->canResume)
                            <form method="POST" action="{{ route('assessments.start', $exam->key) }}" class="zn-assess-start">
                                @csrf
                                <button type="submit" class="zn-btn zn-btn-lg"><i class="bi bi-play-fill"></i> Resume — {{ $left }} {{ Str::plural('minute', $left) }} left</button>
                            </form>
                        @else
                            <p class="zn-assess-panel-text">To continue, ask HR for a new access code and enter it here.</p>
                            @include('pages.partials.assessment-code', ['assessmentKey' => $exam->key])
                        @endif
                    </div>
                </section>
                @break

            @case('submitted')
                <section class="zn-assess-panel is-done">
                    <div class="zn-assess-panel-icon is-ok"><i class="bi bi-check-circle-fill"></i></div>
                    <div class="zn-assess-panel-main">
                        <p class="zn-assess-panel-title">Submitted — thank you</p>
                        <p class="zn-assess-panel-text">Your answers are final{{ $exam->attempt?->ended_at ? ' (received ' . $exam->attempt->ended_at->format('M j, g:i A') . ')' : '' }}.
                            There's nothing more to do here.</p>
                        <a class="zn-btn zn-btn-out" href="{{ route('assessments.index') }}">Back to all assessments</a>
                    </div>
                </section>
                @break

            @case('timed_out')
                <section class="zn-assess-panel">
                    <div class="zn-assess-panel-icon is-caution"><i class="bi bi-hourglass-bottom"></i></div>
                    <div class="zn-assess-panel-main">
                        <p class="zn-assess-panel-title">Time ran out</p>
                        <p class="zn-assess-panel-text">
                            @if ($exam->attempt?->result_saved)
                                The answers you had given were submitted for you. There's nothing more to do here.
                            @else
                                It wasn't complete when time ran out, so no answers were recorded. HR can see this.
                            @endif
                        </p>
                        <a class="zn-btn zn-btn-out" href="{{ route('assessments.index') }}">Back to all assessments</a>
                    </div>
                </section>
                @break

        @endswitch

        @if ($running)
            {{-- The running exam: the server's clock, progress, the question map
                 and autosave, kept in view while scrolling. --}}
            <div class="zn-exam-bar" role="region" aria-label="Assessment status">
                <div class="zn-exam-clock" aria-live="off">
                    <i class="bi bi-stopwatch" aria-hidden="true"></i>
                    <span id="zn-exam-timer">{{ intdiv($exam->remaining, 60) }}:{{ str_pad($exam->remaining % 60, 2, '0', STR_PAD_LEFT) }}</span>
                    <span class="zn-exam-clock-label">left</span>
                </div>
                <div class="zn-exam-progress">
                    <span id="zn-exam-progress"></span>
                    <div class="zn-bar"><i id="zn-exam-progress-bar" style="width:0%"></i></div>
                </div>
                <div class="zn-exam-actions">
                    <button type="button" class="zn-btn zn-btn-out zn-btn-sm" id="zn-exam-map-toggle" aria-expanded="false" aria-controls="zn-exam-map">
                        <i class="bi bi-grid-3x3-gap"></i> Questions
                    </button>
                    <button type="button" class="zn-btn zn-btn-sm" id="zn-exam-submit">Submit</button>
                </div>
                <p class="zn-exam-saved" id="zn-exam-saved" data-state="" role="status" aria-live="polite">Autosave is on</p>
                <div class="zn-exam-map" id="zn-exam-map">
                    <div class="zn-exam-map-head">
                        <span><i class="zn-exam-key is-answered"></i> Answered <i class="zn-exam-key"></i> Not yet</span>
                        <button type="button" class="zn-link" id="zn-exam-next-unanswered">Next unanswered &rarr;</button>
                    </div>
                    <div class="zn-exam-map-list" id="zn-exam-map-list"></div>
                </div>
            </div>
            @php
                $examConfig = [
                    'key' => $exam->key,
                    'token' => $exam->token,
                    'remaining' => $exam->remaining,
                    'heartbeat' => config('application_form.assessments.attempts.heartbeat_seconds'),
                    'pingUrl' => route('assessments.ping', $exam->key),
                    'submitUrl' => route(str_replace('.show', '.store', $meta['route'])),
                ];
            @endphp
            <script type="application/json" id="zn-exam-config">@json($examConfig)</script>
        @endif

        @if ($exam->showQuestions)
            @if (!$running)
                <p class="zn-assess-review-title">Your answers</p>
            @endif
            <div class="zn-assess-body">
                @yield('content')
            </div>
        @endif

        @if ($running)
            <div class="zn-exam-overlay" id="zn-exam-fullscreen" hidden>
                <div class="zn-exam-overlay-box" role="dialog" aria-modal="true" aria-labelledby="zn-exam-fs-title">
                    <i class="bi bi-arrows-fullscreen" aria-hidden="true"></i>
                    <b id="zn-exam-fs-title" data-fs-title></b>
                    <p data-fs-body></p>
                    <button type="button" class="zn-btn" data-fs-action></button>
                    <button type="button" class="zn-link" data-fs-skip hidden>Continue without fullscreen</button>
                </div>
            </div>
            <div class="zn-exam-overlay" id="zn-exam-lock" hidden>
                <div class="zn-exam-overlay-box" role="dialog" aria-modal="true" aria-labelledby="zn-exam-lock-title">
                    <i class="bi bi-window-stack" aria-hidden="true"></i>
                    <b id="zn-exam-lock-title">Open in another window</b>
                    <p>This assessment is now open in another tab or window. Only one can be used at a time — continue there, or use this one instead. Your answers are saved either way.</p>
                    <button type="button" class="zn-btn" data-lock-action>Use this window</button>
                </div>
            </div>
            <div class="zn-exam-overlay" id="zn-exam-submitting" hidden>
                <div class="zn-exam-overlay-box" role="alertdialog" aria-modal="true" aria-live="assertive">
                    <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                    <b data-submitting-text>Submitting your answers…</b>
                </div>
            </div>

            <div class="modal fade" id="zn-exam-confirm" tabindex="-1" aria-labelledby="zn-exam-confirm-title" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="modal-title fs-6" id="zn-exam-confirm-title">Submit {{ $meta['label'] }}?</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p id="zn-exam-confirm-note" style="margin:0 0 6px"></p>
                            <p class="zn-muted" style="margin:0">Once submitted, your answers are final and can't be changed.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="zn-btn zn-btn-out" data-bs-dismiss="modal">Keep working</button>
                            <button type="button" class="zn-btn" data-confirm>Submit answers</button>
                        </div>
                    </div>
                </div>
            </div>

            @push('scripts')
                <script src="{{ asset('zn-exam.js') }}?v={{ @filemtime(public_path('zn-exam.js')) ?: 1 }}"></script>
            @endpush
        @endif

    </div>
</main>
@endsection
