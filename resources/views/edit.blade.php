@extends('index')

@section('content')

<div class="qams-card shadow-sm mx-auto" style="max-width: 860px;">
    <div class="card-header text-white border-0" style="background: linear-gradient(120deg, #ec4899, #8b5cf6);">
        Edit User — {{ $user->name }}
    </div>

    <div class="card-body p-4">
        <form method="POST" action="/admin/users/{{ $user->id }}" enctype="multipart/form-data">
            @csrf

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Full Name</label>
                    <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" name="name" required value="{{ old('name', $user->name) }}">
                    @if($errors->has('name')) <div class="invalid-feedback">{{ $errors->first('name') }}</div> @endif
                </div>

                <div class="col-md-6">
                    <label class="form-label">Username</label>
                    <input class="form-control {{ $errors->has('user_name') ? 'is-invalid' : '' }}" name="user_name" required value="{{ old('user_name', $user->user_name) }}">
                    @if($errors->has('user_name')) <div class="invalid-feedback">{{ $errors->first('user_name') }}</div> @endif
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" name="email" value="{{ old('email', $user->email) }}">
                    @if($errors->has('email')) <div class="invalid-feedback">{{ $errors->first('email') }}</div> @endif
                </div>

                <div class="col-md-6">
                    <label class="form-label">Phone Number</label>
                    <input class="form-control {{ $errors->has('phone') ? 'is-invalid' : '' }}" name="phone" value="{{ old('phone', $user->phone) }}">
                    @if($errors->has('phone')) <div class="invalid-feedback">{{ $errors->first('phone') }}</div> @endif
                </div>

                <div class="col-md-6">
                    <label class="form-label">Role</label>
                    <select class="form-select {{ $errors->has('role') ? 'is-invalid' : '' }}" name="role" id="edit-role-select" onchange="toggleEditFields(this.value)">
                        <option value="admin"   {{ old('role', $user->role) == 'admin'   ? 'selected' : '' }}>Admin</option>
                        <option value="teacher" {{ old('role', $user->role) == 'teacher' ? 'selected' : '' }}>Teacher</option>
                        <option value="student" {{ old('role', $user->role) == 'student' ? 'selected' : '' }}>Student</option>
                    </select>
                    @if($errors->has('role')) <div class="invalid-feedback d-block">{{ $errors->first('role') }}</div> @endif
                </div>

                <div class="col-md-6">
                    <label class="form-label">Active</label>
                    <select class="form-select {{ $errors->has('active') ? 'is-invalid' : '' }}" name="active">
                        <option value="yes" {{ old('active', $user->active) == 'yes' ? 'selected' : '' }}>Yes</option>
                        <option value="no"  {{ old('active', $user->active) == 'no'  ? 'selected' : '' }}>No</option>
                    </select>
                </div>
            </div>

            {{-- Student profile fields --}}
            <div id="edit-student-fields">
                <hr class="border-secondary">
                <p class="small text-secondary mb-3">Student Details</p>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Admission Number</label>
                        <input class="form-control" name="admission_number" value="{{ old('admission_number', $user->admission_number) }}" placeholder="e.g. BC220407207">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Father's Name</label>
                        <input class="form-control" name="father_name" value="{{ old('father_name', $user->father_name) }}" placeholder="Father's full name">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Class</label>
                        <select class="form-select" name="class_id">
                            <option value="">Select class</option>
                            @php /** @var \Illuminate\Support\Collection|\App\Models\SchoolClass[] $classes */ @endphp
                            @foreach($classes as $class)
                                @php /** @var \App\Models\SchoolClass $class */ @endphp
                                <option value="{{ $class->id }}" {{ old('class_id', $user->class_id) == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Profile Picture</label>
                        @if($user->profile_picture)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $user->profile_picture) }}" alt="Profile" width="60" height="60" style="border-radius:50%; object-fit:cover;">
                            </div>
                        @endif
                        <input type="file" class="form-control" name="profile_picture" accept="image/jpeg,image/png,image/jpg">
                        <div class="form-text text-secondary">Leave empty to keep current picture. JPG/PNG, max 2MB.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium">Assigned Subjects (Selection)</label>
                        <div class="row g-2">
                            @php $enrolledIds = $user->enrolledSubjects->pluck('id')->toArray(); @endphp
                            @foreach($subjects as $subj)
                            <div class="col-md-4 edit-subject-item" data-class="{{ $subj->class_id }}">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="subjects[]" value="{{ $subj->id }}" id="subj-{{ $subj->id }}" {{ in_array($subj->id, $enrolledIds) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="subj-{{ $subj->id }}">
                                        {{ $subj->name }} ({{ $subj->schoolClass->name ?? 'Unassigned' }})
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Teacher profile fields --}}
            <div id="edit-teacher-fields" style="display:none;">
                <hr class="border-secondary">
                <p class="small text-secondary mb-3">Teacher Details</p>
                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <label class="form-label">Education / Qualifications</label>
                        <textarea class="form-control" name="education" rows="2" placeholder="e.g. MS Computer Science">{{ old('education', $user->education) }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Job History</label>
                        <textarea class="form-control" name="job_history" rows="3" placeholder="Previous positions and experience...">{{ old('job_history', $user->job_history) }}</textarea>
                    </div>
                </div>
            </div>

            <button class="btn btn-gradient w-100">Update User</button>
        </form>
    </div>
</div>

<script>
function toggleEditFields(role) {
    document.getElementById('edit-student-fields').style.display = role === 'student' ? '' : 'none';
    document.getElementById('edit-teacher-fields').style.display = role === 'teacher' ? '' : 'none';
}

function filterEditSubjectsByClass(classId) {
    const items = document.querySelectorAll('.edit-subject-item');
    items.forEach(item => {
        if (!classId || item.getAttribute('data-class') === classId) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const roleSelect = document.getElementById('edit-role-select');
    const classSelect = document.querySelector('select[name="class_id"]');

    roleSelect.addEventListener('change', (e) => toggleEditFields(e.target.value));
    classSelect.addEventListener('change', (e) => filterEditSubjectsByClass(e.target.value));

    toggleEditFields(roleSelect.value);
    filterEditSubjectsByClass(classSelect.value);
});
</script>

@endsection