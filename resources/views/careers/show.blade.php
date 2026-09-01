@extends('layouts.guest')

@section('title', $posting->posting_title)

@section('content')
<style>
    pre.job-description { white-space: pre-wrap; font-family: inherit; font-size: 14px; }
</style>

<div class="container py-5" style="max-width: 760px;">
    <a href="{{ route('careers.index') }}" class="text-decoration-none small">&larr; Back to all openings</a>

    <div class="card mt-3">
        <div class="card-body">
            <h3 class="mb-3">{{ $posting->posting_title }}</h3>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <pre class="job-description">{{ $posting->posting_description }}</pre>

            <div class="mt-4">
                @auth
                    <form method="POST" action="{{ route('careers.apply', $posting->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary px-4">Apply Now</button>
                    </form>
                @else
                    <a href="{{ route('register', ['job' => $posting->id]) }}" class="btn btn-primary px-4">Apply Now</a>
                @endauth
            </div>
        </div>
    </div>
</div>
@endsection