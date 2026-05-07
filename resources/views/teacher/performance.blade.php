@extends('index')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
    <h3 class="mb-0 fw-bold">Student Performance Report</h3>
    <div class="d-flex gap-2 flex-wrap">
        <a href="/teacher/question-bank"  class="btn btn-outline-light btn-sm">Question Bank</a>
        <a href="/teacher/quizzes"        class="btn btn-outline-light btn-sm">Quizzes</a>
        <a href="/teacher/assignments"    class="btn btn-outline-light btn-sm">Assignments</a>
        <a href="/teacher/performance"    class="btn btn-gradient btn-sm">Performance</a>
    </div>
</div>

<div class="row g-3">
    @forelse($quizzes as $quiz)
    <div class="col-12">
        <div class="glass-panel p-3 p-md-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                <div>
                    <h5 class="mb-1">{{ $quiz->title }}</h5>
                    <span class="badge bg-secondary">{{ $quiz->subject?->code ?? '-' }}</span>
                    <span class="badge bg-info text-dark ms-1">{{ $quiz->questions->count() }} Questions</span>
                </div>
                <div class="d-flex gap-3 text-center flex-wrap">
                    <div class="stat-glass-card stat-gradient-1 p-2 px-3">
                        <div class="stat-glass-inner">
                            <div class="small">Total Attempts</div>
                            <div class="h5 mb-0 fw-bold">{{ $quiz->attempts_count }}</div>
                        </div>
                    </div>
                    <div class="stat-glass-card stat-gradient-3 p-2 px-3">
                        <div class="stat-glass-inner">
                            <div class="small">Avg Score</div>
                            <div class="h5 mb-0 fw-bold">{{ number_format($quiz->avg_score, 1) }}</div>
                        </div>
                    </div>
                    <div class="stat-glass-card stat-gradient-5 p-2 px-3">
                        <div class="stat-glass-inner">
                            <div class="small">Passed</div>
                            <div class="h5 mb-0 fw-bold">{{ $quiz->pass_count }}</div>
                        </div>
                    </div>
                    <div class="stat-glass-card stat-gradient-6 p-2 px-3">
                        <div class="stat-glass-inner">
                            <div class="small">Failed/Zero</div>
                            <div class="h5 mb-0 fw-bold">{{ $quiz->zero_count }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Student</th>
                            <th>Score</th>
                            <th>Out Of</th>
                            <th>Percentage</th>
                            <th>Status</th>
                            <th>Submitted At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($quiz->attempts as $attempt)
                        <tr>
                            <td>{{ $attempt->student?->name ?? '-' }}</td>
                            <td>{{ $attempt->score }}</td>
                            <td>{{ $quiz->questions->count() }}</td>
                            <td>
                                @php $pct = $quiz->questions->count() > 0 ? round(($attempt->score / $quiz->questions->count()) * 100) : 0; @endphp
                                <div class="progress" style="height:16px; min-width:80px;">
                                    <div class="progress-bar {{ $pct >= 50 ? 'bg-success' : 'bg-danger' }}"
                                         style="width:{{ $pct }}%">{{ $pct }}%</div>
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $attempt->status === 'late_zero' ? 'bg-danger' : ($pct >= 50 ? 'bg-success' : 'bg-warning text-dark') }}">
                                    {{ $attempt->status === 'late_zero' ? 'Late (Zero)' : ($pct >= 50 ? 'Pass' : 'Fail') }}
                                </span>
                            </td>
                            <td>{{ $attempt->submitted_at?->format('d M Y, h:i A') ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-secondary">No attempts yet for this quiz.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="glass-panel p-4 text-center text-secondary">
            No quizzes created yet. Create quizzes to see performance data here.
        </div>
    </div>
    @endforelse
</div>
@endsection
