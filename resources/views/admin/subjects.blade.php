@extends('index')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
    <h3 class="mb-0 fw-bold">Subjects Management</h3>
    <div class="d-flex gap-2 flex-wrap">
        <a href="/admin/classes" class="btn btn-outline-light btn-sm">Classes</a>
        <a href="/admin/subjects" class="btn btn-gradient btn-sm">Subjects</a>
        <a href="/admin/assignments" class="btn btn-outline-light btn-sm">Teacher Assignments</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="glass-panel p-3 p-md-4 h-100">
            <h5 class="mb-3">Create Subject</h5>
            <form method="POST" action="/admin/subjects">
                @csrf
                <label class="form-label">Class</label>
                <select class="form-select mb-2 @error('class_id') is-invalid @enderror" name="class_id" required>
                    <option value="">Select class</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }} ({{ $class->code }})</option>
                    @endforeach
                </select>
                @error('class_id') <div class="invalid-feedback d-block mb-2">{{ $message }}</div> @enderror

                <label class="form-label">Subject Name</label>
                <input class="form-control mb-2 @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" placeholder="e.g. Data Structures" required>
                @error('name') <div class="invalid-feedback d-block mb-2">{{ $message }}</div> @enderror

                <label class="form-label">Subject Code</label>
                <input class="form-control mb-2 @error('code') is-invalid @enderror" name="code" value="{{ old('code') }}" placeholder="e.g. CS301" required>
                @error('code') <div class="invalid-feedback d-block mb-2">{{ $message }}</div> @enderror

                <label class="form-label">Credit Hours</label>
                <input type="number" min="1" max="6" class="form-control mb-2 @error('credit_hours') is-invalid @enderror" name="credit_hours" value="{{ old('credit_hours', 3) }}" required>
                @error('credit_hours') <div class="invalid-feedback d-block mb-2">{{ $message }}</div> @enderror

                <label class="form-label">Description</label>
                <textarea class="form-control mb-2 @error('description') is-invalid @enderror" name="description" rows="3">{{ old('description') }}</textarea>
                @error('description') <div class="invalid-feedback d-block mb-2">{{ $message }}</div> @enderror

                <label class="form-label">Active</label>
                <select class="form-select mb-3 @error('active') is-invalid @enderror" name="active">
                    <option value="yes" {{ old('active') === 'no' ? '' : 'selected' }}>Yes</option>
                    <option value="no" {{ old('active') === 'no' ? 'selected' : '' }}>No</option>
                </select>
                @error('active') <div class="invalid-feedback d-block mb-2">{{ $message }}</div> @enderror

                <button class="btn btn-gradient w-100">Save Subject</button>
            </form>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="glass-panel p-2 p-md-3">
            <h5 class="px-2 pt-2">Subjects List</h5>
            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Subject</th>
                            <th>Code</th>
                            <th>Class</th>
                            <th>Credits</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subjects as $subject)
                            <tr>
                                <td>{{ $subject->name }}</td>
                                <td>{{ $subject->code }}</td>
                                <td>{{ $subject->schoolClass?->name ?? '-' }}</td>
                                <td>{{ $subject->credit_hours }}</td>
                                <td><span class="badge {{ $subject->active === 'yes' ? 'bg-success' : 'bg-danger' }}">{{ $subject->active === 'yes' ? 'Active' : 'Inactive' }}</span></td>
                                <td class="text-nowrap">
                                    <form method="POST" action="/admin/subjects/{{ $subject->id }}/toggle" style="display:inline-block">
                                        @csrf
                                        <button class="btn btn-warning btn-sm">{{ $subject->active === 'yes' ? 'Deactivate' : 'Activate' }}</button>
                                    </form>
                                    <button class="btn btn-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#edit-subject-{{ $subject->id }}">Edit</button>
                                </td>
                            </tr>
                            <tr class="collapse" id="edit-subject-{{ $subject->id }}">
                                <td colspan="6">
                                    <form method="POST" action="/admin/subjects/{{ $subject->id }}/update" class="row g-2">
                                        @csrf
                                        <div class="col-md-3">
                                            <select class="form-select" name="class_id" required>
                                                @foreach($classes as $class)
                                                    <option value="{{ $class->id }}" {{ $subject->class_id === $class->id ? 'selected' : '' }}>{{ $class->code }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2"><input class="form-control" name="name" value="{{ $subject->name }}" required></div>
                                        <div class="col-md-2"><input class="form-control" name="code" value="{{ $subject->code }}" required></div>
                                        <div class="col-md-1"><input type="number" class="form-control" name="credit_hours" value="{{ $subject->credit_hours }}" min="1" max="6" required></div>
                                        <div class="col-md-2">
                                            <select class="form-select" name="active">
                                                <option value="yes" {{ $subject->active === 'yes' ? 'selected' : '' }}>Yes</option>
                                                <option value="no" {{ $subject->active === 'no' ? 'selected' : '' }}>No</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2"><button class="btn btn-gradient btn-sm w-100">Update</button></div>
                                        <div class="col-12"><input class="form-control" name="description" value="{{ $subject->description }}" placeholder="Description"></div>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center">No subjects found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
