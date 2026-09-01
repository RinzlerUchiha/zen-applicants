<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Applications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body { background-color: #f4f4f4; }
        .status-badge { font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 12px; background: #E8F0FE; color: #1B4FB0; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-normal text-muted mb-0">My Applications</h3>
            <a href="{{ route('careers.index') }}" class="btn btn-outline-primary btn-sm">Browse More Jobs</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if ($applications->isEmpty())
            <div class="alert alert-secondary">You haven't applied to any positions yet.</div>
        @else
            <table class="table table-bordered bg-white">
                <thead>
                    <tr>
                        <th>Position</th>
                        <th>REQ ID</th>
                        <th>Date Applied</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($applications as $application)
                        <tr>
                            <td>{{ $application->job_title }}</td>
                            <td>{{ $application->mr_no }}</td>
                            <td>{{ $application->applied_at->format('M d, Y') }}</td>
                            <td><span class="status-badge">{{ $application->status }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</body>
</html>