@extends('index')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
    <h3 class="mb-0 fw-bold">System Reports</h3>
    <div class="d-flex gap-2 flex-wrap">
        <a href="/admin/reports?type=students" class="btn btn-sm {{ $reportType === 'students' ? 'btn-gradient' : 'btn-outline-light' }}">Students</a>
        <a href="/admin/reports?type=teachers" class="btn btn-sm {{ $reportType === 'teachers' ? 'btn-gradient' : 'btn-outline-light' }}">Teachers</a>
        <a href="/admin/reports?type=subjects" class="btn btn-sm {{ $reportType === 'subjects' ? 'btn-gradient' : 'btn-outline-light' }}">Subjects</a>
        <a href="/admin/reports/pdf?type={{ $reportType }}&class_id={{ $classId }}&subject_id={{ $subjectId }}"
           class="btn btn-sm btn-danger" target="_blank">
            ⬇ PDF
        </a>
        <a href="/admin/reports/csv?type={{ $reportType }}&class_id={{ $classId }}&subject_id={{ $subjectId }}"
           class="btn btn-sm btn-success">
            ⬇ CSV
        </a>
    </div>
</div>

@if($reportType === 'students')
<div class="glass-panel p-3 mb-3">
    <form method="GET" action="/admin/reports" class="d-flex gap-2 flex-wrap align-items-end">
        <input type="hidden" name="type" value="students">
        <div>
            <label class="form-label mb-1 small">Filter by Class</label>
            <select name="class_id" class="form-select form-select-sm" style="min-width:180px;">
                <option value="">All Classes</option>
                @php /** @var \Illuminate\Support\Collection|\App\Models\SchoolClass[] $classes */ @endphp
                @foreach($classes as $class)
                    @php /** @var \App\Models\SchoolClass $class */ @endphp
                    <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn btn-gradient btn-sm">Filter</button>
        <a href="/admin/reports?type=students" class="btn btn-outline-light btn-sm">Reset</a>
    </form>
</div>

<div class="glass-panel p-2 p-md-3">
    <h5 class="px-2 pt-2">Student Report</h5>
    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Admission No.</th>
                    <th>Father Name</th>
                    <th>Class</th>
                    <th>Quiz Attempts</th>
                    <th>Avg Quiz Score</th>
                    <th>Graded Assignments</th>
                    <th>Avg Assignment Marks</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['students'] as $s)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $s->name }}</td>
                    <td>{{ $s->user_name }}</td>
                    <td>{{ $s->admission_number ?? '-' }}</td>
                    <td>{{ $s->father_name ?? '-' }}</td>
                    <td>{{ $s->schoolClass->name ?? '-' }}</td>
                    <td>{{ $s->quiz_attempts_count }}</td>
                    <td>{{ number_format($s->avg_quiz_score, 1) }}</td>
                    <td>{{ $s->graded_assignments }}</td>
                    <td>{{ number_format($s->avg_assignment_marks, 1) }}</td>
                    <td><span class="badge {{ $s->active === 'yes' ? 'bg-success' : 'bg-danger' }}">{{ $s->active === 'yes' ? 'Active' : 'Blocked' }}</span></td>
                </tr>
                @empty
                <tr><td colspan="11" class="text-center">No students found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@elseif($reportType === 'teachers')
<div class="glass-panel p-2 p-md-3">
    <h5 class="px-2 pt-2">Teacher Report</h5>
    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Education</th>
                    <th>Assigned Subjects</th>
                    <th>Quizzes Created</th>
                    <th>Assignments Created</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['teachers'] as $t)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $t->name }}</td>
                    <td>{{ $t->user_name }}</td>
                    <td>{{ $t->education ?? '-' }}</td>
                    <td>{{ $t->taughtSubjects->count() }}</td>
                    <td>{{ $t->quizzes_count }}</td>
                    <td>{{ $t->assignments_count }}</td>
                    <td><span class="badge {{ $t->active === 'yes' ? 'bg-success' : 'bg-danger' }}">{{ $t->active === 'yes' ? 'Active' : 'Blocked' }}</span></td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center">No teachers found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@elseif($reportType === 'subjects')
<div class="glass-panel p-3 mb-3">
    <form method="GET" action="/admin/reports" class="d-flex gap-2 flex-wrap align-items-end">
        <input type="hidden" name="type" value="subjects">
        <div>
            <label class="form-label mb-1 small">Filter by Subject</label>
            <select name="subject_id" class="form-select form-select-sm" style="min-width:200px;">
                <option value="">All Subjects</option>
                @php /** @var \Illuminate\Support\Collection|\App\Models\Subject[] $subjects */ @endphp
                @foreach($subjects as $subject)
                    @php /** @var \App\Models\Subject $subject */ @endphp
                    <option value="{{ $subject->id }}" {{ $subjectId == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn btn-gradient btn-sm">Filter</button>
        <a href="/admin/reports?type=subjects" class="btn btn-outline-light btn-sm">Reset</a>
    </form>
</div>

<div class="glass-panel p-2 p-md-3">
    <h5 class="px-2 pt-2">Subject Report</h5>
    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Subject</th>
                    <th>Code</th>
                    <th>Class</th>
                    <th>Credit Hours</th>
                    <th>Quizzes</th>
                    <th>Avg Quiz Score</th>
                    <th>Assignments</th>
                    <th>Total Submissions</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['subjects'] as $sub)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $sub->name }}</td>
                    <td>{{ $sub->code }}</td>
                    <td>{{ $sub->schoolClass->name ?? '-' }}</td>
                    <td>{{ $sub->credit_hours }}</td>
                    <td>{{ $sub->quizzes_count }}</td>
                    <td>{{ number_format($sub->avg_quiz_score, 1) }}</td>
                    <td>{{ $sub->assignments_count }}</td>
                    <td>{{ $sub->total_submissions }}</td>
                    <td><span class="badge {{ $sub->active === 'yes' ? 'bg-success' : 'bg-secondary' }}">{{ $sub->active === 'yes' ? 'Active' : 'Inactive' }}</span></td>
                </tr>
                @empty
                <tr><td colspan="10" class="text-center">No subjects found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
