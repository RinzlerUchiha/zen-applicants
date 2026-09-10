{{--
    Public-width shell: careers listing and job detail.

    Kept as its own layout rather than retired, because careers/index and
    careers/show already extend it — this way they pick up the redesign with
    no edit. It is now a thin wrapper over layouts.app, so signed-out and
    signed-in pages share one header and one visual language.
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
