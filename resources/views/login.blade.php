@extends('index')

@section('content')

<div class="qams-card auth-shell overflow-hidden">
    <div class="row g-0">
        <div class="col-lg-5 auth-side p-4 p-md-5 d-flex flex-column justify-content-between">
            <div>
                <span class="auth-chip mb-3">Welcome Back</span>
                <h3 class="fw-bold mb-3">Sign in to QAMS</h3>
                <p class="mb-0 text-light-emphasis">
                    Access your dashboard to manage quizzes, assignments, and users in one place.
                </p>
            </div>
            <div class="mt-4 small">
                <div class="mb-2">- Fast login flow</div>
                <div class="mb-2">- Secure account access</div>
                <div>- Responsive on all devices</div>
            </div>
        </div>

        <div class="col-lg-7 auth-form-side p-4 p-md-5">
            <h4 class="fw-semibold mb-1">Login</h4>
            <p class="text-secondary small mb-4">Enter your credentials to continue.</p>

            <form method="POST" action="/login">
                @csrf

                <label class="form-label fw-medium">User Name</label>
                <input class="form-control form-control-lg mb-3 {{ $errors->has('user_name') ? 'is-invalid' : '' }}" name="user_name" required value="{{ old('user_name') }}" placeholder="e.g. ali_student">
                @if($errors->has('user_name'))
                    <div class="invalid-feedback d-block mb-2">{{ $errors->first('user_name') }}</div>
                @endif

                <label class="form-label fw-medium">Password</label>
                <input type="password" class="form-control form-control-lg mb-2 {{ $errors->has('password') ? 'is-invalid' : '' }}" name="password" required placeholder="Enter password">
                @if($errors->has('password'))
                    <div class="invalid-feedback d-block mb-2">{{ $errors->first('password') }}</div>
                @endif

                <div class="text-secondary small mb-4">Make sure no one can see your password.</div>

                <button class="btn btn-gradient w-100 py-2">Login</button>
            </form>

            <p class="mt-4 mb-0 text-center">
                New user? <a href="/register" class="fw-semibold text-decoration-none">Create account</a>
            </p>
        </div>
    </div>
</div>

@endsection