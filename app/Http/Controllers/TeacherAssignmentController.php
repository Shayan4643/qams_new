<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Notification;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use App\Models\User;

class TeacherAssignmentController extends Controller
{
    public function index(): View
    {
        $teacher    = Auth::user();
        $subjects   = $teacher->taughtSubjects()->with('schoolClass')->where('subjects.active', 'yes')->orderBy('name')->get();
        $assignments = Assignment::where('teacher_id', $teacher->id)
            ->with(['subject', 'submissions'])
            ->orderByDesc('id')
            ->get();

        $submissions = AssignmentSubmission::whereHas('assignment', function ($query) use ($teacher) {
            $query->where('teacher_id', $teacher->id);
        })->with(['assignment.subject', 'student'])->orderByDesc('id')->get();

        return view('teacher.assignments', compact('subjects', 'assignments', 'submissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject_id'  => 'required|exists:subjects,id',
            'title'       => 'required|string|max:140',
            'description' => 'nullable|string|max:2000',
            'deadline'    => 'required|date|after:now',
            'published'   => 'required|in:yes,no',
            'instruction_file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        $teacher = Auth::user();
        $allowed = $teacher->taughtSubjects()->where('subjects.id', (int) $validated['subject_id'])->exists();
        if (! $allowed) {
            return back()->withErrors(['subject_id' => 'You can only create assignments for your assigned subjects.'])->withInput();
        }

        $filePath = null;
        if ($request->hasFile('instruction_file')) {
            $filePath = $request->file('instruction_file')->store('assignment_instructions', 'public');
        }

        $assignment = Assignment::create([
            'teacher_id'  => $teacher->id,
            'subject_id'  => $validated['subject_id'],
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'file_path'   => $filePath,
            'deadline'    => $validated['deadline'],
            'published'   => $validated['published'],
        ]);

        ActivityLog::create([
            'user_id'    => $teacher->id,
            'action'     => 'assignment_created',
            'description'=> 'Teacher created assignment: ' . $assignment->title,
            'ip_address' => $request->ip(),
        ]);

        if ($assignment->published === 'yes') {
            $students = User::where('role', 'student')
                ->where(function($query) use ($assignment) {
                    $query->whereHas('enrolledSubjects', function($q) use ($assignment) {
                        $q->where('subjects.id', $assignment->subject_id);
                    })
                    ->orWhere('class_id', $assignment->subject->class_id);
                })->get();

            foreach ($students as $student) {
                Notification::create([
                    'user_id' => $student->id,
                    'title'   => 'New Assignment',
                    'message' => 'A new assignment "' . $assignment->title . '" has been posted for ' . $assignment->subject->name . '.',
                ]);
            }
        }

        return back()->with('success', 'Assignment created successfully.');
    }

    public function togglePublish(int $id): RedirectResponse
    {
        $assignment = Assignment::where('teacher_id', Auth::id())->findOrFail($id);
        $assignment->published = $assignment->published === 'yes' ? 'no' : 'yes';
        $assignment->save();

        if ($assignment->published === 'yes') {
            $students = User::where('role', 'student')
                ->where(function($query) use ($assignment) {
                    $query->whereHas('enrolledSubjects', function($q) use ($assignment) {
                        $q->where('subjects.id', $assignment->subject_id);
                    })
                    ->orWhere('class_id', $assignment->subject->class_id);
                })->get();

            foreach ($students as $student) {
                Notification::create([
                    'user_id' => $student->id,
                    'title'   => 'Assignment Published',
                    'message' => 'The assignment "' . $assignment->title . '" for ' . $assignment->subject->name . ' is now available.',
                ]);
            }
        }

        ActivityLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'assignment_published',
            'description'=> 'Assignment "' . $assignment->title . '" publication toggled to ' . $assignment->published,
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Assignment publication status updated.');
    }

    // =============================================
    // UC-11: Extend assignment deadline
    // =============================================
    public function extendDeadline(Request $request, int $id): RedirectResponse
    {
        $assignment = Assignment::where('teacher_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'deadline' => 'required|date|after:now',
        ]);

        $assignment->update(['deadline' => $validated['deadline']]);

        ActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'assignment_deadline_extended',
            'description' => 'Deadline for assignment "' . $assignment->title . '" extended to ' . $validated['deadline'],
            'ip_address'  => $request->ip(),
        ]);

        // Notify students
        if ($assignment->published === 'yes') {
            $students = User::where('role', 'student')
                ->where(function($query) use ($assignment) {
                    $query->whereHas('enrolledSubjects', function($q) use ($assignment) {
                        $q->where('subjects.id', $assignment->subject_id);
                    })
                    ->orWhere('class_id', $assignment->subject->class_id);
                })->get();

            foreach ($students as $student) {
                Notification::create([
                    'user_id' => $student->id,
                    'title'   => 'Assignment Deadline Extended',
                    'message' => 'The deadline for assignment "' . $assignment->title . '" has been extended to ' . \Carbon\Carbon::parse($validated['deadline'])->format('d M Y, h:i A') . '.',
                ]);
            }
        }

        return back()->with('success', 'Assignment deadline extended successfully.');
    }

    public function grade(Request $request, int $submissionId): RedirectResponse
    {
        $submission = AssignmentSubmission::whereHas('assignment', function ($query) {
            $query->where('teacher_id', Auth::id());
        })->findOrFail($submissionId);

        $validated = $request->validate([
            'marks'            => 'required|integer|min:0|max:100',
            'teacher_feedback' => 'nullable|string|max:1000',
        ]);

        $submission->update([
            'marks'            => $validated['marks'],
            'teacher_feedback' => $validated['teacher_feedback'] ?? null,
            'feedback'         => $validated['teacher_feedback'] ?? null,  // sync actual DB column
            'status'           => 'graded',
        ]);

        Notification::create([
            'user_id' => $submission->user_id,
            'title'   => 'Assignment Graded',
            'message' => 'Your assignment "' . $submission->assignment->title . '" has been graded.',
        ]);

        ActivityLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'assignment_graded',
            'description'=> 'Teacher graded submission #' . $submissionId . ' with marks: ' . $validated['marks'],
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Assignment graded successfully.');
    }

    public function downloadSubmission(int $submissionId)
    {
        $submission = AssignmentSubmission::whereHas('assignment', function ($query) {
            $query->where('teacher_id', Auth::id());
        })->findOrFail($submissionId);

        if (!$submission->file_path || !Storage::disk('public')->exists($submission->file_path)) {
            return back()->with('error', 'Submission file not found.');
        }

        return response()->download(storage_path('app/public/' . $submission->file_path));
    }

    public function destroy(int $id): RedirectResponse
    {
        $assignment = Assignment::where('teacher_id', Auth::id())->findOrFail($id);
        
        // Delete submissions and their files if any
        foreach ($assignment->submissions as $sub) {
            if ($sub->file_path) {
                Storage::disk('public')->delete($sub->file_path);
            }
            $sub->delete();
        }

        if ($assignment->file_path) {
            Storage::disk('public')->delete($assignment->file_path);
        }

        $assignment->delete();

        ActivityLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'assignment_deleted',
            'description'=> 'Teacher deleted assignment: ' . $assignment->title,
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Assignment deleted successfully.');
    }
}
