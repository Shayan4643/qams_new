<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QAMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --qams-primary: #4f46e5;
            --qams-secondary: #06b6d4;
            --qams-accent: #ec4899;
            --qams-text: #ffffff;
            --qams-muted: #e2e8f0;
        }

        body {
            min-height: 100vh;
            color: #ffffff !important;
            background: radial-gradient(circle at 20% 10%, #1d2356 0%, #0b1022 45%, #070b18 100%);
            overflow-x: hidden;
        }

        .page-shell {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            position: relative;
            z-index: 1;
        }

        .content-wrap {
            flex: 1;
        }

        .navbar-animated {
            background: rgba(15, 23, 50, 0.65);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(79, 70, 229, 0.35);
            border-bottom: 2px solid rgba(6, 182, 212, 0.4);
            box-shadow: 0 8px 32px rgba(79, 70, 229, 0.15);
        }

        .brand-dot {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            display: inline-block;
            margin-right: 8px;
            background: linear-gradient(120deg, var(--qams-primary), var(--qams-accent));
            animation: pulseGlow 2.4s infinite;
        }

        .hero-card,
        .feature-card,
        .qams-card {
            border: 1px solid rgba(148, 163, 184, 0.24);
            border-radius: 16px;
            background: rgba(15, 23, 42, 0.66);
            color: #e2e8f0;
            box-shadow: 0 16px 40px rgba(2, 6, 23, 0.45);
            animation: riseUp .7s ease both;
            backdrop-filter: blur(12px);
        }

        .feature-card {
            transition: transform .25s ease, box-shadow .25s ease;
        }

        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 34px rgba(79, 70, 229, 0.35);
        }

        .btn-gradient {
            border: 0;
            color: #fff;
            background: linear-gradient(120deg, var(--qams-primary), var(--qams-secondary));
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .btn-gradient:hover {
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 10px 24px rgba(79, 70, 229, 0.3);
        }

        .footer-gradient {
            color: #e2e8f0;
            background: linear-gradient(120deg, rgba(15, 23, 50, 0.8), rgba(30, 41, 70, 0.75) 40%, rgba(25, 35, 65, 0.8));
            backdrop-filter: blur(12px);
            border-top: 2px solid rgba(79, 70, 229, 0.3);
            box-shadow: 0 -8px 32px rgba(79, 70, 229, 0.1);
        }

        .auth-shell {
            max-width: 980px;
            margin-inline: auto;
        }

        .auth-side {
            color: #fff;
            border-radius: 16px 0 0 16px;
            background: linear-gradient(140deg, var(--qams-primary), var(--qams-secondary), var(--qams-accent));
        }

        .auth-form-side {
            border-radius: 0 16px 16px 0;
            background: rgba(15, 23, 42, 0.82);
            color: #e2e8f0;
        }

        .auth-chip {
            border: 1px solid rgba(255, 255, 255, 0.45);
            border-radius: 999px;
            padding: 4px 10px;
            font-size: .76rem;
            display: inline-block;
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(4px);
        }

        .glass-panel {
            border: 1px solid rgba(148, 163, 184, 0.34);
            border-radius: 18px;
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(14px);
            box-shadow: 0 16px 36px rgba(2, 6, 23, 0.4);
        }

        .stat-glass-card {
            color: #fff;
            border-radius: 14px;
            overflow: hidden;
            position: relative;
            box-shadow: 0 12px 28px rgba(79, 70, 229, 0.22);
            animation: riseUp .7s ease both;
        }

        .stat-glass-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(3px);
        }

        .stat-glass-inner {
            position: relative;
            z-index: 1;
        }

        .stat-gradient-1 {
            background: linear-gradient(130deg, #4f46e5, #7c3aed);
        }

        .stat-gradient-2 {
            background: linear-gradient(130deg, #2563eb, #06b6d4);
        }

        .stat-gradient-3 {
            background: linear-gradient(130deg, #0d9488, #22c55e);
        }

        .stat-gradient-4 {
            background: linear-gradient(130deg, #db2777, #9333ea);
        }

        .stat-gradient-5 {
            background: linear-gradient(130deg, #16a34a, #14b8a6);
        }

        .stat-gradient-6 {
            background: linear-gradient(130deg, #dc2626, #f97316);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #818cf8;
            box-shadow: 0 0 0 .2rem rgba(79, 70, 229, 0.15);
        }

        .form-control,
        .form-select {
            color: #e2e8f0;
            border-color: rgba(148, 163, 184, 0.35);
            background-color: rgba(15, 23, 42, 0.55);
        }

        .form-control::placeholder {
            color: #cbd5e1;
        }

        .table {
            color: #e2e8f0;
        }

        .table-striped>tbody>tr:nth-of-type(odd)>* {
            color: #e2e8f0;
            background-color: rgba(30, 41, 59, 0.44);
        }

        .table-striped>tbody>tr:nth-of-type(even)>* {
            color: #e2e8f0;
            background-color: rgba(15, 23, 42, 0.35);
        }

        .table-bordered> :not(caption)>*>* {
            border-color: rgba(148, 163, 184, 0.24);
        }

        .text-dark {
            color: #f8fafc !important;
        }

        .text-secondary {
            color: #ffffff !important;
        }

        .text-light-emphasis {
            color: #ffffff !important;
        }

        .badge.text-bg-light {
            color: #e2e8f0 !important;
            background-color: rgba(30, 41, 59, 0.85) !important;
        }

        .theme-orb {
            position: fixed;
            border-radius: 999px;
            filter: blur(3px);
            z-index: 0;
            pointer-events: none;
            opacity: 0.35;
        }

        .orb-one {
            width: 320px;
            height: 320px;
            top: -70px;
            left: -80px;
            background: radial-gradient(circle, #6366f1 0%, transparent 70%);
            animation: floatY 9s ease-in-out infinite;
        }

        .orb-two {
            width: 380px;
            height: 380px;
            right: -120px;
            top: 20%;
            background: radial-gradient(circle, #06b6d4 0%, transparent 70%);
            animation: floatY 11s ease-in-out infinite reverse;
        }

        .orb-three {
            width: 280px;
            height: 280px;
            bottom: -90px;
            left: 30%;
            background: radial-gradient(circle, #ec4899 0%, transparent 70%);
            animation: floatY 10s ease-in-out infinite;
        }

        .animate-fade {
            animation: fadeIn .6s ease both;
        }

        @keyframes pulseGlow {

            0%,
            100% {
                transform: scale(1);
                opacity: .8;
            }

            50% {
                transform: scale(1.15);
                opacity: 1;
            }
        }

        @keyframes riseUp {
            from {
                transform: translateY(10px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes floatY {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-18px);
            }
        }

        @media (max-width: 576px) {
            .display-6 {
                font-size: 1.7rem;
            }
        }

        @media (max-width: 991px) {
            .auth-side {
                border-radius: 16px 16px 0 0;
            }

            .auth-form-side {
                border-radius: 0 0 16px 16px;
            }
        }
    </style>
</head>

<body class="bg-light">
    <span class="theme-orb orb-one"></span>
    <span class="theme-orb orb-two"></span>
    <span class="theme-orb orb-three"></span>
    <div class="page-shell">
        <nav class="navbar navbar-expand-lg navbar-animated border-bottom sticky-top">
            <div class="container py-2">
                <a class="navbar-brand fw-semibold text-dark d-flex align-items-center" href="/">
                    <span class="brand-dot"></span>QAMS
                </a>
                <div class="d-flex align-items-center gap-2">
                    @if(auth()->check())
                    <a href="/profile" class="small text-secondary text-decoration-none me-3">
                        <i class="fas fa-user-circle me-1"></i> {{ auth()->user()->name }}
                    </a>
                    <a href="/notifications" class="text-secondary text-decoration-none me-3 position-relative">
                        <i class="fas fa-bell"></i>
                        @php $unreadCount = auth()->user()->notifications()->whereNull('read_at')->count(); @endphp
                        @if($unreadCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                            {{ $unreadCount }}
                        </span>
                        @endif
                    </a>
                    <form method="POST" action="/logout" class="ms-0">
                        @csrf
                        <button class="btn btn-outline-secondary btn-sm">Logout</button>
                    </form>
                    @else
                    <a href="/login" class="btn btn-outline-secondary btn-sm">Login</a>
                    <a href="/register" class="btn btn-gradient btn-sm">Register</a>
                    @endif
                </div>
            </div>
        </nav>

        <main class="content-wrap">
            <div class="container py-4 py-md-5 animate-fade">
                @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @hasSection('content')
                @yield('content')
                @else
                <section class="hero-card p-4 p-md-5 mx-auto text-center" style="max-width: 860px;">
                    <span class="badge text-bg-light border text-secondary mb-3">Quiz & Assignment Management</span>
                    <h1 class="display-6 fw-bold text-dark mb-3">A simple and reliable workspace for academic activities</h1>
                    <p class="lead text-secondary mb-4">
                        Manage quizzes, assignments, and user access in one clean dashboard for students, teachers, and admins.
                    </p>
                    <div class="d-flex justify-content-center gap-2 flex-wrap mb-4">
                        <a href="/login" class="btn btn-dark px-4">Login</a>
                        <a href="/register" class="btn btn-gradient px-4">Create Account</a>
                    </div>
                    <div class="row g-3 text-start mt-2">
                        <div class="col-md-4">
                            <div class="feature-card p-3 h-100">
                                <h6 class="fw-semibold mb-1">Role-based access</h6>
                                <p class="text-secondary small mb-0">Controlled access for Admin, Teacher, and Student users.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="feature-card p-3 h-100">
                                <h6 class="fw-semibold mb-1">Quick workflow</h6>
                                <p class="text-secondary small mb-0">Register, login, and manage records with minimal steps.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="feature-card p-3 h-100">
                                <h6 class="fw-semibold mb-1">Clean interface</h6>
                                <p class="text-secondary small mb-0">Professional layout focused on clarity and usability.</p>
                            </div>
                        </div>
                    </div>
                </section>
                @endif
            </div>
        </main>

        <footer class="footer-gradient mt-auto">
            <div class="container py-3 py-md-4 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                <p class="mb-0 small">© {{ date('Y') }} QAMS. All rights reserved.</p>
                <p class="mb-0 small text-center">Built for smooth quiz and assignment management.</p>
            </div>
        </footer>
    </div>

    @stack('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>

</html>