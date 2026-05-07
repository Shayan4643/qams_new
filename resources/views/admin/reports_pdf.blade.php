<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>QAMS Report — {{ ucfirst($reportType) }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a202c; margin: 20px; }
        h1 { font-size: 18px; color: #4f46e5; margin-bottom: 4px; }
        .meta { font-size: 10px; color: #64748b; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th { background: #4f46e5; color: #fff; padding: 7px 8px; text-align: left; font-size: 10px; }
        td { padding: 6px 8px; border-bottom: 1px solid #e2e8f0; }
        tr:nth-child(even) td { background: #f8fafc; }
        .badge-active   { color: #16a34a; font-weight: bold; }
        .badge-blocked  { color: #dc2626; font-weight: bold; }
        .badge-inactive { color: #64748b; }
        .footer { margin-top: 20px; font-size: 9px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
    <h1>QAMS — {{ ucfirst($reportType) }} Report</h1>
    <div class="meta">Generated on: {{ now()->format('d M Y, h:i A') }}</div>

    @if($reportType === 'students')
    <table>
        <thead>
            <tr>
                <th>#</th><th>Name</th><th>Username</th><th>Admission No.</th>
                <th>Father Name</th><th>Class</th><th>Quiz Attempts</th>
                <th>Avg Quiz Score</th><th>Graded Assignments</th><th>Avg Assignment Marks</th><th>Status</th>
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
                <td class="{{ $s->active === 'yes' ? 'badge-active' : 'badge-blocked' }}">
                    {{ $s->active === 'yes' ? 'Active' : 'Blocked' }}
                </td>
            </tr>
            @empty
            <tr><td colspan="11">No students found.</td></tr>
            @endforelse
        </tbody>
    </table>

    @elseif($reportType === 'teachers')
    <table>
        <thead>
            <tr>
                <th>#</th><th>Name</th><th>Username</th><th>Education</th>
                <th>Assigned Subjects</th><th>Quizzes Created</th><th>Assignments Created</th><th>Status</th>
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
                <td class="{{ $t->active === 'yes' ? 'badge-active' : 'badge-blocked' }}">
                    {{ $t->active === 'yes' ? 'Active' : 'Blocked' }}
                </td>
            </tr>
            @empty
            <tr><td colspan="8">No teachers found.</td></tr>
            @endforelse
        </tbody>
    </table>

    @elseif($reportType === 'subjects')
    <table>
        <thead>
            <tr>
                <th>#</th><th>Subject</th><th>Code</th><th>Class</th><th>Credit Hours</th>
                <th>Quizzes</th><th>Avg Quiz Score</th><th>Assignments</th><th>Submissions</th><th>Status</th>
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
                <td class="{{ $sub->active === 'yes' ? 'badge-active' : 'badge-inactive' }}">
                    {{ $sub->active === 'yes' ? 'Active' : 'Inactive' }}
                </td>
            </tr>
            @empty
            <tr><td colspan="10">No subjects found.</td></tr>
            @endforelse
        </tbody>
    </table>
    @endif

    <div class="footer">QAMS — Quiz and Assignment Management System | Confidential</div>
</body>
</html>
