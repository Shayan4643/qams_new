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
        // Add soft deletes to users for audit trail
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Add soft deletes to quizzes
        Schema::table('quizzes', function (Blueprint $table) {
            if (!Schema::hasColumn('quizzes', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Add soft deletes to assignments
        Schema::table('assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('assignments', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Add soft deletes to questions
        Schema::table('questions', function (Blueprint $table) {
            if (!Schema::hasColumn('questions', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });

        Schema::table('quizzes', function (Blueprint $table) {
            if (Schema::hasColumn('quizzes', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });

        Schema::table('assignments', function (Blueprint $table) {
            if (Schema::hasColumn('assignments', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });

        Schema::table('questions', function (Blueprint $table) {
            if (Schema::hasColumn('questions', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
