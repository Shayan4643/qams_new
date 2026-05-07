<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentQuizController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $quizzes = Quiz::where(function($query) use ($user) {
            $query->whereIn('subject_id', $user->enrolledSubjects->pluck('id'))
                  ->orWhereHas('subject', function($q) use ($user) {
                      $q->where('class_id', $user->class_id);
                  });
        })
        ->with(['subject', 'questions'])
        ->where('published', 'yes')
        ->orderBy('deadline')
        ->get();

        // Use user_id (actual DB column)
        $attempts = QuizAttempt::where('user_id', $user->id)
            ->get()
            ->keyBy('quiz_id');

        return view('student.quizzes', compact('quizzes', 'attempts'));
    }

    public function attempt(int $quizId): View|RedirectResponse
    {
        $quiz   = Quiz::with(['subject', 'questions'])->findOrFail($quizId);
        $userId = Auth::id();

        if ($quiz->published !== 'yes') {
            return redirect('/student/quizzes')->with('error', 'This quiz is not available yet.');
        }

        $existingAttempt = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('user_id', $userId)
            ->first();

        if ($existingAttempt) {
            return redirect('/student/quizzes/results')->with('error', 'You already submitted this quiz.');
        }

        if (now()->greaterThan($quiz->deadline)) {
            $this->assignZeroForExpiredQuiz($quiz->id, $userId);
            return redirect('/student/quizzes')->with('error', 'Deadline has passed. Zero marks assigned.');
        }

        return view('student.attempt_quiz', compact('quiz'));
    }

    public function submit(Request $request, int $quizId): RedirectResponse
    {
        $quiz   = Quiz::with('questions')->findOrFail($quizId);
        $userId = Auth::id();

        if ($quiz->published !== 'yes') {
            return redirect('/student/quizzes')->with('error', 'This quiz is not available.');
        }

        $existingAttempt = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('user_id', $userId)
            ->first();

        if ($existingAttempt) {
            return redirect('/student/quizzes/results')->with('error', 'You already submitted this quiz.');
        }

        if (now()->greaterThan($quiz->deadline)) {
            $this->assignZeroForExpiredQuiz($quiz->id, $userId);
            return redirect('/student/quizzes')->with('error', 'Deadline has passed. Zero marks assigned.');
        }

        $request->validate([
            'answers'   => 'required|array',
            'answers.*' => 'nullable|in:A,B,C,D',
        ]);

        // Create attempt — model boot syncs user_id→student_id and submitted_at→attempted_at
        $attempt = QuizAttempt::create([
            'quiz_id'      => $quiz->id,
            'user_id'      => $userId,
            'student_id'   => $userId,
            'score'        => 0,
            'status'       => 'submitted',
            'submitted_at' => now(),
            'attempted_at' => now(),
        ]);

        $score       = 0;
        $questionIds = $quiz->questions->pluck('id')->toArray();

        foreach ($quiz->questions as $question) {
            $selected  = $request->input('answers.' . $question->id);
            $isCorrect = $selected !== null && $selected === $question->correct_option;
            if ($isCorrect) {
                $score++;
            }

            QuizAnswer::create([
                'quiz_attempt_id' => $attempt->id,
                'question_id'     => $question->id,
                'selected_option' => $selected,
                'is_correct'      => $isCorrect,
            ]);
        }

        $attempt->update(['score' => $score]);

        ActivityLog::create([
            'user_id'     => $userId,
            'action'      => 'quiz_submitted',
            'description' => 'Student submitted quiz: ' . $quiz->title . ' — Score: ' . $score . '/' . count($questionIds),
            'ip_address'  => $request->ip(),
        ]);

        return redirect('/student/quizzes/results')->with('success', 'Quiz submitted! Score: ' . $score . '/' . count($questionIds));
    }

    public function results(): View
    {
        $attempts = QuizAttempt::with(['quiz.subject'])
            ->where('user_id', Auth::id())
            ->orderByDesc('id')
            ->get();

        return view('student.results', compact('attempts'));
    }

    private function assignZeroForExpiredQuiz(int $quizId, int $userId): void
    {
        $existing = QuizAttempt::where('quiz_id', $quizId)
            ->where('user_id', $userId)
            ->first();

        if (!$existing) {
            QuizAttempt::create([
                'quiz_id'      => $quizId,
                'user_id'      => $userId,
                'student_id'   => $userId,
                'score'        => 0,
                'status'       => 'late_zero',
                'attempted_at' => now(),
            ]);
        }
    }

    public function performance(): View
    {
        $userId = Auth::id();
        $attempts = QuizAttempt::where('user_id', $userId)
            ->with(['quiz.subject', 'quiz.questions'])
            ->get();

        $passed = 0;
        $failed = 0;
        foreach ($attempts as $attempt) {
            $totalQuestions = $attempt->quiz->questions->count();
            $passScore = ceil($totalQuestions * 0.5);
            if ($attempt->score >= $passScore) {
                $passed++;
            } else {
                $failed++;
            }
        }

        $stats = [
            'total_quizzes' => $attempts->count(),
            'avg_score'     => $attempts->avg('score') ?? 0,
            'passed'        => $passed,
            'failed'        => $failed,
        ];

        return view('student.performance', compact('attempts', 'stats'));
    }
}
