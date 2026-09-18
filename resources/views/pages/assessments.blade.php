@extends('layouts.layout')

@section('title', 'Assessments')

@section('content')

        <p class="zn-page-title">Assessments</p>
        <p class="zn-page-sub">
            {{ $completed }} of {{ count($assessments) }} completed.
        </p>

        {{-- When these open is decided by HR, and the mechanism for that is
             still being designed. Nothing here blocks access — the note simply
             tells the applicant where these sit in the process. --}}
        <div class="zn-assess-note">
            <i class="bi bi-info-circle-fill"></i>
            <div>
                <b>These are provided by HR after your initial interview</b>
                <span>{{ config('application_form.assessments.gate_message') }}</span>
            </div>
        </div>

        <div class="zn-bar mb-3">
            <i style="width: {{ count($assessments) ? round($completed / count($assessments) * 100) : 0 }}%"></i>
        </div>

        <div class="zn-assess-grid">
            @foreach ($assessments as $item)
                <a class="zn-assess-card {{ $item['done'] ? 'done' : '' }}"
                   href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}">
                    <span class="zn-assess-mark">
                        @if ($item['done'])
                            <i class="bi bi-check-lg"></i>
                        @else
                            {{ $loop->iteration }}
                        @endif
                    </span>
                    <span class="zn-assess-name">{{ $item['label'] }}</span>
                    <span class="zn-assess-mins">
                        @if ($item['done'])
                            Completed
                        @else
                            ~{{ $item['minutes'] }} min
                        @endif
                    </span>
                </a>
            @endforeach
        </div>

@endsection
