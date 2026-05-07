<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignmentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_create_assignment_for_assigned_subject(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher', 'active' => 'yes']);
        $class = SchoolClass::create([
            'name' => 'BSSE 8th',
            'code' => 'BSSE-8',
            'section' => 'A',
            'description' => null,
            'active' => 'yes',
        ]);
        $subject = Subject::create([
            'class_id' => $class->id,
            'name' => 'DevOps',
            'code' => 'CS702',
            'credit_hours' => 3,
            'description' => null,
            'active' => 'yes',
        ]);
        $teacher->taughtSubjects()->attach($subject->id);

        $this->actingAs($teacher)->post('/teacher/assignments', [
            'subject_id' => $subject->id,
            'title' => 'Docker Deployment Assignment',
            'description' => 'Prepare deployment file',
            'deadline' => now()->addDay()->format('Y-m-d H:i:s'),
            'published' => 'yes',
        ])->assertRedirect();

        $this->assertDatabaseHas('assignments', [
            'teacher_id' => $teacher->id,
            'title' => 'Docker Deployment Assignment',
        ]);
    }

    public function test_student_can_submit_assignment_before_deadline(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher', 'active' => 'yes']);
        $student = User::factory()->create(['role' => 'student', 'active' => 'yes']);
        $class = SchoolClass::create([
            'name' => 'BSIT 8th',
            'code' => 'BSIT-8',
            'section' => 'B',
            'description' => null,
            'active' => 'yes',
        ]);
        $subject = Subject::create([
            'class_id' => $class->id,
            'name' => 'Cloud Computing',
            'code' => 'CS704',
            'credit_hours' => 3,
            'description' => null,
            'active' => 'yes',
        ]);

        $assignment = Assignment::create([
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'title' => 'Cloud Lab Task',
            'description' => null,
            'deadline' => now()->addHour(),
            'published' => 'yes',
        ]);

        $this->actingAs($student)->post('/student/assignments/' . $assignment->id . '/submit', [
            'submission_text' => 'My solution content',
        ])->assertRedirect();

        $this->assertDatabaseHas('assignment_submissions', [
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'status' => 'submitted',
        ]);
    }

    public function test_late_submission_gets_zero_marks(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher', 'active' => 'yes']);
        $student = User::factory()->create(['role' => 'student', 'active' => 'yes']);
        $class = SchoolClass::create([
            'name' => 'BSCS 7th',
            'code' => 'BSCS-7',
            'section' => 'A',
            'description' => null,
            'active' => 'yes',
        ]);
        $subject = Subject::create([
            'class_id' => $class->id,
            'name' => 'Information Security',
            'code' => 'CS703',
            'credit_hours' => 3,
            'description' => null,
            'active' => 'yes',
        ]);

        $assignment = Assignment::create([
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'title' => 'Expired Assignment',
            'description' => null,
            'deadline' => now()->subHour(),
            'published' => 'yes',
        ]);

        $this->actingAs($student)->post('/student/assignments/' . $assignment->id . '/submit', [
            'submission_text' => 'Late text',
        ])->assertRedirect();

        $this->assertDatabaseHas('assignment_submissions', [
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'marks' => 0,
            'status' => 'late_zero',
        ]);
    }

    public function test_teacher_can_grade_student_submission(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher', 'active' => 'yes']);
        $student = User::factory()->create(['role' => 'student', 'active' => 'yes']);
        $class = SchoolClass::create([
            'name' => 'BSCS 6th',
            'code' => 'BSCS-6',
            'section' => 'A',
            'description' => null,
            'active' => 'yes',
        ]);
        $subject = Subject::create([
            'class_id' => $class->id,
            'name' => 'Data Mining',
            'code' => 'CS705',
            'credit_hours' => 3,
            'description' => null,
            'active' => 'yes',
        ]);

        $assignment = Assignment::create([
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'title' => 'Mining Task',
            'description' => null,
            'deadline' => now()->addDay(),
            'published' => 'yes',
        ]);

        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'file_path' => null,
            'marks' => 0,
            'status' => 'submitted',
            'teacher_feedback' => null,
            'submitted_at' => now(),
        ]);

        $this->actingAs($teacher)->post('/teacher/assignments/submissions/' . $submission->id . '/grade', [
            'marks' => 88,
            'teacher_feedback' => 'Good work.',
        ])->assertRedirect();

        $this->assertDatabaseHas('assignment_submissions', [
            'id' => $submission->id,
            'marks' => 88,
            'status' => 'graded',
        ]);
    }
}
