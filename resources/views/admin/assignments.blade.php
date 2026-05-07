@extends('index')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
    <h3 class="mb-0 fw-bold">Teacher Subject Assignment</h3>
    <div class="d-flex gap-2 flex-wrap">
        <a href="/admin/classes" class="btn btn-outline-light btn-sm">Classes</a>
        <a href="/admin/subjects" class="btn btn-outline-light btn-sm">Subjects</a>
        <a href="/admin/assignments" class="btn btn-gradient btn-sm">Teacher Assignments</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="glass-panel p-3 p-md-4 h-100">
            <h5 class="mb-3">Assign Subject</h5>
            <form method="POST" action="/admin/assignments">
                @csrf
                <label class="form-label">Teacher</label>
                <select class="form-select mb-2 @error('teacher_id') is-invalid @enderror" name="teacher_id" required>
                    <option value="">Select teacher</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                            {{ $teacher->name }} ({{ $teacher->active === 'yes' ? 'Active' : 'Inactive' }})
                        </option>
                    @endforeach
                </select>
                @error('teacher_id') <div class="invalid-feedback d-block mb-2">{{ $message }}</div> @enderror

                <label class="form-label">Subject</label>
                <select class="form-select mb-3 @error('subject_id') is-invalid @enderror" name="subject_id" required>
                    <option value="">Select subject</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                            {{ $subject->name }} - {{ $subject->code }} ({{ $subject->schoolClass?->code ?? '-' }})
                        </option>
                    @endforeach
                </select>
                @error('subject_id') <div class="invalid-feedback d-block mb-2">{{ $message }}</div> @enderror

                <button class="btn btn-gradient w-100">Assign Subject</button>
            </form>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="glass-panel p-2 p-md-3">
            <h5 class="px-2 pt-2">Assigned Subjects by Teacher</h5>
            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Teacher</th>
                            <th>Status</th>
                            <th>Subjects</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignments as $teacher)
                            <tr>
                                <td>{{ $teacher->name }}</td>
                                <td>
                                    <span class="badge {{ $teacher->active === 'yes' ? 'bg-success' : 'bg-danger' }}">
                                        {{ $teacher->active === 'yes' ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    @forelse($teacher->taughtSubjects as $subject)
                                        <form method="POST" action="/admin/assignments/{{ $teacher->id }}/{{ $subject->id }}" class="d-inline-flex align-items-center gap-1 me-1 mb-1">
                                            @csrf
                                            @method('DELETE')
                                            <span class="badge text-bg-light border">
                                                {{ $subject->name }} ({{ $subject->code }})
                                            </span>
                                            <button class="btn btn-sm btn-danger">x</button>
                                        </form>
                                    @empty
                                        <span class="text-secondary">No subject assigned.</span>
                                    @endforelse
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center">No teachers found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
