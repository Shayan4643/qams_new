<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Notification;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TeacherQuizController extends Controller
{
    public function questionBankIndex(): View
    {
        $teacher  = Auth::user();
        $subjects = $teacher->taughtSubjects()->with('schoolClass')->where('subjects.active', 'yes')->orderBy('name')->get();
        $questions = Question::with('subject')
            ->where('teacher_id', $teacher->id)
            ->latest()
            ->get();

        return view('teacher.question_bank', compact('subjects', 'questions'));
    }

    public function questionBankStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject_id'     => 'required|exists:subjects,id',
            'question_text'  => 'required|string|max:1000',
            'option_a'       => 'required|string|max:255',
            'option_b'       => 'required|string|max:255',
            'option_c'       => 'nullable|string|max:255',
            'option_d'       => 'nullable|string|max:255',
            'correct_option' => 'required|in:A,B,C,D',
            'active'         => 'required|in:yes,no',
        ]);

        $teacher       = Auth::user();
        $subjectAllowed = $teacher->taughtSubjects()->where('subjects.id', (int) $validated['subject_id'])->exists();
        if (! $subjectAllowed) {
            return back()->withErrors(['subject_id' => 'You can only add questions for assigned subjects.'])->withInput();
        }

        Question::create(array_merge($validated, ['teacher_id' => $teacher->id]));

        ActivityLog::create([
            'user_id'    => $teacher->id,
            'action'     => 'question_created',
            'description'=> 'Teacher added question to bank.',
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Question added to bank successfully.');
    }

    public function questionBankToggle(int $id): RedirectResponse
    {
        $question = Question::where('teacher_id', Auth::id())->findOrFail($id);
        $question->active = $question->active === 'yes' ? 'no' : 'yes';
        $question->save();

        return back()->with('success', 'Question status updated.');
    }

    public function questionBankDelete(int $id): RedirectResponse
    {
        $question = Question::where('teacher_id', Auth::id())->findOrFail($id);
        $question->delete();

        return back()->with('success', 'Question removed from bank.');
    }

    public function quizIndex(): View
    {
        $teacher   = Auth::user();
        $subjects  = $teacher->taughtSubjects()->with('schoolClass')->where('subjects.active', 'yes')->orderBy('name')->get();
        $questions = Question::where('teacher_id', $teacher->id)
            ->where('active', 'yes')
            ->with('subject')
            ->orderByDesc('id')
            ->get();
        $quizzes = Quiz::where('teacher_id', $teacher->id)
            ->with(['subject', 'questions'])
            ->orderByDesc('id')
            ->get();

        return view('teacher.quizzes', compact('subjects', 'questions', 'quizzes'));
    }

    public function quizStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject_id'   => 'required|exists:subjects,id',
            'title'        => 'required|string|max:120',
            'description'  => 'nullable|string|max:1000',
            'deadline'     => 'required|date|after:now',
            'question_ids' => 'required|array|min:1',
            'question_ids.*' => 'integer|exists:questions,id',
        ]);

        $teacher = Auth::user();
        $subjectAllowed = $teacher->taughtSubjects()->where('subjects.id', (int) $validated['subject_id'])->exists();
        if (! $subjectAllowed) {
            return back()->withErrors(['subject_id' => 'You can only create quiz for assigned subjects.'])->withInput();
        }

        $teacherQuestionCount = Question::where('teacher_id', $teacher->id)
            ->whereIn('id', $validated['question_ids'])
            ->count();
        if ($teacherQuestionCount !== count($validated['question_ids'])) {
            return back()->withErrors(['question_ids' => 'Selected questions must belong to you.'])->withInput();
        }

        $quiz = Quiz::create([
            'teacher_id'  => $teacher->id,
            'subject_id'  => $validated['subject_id'],
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'deadline'    => $validated['deadline'],
            'published'   => 'no',
        ]);

        $quiz->questions()->sync($validated['question_ids']);

        ActivityLog::create([
            'user_id'    => $teacher->id,
            'action'     => 'quiz_created',
            'description'=> 'Teacher created quiz: ' . $validated['title'],
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Quiz created successfully.');
    }


    public function quizToggleResults(int $id): RedirectResponse
    {
        $quiz = Quiz::where('teacher_id', Auth::id())->findOrFail($id);
        $quiz->results_published = !$quiz->results_published;

        ActivityLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'quiz_results_published',
            'description'=> 'Quiz "' . $quiz->title . '" results publication toggled to ' . ($quiz->results_published ? 'Published' : 'Hidden'),
            'ip_address' => request()->ip(),
        ]);
        $quiz->save();

        if ($quiz->results_published) {
            $attempts = $quiz->attempts()->with('student')->get();
            foreach ($attempts as $attempt) {
                Notification::create([
                    'user_id' => $attempt->user_id,
                    'title'   => 'Quiz Results Published',
                    'message' => 'Results for quiz "' . $quiz->title . '" are now available.',
                ]);
            }
        }

        return back()->with('success', 'Quiz results publication toggled.');
    }

    public function quizTogglePublish(int $id): RedirectResponse
    {
        $quiz = Quiz::where('teacher_id', Auth::id())->withCount('questions')->findOrFail($id);
        
        if ($quiz->published === 'no' && $quiz->questions_count === 0) {
            return back()->withErrors(['quiz' => 'Quiz must contain at least one question before publishing.']);
        }

        $oldStatus = $quiz->published;
        $quiz->published = $quiz->published === 'yes' ? 'no' : 'yes';
        $quiz->save();

        if ($quiz->published === 'yes' && $oldStatus === 'no') {
            // Notify students enrolled in this subject or in the same class
            $students = User::where('role', 'student')
                ->where(function($query) use ($quiz) {
                    $query->whereHas('enrolledSubjects', function($q) use ($quiz) {
                        $q->where('subjects.id', $quiz->subject_id);
                    })
                    ->orWhere('class_id', $quiz->subject->class_id);
                })->get();

            foreach ($students as $student) {
                Notification::create([
                    'user_id' => $student->id,
                    'title'   => 'New Quiz Available',
                    'message' => 'A new quiz "' . $quiz->title . '" has been published for ' . $quiz->subject->name . '.',
                ]);
            }
        }

        ActivityLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'quiz_published_toggled',
            'description'=> 'Quiz "' . $quiz->title . '" publication toggled to ' . ($quiz->published === 'yes' ? 'Published' : 'Hidden'),
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Quiz visibility updated.');
    }

    public function quizPreview(int $id): View
    {
        $quiz = Quiz::where('teacher_id', Auth::id())->with(['subject', 'questions'])->findOrFail($id);
        return view('teacher.preview_quiz', compact('quiz'));
    }

    // =============================================
    // UC-11: Extend quiz deadline
    // =============================================
    public function quizExtendDeadline(Request $request, int $id): RedirectResponse
    {
        $quiz = Quiz::where('teacher_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'deadline' => 'required|date|after:now',
        ]);

        $quiz->update(['deadline' => $validated['deadline']]);

        ActivityLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'quiz_deadline_extended',
            'description'=> 'Deadline for quiz "' . $quiz->title . '" extended to ' . $validated['deadline'],
            'ip_address' => $request->ip(),
        ]);

        // Notify students
        if ($quiz->published === 'yes') {
            $students = User::where('role', 'student')
                ->where(function($query) use ($quiz) {
                    $query->whereHas('enrolledSubjects', function($q) use ($quiz) {
                        $q->where('subjects.id', $quiz->subject_id);
                    })
                    ->orWhere('class_id', $quiz->subject->class_id);
                })->get();

            foreach ($students as $student) {
                Notification::create([
                    'user_id' => $student->id,
                    'title'   => 'Quiz Deadline Extended',
                    'message' => 'The deadline for quiz "' . $quiz->title . '" has been extended to ' . \Carbon\Carbon::parse($validated['deadline'])->format('d M Y, h:i A') . '.',
                ]);
            }
        }

        return back()->with('success', 'Quiz deadline extended successfully.');
    }

    // =============================================
    // UC-13: Teacher performance report
    // =============================================
    public function performanceReport(): View
    {
        $teacher = Auth::user();

        // Get quizzes with attempt statistics optimized
        $quizzes = Quiz::where('teacher_id', $teacher->id)
            ->with(['subject', 'questions', 'attempts' => function($q) {
                $q->with('student');
            }])
            ->withCount('attempts')
            ->withCount(['attempts as zero_count' => fn($q) => $q->where('status', 'late_zero')])
            ->withAvg(['attempts as avg_score' => fn($q) => $q->where('status', 'submitted')], 'score')
            ->get();

        foreach ($quizzes as $quiz) {
            $totalQuestions = $quiz->questions->count();
            $passScore = ceil($totalQuestions * 0.5);
            
            // Calculate pass_count from the loaded collection to avoid N+1
            $quiz->pass_count = $quiz->attempts->filter(function($attempt) use ($passScore) {
                return $attempt->status === 'submitted' && $attempt->score >= $passScore;
            })->count();

            // Ensure avg_score is at least 0 for number_format
            $quiz->avg_score = $quiz->avg_score ?? 0;
        }

        return view('teacher.performance', compact('quizzes'));
    }

    public function destroy(int $id): RedirectResponse
    {
        $quiz = Quiz::where('teacher_id', Auth::id())->findOrFail($id);
        
        // Delete related attempts and answers
        foreach ($quiz->attempts as $attempt) {
            $attempt->answers()->delete();
            $attempt->delete();
        }
        $quiz->delete();

        ActivityLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'quiz_deleted',
            'description'=> 'Teacher deleted quiz: ' . $quiz->title,
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Quiz deleted successfully.');
    }
}
