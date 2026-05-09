<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // quiz_attempts: add student_id alias, status, submitted_at
        Schema::table('quiz_attempts', function (Blueprint $table) {
            if (!Schema::hasColumn('quiz_attempts', 'student_id')) {
                $table->unsignedBigInteger('student_id')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('quiz_attempts', 'status')) {
                $table->string('status', 20)->default('submitted')->after('score');
            }
            if (!Schema::hasColumn('quiz_attempts', 'submitted_at')) {
                $table->dateTime('submitted_at')->nullable()->after('status');
            }
        });

        // Copy user_id → student_id for existing rows
        if (Schema::hasColumn('quiz_attempts', 'user_id') && Schema::hasColumn('quiz_attempts', 'student_id')) {
            \DB::statement('UPDATE quiz_attempts SET student_id = user_id WHERE student_id IS NULL');
        }

        // assignment_submissions: add student_id alias, status, teacher_feedback
        Schema::table('assignment_submissions', function (Blueprint $table) {
            if (!Schema::hasColumn('assignment_submissions', 'student_id')) {
                $table->unsignedBigInteger('student_id')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('assignment_submissions', 'teacher_feedback')) {
                $table->text('teacher_feedback')->nullable()->after('feedback');
            }
            if (!Schema::hasColumn('assignment_submissions', 'status')) {
                $table->string('status', 30)->default('submitted')->after('marks');
            }
        });

        // Copy user_id → student_id + feedback → teacher_feedback for existing rows
        if (Schema::hasColumn('assignment_submissions', 'user_id') && Schema::hasColumn('assignment_submissions', 'student_id')) {
            \DB::statement('UPDATE assignment_submissions SET student_id = user_id WHERE student_id IS NULL');
        }
        
        if (Schema::hasColumn('assignment_submissions', 'feedback') && Schema::hasColumn('assignment_submissions', 'teacher_feedback')) {
            \DB::statement('UPDATE assignment_submissions SET teacher_feedback = feedback WHERE teacher_feedback IS NULL AND feedback IS NOT NULL');
        }

        // quizzes: add teacher_id and published columns
        Schema::table('quizzes', function (Blueprint $table) {
            if (!Schema::hasColumn('quizzes', 'teacher_id')) {
                $table->unsignedBigInteger('teacher_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('quizzes', 'published')) {
                $table->string('published', 3)->default('no')->after('is_published');
            }
        });

        // Sync published from is_published
        if (Schema::hasColumn('quizzes', 'is_published') && Schema::hasColumn('quizzes', 'published')) {
            \DB::statement("UPDATE quizzes SET published = CASE WHEN is_published = 1 THEN 'yes' ELSE 'no' END");
        }

        // assignments: add teacher_id and published columns
        Schema::table('assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('assignments', 'teacher_id')) {
                $table->unsignedBigInteger('teacher_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('assignments', 'published')) {
                $table->string('published', 3)->default('no')->after('is_published');
            }
        });

        if (Schema::hasColumn('assignments', 'is_published') && Schema::hasColumn('assignments', 'published')) {
            \DB::statement("UPDATE assignments SET published = CASE WHEN is_published = 1 THEN 'yes' ELSE 'no' END");
        }

        // classes: add active column
        Schema::table('classes', function (Blueprint $table) {
            if (!Schema::hasColumn('classes', 'active')) {
                $table->string('active', 3)->default('yes')->after('section');
            }
        });

        // subjects: check active column exists
        Schema::table('subjects', function (Blueprint $table) {
            if (!Schema::hasColumn('subjects', 'active')) {
                $table->string('active', 3)->default('yes');
            }
        });
    }

    public function down(): void
    {
        // Intentionally not dropping columns to preserve data safety
    }
};
