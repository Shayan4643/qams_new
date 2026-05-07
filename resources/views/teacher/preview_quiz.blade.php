@extends('index')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
    <h3 class="mb-0 fw-bold">Quiz Preview: {{ $quiz->title }}</h3>
    <div class="d-flex gap-2">
        <a href="/teacher/quizzes" class="btn btn-outline-light btn-sm">Back to List</a>
        @if($quiz->published === 'no')
            <form action="/teacher/quizzes/{{ $quiz->id }}/toggle-publish" method="POST">
                @csrf
                <button class="btn btn-gradient btn-sm">Publish Quiz Now</button>
            </form>
        @endif
    </div>
</div>

<div class="glass-panel p-3 mb-3">
    <div class="row">
        <div class="col-md-6">
            <p class="mb-1"><strong>Subject:</strong> {{ $quiz->subject?->name }} ({{ $quiz->subject?->code }})</p>
            <p class="mb-1"><strong>Deadline:</strong> {{ $quiz->deadline->format('d M Y, h:i A') }}</p>
        </div>
        <div class="col-md-6">
            <p class="mb-1"><strong>Total Questions:</strong> {{ $quiz->questions->count() }}</p>
            <p class="mb-1"><strong>Status:</strong> 
                <span class="badge {{ $quiz->published === 'yes' ? 'bg-success' : 'bg-warning' }}">
                    {{ $quiz->published === 'yes' ? 'Published' : 'Draft' }}
                </span>
            </p>
        </div>
    </div>
    @if($quiz->description)
        <hr>
        <p class="mb-0"><strong>Description:</strong><br>{{ $quiz->description }}</p>
    @endif
</div>

<h4 class="mb-3">Questions</h4>

@foreach($quiz->questions as $index => $q)
<div class="glass-panel p-3 mb-3 border-start border-4 border-primary">
    <div class="d-flex justify-content-between">
        <h6 class="fw-bold">Question {{ $index + 1 }}</h6>
        <span class="text-muted small">ID: #{{ $q->id }}</span>
    </div>
    <p class="mb-3 fs-5">{{ $q->question_text }}</p>
    
    <div class="row g-2">
        <div class="col-md-6">
            <div class="p-2 border rounded {{ $q->correct_option === 'A' ? 'bg-success bg-opacity-25 border-success' : 'border-light border-opacity-25' }}">
                <strong>A)</strong> {{ $q->option_a }}
                @if($q->correct_option === 'A') <i class="fas fa-check-circle float-end mt-1 text-success"></i> @endif
            </div>
        </div>
        <div class="col-md-6">
            <div class="p-2 border rounded {{ $q->correct_option === 'B' ? 'bg-success bg-opacity-25 border-success' : 'border-light border-opacity-25' }}">
                <strong>B)</strong> {{ $q->option_b }}
                @if($q->correct_option === 'B') <i class="fas fa-check-circle float-end mt-1 text-success"></i> @endif
            </div>
        </div>
        <div class="col-md-6">
            <div class="p-2 border rounded {{ $q->correct_option === 'C' ? 'bg-success bg-opacity-25 border-success' : 'border-light border-opacity-25' }}">
                <strong>C)</strong> {{ $q->option_c }}
                @if($q->correct_option === 'C') <i class="fas fa-check-circle float-end mt-1 text-success"></i> @endif
            </div>
        </div>
        <div class="col-md-6">
            <div class="p-2 border rounded {{ $q->correct_option === 'D' ? 'bg-success bg-opacity-25 border-success' : 'border-light border-opacity-25' }}">
                <strong>D)</strong> {{ $q->option_d }}
                @if($q->correct_option === 'D') <i class="fas fa-check-circle float-end mt-1 text-success"></i> @endif
            </div>
        </div>
    </div>
</div>
@endforeach

@endsection
