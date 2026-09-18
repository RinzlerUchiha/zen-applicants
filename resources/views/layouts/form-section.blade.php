{{--
    One section of the Application Form.

    The eight sections are a single form, not eight destinations. The sidebar is
    what makes that legible: it lists all eight in order, shows which are done,
    and carries the form's overall progress. The main column is the section
    itself — its title, what is still needed in it, its fields, and Back / Next.

    Child views supply @section('section') for the fields, and optionally
    @section('section-actions') for buttons that belong in the section's single
    action bar (Personal details' Edit / Save).
--}}
@extends('layouts.app')

@php
    $sections = collect($formSections ?? []);
    $keys = $sections->keys()->values();

    // Derived from the route rather than declared in each view.
    $currentKey = $sections->search(fn ($section) => request()->routeIs($section['route'])) ?: null;
    $index = $keys->search($currentKey);
    $current = $sections->get($currentKey);

    $prevKey = $index > 0 ? $keys[$index - 1] : null;
    $nextKey = $index !== false && $index < $keys->count() - 1 ? $keys[$index + 1] : null;
@endphp

@section('body')
<div class="zn-form-shell">

    @include('layouts.partials.form-rail')

    <main class="zn-form-main">
        @if (session('success'))
            <div class="zn-toast"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="zn-toast error"><i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="zn-toast error">
                <i class="bi bi-exclamation-circle-fill"></i>
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        {{-- The section's own heading. Overall progress is in the sidebar and
             is not repeated here. --}}
        <div class="zn-form-head">
            <p class="zn-crumb">
                Application Form
                @if ($index !== false)
                    · Section {{ $index + 1 }} of {{ $keys->count() }}
                @endif
            </p>
            <p class="zn-page-title">
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

        {{-- One action bar per section. --}}
        <div class="zn-formnav zn-section-nav">
            <span class="zn-formnav-hint">
                <span class="zn-req">*</span> Required to complete your application.
            </span>
            <div class="d-flex gap-2 flex-wrap">
                @yield('section-actions')
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
    </main>
</div>

@include('layouts.partials.unsaved-changes')
@endsection
