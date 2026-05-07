@extends('index')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
    <h3 class="mb-0 fw-bold">Teacher Question Bank</h3>
    <div class="d-flex gap-2 flex-wrap">
        <a href="/teacher/question-bank" class="btn btn-gradient btn-sm">Question Bank</a>
        <a href="/teacher/quizzes" class="btn btn-outline-light btn-sm">Quizzes</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="glass-panel p-3 p-md-4 h-100">
            <h5 class="mb-3">Add Question</h5>
            <form method="POST" action="/teacher/question-bank">
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

                <label class="form-label">Question</label>
                <textarea class="form-control mb-2 @error('question_text') is-invalid @enderror" name="question_text" rows="3" required>{{ old('question_text') }}</textarea>
                @error('question_text') <div class="invalid-feedback d-block mb-2">{{ $message }}</div> @enderror

                <label class="form-label">Option A</label>
                <input class="form-control mb-2 @error('option_a') is-invalid @enderror" name="option_a" value="{{ old('option_a') }}" required>
                @error('option_a') <div class="invalid-feedback d-block mb-2">{{ $message }}</div> @enderror

                <label class="form-label">Option B</label>
                <input class="form-control mb-2 @error('option_b') is-invalid @enderror" name="option_b" value="{{ old('option_b') }}" required>
                @error('option_b') <div class="invalid-feedback d-block mb-2">{{ $message }}</div> @enderror

                <label class="form-label">Option C</label>
                <input class="form-control mb-2 @error('option_c') is-invalid @enderror" name="option_c" value="{{ old('option_c') }}">
                @error('option_c') <div class="invalid-feedback d-block mb-2">{{ $message }}</div> @enderror

                <label class="form-label">Option D</label>
                <input class="form-control mb-2 @error('option_d') is-invalid @enderror" name="option_d" value="{{ old('option_d') }}">
                @error('option_d') <div class="invalid-feedback d-block mb-2">{{ $message }}</div> @enderror

                <label class="form-label">Correct Option</label>
                <select class="form-select mb-2 @error('correct_option') is-invalid @enderror" name="correct_option" required>
                    @foreach(['A','B','C','D'] as $opt)
                        <option value="{{ $opt }}" {{ old('correct_option') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                    @endforeach
                </select>
                @error('correct_option') <div class="invalid-feedback d-block mb-2">{{ $message }}</div> @enderror

                <label class="form-label">Active</label>
                <select class="form-select mb-3" name="active">
                    <option value="yes">Yes</option>
                    <option value="no">No</option>
                </select>

                <button class="btn btn-gradient w-100">Save Question</button>
            </form>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="glass-panel p-2 p-md-3">
            <div class="d-flex justify-content-between align-items-center px-2 pt-2 mb-2">
                <h5 class="mb-0">My Questions</h5>
                <select class="form-select form-select-sm" style="width: auto;" onchange="filterQuestions(this.value)">
                    <option value="">All Subjects</option>
                    @foreach($subjects as $subj)
                        <option value="{{ $subj->code }}">{{ $subj->code }}</option>
                    @endforeach
                </select>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Subject</th>
                            <th>Question</th>
                            <th>Correct</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($questions as $question)
                            <tr>
                                <td>{{ $question->subject?->code ?? '-' }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($question->question_text, 80) }}</td>
                                <td>{{ $question->correct_option }}</td>
                                <td><span class="badge {{ $question->active === 'yes' ? 'bg-success' : 'bg-danger' }}">{{ $question->active === 'yes' ? 'Active' : 'Inactive' }}</span></td>
                                <td class="text-nowrap">
                                    <form method="POST" action="/teacher/question-bank/{{ $question->id }}/toggle" style="display:inline-block">
                                        @csrf
                                        <button class="btn btn-warning btn-sm">{{ $question->active === 'yes' ? 'Deactivate' : 'Activate' }}</button>
                                    </form>
                                    <form method="POST" action="/teacher/question-bank/{{ $question->id }}" style="display:inline-block" onsubmit="return confirm('Delete this question?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center">No questions in bank yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function filterQuestions(code) {
    const rows = document.querySelectorAll('tbody tr');
    rows.forEach(row => {
        const subjectCell = row.querySelector('td:first-child');
        if (subjectCell) {
            if (!code || subjectCell.innerText.trim() === code) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    });
}
</script>
@endsection
