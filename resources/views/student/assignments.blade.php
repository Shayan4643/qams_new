@extends('index')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
    <h3 class="mb-0 fw-bold">Available Assignments</h3>
    <div class="d-flex gap-2 flex-wrap">
        <a href="/student/assignments"         class="btn btn-gradient btn-sm">Assignments</a>
        <a href="/student/assignments/results" class="btn btn-outline-light btn-sm">My Results</a>
    </div>
</div>

<div class="row g-3">
    @forelse($assignments as $assignment)
        @php
            $submission = $submissions[$assignment->id] ?? null;
            $expired    = now()->greaterThan($assignment->deadline);
        @endphp
        <div class="col-lg-6">
            <div class="glass-panel p-3 h-100">
                <h5 class="mb-1">{{ $assignment->title }}</h5>
                <p class="small text-secondary mb-1">{{ $assignment->subject?->name ?? '-' }} | {{ $assignment->teacher?->name ?? '-' }}</p>
                <p class="small {{ $expired ? 'text-danger' : 'text-secondary' }} mb-3">
                    Deadline: {{ $assignment->deadline?->format('d M Y, h:i A') }}
                    @if($expired) <span class="badge bg-danger ms-1">Expired</span> @endif
                </p>
                @if($assignment->description)
                <p class="mb-2 small">{{ $assignment->description }}</p>
                @endif

                @if($assignment->file_path)
                    <div class="mb-3">
                        <a href="/student/assignments/{{ $assignment->id }}/download" class="btn btn-sm btn-outline-info">
                            <i class="fas fa-file-download me-1"></i> Download Instructions
                        </a>
                    </div>
                @endif

                @if($submission && in_array($submission->status, ['submitted', 'graded', 'late_zero'], true))
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <span class="badge {{ $submission->status === 'graded' ? 'bg-success' : ($submission->status === 'late_zero' ? 'bg-danger' : 'bg-info text-dark') }}">
                            {{ ucfirst(str_replace('_', ' ', $submission->status)) }}
                        </span>
                        @if($submission->status === 'graded')
                            <span class="badge bg-primary">Marks: {{ $submission->marks }}/100</span>
                        @endif
                        @if($submission->teacher_feedback)
                            <span class="small text-secondary">Feedback: {{ $submission->teacher_feedback }}</span>
                        @endif
                    </div>

                @elseif($expired)
                    {{-- Trigger late zero on click --}}
                    <form method="POST" action="/student/assignments/{{ $assignment->id }}/submit">
                        @csrf
                        <input type="hidden" name="submission_text" value="">
                        <button class="btn btn-danger btn-sm">Mark as Late (Zero)</button>
                    </form>

                @else
                    {{-- Active submission form with file upload --}}
                    <form method="POST" action="/student/assignments/{{ $assignment->id }}/submit" enctype="multipart/form-data">
                        @csrf
                        <label class="form-label small mb-1">Upload Assignment Solution (PDF or Word)</label>
                        <input type="file" name="submission_file" class="form-control form-control-sm mb-3" accept=".pdf,.doc,.docx" required>

                        <button class="btn btn-gradient btn-sm">Submit Assignment</button>
                    </form>
                @endif
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="glass-panel p-3 text-center">No assignments available right now.</div>
        </div>
    @endforelse
</div>
@endsection
