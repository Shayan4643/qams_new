@extends('index')

@section('content')

<div class="qams-card auth-shell overflow-hidden">
    <div class="row g-0">
        <div class="col-lg-5 auth-side p-4 p-md-5 d-flex flex-column justify-content-between">
            <div>
                <span class="auth-chip mb-3">New Account</span>
                <h3 class="fw-bold mb-3">Join QAMS Today</h3>
                <p class="mb-0 text-light-emphasis">
                    Create your account and start managing academic tasks with a cleaner workflow.
                </p>
            </div>
            <div class="mt-4 small">
                <div class="mb-2">- Student, Teacher, Admin roles</div>
                <div class="mb-2">- Quick onboarding</div>
                <div>- Easy dashboard access</div>
            </div>
        </div>

        <div class="col-lg-7 auth-form-side p-4 p-md-5">
            <h4 class="fw-semibold mb-1">Register</h4>
            <p class="text-secondary small mb-4">Fill details below to create your account.</p>

            <form method="POST" action="/register">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Full Name</label>
                        <input class="form-control @error('name') is-invalid @enderror"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            placeholder="Full name">
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">User Name</label>
                        <input class="form-control @error('user_name') is-invalid @enderror"
                            name="user_name"
                            value="{{ old('user_name') }}"
                            required
                            placeholder="Unique username">
                        @error('user_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Email Address</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="Email address (optional)">
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Phone Number</label>
                        <input class="form-control @error('phone') is-invalid @enderror"
                            name="phone"
                            value="{{ old('phone') }}"
                            placeholder="Phone number (optional)">
                        @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Role</label>
                        <select class="form-select @error('role') is-invalid @enderror" name="role">
                            <option value="student">Student</option>
                            <option value="teacher">Teacher</option>
                            <option value="admin">Admin</option>
                        </select>
                        @error('role')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Active</label>
                        <select class="form-select" name="active">
                            <option value="yes">Yes</option>
                            <option value="no">No</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Password</label>
                        <input type="password"
                            class="form-control @error('password') is-invalid @enderror"
                            name="password"
                            required
                            placeholder="Create password">
                        @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Retype Password</label>
                        <input type="password"
                            class="form-control"
                            name="password_confirmation"
                            required
                            placeholder="Confirm password">
                    </div>
                </div>

                <button class="btn btn-gradient w-100 py-2 mt-4">Create Account</button>
            </form>

            <p class="mt-4 mb-0 text-center">
                Already have account? <a href="/login" class="fw-semibold text-decoration-none">Login</a>
            </p>
        </div>
    </div>
</div>

@endsection