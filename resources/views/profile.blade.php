@extends('index')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <h3 class="mb-4 fw-bold">My Profile</h3>
        
        <div class="glass-panel p-4">
            <div class="text-center mb-4">
                <div class="position-relative d-inline-block">
                    @if($user->profile_picture)
                        <img src="{{ asset('storage/' . $user->profile_picture) }}" alt="Profile" class="rounded-circle border border-3 border-light shadow" style="width: 120px; height: 120px; object-fit: cover;">
                    @else
                        <div class="rounded-circle bg-gradient d-flex align-items-center justify-content-center text-white border border-3 border-light shadow" style="width: 120px; height: 120px; font-size: 3rem;">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                    @endif
                </div>
                <h4 class="mt-3 mb-1 fw-bold">{{ $user->name }}</h4>
                <p class="text-white text-opacity-75 mb-0"><span class="badge bg-light text-dark">{{ ucfirst($user->role) }}</span> | @ {{ $user->user_name }}</p>
            </div>

            <form action="/profile" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-bold">Full Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                </div>

                <div class="row">
                    @if($user->role === 'student')
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold">Admission Number</label>
                        <input type="text" name="admission_number" class="form-control" value="{{ old('admission_number', $user->admission_number) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold">Father's Name</label>
                        <input type="text" name="father_name" class="form-control" value="{{ old('father_name', $user->father_name) }}">
                    </div>
                    @endif

                    @if($user->role === 'teacher')
                    <div class="col-12 mb-3">
                        <label class="form-label small fw-bold">Education</label>
                        <textarea name="education" class="form-control" rows="2">{{ old('education', $user->education) }}</textarea>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label small fw-bold">Job History</label>
                        <textarea name="job_history" class="form-control" rows="3">{{ old('job_history', $user->job_history) }}</textarea>
                    </div>
                    @endif
                </div>

                <hr class="my-4 border-light opacity-25">
                <h6 class="mb-3 fw-bold text-gradient">Change Password <span class="text-white text-opacity-50 fw-normal small">(leave blank to keep current)</span></h6>

                <div class="mb-3">
                    <label class="form-label small fw-bold">New Password</label>
                    <input type="password" name="password" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Confirm New Password</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>

                <hr class="my-4 border-light opacity-25">

                <div class="mb-4">
                    <label class="form-label small fw-bold">Update Profile Picture</label>
                    <input type="file" name="profile_picture" class="form-control">
                    <div class="form-text text-white text-opacity-50 small">JPG, PNG allowed. Max 2MB.</div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-gradient py-2">Save Profile Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
