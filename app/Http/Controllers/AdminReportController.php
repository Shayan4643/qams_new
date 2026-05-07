<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminReportController extends Controller
{
    // =============================================
    // UC-06: Admin Reports page (student/teacher/subject)
    // =============================================
    public function index(Request $request): View
    {
        $reportType = $request->input('type', 'students');
        $classId    = $request->input('class_id');
        $subjectId  = $request->input('subject_id');

        $classes  = SchoolClass::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();

        $data = match ($reportType) {
            'teachers' => $this->teacherReportData(),
            'subjects' => $this->subjectReportData($subjectId),
            default    => $this->studentReportData($classId),
        };

        ActivityLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'report_viewed',
            'description'=> 'Admin viewed ' . $reportType . ' report.',
            'ip_address' => $request->ip(),
        ]);

        return view('admin.reports', compact('reportType', 'classes', 'subjects', 'classId', 'subjectId', 'data'));
    }

    // =============================================
    // UC-06: Download PDF report
    // =============================================
    public function downloadPdf(Request $request)
    {
        $reportType = $request->input('type', 'students');
        $classId    = $request->input('class_id');
        $subjectId  = $request->input('subject_id');

        $classes  = SchoolClass::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();

        $data = match ($reportType) {
            'teachers' => $this->teacherReportData(),
            'subjects' => $this->subjectReportData($subjectId),
            default    => $this->studentReportData($classId),
        };

        $pdf = Pdf::loadView('admin.reports_pdf', compact('reportType', 'classes', 'subjects', 'classId', 'subjectId', 'data'))
            ->setPaper('a4', 'landscape');

        $filename = 'QAMS_Report_' . ucfirst($reportType) . '_' . now()->format('Ymd_His') . '.pdf';

        ActivityLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'report_pdf_downloaded',
            'description'=> 'Admin downloaded PDF ' . $reportType . ' report.',
            'ip_address' => $request->ip(),
        ]);

        return $pdf->download($filename);
    }

    public function exportCsv(Request $request)
    {
        $reportType = $request->input('type', 'students');
        $classId    = $request->input('class_id');
        $subjectId  = $request->input('subject_id');

        $data = match ($reportType) {
            'teachers' => $this->teacherReportData(),
            'subjects' => $this->subjectReportData($subjectId),
            default    => $this->studentReportData($classId),
        };

        $filename = 'QAMS_Report_' . ucfirst($reportType) . '_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($reportType, $data) {
            $file = fopen('php://output', 'w');

            if ($reportType === 'students') {
                fputcsv($file, ['Name', 'Username', 'Class', 'Quizzes Attempted', 'Avg Quiz Score', 'Assignments Graded', 'Avg Assignment Marks']);
                foreach ($data['students'] as $row) {
                    fputcsv($file, [$row->name, $row->user_name, $row->schoolClass->name ?? 'N/A', $row->quiz_attempts_count, round($row->avg_quiz_score, 2), $row->graded_assignments, round($row->avg_assignment_marks, 2)]);
                }
            } elseif ($reportType === 'teachers') {
                fputcsv($file, ['Name', 'Username', 'Subjects', 'Quizzes Created', 'Assignments Created']);
                foreach ($data['teachers'] as $row) {
                    fputcsv($file, [$row->name, $row->user_name, $row->taughtSubjects->pluck('name')->implode(', '), $row->quizzes_count, $row->assignments_count]);
                }
            } else {
                fputcsv($file, ['Subject', 'Class', 'Total Quizzes', 'Total Quiz Attempts', 'Avg Quiz Score', 'Total Assignments', 'Total Submissions']);
                foreach ($data['subjects'] as $row) {
                    fputcsv($file, [$row->name, $row->schoolClass->name ?? 'N/A', $row->quizzes_count, $row->total_quiz_attempts, round($row->avg_quiz_score, 2), $row->assignments_count, $row->total_submissions]);
                }
            }
            fclose($file);
        };

        ActivityLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'report_csv_exported',
            'description'=> 'Admin exported CSV ' . $reportType . ' report.',
            'ip_address' => $request->ip(),
        ]);

        return response()->stream($callback, 200, $headers);
    }

    // =============================================
    // UC-07: Admin view all quiz + assignment results
    // =============================================
    public function results(Request $request): View
    {
        $tab = $request->input('tab', 'quiz');

        $quizAttempts = QuizAttempt::with(['quiz.subject', 'quiz.teacher', 'student'])
            ->orderByDesc('id')
            ->paginate(15, ['*'], 'quiz_page');

        $assignmentSubmissions = AssignmentSubmission::with(['assignment.subject', 'assignment.teacher', 'student'])
            ->orderByDesc('id')
            ->paginate(15, ['*'], 'assignment_page');

        return view('admin.results', compact('quizAttempts', 'assignmentSubmissions', 'tab'));
    }

    // =============================================
    // Private helpers
    // =============================================
    private function studentReportData(?string $classId): array
    {
        $query = User::where('role', 'student')
            ->with(['schoolClass'])
            ->withCount([
                'quizAttempts',
                'assignmentSubmissions',
                'quizAttempts as submitted_quizzes'     => fn($q) => $q->where('status', 'submitted'),
                'assignmentSubmissions as graded_assignments' => fn($q) => $q->whereNotNull('marks')->where('marks', '>', 0),
            ])
            ->withAvg(['quizAttempts as avg_quiz_score' => fn($q) => $q->where('status', 'submitted')], 'score')
            ->withAvg(['assignmentSubmissions as avg_assignment_marks' => fn($q) => $q->whereNotNull('marks')], 'marks');

        if ($classId) {
            $query->where('class_id', $classId);
        }

        $students = $query->orderBy('name')->get();

        return ['students' => $students];
    }

    private function teacherReportData(): array
    {
        $teachers = User::where('role', 'teacher')
            ->with(['taughtSubjects', 'quizzes', 'assignments'])
            ->withCount(['quizzes', 'assignments'])
            ->orderBy('name')
            ->get();

        return ['teachers' => $teachers];
    }

    private function subjectReportData(?string $subjectId): array
    {
        $subjectsQuery = Subject::with('schoolClass')
            ->withCount(['quizzes', 'assignments'])
            ->withCount('quizAttempts as total_quiz_attempts')
            ->withAvg(['quizAttempts as avg_quiz_score' => fn($q) => $q->where('status', 'submitted')], 'score')
            ->withCount('assignmentSubmissions as total_submissions')
            ->orderBy('name');

        if ($subjectId) {
            $subjectsQuery->where('id', $subjectId);
        }

        $subjects = $subjectsQuery->get();

        return ['subjects' => $subjects];
    }
}
