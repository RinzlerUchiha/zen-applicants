@extends('layouts.layout')

@section('title', 'Assessments')

@section('content')

@php
    $byKind = collect($assessments)->groupBy('kind');
    $paused = collect($assessments)->where('status', 'interrupted');
    $allDone = $completed === count($assessments);
    $groups = [
        'aptitude' => ['Aptitude tests', 'graded'],
        'questionnaire' => ['Questionnaires', 'no right or wrong answers'],
    ];
@endphp

        <p class="zn-page-title">Assessments</p>
        <p class="zn-page-sub">{{ $completed }} of {{ count($assessments) }} completed.</p>
        <div class="zn-bar mb-3">
            <i style="width: {{ count($assessments) ? round($completed / count($assessments) * 100) : 0 }}%"></i>
        </div>

        {{-- The gate: HR provides the assessments after the initial interview by
             giving the applicant a one-time access code (issued in zen-admin). --}}
        @if ($allDone)
            <section class="zn-assess-panel is-done">
                <div class="zn-assess-panel-icon is-ok"><i class="bi bi-check-circle-fill"></i></div>
                <div class="zn-assess-panel-main">
                    <p class="zn-assess-panel-title">All assessments are complete</p>
                    <p class="zn-assess-panel-text" style="margin:0">Thank you. HR will be in touch about the next step.</p>
                </div>
            </section>
        @elseif ($unlock)
            <section class="zn-assess-panel">
                <div class="zn-assess-panel-icon is-ok"><i class="bi bi-unlock-fill"></i></div>
                <div class="zn-assess-panel-main">
                    <p class="zn-assess-panel-title">Open on this device until {{ $unlock->unlocked_until->format('g:i A') }}</p>
                    <p class="zn-assess-panel-text" style="margin:0">Pick one below. Each is timed and opens with its instructions — the timer only starts when you press Start.</p>
                </div>
            </section>
        @else
            <section class="zn-assess-panel">
                <div class="zn-assess-panel-icon"><i class="bi bi-lock-fill"></i></div>
                <div class="zn-assess-panel-main">
                    <p class="zn-assess-panel-title">These are provided by HR after your initial interview</p>
                    <p class="zn-assess-panel-text">{{ config('application_form.assessments.gate_message') }}</p>
                    @include('pages.partials.assessment-code')
                </div>
            </section>
        @endif

        @if ($paused->isNotEmpty())
            <div class="zn-assess-note">
                <i class="bi bi-pause-circle-fill"></i>
                <div>
                    <b>{{ $paused->count() === 1 ? $paused->first()['label'] . ' is paused' : $paused->count() . ' assessments are paused' }}</b>
                    <span>Your answers are safe and the timer is stopped. Ask HR for a new access code to continue with the time you had left.</span>
                </div>
            </div>
        @endif

        @foreach ($groups as $kind => [$title, $note])
            <p class="zn-assess-group">{{ $title }} <small>{{ $note }}</small></p>
            <div class="zn-assess-grid">
                @foreach ($byKind[$kind] ?? [] as $item)
                    @php
                        $left = intdiv($item['remaining'] + 59, 60);
                        [$line, $cta] = match ($item['status']) {
                            'submitted' => ['Completed', 'View'],
                            'timed_out' => ['Time ran out', 'View'],
                            'active' => ["In progress · $left min left", 'Continue'],
                            'elsewhere' => ["Open in another browser · $left min left", 'Continue'],
                            'interrupted' => ["Paused · $left min left", 'Resume'],
                            'ready' => [$item['minutes'] . ' min · ' . $item['size'], 'Start'],
                            default => [$item['minutes'] . ' min · ' . $item['size'], 'Locked'],
                        };
                    @endphp
                    <a class="zn-assess-card {{ $item['done'] ? 'done' : '' }} is-{{ $item['status'] }}"
                       href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}">
                        <span class="zn-assess-mark">
                            @switch($item['status'])
                                @case('submitted') <i class="bi bi-check-lg"></i> @break
                                @case('timed_out') <i class="bi bi-hourglass-bottom"></i> @break
                                @case('interrupted') <i class="bi bi-pause-fill"></i> @break
                                @case('locked') <i class="bi bi-lock-fill"></i> @break
                                @default <i class="bi bi-play-fill"></i>
                            @endswitch
                        </span>
                        <span class="zn-assess-name">
                            {{ $item['label'] }}
                            <span class="zn-assess-card-sub">{{ $line }}</span>
                        </span>
                        <span class="zn-assess-cta">{{ $cta }}@if (!in_array($cta, ['Locked'], true)) &rarr;@endif</span>
                    </a>
                @endforeach
            </div>
        @endforeach

@endsection
