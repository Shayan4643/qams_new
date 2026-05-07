@extends('index')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
    <h3 class="mb-0 fw-bold">Assignment Management</h3>
    <div class="d-flex gap-2 flex-wrap">
        <a href="/teacher/question-bank" class="btn btn-outline-light btn-sm">Question Bank</a>
        <a href="/teacher/quizzes"       class="btn btn-outline-light btn-sm">Quizzes</a>
        <a href="/teacher/assignments"   class="btn btn-gradient btn-sm">Assignments</a>
        <a href="/teacher/performance"   class="btn btn-outline-light btn-sm">Performance</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="glass-panel p-3 p-md-4 h-100">
            <h5 class="mb-3">Create Assignment</h5>
            <form method="POST" action="/teacher/assignments" enctype="multipart/form-data">
                @csrf
                <label class="form-label">Subject</label>
                <select class="form-select mb-2 @error('subject_id') is-invalid @enderror" name="subject_id" required>
                    <option value="">Select subject</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->name }} ({{ $subject->code }})</option>
                    @endforeach
                </select>
                @error('subject_id') <div class="invalid-feedback d-block mb-2">{{ $message }}</div> @enderror

                <label class="form-label">Title</label>
                <input class="form-control mb-2 @error('title') is-invalid @enderror" name="title" required>
                @error('title') <div class="invalid-feedback d-block mb-2">{{ $message }}</div> @enderror

                <label class="form-label">Description / Instructions</label>
                <textarea class="form-control mb-2 @error('description') is-invalid @enderror" name="description" rows="3"></textarea>
                @error('description') <div class="invalid-feedback d-block mb-2">{{ $message }}</div> @enderror

                <label class="form-label">Deadline</label>
                <input type="datetime-local" class="form-control mb-2 @error('deadline') is-invalid @enderror" name="deadline" required>
                @error('deadline') <div class="invalid-feedback d-block mb-2">{{ $message }}</div> @enderror

                <select class="form-select mb-2" name="published">
                    <option value="yes">Yes</option>
                    <option value="no">No</option>
                </select>

                <label class="form-label">Instruction File (Optional)</label>
                <input type="file" class="form-control mb-3" name="instruction_file" accept=".pdf,.doc,.docx">

                <button class="btn btn-gradient w-100">Create Assignment</button>
            </form>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="glass-panel p-2 p-md-3 mb-3">
            <h5 class="px-2 pt-2">My Assignments</h5>
            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Title</th>
                            <th>Subject</th>
                            <th>Deadline</th>
                            <th>Status</th>
                            <th>Submissions</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignments as $assignment)
                            <tr>
                                <td>{{ $assignment->title }}</td>
                                <td>{{ $assignment->subject?->code ?? '-' }}</td>
                                <td>{{ $assignment->deadline?->format('d M Y, h:i A') }}</td>
                                <td><span class="badge {{ $assignment->published === 'yes' ? 'bg-success' : 'bg-secondary' }}">{{ $assignment->published === 'yes' ? 'Published' : 'Draft' }}</span></td>
                                <td>{{ $assignment->submissions->count() }}</td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        {{-- Publish toggle --}}
                                        <form method="POST" action="/teacher/assignments/{{ $assignment->id }}/toggle-publish">
                                            @csrf
                                            <button class="btn btn-sm {{ $assignment->published === 'yes' ? 'btn-warning' : 'btn-primary' }}">
                                                {{ $assignment->published === 'yes' ? 'Unpublish' : 'Publish' }}
                                            </button>
                                        </form>
                                        {{-- UC-11: Extend Deadline --}}
                                        <button class="btn btn-sm btn-outline-light"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#extend-asgn-{{ $assignment->id }}">
                                            Extend Deadline
                                        </button>

                                        {{-- Delete Assignment --}}
                                        <form method="POST" action="/teacher/assignments/{{ $assignment->id }}" onsubmit="return confirm('Are you sure you want to delete this assignment? All student submissions will be deleted.')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </div>
                                    {{-- Extend Deadline Collapse Form --}}
                                    <div class="collapse mt-2" id="extend-asgn-{{ $assignment->id }}">
                                        <form method="POST" action="/teacher/assignments/{{ $assignment->id }}/extend-deadline" class="d-flex gap-2 align-items-center">
                                            @csrf
                                            <input type="datetime-local" name="deadline" class="form-control form-control-sm" required>
                                            <button class="btn btn-sm btn-gradient">Save</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center">No assignments found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="glass-panel p-2 p-md-3">
            <h5 class="px-2 pt-2">Student Submissions</h5>
            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Assignment</th>
                            <th>Student</th>
                            <th>Submission</th>
                            <th>Status</th>
                            <th>Marks</th>
                            <th>Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($submissions as $submission)
                            <tr>
                                <td>{{ $submission->assignment?->title ?? '-' }}</td>
                                <td>{{ $submission->student?->name ?? '-' }}</td>
                                <td>
                                    @if($submission->file_path)
                                        <a href="/teacher/assignments/submissions/{{ $submission->id }}/download" class="btn btn-sm btn-outline-light">
                                            <i class="fas fa-download me-1"></i> Download
                                        </a>
                                    @elseif($submission->submission_text)
                                        <span class="small text-secondary" title="{{ $submission->submission_text }}">
                                            {{ \Illuminate\Support\Str::limit($submission->submission_text, 40) }}
                                        </span>
                                    @else
                                        <span class="text-secondary">-</span>
                                    @endif
                                </td>
                                <td><span class="badge {{ $submission->status === 'graded' ? 'bg-success' : ($submission->status === 'late_zero' ? 'bg-danger' : 'bg-info text-dark') }}">{{ ucfirst(str_replace('_', ' ', $submission->status)) }}</span></td>
                                <td>{{ $submission->marks }}</td>
                                <td>
                                    <form method="POST" action="/teacher/assignments/submissions/{{ $submission->id }}/grade" class="d-flex gap-2">
                                        @csrf
                                        <input type="number" min="0" max="100" name="marks" class="form-control form-control-sm" value="{{ $submission->marks }}" required style="width:70px;">
                                        <input type="text" name="teacher_feedback" class="form-control form-control-sm" placeholder="Feedback" value="{{ $submission->teacher_feedback }}">
                                        <button class="btn btn-sm btn-gradient">Save</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center">No submissions yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
