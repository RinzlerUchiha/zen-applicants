<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Careers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body { background-color: #f4f4f4; }
        .job-card { transition: box-shadow .15s ease, transform .15s ease; }
        .job-card:hover { box-shadow: 0 8px 20px rgba(0,0,0,.08); transform: translateY(-2px); }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-normal text-muted mb-0">Career Opportunities</h3>
            @auth
                <div class="d-flex gap-2">
                    <a href="{{ route('applications.index') }}" class="btn btn-outline-primary btn-sm">My Applications</a>
                    <a href="{{ route('personal.show') }}" class="btn btn-outline-secondary btn-sm">My Profile</a>
                </div>
            @else
                <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">Login</a>
            @endauth
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if ($postings->isEmpty())
            <div class="alert alert-secondary">No open positions at the moment. Please check back soon.</div>
        @else
            <div class="row g-3">
                @foreach ($postings as $posting)
                    <div class="col-md-6">
                        <a href="{{ route('careers.show', $posting->id) }}" class="text-decoration-none text-reset">
                            <div class="card job-card h-100">
                                <div class="card-body">
                                    <h5 class="card-title mb-2">{{ $posting->posting_title }}</h5>
                                    <p class="card-text text-muted small mb-0">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($posting->posting_description), 140) }}
                                    </p>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</body>
</html>