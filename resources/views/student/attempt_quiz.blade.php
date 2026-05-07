@extends('index')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
    <h3 class="mb-0 fw-bold">Attempt Quiz</h3>
    <a href="/student/quizzes" class="btn btn-outline-light btn-sm">Back to Quizzes</a>
</div>

<div class="glass-panel p-3 p-md-4 mb-3">
    <h5 class="mb-1">{{ $quiz->title }}</h5>
    <p class="small text-secondary mb-1">Subject: {{ $quiz->subject?->name ?? '-' }} | Teacher: {{ $quiz->teacher?->name ?? '-' }}</p>
    <p class="small text-secondary mb-0">Deadline: {{ $quiz->deadline?->format('d M Y, h:i A') }}</p>
</div>

<form method="POST" action="/student/quizzes/{{ $quiz->id }}/submit">
    @csrf
    <div class="glass-panel p-3 p-md-4">
        @forelse($quiz->questions as $question)
            <div class="mb-4 pb-3 border-bottom border-secondary-subtle">
                <h6 class="mb-3">Q{{ $loop->iteration }}. {{ $question->question_text }}</h6>
                @foreach(['A' => $question->option_a, 'B' => $question->option_b, 'C' => $question->option_c, 'D' => $question->option_d] as $key => $value)
                    @if($value)
                        <div class="form-check mb-1">
                            <input class="form-check-input" type="radio" name="answers[{{ $question->id }}]" value="{{ $key }}" id="q{{ $question->id }}{{ $key }}">
                            <label class="form-check-label" for="q{{ $question->id }}{{ $key }}">{{ $key }}. {{ $value }}</label>
                        </div>
                    @endif
                @endforeach
            </div>
        @empty
            <p class="mb-0">No questions available for this quiz.</p>
        @endforelse

        @if($quiz->questions->count() > 0)
            <button class="btn btn-gradient w-100">Submit Quiz</button>
        @endif
    </div>
</form>
@endsection
