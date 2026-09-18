{{--
    The Screening pages: Documents and Assessments.

    Same shell as the Application Form (layouts.form-section) so the applicant
    keeps one sidebar everywhere — the form's eight sections, and Screening
    below them. The page itself is a single column, as before.
--}}
@extends('layouts.app')

@section('body')
<div class="zn-form-shell">
    @include('layouts.partials.form-rail')

    <main class="zn-form-main">
        <div class="zn-screening-page">
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
        </div>
    </main>
</div>
@endsection
