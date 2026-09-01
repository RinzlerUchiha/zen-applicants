<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $posting->posting_title }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body { background-color: #f4f4f4; }
        pre.job-description { white-space: pre-wrap; font-family: inherit; font-size: 14px; }
    </style>
</head>
<body>
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
</body>
</html>