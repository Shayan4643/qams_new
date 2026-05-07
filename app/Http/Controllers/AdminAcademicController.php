<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAcademicController extends Controller
{
    public function classesIndex(): View
    {
        $classes = SchoolClass::orderByDesc('id')->get();

        return view('admin.classes', compact('classes'));
    }

    public function classesStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'code' => 'required|string|max:30|unique:classes,code',
            'section' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:1000',
            'active' => 'required|in:yes,no',
        ]);

        $class = SchoolClass::create($validated);
        
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'class_created',
            'description' => 'Admin created class: ' . $class->name,
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Class created successfully.');
    }

    public function classesUpdate(Request $request, int $id): RedirectResponse
    {
        $class = SchoolClass::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'code' => 'required|string|max:30|unique:classes,code,' . $class->id,
            'section' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:1000',
            'active' => 'required|in:yes,no',
        ]);

        $class->update($validated);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'class_updated',
            'description' => 'Admin updated class: ' . $class->name,
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Class updated successfully.');
    }

    public function classesToggle(int $id): RedirectResponse
    {
        $class = SchoolClass::findOrFail($id);
        $class->active = $class->active === 'yes' ? 'no' : 'yes';
        $class->save();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'class_status_toggled',
            'description' => 'Admin toggled status for class: ' . $class->name . ' to ' . $class->active,
        ]);

        return back()->with('success', 'Class status updated.');
    }

    public function subjectsIndex(): View
    {
        $subjects = Subject::with('schoolClass')->orderByDesc('id')->get();
        $classes = SchoolClass::where('active', 'yes')->orderBy('name')->get();

        return view('admin.subjects', compact('subjects', 'classes'));
    }

    public function subjectsStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'name' => 'required|string|max:70',
            'code' => 'required|string|max:30|unique:subjects,code',
            'credit_hours' => 'required|integer|min:1|max:6',
            'description' => 'nullable|string|max:1000',
            'active' => 'required|in:yes,no',
        ]);

        $subject = Subject::create($validated);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'subject_created',
            'description' => 'Admin created subject: ' . $subject->name,
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Subject created successfully.');
    }

    public function subjectsUpdate(Request $request, int $id): RedirectResponse
    {
        $subject = Subject::findOrFail($id);

        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'name' => 'required|string|max:70',
            'code' => 'required|string|max:30|unique:subjects,code,' . $subject->id,
            'credit_hours' => 'required|integer|min:1|max:6',
            'description' => 'nullable|string|max:1000',
            'active' => 'required|in:yes,no',
        ]);

        $subject->update($validated);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'subject_updated',
            'description' => 'Admin updated subject: ' . $subject->name,
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Subject updated successfully.');
    }

    public function subjectsToggle(int $id): RedirectResponse
    {
        $subject = Subject::findOrFail($id);
        $subject->active = $subject->active === 'yes' ? 'no' : 'yes';
        $subject->save();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'subject_status_toggled',
            'description' => 'Admin toggled status for subject: ' . $subject->name . ' to ' . $subject->active,
        ]);

        return back()->with('success', 'Subject status updated.');
    }

    public function assignmentsIndex(): View
    {
        $teachers = User::where('role', 'teacher')->orderBy('name')->get();
        $subjects = Subject::with('schoolClass')
            ->where('active', 'yes')
            ->orderBy('name')
            ->get();
        $assignments = User::where('role', 'teacher')
            ->with(['taughtSubjects.schoolClass'])
            ->orderBy('name')
            ->get();

        return view('admin.assignments', compact('teachers', 'subjects', 'assignments'));
    }

    public function assignmentsStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $teacher = User::findOrFail((int) $validated['teacher_id']);
        if ($teacher->role !== 'teacher') {
            return back()->withErrors(['teacher_id' => 'Only teachers can be assigned subjects.'])->withInput();
        }
        if ($teacher->active !== 'yes') {
            return back()->withErrors(['teacher_id' => 'Cannot assign subject to an inactive teacher.'])->withInput();
        }

        $subject = Subject::findOrFail((int) $validated['subject_id']);
        if ($subject->active !== 'yes') {
            return back()->withErrors(['subject_id' => 'Cannot assign an inactive subject.'])->withInput();
        }

        if ($teacher->taughtSubjects()->where('subjects.id', $subject->id)->exists()) {
            return back()->withErrors(['subject_id' => 'This subject is already assigned to the selected teacher.'])->withInput();
        }

        $teacher->taughtSubjects()->attach($subject->id);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'teacher_subject_assigned',
            'description' => 'Admin assigned subject ' . $subject->name . ' to teacher ' . $teacher->name,
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Subject assigned to teacher successfully.');
    }

    public function assignmentsDelete(int $teacherId, int $subjectId): RedirectResponse
    {
        $teacher = User::where('role', 'teacher')->findOrFail($teacherId);
        $teacher->taughtSubjects()->detach($subjectId);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'teacher_subject_removed',
            'description' => 'Admin removed subject assignment from teacher ' . $teacher->name,
        ]);

        return back()->with('success', 'Subject assignment removed successfully.');
    }
}
