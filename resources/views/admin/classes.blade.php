@extends('index')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
    <h3 class="mb-0 fw-bold">Admin Academic Setup</h3>
    <div class="d-flex gap-2 flex-wrap">
        <a href="/admin/classes" class="btn btn-gradient btn-sm">Classes</a>
        <a href="/admin/subjects" class="btn btn-outline-light btn-sm">Subjects</a>
        <a href="/admin/assignments" class="btn btn-outline-light btn-sm">Teacher Assignments</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="glass-panel p-3 p-md-4 h-100">
            <h5 class="mb-3">Create Class</h5>
            <form method="POST" action="/admin/classes">
                @csrf
                <label class="form-label">Class Name</label>
                <input class="form-control mb-2 @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" placeholder="e.g. BSCS 5th" required>
                @error('name') <div class="invalid-feedback d-block mb-2">{{ $message }}</div> @enderror

                <label class="form-label">Class Code</label>
                <input class="form-control mb-2 @error('code') is-invalid @enderror" name="code" value="{{ old('code') }}" placeholder="e.g. BSCS-5" required>
                @error('code') <div class="invalid-feedback d-block mb-2">{{ $message }}</div> @enderror

                <label class="form-label">Section</label>
                <input class="form-control mb-2 @error('section') is-invalid @enderror" name="section" value="{{ old('section') }}" placeholder="A / B">
                @error('section') <div class="invalid-feedback d-block mb-2">{{ $message }}</div> @enderror

                <label class="form-label">Description</label>
                <textarea class="form-control mb-2 @error('description') is-invalid @enderror" name="description" rows="3" placeholder="Optional notes">{{ old('description') }}</textarea>
                @error('description') <div class="invalid-feedback d-block mb-2">{{ $message }}</div> @enderror

                <label class="form-label">Active</label>
                <select class="form-select mb-3 @error('active') is-invalid @enderror" name="active">
                    <option value="yes" {{ old('active') === 'no' ? '' : 'selected' }}>Yes</option>
                    <option value="no" {{ old('active') === 'no' ? 'selected' : '' }}>No</option>
                </select>
                @error('active') <div class="invalid-feedback d-block mb-2">{{ $message }}</div> @enderror

                <button class="btn btn-gradient w-100">Save Class</button>
            </form>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="glass-panel p-2 p-md-3">
            <h5 class="px-2 pt-2">Classes List</h5>
            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Section</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($classes as $class)
                            <tr>
                                <td>{{ $class->name }}</td>
                                <td>{{ $class->code }}</td>
                                <td>{{ $class->section ?: '-' }}</td>
                                <td>
                                    <span class="badge {{ $class->active === 'yes' ? 'bg-success' : 'bg-danger' }}">
                                        {{ $class->active === 'yes' ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-nowrap">
                                    <form method="POST" action="/admin/classes/{{ $class->id }}/toggle" style="display:inline-block">
                                        @csrf
                                        <button class="btn btn-warning btn-sm">{{ $class->active === 'yes' ? 'Deactivate' : 'Activate' }}</button>
                                    </form>
                                    <button class="btn btn-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#edit-class-{{ $class->id }}">Edit</button>
                                </td>
                            </tr>
                            <tr class="collapse" id="edit-class-{{ $class->id }}">
                                <td colspan="5">
                                    <form method="POST" action="/admin/classes/{{ $class->id }}/update" class="row g-2">
                                        @csrf
                                        <div class="col-md-3"><input class="form-control" name="name" value="{{ $class->name }}" required></div>
                                        <div class="col-md-2"><input class="form-control" name="code" value="{{ $class->code }}" required></div>
                                        <div class="col-md-2"><input class="form-control" name="section" value="{{ $class->section }}"></div>
                                        <div class="col-md-3"><input class="form-control" name="description" value="{{ $class->description }}"></div>
                                        <div class="col-md-2">
                                            <select class="form-select" name="active">
                                                <option value="yes" {{ $class->active === 'yes' ? 'selected' : '' }}>Yes</option>
                                                <option value="no" {{ $class->active === 'no' ? 'selected' : '' }}>No</option>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <button class="btn btn-gradient btn-sm">Update Class</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center">No classes found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
