<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherQuizFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_teacher_cannot_access_teacher_question_bank(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'active' => 'yes',
        ]);

        $response = $this->actingAs($student)->get('/teacher/question-bank');

        $response->assertRedirect('/dashboard');
    }

    public function test_teacher_can_add_question_for_assigned_subject(): void
    {
        $teacher = User::factory()->create([
            'role' => 'teacher',
            'active' => 'yes',
        ]);

        $class = SchoolClass::create([
            'name' => 'BSCS 6th',
            'code' => 'BSCS-6',
            'section' => 'A',
            'description' => null,
            'active' => 'yes',
        ]);

        $subject = Subject::create([
            'class_id' => $class->id,
            'name' => 'Web Engineering',
            'code' => 'CS604',
            'credit_hours' => 3,
            'description' => null,
            'active' => 'yes',
        ]);

        $teacher->taughtSubjects()->attach($subject->id);

        $this->actingAs($teacher)->post('/teacher/question-bank', [
            'subject_id' => $subject->id,
            'question_text' => 'What does MVC stand for?',
            'option_a' => 'Model View Controller',
            'option_b' => 'Model Visual Code',
            'option_c' => 'Main View Class',
            'option_d' => 'None',
            'correct_option' => 'A',
            'active' => 'yes',
        ])->assertRedirect();

        $this->assertDatabaseHas('questions', [
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
        ]);
    }

    public function test_teacher_can_create_quiz_with_own_questions(): void
    {
        $teacher = User::factory()->create([
            'role' => 'teacher',
            'active' => 'yes',
        ]);

        $class = SchoolClass::create([
            'name' => 'BSIT 7th',
            'code' => 'BSIT-7',
            'section' => 'B',
            'description' => null,
            'active' => 'yes',
        ]);

        $subject = Subject::create([
            'class_id' => $class->id,
            'name' => 'Software QA',
            'code' => 'CS601',
            'credit_hours' => 3,
            'description' => null,
            'active' => 'yes',
        ]);

        $teacher->taughtSubjects()->attach($subject->id);

        $question = Question::create([
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'question_text' => 'Testing type for small code units?',
            'option_a' => 'Unit Testing',
            'option_b' => 'System Testing',
            'option_c' => 'Acceptance Testing',
            'option_d' => 'Smoke Testing',
            'correct_option' => 'A',
            'active' => 'yes',
        ]);

        $this->actingAs($teacher)->post('/teacher/quizzes', [
            'subject_id' => $subject->id,
            'title' => 'Quiz 1',
            'description' => 'Basic concepts',
            'deadline' => now()->addDay()->format('Y-m-d H:i:s'),
            'question_ids' => [$question->id],
        ])->assertRedirect();

        $this->assertDatabaseHas('quizzes', [
            'teacher_id' => $teacher->id,
            'title' => 'Quiz 1',
        ]);
        $this->assertDatabaseHas('quiz_question', [
            'question_id' => $question->id,
        ]);
    }
}
