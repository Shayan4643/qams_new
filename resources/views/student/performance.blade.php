@extends('index')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
    <h3 class="mb-0 fw-bold">Performance Summary</h3>
    <a href="/student/quizzes/results" class="btn btn-outline-light btn-sm">Detailed Results</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-glass-card stat-gradient-1 p-3 h-100">
            <div class="stat-glass-inner">
                <div class="small">Total Quizzes</div>
                <div class="h4 mb-0 fw-bold">{{ $stats['total_quizzes'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-glass-card stat-gradient-2 p-3 h-100">
            <div class="stat-glass-inner">
                <div class="small">Average Score</div>
                <div class="h4 mb-0 fw-bold">{{ number_format($stats['avg_score'], 1) }}%</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-glass-card stat-gradient-3 p-3 h-100">
            <div class="stat-glass-inner">
                <div class="small">Quizzes Passed</div>
                <div class="h4 mb-0 fw-bold text-success">{{ $stats['passed'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-glass-card stat-gradient-4 p-3 h-100">
            <div class="stat-glass-inner">
                <div class="small">Quizzes Failed</div>
                <div class="h4 mb-0 fw-bold text-danger">{{ $stats['failed'] }}</div>
            </div>
        </div>
    </div>
</div>

<div class="glass-panel p-3">
    <h5 class="mb-3">Performance Over Time</h5>
    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Date</th>
                    <th>Quiz Title</th>
                    <th>Subject</th>
                    <th>Score</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attempts as $attempt)
                <tr>
                    <td>{{ $attempt->created_at->format('d M Y') }}</td>
                    <td>{{ $attempt->quiz->title }}</td>
                    <td>{{ $attempt->quiz->subject->name }}</td>
                    <td>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar {{ $attempt->score >= 50 ? 'bg-success' : 'bg-danger' }}" 
                                 role="progressbar" 
                                 style="width: {{ $attempt->score }}%;" 
                                 aria-valuenow="{{ $attempt->score }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                {{ $attempt->score }}%
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge {{ $attempt->score >= 50 ? 'bg-success' : 'bg-danger' }}">
                            {{ $attempt->score >= 50 ? 'Pass' : 'Fail' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center">No quiz attempts found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
