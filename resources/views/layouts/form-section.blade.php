{{--
    One section of the Application Form.

    The eight sections are a single form, not eight destinations. This layout
    supplies everything that makes that legible on every one of them:

      · where you are          — "Section 3 of 8" and the stepper
      · what's done            — per-section marks in the stepper and rail
      · overall progress       — the bar, from the same rule the rail uses
      · required vs optional   — the section badge, and per-field marks
      · how to move on         — Back / Save & continue

    Child views supply @section('content') for the fields themselves, so the
    chrome never has to be repeated.
--}}
@extends('layouts.layout')

@php
    $sections = collect($formSections ?? []);
    $keys = $sections->keys()->values();

    // Derived from the route rather than declared in each view. A per-view
    // @php() declaration would also collide with the block-form @php blocks
    // some of these views already use.
    $currentKey = $sections->search(fn ($section) => request()->routeIs($section['route'])) ?: null;
    $index = $keys->search($currentKey);
    $current = $sections->get($currentKey);

    $prevKey = $index > 0 ? $keys[$index - 1] : null;
    $nextKey = $index !== false && $index < $keys->count() - 1 ? $keys[$index + 1] : null;
@endphp

@section('content')

    <div class="zn-formtop">
        <div class="zn-formtop-head">
            <div>
                <p class="zn-crumb">
                    Application Form
                    @if ($index !== false)
                        · Section {{ $index + 1 }} of {{ $keys->count() }}
                    @endif
                </p>
                <p class="zn-page-title" style="margin-bottom:0">
                    {{ $current['label'] ?? ($title ?? '') }}
                    @if ($current)
                        @if ($current['blocking'])
                            <span class="zn-pill zn-pill-req">Required</span>
                        @else
                            <span class="zn-pill zn-pill-opt">Optional</span>
                        @endif
                    @endif
                </p>
            </div>

            <div class="zn-formtop-progress">
                <div class="t"><span>Overall</span><b>{{ $formPercent ?? 0 }}%</b></div>
                <div class="zn-bar"><i style="width: {{ $formPercent ?? 0 }}%"></i></div>
                <span class="zn-count">{{ $formCounts['done'] ?? 0 }} of {{ $formCounts['total'] ?? 4 }} required sections done</span>
            </div>
        </div>

        {{-- Horizontal stepper. Doubles as navigation and as the answer to
             "which ones have I finished?" without leaving the page. --}}
        <nav class="zn-stepper" aria-label="Application form sections">
            @foreach ($sections as $key => $section)
                @php
                    $state = $section['complete']
                        ? 'done'
                        : ($section['started'] ? 'part' : 'todo');
                @endphp
                <a class="zn-stepper-item {{ $key === $currentKey ? 'active' : '' }} {{ $state }}"
                   href="{{ Route::has($section['route']) ? route($section['route']) : '#' }}"
                   @if ($key === $currentKey) aria-current="step" @endif>
                    <span class="zn-stepper-mark">
                        @if ($section['complete'])
                            <i class="bi bi-check-lg"></i>
                        @else
                            {{ $loop->iteration }}
                        @endif
                    </span>
                    <span class="zn-stepper-label">{{ $section['label'] }}</span>
                </a>
            @endforeach
        </nav>
    </div>

    @if ($current && !$current['complete'] && count($current['missing']))
        <div class="zn-missing">
            <i class="bi bi-info-circle"></i>
            <div>
                <b>Still needed in this section</b>
                <span>{{ implode(' · ', array_slice($current['missing'], 0, 6)) }}{{ count($current['missing']) > 6 ? ' · …' : '' }}</span>
            </div>
        </div>
    @endif

    @yield('section')

    <div class="zn-formnav">
        <span class="zn-formnav-hint">
            <span class="zn-req">*</span> Required to complete your application. Everything saves as you go.
        </span>
        <div class="d-flex gap-2">
            @if ($prevKey)
                <a class="zn-btn zn-btn-out zn-btn-sm"
                   href="{{ Route::has($sections[$prevKey]['route']) ? route($sections[$prevKey]['route']) : '#' }}">
                    &larr; {{ $sections[$prevKey]['label'] }}
                </a>
            @endif
            @if ($nextKey)
                <a class="zn-btn zn-btn-sm"
                   href="{{ Route::has($sections[$nextKey]['route']) ? route($sections[$nextKey]['route']) : '#' }}">
                    Next: {{ $sections[$nextKey]['label'] }} &rarr;
                </a>
            @else
                <a class="zn-btn zn-btn-sm" href="{{ route('documents.index') }}">Continue to documents &rarr;</a>
            @endif
        </div>
    </div>

@endsection
