@extends('index')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
    <h3 class="mb-0 fw-bold">Available Quizzes</h3>
    <div class="d-flex gap-2 flex-wrap">
        <a href="/student/quizzes" class="btn btn-gradient btn-sm">Quizzes</a>
        <a href="/student/quizzes/results" class="btn btn-outline-light btn-sm">My Results</a>
    </div>
</div>

<div class="glass-panel p-2 p-md-3">
    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Quiz</th>
                    <th>Subject</th>
                    <th>Teacher</th>
                    <th>Questions</th>
                    <th>Deadline</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($quizzes as $quiz)
                    @php
                        $attempt = $attempts[$quiz->id] ?? null;
                        $expired = now()->greaterThan($quiz->deadline);
                    @endphp
                    <tr>
                        <td>{{ $quiz->title }}</td>
                        <td>{{ $quiz->subject?->code ?? '-' }}</td>
                        <td>{{ $quiz->teacher?->name ?? '-' }}</td>
                        <td>{{ $quiz->questions->count() }}</td>
                        <td>{{ $quiz->deadline?->format('d M Y, h:i A') }}</td>
                        <td>
                            @if($attempt && in_array($attempt->status, ['submitted', 'late_zero'], true))
                                <span class="badge bg-success">Submitted</span>
                            @elseif($expired)
                                <span class="badge bg-danger">Expired</span>
                            @else
                                <span class="badge bg-info text-dark">Open</span>
                            @endif
                        </td>
                        <td>
                            @if($attempt && in_array($attempt->status, ['submitted', 'late_zero'], true))
                                <a href="/student/quizzes/results" class="btn btn-secondary btn-sm">View Result</a>
                            @elseif($expired)
                                <a href="/student/quizzes/{{ $quiz->id }}/attempt" class="btn btn-danger btn-sm">Expired</a>
                            @else
                                <a href="/student/quizzes/{{ $quiz->id }}/attempt" class="btn btn-primary btn-sm">Attempt</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center">No published quizzes found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
