@extends('index')

@section('content')

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
    <h3 class="mb-0 fw-bold">Dashboard</h3>
    <span class="badge text-bg-light border text-secondary">Live User Management</span>
</div>

@if(auth()->user()->role === 'teacher')
<div class="glass-panel p-3 p-md-4 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div>
            <h5 class="mb-1">Teacher Workspace</h5>
            <p class="small text-secondary mb-0">Create and manage your question bank and quizzes.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="/teacher/question-bank" class="btn btn-gradient btn-sm">Question Bank</a>
            <a href="/teacher/quizzes"       class="btn btn-outline-light btn-sm">Quizzes</a>
            <a href="/teacher/assignments"   class="btn btn-outline-light btn-sm">Assignments</a>
            <a href="/teacher/performance"   class="btn btn-outline-light btn-sm">Performance</a>
        </div>
    </div>
</div>
@endif

@if(auth()->user()->role === 'student')
<div class="glass-panel p-3 p-md-4 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div>
            <h5 class="mb-1">Student Workspace</h5>
            <p class="small text-secondary mb-0">Attempt published quizzes before deadline and track your results.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="/student/quizzes" class="btn btn-gradient btn-sm">Attempt Quizzes</a>
            <a href="/student/quizzes/results" class="btn btn-outline-light btn-sm">My Results</a>
            <a href="/student/quizzes/performance" class="btn btn-outline-light btn-sm">Performance Report</a>
            <a href="/student/assignments" class="btn btn-outline-light btn-sm">Assignments</a>
        </div>
    </div>
</div>
@endif

@if(auth()->user()->role === 'admin')
<div class="glass-panel p-3 p-md-4 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
        <h5 class="mb-0">Academic Setup</h5>
        <div class="d-flex flex-wrap gap-2">
            <a href="/admin/classes"      class="btn btn-gradient btn-sm">Classes</a>
            <a href="/admin/subjects"     class="btn btn-outline-light btn-sm">Subjects</a>
            <a href="/admin/assignments"  class="btn btn-outline-light btn-sm">Teacher Assignments</a>
            <a href="/admin/results"      class="btn btn-outline-light btn-sm">All Results</a>
            <a href="/admin/reports"      class="btn btn-outline-light btn-sm">Reports &amp; PDF</a>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-6 col-lg-3">
            <div class="stat-glass-card stat-gradient-2 p-3 h-100">
                <div class="stat-glass-inner">
                    <div class="small">Classes</div>
                    <div class="h4 mb-0 fw-bold">{{ $stats['total_classes'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-glass-card stat-gradient-3 p-3 h-100">
                <div class="stat-glass-inner">
                    <div class="small">Subjects</div>
                    <div class="h4 mb-0 fw-bold">{{ $stats['total_subjects'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-glass-card stat-gradient-4 p-3 h-100">
                <div class="stat-glass-inner">
                    <div class="small">Assigned Subjects</div>
                    <div class="h4 mb-0 fw-bold">{{ $stats['assigned_subjects'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-glass-card stat-gradient-5 p-3 h-100">
                <div class="stat-glass-inner">
                    <div class="small">Active Teachers</div>
                    <div class="h4 mb-0 fw-bold">{{ $stats['active_teachers'] ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="glass-panel p-3 p-md-4 mb-4">
    <div class="row g-3">
        <div class="col-6 col-lg-2">
            <div class="stat-glass-card stat-gradient-1 p-3 h-100">
                <div class="stat-glass-inner">
                    <div class="small">Total Users</div>
                    <div class="h4 mb-0 fw-bold">{{ $stats['total_users'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="stat-glass-card stat-gradient-2 p-3 h-100">
                <div class="stat-glass-inner">
                    <div class="small">Students</div>
                    <div class="h4 mb-0 fw-bold">{{ $stats['total_students'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="stat-glass-card stat-gradient-3 p-3 h-100">
                <div class="stat-glass-inner">
                    <div class="small">Teachers</div>
                    <div class="h4 mb-0 fw-bold">{{ $stats['total_teachers'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="stat-glass-card stat-gradient-4 p-3 h-100">
                <div class="stat-glass-inner">
                    <div class="small">Admins</div>
                    <div class="h4 mb-0 fw-bold">{{ $stats['total_admins'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="stat-glass-card stat-gradient-5 p-3 h-100">
                <div class="stat-glass-inner">
                    <div class="small">Active</div>
                    <div class="h4 mb-0 fw-bold">{{ $stats['total_active'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="stat-glass-card stat-gradient-6 p-3 h-100">
                <div class="stat-glass-inner">
                    <div class="small">Blocked</div>
                    <div class="h4 mb-0 fw-bold">{{ $stats['total_blocked'] ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<form method="GET" action="/search" class="mb-3">
    <div class="input-group glass-panel p-2">
        <input id="search-input"
               name="query"
               class="form-control border-0 bg-transparent"
               placeholder="Search student by name"
               value="{{ old('query', $term ?? '') }}">
    </div>
</form>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
    <h5 class="mb-0">Users</h5>

    @auth
        @if(auth()->user()->role === 'admin')
            <a href="/admin/users/create" class="btn btn-gradient">Add User</a>
        @endif
    @endauth
</div>

<div class="glass-panel p-2 p-md-3">
    <div class="table-responsive">
        <table class="table table-bordered table-striped mb-0 align-middle">
            <thead class="table-dark">
                <tr>
                    <th>No.</th>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Status</th>
                    @auth
                        @if(auth()->user()->role === 'admin')
                            <th>Action</th>
                        @endif
                    @endauth
                </tr>
            </thead>

            <tbody id="users-tbody">
                @foreach($users as $u)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $u->name }}</td>
                    <td>{{ $u->user_name }}</td>
                    <td>{{ ucfirst($u->role) }}</td>
                    <td>
                        @if($u->active === 'yes')
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Blocked</span>
                        @endif
                    </td>

                    @auth
                        @if(auth()->user()->role === 'admin')
                        <td class="text-nowrap">
                            <a href="/admin/users/{{ $u->id }}/edit" class="btn btn-primary btn-sm">Edit</a>
                            <form action="/admin/users/{{ $u->id }}/toggle-status"
                                  method="POST"
                                  style="display:inline-block"
                                  onsubmit="return confirm('Change account status for this user?')">
                                @csrf
                                <button class="btn btn-warning btn-sm">
                                    {{ $u->active === 'yes' ? 'Block' : 'Unblock' }}
                                </button>
                            </form>
                            <form action="/admin/users/{{ $u->id }}/delete"
                                  method="POST"
                                  style="display:inline-block"
                                  onsubmit="return confirm('Delete this user?')">
                                @csrf
                                <button class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                        @endif
                    @endauth
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-3">
        {{ $users->links() }}
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('search-input');
    const tbody = document.getElementById('users-tbody');

    if (!input || !tbody) {
        return;
    }

    let timer = null;

    input.addEventListener('keyup', function () {
        clearTimeout(timer);

        timer = setTimeout(() => {
            const term = this.value;

            fetch(`/search?query=${encodeURIComponent(term)}`)
                .then((res) => res.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newTbody = doc.querySelector('#users-tbody');

                    if (newTbody) {
                        tbody.innerHTML = newTbody.innerHTML;
                    }
                })
                .catch(() => {
                    // Keep UI stable when search request fails.
                });
        }, 300);
    });
});
</script>

@endsection