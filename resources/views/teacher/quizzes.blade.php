@extends('index')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
    <h3 class="mb-0 fw-bold">Quiz Management</h3>
    <div class="d-flex gap-2 flex-wrap">
        <a href="/teacher/question-bank" class="btn btn-outline-light btn-sm">Question Bank</a>
        <a href="/teacher/quizzes"       class="btn btn-gradient btn-sm">Quizzes</a>
        <a href="/teacher/assignments"   class="btn btn-outline-light btn-sm">Assignments</a>
        <a href="/teacher/performance"   class="btn btn-outline-light btn-sm">Performance</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="glass-panel p-3 p-md-4 h-100">
            <h5 class="mb-3">Create Quiz</h5>
            <form method="POST" action="/teacher/quizzes">
                @csrf
                <label class="form-label">Subject</label>
                <select class="form-select mb-2 @error('subject_id') is-invalid @enderror" name="subject_id" required>
                    <option value="">Select subject</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                            {{ $subject->name }} ({{ $subject->code }})
                        </option>
                    @endforeach
                </select>
                @error('subject_id') <div class="invalid-feedback d-block mb-2">{{ $message }}</div> @enderror

                <label class="form-label">Quiz Title</label>
                <input class="form-control mb-2 @error('title') is-invalid @enderror" name="title" value="{{ old('title') }}" required>
                @error('title') <div class="invalid-feedback d-block mb-2">{{ $message }}</div> @enderror

                <label class="form-label">Description</label>
                <textarea class="form-control mb-2 @error('description') is-invalid @enderror" name="description" rows="2">{{ old('description') }}</textarea>
                @error('description') <div class="invalid-feedback d-block mb-2">{{ $message }}</div> @enderror

                <label class="form-label">Deadline</label>
                <input type="datetime-local" class="form-control mb-2 @error('deadline') is-invalid @enderror" name="deadline" value="{{ old('deadline') }}" required>
                @error('deadline') <div class="invalid-feedback d-block mb-2">{{ $message }}</div> @enderror

                <label class="form-label">Select Questions</label>
                <div class="glass-panel p-2 mb-2" style="max-height: 220px; overflow:auto;">
                    @forelse($questions as $question)
                        <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox" name="question_ids[]" value="{{ $question->id }}" id="q-{{ $question->id }}">
                            <label class="form-check-label small" for="q-{{ $question->id }}">
                                [{{ $question->subject?->code ?? '-' }}] {{ \Illuminate\Support\Str::limit($question->question_text, 70) }}
                            </label>
                        </div>
                    @empty
                        <p class="text-secondary small mb-0">No active questions found in your bank.</p>
                    @endforelse
                </div>
                @error('question_ids') <div class="invalid-feedback d-block mb-2">{{ $message }}</div> @enderror

                <button class="btn btn-gradient w-100">Create Quiz</button>
            </form>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="glass-panel p-2 p-md-3">
            <h5 class="px-2 pt-2">My Quizzes</h5>
            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Title</th>
                            <th>Subject</th>
                            <th>Questions</th>
                            <th>Deadline</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($quizzes as $quiz)
                            <tr>
                                <td>{{ $quiz->title }}</td>
                                <td>{{ $quiz->subject?->code ?? '-' }}</td>
                                <td>{{ $quiz->questions->count() }}</td>
                                <td>{{ $quiz->deadline?->format('d M Y, h:i A') }}</td>
                                <td>
                                    <span class="badge {{ $quiz->published === 'yes' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $quiz->published === 'yes' ? 'Published' : 'Draft' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        {{-- Preview --}}
                                        <a href="/teacher/quizzes/{{ $quiz->id }}/preview" class="btn btn-sm btn-info text-white">Preview</a>

                                        {{-- Publish/Unpublish --}}
                                        <form method="POST" action="/teacher/quizzes/{{ $quiz->id }}/toggle-publish">
                                            @csrf
                                            <button class="btn btn-sm {{ $quiz->published === 'yes' ? 'btn-warning' : 'btn-primary' }}">
                                                {{ $quiz->published === 'yes' ? 'Unpublish' : 'Publish' }}
                                            </button>
                                        </form>

                                        {{-- Toggle Results Publication --}}
                                        <form method="POST" action="/teacher/quizzes/{{ $quiz->id }}/toggle-results">
                                            @csrf
                                            <button class="btn btn-sm {{ $quiz->results_published ? 'btn-success' : 'btn-outline-success' }}" title="{{ $quiz->results_published ? 'Results are visible to students' : 'Results are hidden from students' }}">
                                                {{ $quiz->results_published ? 'Hide Results' : 'Show Results' }}
                                            </button>
                                        </form>

                                        {{-- Delete Quiz --}}
                                        <form method="POST" action="/teacher/quizzes/{{ $quiz->id }}" onsubmit="return confirm('Are you sure you want to delete this quiz? All student attempts will be deleted.')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger">Delete</button>
                                        </form>

                                        {{-- UC-11: Extend Deadline --}}
                                        <button class="btn btn-sm btn-outline-light"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#extend-quiz-{{ $quiz->id }}">
                                            Deadline
                                        </button>
                                    </div>
                                    {{-- Extend Deadline Collapse Form --}}
                                    <div class="collapse mt-2" id="extend-quiz-{{ $quiz->id }}">
                                        <form method="POST" action="/teacher/quizzes/{{ $quiz->id }}/extend-deadline" class="d-flex gap-2 align-items-center">
                                            @csrf
                                            <input type="datetime-local" name="deadline" class="form-control form-control-sm" required>
                                            <button class="btn btn-sm btn-gradient">Save</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center">No quizzes created yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
