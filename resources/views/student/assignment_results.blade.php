@extends('index')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
    <h3 class="mb-0 fw-bold">My Assignment Results</h3>
    <a href="/student/assignments" class="btn btn-outline-light btn-sm">Back to Assignments</a>
</div>

<div class="glass-panel p-2 p-md-3">
    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Assignment</th>
                    <th>Subject</th>
                    <th>Teacher</th>
                    <th>Status</th>
                    <th>Marks</th>
                    <th>Feedback</th>
                </tr>
            </thead>
            <tbody>
                @forelse($submissions as $submission)
                    <tr>
                        <td>{{ $submission->assignment?->title ?? '-' }}</td>
                        <td>{{ $submission->assignment?->subject?->code ?? '-' }}</td>
                        <td>{{ $submission->assignment?->teacher?->name ?? '-' }}</td>
                        <td><span class="badge {{ $submission->status === 'graded' ? 'bg-success' : ($submission->status === 'late_zero' ? 'bg-danger' : 'bg-info text-dark') }}">{{ ucfirst(str_replace('_', ' ', $submission->status)) }}</span></td>
                        <td>{{ $submission->marks }}</td>
                        <td>{{ $submission->teacher_feedback ?: '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center">No assignment results yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
