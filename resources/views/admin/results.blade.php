@extends('index')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
    <h3 class="mb-0 fw-bold">All Results</h3>
    <div class="d-flex gap-2 flex-wrap">
        <a href="/admin/results?tab=quiz"       class="btn btn-sm {{ $tab === 'quiz' ? 'btn-gradient' : 'btn-outline-light' }}">Quiz Results</a>
        <a href="/admin/results?tab=assignment" class="btn btn-sm {{ $tab === 'assignment' ? 'btn-gradient' : 'btn-outline-light' }}">Assignment Results</a>
    </div>
</div>

@if($tab === 'quiz')
<div class="glass-panel p-2 p-md-3">
    <h5 class="px-2 pt-2">Quiz Attempts — All Students</h5>
    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Student</th>
                    <th>Quiz</th>
                    <th>Subject</th>
                    <th>Teacher</th>
                    <th>Score</th>
                    <th>Total Questions</th>
                    <th>Status</th>
                    <th>Submitted At</th>
                </tr>
            </thead>
            <tbody>
                @forelse($quizAttempts as $attempt)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $attempt->student?->name ?? '-' }}</td>
                    <td>{{ $attempt->quiz?->title ?? '-' }}</td>
                    <td>{{ $attempt->quiz?->subject?->code ?? '-' }}</td>
                    <td>{{ $attempt->quiz?->teacher?->name ?? '-' }}</td>
                    <td>{{ $attempt->score }}</td>
                    <td>{{ $attempt->quiz?->questions?->count() ?? '-' }}</td>
                    <td>
                        <span class="badge {{ $attempt->status === 'late_zero' ? 'bg-danger' : 'bg-success' }}">
                            {{ $attempt->status === 'late_zero' ? 'Late (Zero)' : ucfirst($attempt->status) }}
                        </span>
                    </td>
                    <td>{{ $attempt->submitted_at ? $attempt->submitted_at->format('d M Y, h:i A') : '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center">No quiz attempts yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    <div class="mt-3 px-2">
        {{ $quizAttempts->appends(['tab' => 'quiz'])->links() }}
    </div>
</div>

@else
<div class="glass-panel p-2 p-md-3">
    <h5 class="px-2 pt-2">Assignment Submissions — All Students</h5>
    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Student</th>
                    <th>Assignment</th>
                    <th>Subject</th>
                    <th>Teacher</th>
                    <th>Marks</th>
                    <th>Status</th>
                    <th>Feedback</th>
                    <th>Submitted At</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignmentSubmissions as $sub)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $sub->student?->name ?? '-' }}</td>
                    <td>{{ $sub->assignment?->title ?? '-' }}</td>
                    <td>{{ $sub->assignment?->subject?->code ?? '-' }}</td>
                    <td>{{ $sub->assignment?->teacher?->name ?? '-' }}</td>
                    <td>{{ $sub->marks }}</td>
                    <td>
                        <span class="badge {{ $sub->status === 'graded' ? 'bg-success' : ($sub->status === 'late_zero' ? 'bg-danger' : 'bg-info text-dark') }}">
                            {{ ucfirst(str_replace('_', ' ', $sub->status)) }}
                        </span>
                    </td>
                    <td>{{ \Illuminate\Support\Str::limit($sub->teacher_feedback ?? '-', 50) }}</td>
                    <td>{{ $sub->submitted_at ? $sub->submitted_at->format('d M Y, h:i A') : '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center">No assignment submissions yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    <div class="mt-3 px-2">
        {{ $assignmentSubmissions->appends(['tab' => 'assignment'])->links() }}
    </div>
</div>
@endif

@endsection
