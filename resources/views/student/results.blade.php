@extends('index')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
    <h3 class="mb-0 fw-bold">My Quiz Results</h3>
    <a href="/student/quizzes" class="btn btn-outline-light btn-sm">Back to Quizzes</a>
</div>

<div class="glass-panel p-2 p-md-3">
    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Quiz</th>
                    <th>Subject</th>
                    <th>Teacher</th>
                    <th>Score</th>
                    <th>Status</th>
                    <th>Submitted At</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attempts as $attempt)
                    <tr>
                        <td>{{ $attempt->quiz?->title ?? '-' }}</td>
                        <td>{{ $attempt->quiz?->subject?->code ?? '-' }}</td>
                        <td>{{ $attempt->quiz?->teacher?->name ?? '-' }}</td>
                        <td>
                            <span class="fw-bold">{{ $attempt->score }} / {{ $attempt->quiz?->questions->count() ?? 0 }}</span>
                        </td>
                        <td>
                            <span class="badge {{ $attempt->status === 'late_zero' ? 'bg-danger' : 'bg-success' }}">
                                {{ $attempt->status === 'late_zero' ? 'Late (Zero)' : ucfirst($attempt->status) }}
                            </span>
                        </td>
                        <td>{{ $attempt->submitted_at ? $attempt->submitted_at->format('d M Y, h:i A') : '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center">No quiz results yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
