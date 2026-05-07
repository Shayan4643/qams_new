<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAcademicSetupTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_admin_classes_page(): void
    {
        $teacher = User::factory()->create([
            'role' => 'teacher',
            'active' => 'yes',
        ]);

        $response = $this->actingAs($teacher)->get('/admin/classes');

        $response->assertRedirect('/dashboard');
    }

    public function test_admin_can_create_class_and_subject(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'active' => 'yes',
        ]);

        $this->actingAs($admin)->post('/admin/classes', [
            'name' => 'BSCS 5th',
            'code' => 'BSCS-5',
            'section' => 'A',
            'description' => 'Morning section',
            'active' => 'yes',
        ])->assertRedirect();

        $class = SchoolClass::where('code', 'BSCS-5')->first();
        $this->assertNotNull($class);

        $this->actingAs($admin)->post('/admin/subjects', [
            'class_id' => $class->id,
            'name' => 'Data Structures',
            'code' => 'CS301',
            'credit_hours' => 3,
            'description' => 'Core subject',
            'active' => 'yes',
        ])->assertRedirect();

        $this->assertDatabaseHas('subjects', [
            'code' => 'CS301',
            'class_id' => $class->id,
        ]);
    }

    public function test_duplicate_teacher_subject_assignment_is_blocked(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'active' => 'yes',
        ]);

        $teacher = User::factory()->create([
            'role' => 'teacher',
            'active' => 'yes',
        ]);

        $class = SchoolClass::create([
            'name' => 'BSSE 6th',
            'code' => 'BSSE-6',
            'section' => 'B',
            'description' => null,
            'active' => 'yes',
        ]);

        $subject = Subject::create([
            'class_id' => $class->id,
            'name' => 'Software Project Management',
            'code' => 'CS605',
            'credit_hours' => 3,
            'description' => null,
            'active' => 'yes',
        ]);

        $this->actingAs($admin)->post('/admin/assignments', [
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
        ])->assertRedirect();

        $this->actingAs($admin)->post('/admin/assignments', [
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
        ])->assertSessionHasErrors('subject_id');
    }
}
