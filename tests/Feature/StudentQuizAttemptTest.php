<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentQuizAttemptTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_student_cannot_access_student_quizzes(): void
    {
        $teacher = User::factory()->create([
            'role' => 'teacher',
            'active' => 'yes',
        ]);

        $this->actingAs($teacher)
            ->get('/student/quizzes')
            ->assertRedirect('/dashboard');
    }

    public function test_student_submission_is_auto_marked(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher', 'active' => 'yes']);
        $student = User::factory()->create(['role' => 'student', 'active' => 'yes']);

        $class = SchoolClass::create([
            'name' => 'BSCS 8th',
            'code' => 'BSCS-8',
            'section' => 'A',
            'description' => null,
            'active' => 'yes',
        ]);

        $subject = Subject::create([
            'class_id' => $class->id,
            'name' => 'Software Testing',
            'code' => 'CS607',
            'credit_hours' => 3,
            'description' => null,
            'active' => 'yes',
        ]);

        $question = Question::create([
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'question_text' => 'Which one is unit testing?',
            'option_a' => 'Testing a small module',
            'option_b' => 'Testing full product',
            'option_c' => 'User testing',
            'option_d' => 'None',
            'correct_option' => 'A',
            'active' => 'yes',
        ]);

        $quiz = Quiz::create([
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'title' => 'Quiz Auto Mark',
            'description' => null,
            'deadline' => now()->addHour(),
            'published' => 'yes',
        ]);
        $quiz->questions()->attach($question->id);

        $this->actingAs($student)->post('/student/quizzes/' . $quiz->id . '/submit', [
            'answers' => [
                $question->id => 'A',
            ],
        ])->assertRedirect('/student/quizzes/results');

        $this->assertDatabaseHas('quiz_attempts', [
            'quiz_id' => $quiz->id,
            'student_id' => $student->id,
            'score' => 1,
            'status' => 'submitted',
        ]);
    }

    public function test_late_attempt_assigns_zero_marks(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher', 'active' => 'yes']);
        $student = User::factory()->create(['role' => 'student', 'active' => 'yes']);

        $class = SchoolClass::create([
            'name' => 'BSIT 5th',
            'code' => 'BSIT-5',
            'section' => 'B',
            'description' => null,
            'active' => 'yes',
        ]);

        $subject = Subject::create([
            'class_id' => $class->id,
            'name' => 'Compiler Construction',
            'code' => 'CS606',
            'credit_hours' => 3,
            'description' => null,
            'active' => 'yes',
        ]);

        $quiz = Quiz::create([
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'title' => 'Expired Quiz',
            'description' => null,
            'deadline' => now()->subHour(),
            'published' => 'yes',
        ]);

        $this->actingAs($student)->get('/student/quizzes/' . $quiz->id . '/attempt')
            ->assertRedirect('/student/quizzes');

        $this->assertDatabaseHas('quiz_attempts', [
            'quiz_id' => $quiz->id,
            'student_id' => $student->id,
            'score' => 0,
            'status' => 'late_zero',
        ]);
    }
}
