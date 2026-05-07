<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class StudentAssignmentController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $assignments = Assignment::where(function($query) use ($user) {
            $query->whereIn('subject_id', $user->enrolledSubjects->pluck('id'))
                  ->orWhereHas('subject', function($q) use ($user) {
                      $q->where('class_id', $user->class_id);
                  });
        })
        ->with(['subject'])
        ->where('published', 'yes')
        ->orderBy('deadline')
        ->get();

        $submissions = AssignmentSubmission::where('user_id', $user->id)
            ->get()
            ->keyBy('assignment_id');

        return view('student.assignments', compact('assignments', 'submissions'));
    }

    public function submit(Request $request, int $assignmentId): RedirectResponse
    {
        $assignment = Assignment::findOrFail($assignmentId);
        $userId     = Auth::id();

        if ($assignment->published !== 'yes') {
            return back()->with('error', 'Assignment is not published.');
        }

        $existing = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('user_id', $userId)
            ->first();

        if ($existing && !is_null($existing->submitted_at)) {
            return back()->with('error', 'You already submitted this assignment.');
        }

        if (now()->greaterThan($assignment->deadline)) {
            $this->assignLateZero($assignment->id, $userId, $request->ip());
            return back()->with('error', 'Deadline has passed. Zero marks assigned automatically.');
        }

        $validated = $request->validate([
            'submission_file' => 'required|file|mimes:pdf,doc,docx|max:5120',
        ]);

        $filePath = null;
        if ($request->hasFile('submission_file')) {
            $filePath = $request->file('submission_file')->store('assignment_submissions', 'public');
        }

        // Model boot event will sync user_id→student_id and feedback→teacher_feedback
        AssignmentSubmission::updateOrCreate(
            ['assignment_id' => $assignment->id, 'user_id' => $userId],
            [
                'student_id'      => $userId,
                'file_path'       => $filePath,
                'submission_text' => $validated['submission_text'] ?? null,
                'marks'           => null,
                'status'          => 'submitted',
                'teacher_feedback'=> null,
                'feedback'        => null,
                'submitted_at'    => now(),
            ]
        );

        ActivityLog::create([
            'user_id'     => $userId,
            'action'      => 'assignment_submitted',
            'description' => 'Student submitted assignment: ' . $assignment->title,
            'ip_address'  => $request->ip(),
        ]);

        return back()->with('success', 'Assignment submitted successfully.');
    }

    public function results(): View
    {
        $submissions = AssignmentSubmission::with(['assignment.subject'])
            ->where('user_id', Auth::id())
            ->orderByDesc('id')
            ->get();

        return view('student.assignment_results', compact('submissions'));
    }

    private function assignLateZero(int $assignmentId, int $userId, string $ip): void
    {
        AssignmentSubmission::updateOrCreate(
            ['assignment_id' => $assignmentId, 'user_id' => $userId],
            [
                'student_id'      => $userId,
                'file_path'       => null,
                'submission_text' => null,
                'marks'           => 0,
                'status'          => 'late_zero',
                'teacher_feedback'=> 'Missed deadline — zero marks assigned automatically.',
                'feedback'        => 'Missed deadline — zero marks assigned automatically.',
                'submitted_at'    => now(),
            ]
        );

        ActivityLog::create([
            'user_id'     => $userId,
            'action'      => 'assignment_late_zero',
            'description' => 'Zero marks assigned for late assignment ID: ' . $assignmentId,
            'ip_address'  => $ip,
        ]);
    }

    public function downloadInstruction(int $assignmentId)
    {
        $assignment = Assignment::whereHas('subject', function($q) {
            $q->where('class_id', Auth::user()->class_id);
        })->findOrFail($assignmentId);

        if (!$assignment->file_path || !Storage::disk('public')->exists($assignment->file_path)) {
            return back()->with('error', 'Instruction file not found.');
        }

        return response()->download(storage_path('app/public/' . $assignment->file_path));
    }
}
