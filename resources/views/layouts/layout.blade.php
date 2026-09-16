{{--
    Single-column work-area shell (the Documents page).

    The Application Form sections do not use this: layouts.form-section has its
    own sidebar for the form's eight sections. Documents and Assessments are
    their own destinations, reached from the header, so they have no sidebar.
--}}
@extends('layouts.app')

@section('body')
    <main class="zn-canvas">
        <div class="zn-narrow">
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
@endsection
