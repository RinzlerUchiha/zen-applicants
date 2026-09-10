{{--
    The Application Form shell: progress rail plus work area.

    All 19 existing pages/*.blade.php already say @extends('layouts.layout')
    and @section('content'), so they inherit the redesign without a single
    edit to any of them. That is deliberate — the shell and main.css carry
    the visual change, and the per-page bodies stay as they are.

    $formSections / $formPercent / $formCounts are shared by the view composer
    registered in AppServiceProvider.
--}}
@extends('layouts.app')

@section('body')
    <div class="zn-shell">
        <aside class="zn-rail">
            @auth
                <div class="zn-rail-progress">
                    <div class="t"><span>Application Form</span><b>{{ $formPercent ?? 0 }}%</b></div>
                    <div class="zn-bar mt-2"><i style="width: {{ $formPercent ?? 0 }}%"></i></div>
                    <div class="t mt-2" style="font-weight:400">
                        <span>{{ $formCounts['done'] ?? 0 }} of {{ $formCounts['total'] ?? 4 }} required sections</span>
                    </div>
                </div>

                @php
                    $blocking = collect($formSections ?? [])->filter(fn ($s) => $s['blocking']);
                    $optional = collect($formSections ?? [])->reject(fn ($s) => $s['blocking']);
                @endphp

                <div class="zn-rail-group">
                    <h4><span>Required to apply</span></h4>
                    @foreach ($blocking as $key => $section)
                        <a class="zn-rail-item {{ request()->routeIs($section['route']) ? 'active' : '' }}"
                           href="{{ Route::has($section['route']) ? route($section['route']) : '#' }}">
                            <span class="zn-mark {{ $section['complete'] ? 'zn-mark-done' : ($section['started'] ? 'zn-mark-part' : 'zn-mark-todo') }}">{!! $section['complete'] ? '&check;' : '' !!}</span>
                            <span>{{ $section['label'] }}</span>
                            @if (!$section['complete'] && count($section['missing']))
                                <span class="zn-count">{{ count($section['missing']) }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>

                <div class="zn-rail-group">
                    <h4><span>Optional</span></h4>
                    @foreach ($optional as $key => $section)
                        <a class="zn-rail-item {{ request()->routeIs($section['route']) ? 'active' : '' }}"
                           href="{{ Route::has($section['route']) ? route($section['route']) : '#' }}">
                            <span class="zn-mark {{ $section['rows'] > 0 && $section['complete'] ? 'zn-mark-done' : 'zn-mark-todo' }}">{!! $section['rows'] > 0 && $section['complete'] ? '&check;' : '' !!}</span>
                            <span>{{ $section['label'] }}</span>
                            @if ($section['rows'] > 0)
                                <span class="zn-count">{{ $section['rows'] }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>

                <div class="zn-rail-group">
                    <h4><span>Send to HR</span></h4>
                    <a class="zn-rail-item {{ request()->routeIs('documents.*') ? 'active' : '' }}"
                       href="{{ route('documents.index') }}">
                        <span class="zn-mark zn-mark-todo"></span>
                        <span>Documents</span>
                    </a>
                </div>

                {{-- Assessments stay visible but locked. The gate itself is a
                     separate design item (anti-cheating / OTP), so nothing here
                     invents one — this simply tells the truth: HR opens them
                     after the initial interview. --}}
                <div class="zn-rail-group">
                    <h4><span>Assessments</span> <span class="zn-pill zn-pill-later">Locked</span></h4>
                    <a class="zn-rail-item" href="{{ route('assessments.index') }}">
                        <span class="zn-mark zn-mark-lock"><i class="bi bi-lock-fill"></i></span>
                        <span>{{ count(config('application_form.assessments.list')) }} assessments</span>
                    </a>
                    <p class="zn-locknote">{{ config('application_form.assessments.gate_message') }}</p>
                </div>
            @endauth
        </aside>

        <main class="zn-work">
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

            @yield('content')
        </main>
    </div>
@endsection
