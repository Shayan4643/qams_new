<?php

namespace App\Console\Commands;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Console\Command;

class AutoAssignZeroMarks extends Command
{
    protected $signature = 'marks:auto-zero';
    protected $description = 'Automatically assigns zero marks for missed quiz and assignment deadlines';

    public function handle()
    {
        $this->info('Starting auto-zero assignment...');

        // 1. Quizzes
        $expiredQuizzes = Quiz::where('deadline', '<', now())->get();
        foreach ($expiredQuizzes as $quiz) {
            // Find students in the class who haven't attempted this quiz
            $students = User::where('role', 'student')
                ->where('class_id', $quiz->subject->class_id)
                ->whereDoesntHave('quizAttempts', function ($q) use ($quiz) {
                    $q->where('quiz_id', $quiz->id);
                })->get();

            foreach ($students as $student) {
                QuizAttempt::create([
                    'quiz_id'    => $quiz->id,
                    'user_id'    => $student->id,
                    'student_id' => $student->id,
                    'score'      => 0,
                    'status'     => 'late_zero',
                    'attempted_at' => now(),
                ]);
            }
        }

        // 2. Assignments
        $expiredAssignments = Assignment::where('deadline', '<', now())->get();
        foreach ($expiredAssignments as $assignment) {
            // Find students enrolled in the class of the subject who haven't submitted
            $students = User::where('role', 'student')
                ->where('class_id', $assignment->subject->class_id)
                ->whereDoesntHave('assignmentSubmissions', function ($q) use ($assignment) {
                    $q->where('assignment_id', $assignment->id);
                })->get();

            foreach ($students as $student) {
                AssignmentSubmission::create([
                    'assignment_id' => $assignment->id,
                    'user_id'       => $student->id,
                    'student_id'    => $student->id,
                    'marks'         => 0,
                    'status'        => 'late_zero',
                    'feedback'      => 'Automated zero assigned for missed deadline.',
                    'submitted_at'  => now(),
                ]);
            }
        }

        $this->info('Auto-zero assignment completed.');
    }
}
