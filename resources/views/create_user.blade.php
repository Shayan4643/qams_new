@extends('index')

@section('content')

<div class="qams-card auth-shell overflow-hidden">
    <div class="row g-0">
        <div class="col-lg-5 auth-side p-4 p-md-5 d-flex flex-column justify-content-between">
            <div>
                <span class="auth-chip mb-3">Admin Panel</span>
                <h3 class="fw-bold mb-3">Create a New User</h3>
                <p class="mb-0 text-light-emphasis">
                    Add student, teacher, or admin accounts with proper role and active status.
                </p>
            </div>
            <div class="mt-4 small">
                <div class="mb-2">- Fast user onboarding</div>
                <div class="mb-2">- Role based management</div>
                <div>- Secure password setup</div>
            </div>
        </div>

        <div class="col-lg-7 auth-form-side p-4 p-md-5">
            <h4 class="fw-semibold mb-1">Add User</h4>
            <p class="text-secondary small mb-4">Complete the required information below.</p>

            <form method="POST" action="/admin/users" enctype="multipart/form-data">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Full Name</label>
                        <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" name="name" required value="{{ old('name') }}" placeholder="Full name">
                        @if($errors->has('name')) <div class="invalid-feedback">{{ $errors->first('name') }}</div> @endif
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Username</label>
                        <input class="form-control {{ $errors->has('user_name') ? 'is-invalid' : '' }}" name="user_name" required value="{{ old('user_name') }}" placeholder="Unique username">
                        @if($errors->has('user_name')) <div class="invalid-feedback">{{ $errors->first('user_name') }}</div> @endif
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Email Address</label>
                        <input type="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" name="email" value="{{ old('email') }}" placeholder="Email address (optional)">
                        @if($errors->has('email')) <div class="invalid-feedback">{{ $errors->first('email') }}</div> @endif
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Phone Number</label>
                        <input class="form-control {{ $errors->has('phone') ? 'is-invalid' : '' }}" name="phone" value="{{ old('phone') }}" placeholder="Phone number (optional)">
                        @if($errors->has('phone')) <div class="invalid-feedback">{{ $errors->first('phone') }}</div> @endif
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Role</label>
                        <select class="form-select {{ $errors->has('role') ? 'is-invalid' : '' }}" name="role" id="create-role-select" onchange="toggleProfileFields(this.value)">
                            <option value="student" {{ old('role') == 'student' ? 'selected' : '' }}>Student</option>
                            <option value="teacher" {{ old('role') == 'teacher' ? 'selected' : '' }}>Teacher</option>
                            <option value="admin"   {{ old('role') == 'admin'   ? 'selected' : '' }}>Admin</option>
                        </select>
                        @if($errors->has('role')) <div class="invalid-feedback">{{ $errors->first('role') }}</div> @endif
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Active</label>
                        <select class="form-select {{ $errors->has('active') ? 'is-invalid' : '' }}" name="active">
                            <option value="yes" {{ old('active','yes') == 'yes' ? 'selected' : '' }}>Yes</option>
                            <option value="no"  {{ old('active') == 'no' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Password</label>
                        <input type="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" name="password" required placeholder="Create password">
                        @if($errors->has('password')) <div class="invalid-feedback">{{ $errors->first('password') }}</div> @endif
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Retype Password</label>
                        <input type="password" class="form-control" name="password_confirmation" required placeholder="Confirm password">
                    </div>
                </div>

                {{-- Student-specific fields --}}
                <div id="student-fields" class="row g-3 mt-1">
                    <div class="col-12"><hr class="border-secondary"><p class="small text-secondary mb-0">Student Details</p></div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Admission Number</label>
                        <input class="form-control" name="admission_number" value="{{ old('admission_number') }}" placeholder="e.g. BC220407207">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Father's Name</label>
                        <input class="form-control" name="father_name" value="{{ old('father_name') }}" placeholder="Father's full name">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Class</label>
                        <select class="form-select" name="class_id">
                            <option value="">Select class</option>
                            @php /** @var \Illuminate\Support\Collection|\App\Models\SchoolClass[] $classes */ @endphp
                            @foreach($classes as $class)
                                @php /** @var \App\Models\SchoolClass $class */ @endphp
                                <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Profile Picture</label>
                        <input type="file" class="form-control" name="profile_picture" accept="image/jpeg,image/png,image/jpg">
                        <div class="form-text text-secondary">JPG/PNG, max 2MB</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium">Assigned Subjects (Selection)</label>
                        <div class="row g-2">
                            @foreach($subjects as $subj)
                            <div class="col-md-4 class-subject-item" data-class="{{ $subj->class_id }}">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="subjects[]" value="{{ $subj->id }}" id="subj-{{ $subj->id }}">
                                    <label class="form-check-label small" for="subj-{{ $subj->id }}">
                                        {{ $subj->name }} ({{ $subj->schoolClass->name ?? 'Unassigned' }})
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Teacher-specific fields --}}
                <div id="teacher-fields" class="row g-3 mt-1" style="display:none;">
                    <div class="col-12"><hr class="border-secondary"><p class="small text-secondary mb-0">Teacher Details</p></div>
                    <div class="col-12">
                        <label class="form-label fw-medium">Education / Qualifications</label>
                        <textarea class="form-control" name="education" rows="2" placeholder="e.g. MS Computer Science, FAST-NUCES">{{ old('education') }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium">Job History</label>
                        <textarea class="form-control" name="job_history" rows="3" placeholder="Previous positions and experience...">{{ old('job_history') }}</textarea>
                    </div>
                </div>

                <button class="btn btn-gradient w-100 py-2 mt-4">Create User</button>
            </form>
        </div>
    </div>
</div>

<script>
function toggleProfileFields(role) {
    document.getElementById('student-fields').style.display = role === 'student' ? '' : 'none';
    document.getElementById('teacher-fields').style.display = role === 'teacher' ? '' : 'none';
}

function filterSubjectsByClass(classId) {
    const items = document.querySelectorAll('.class-subject-item');
    items.forEach(item => {
        if (!classId || item.getAttribute('data-class') === classId) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
            item.querySelector('input').checked = false;
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const roleSelect = document.getElementById('create-role-select');
    const classSelect = document.querySelector('select[name="class_id"]');

    roleSelect.addEventListener('change', (e) => toggleProfileFields(e.target.value));
    classSelect.addEventListener('change', (e) => filterSubjectsByClass(e.target.value));

    toggleProfileFields(roleSelect.value);
    filterSubjectsByClass(classSelect.value);
});
</script>

@endsection