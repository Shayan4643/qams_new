<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('assignment_submissions')) {
            Schema::create('assignment_submissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('assignment_id')->constrained('assignments')->cascadeOnDelete();
                $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
                $table->string('file_path')->nullable();
                $table->unsignedInteger('marks')->default(0);
                $table->string('status', 30)->default('pending');
                $table->text('teacher_feedback')->nullable();
                $table->dateTime('submitted_at')->nullable();
                $table->timestamps();

                $table->unique(['assignment_id', 'student_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_submissions');
    }
};
