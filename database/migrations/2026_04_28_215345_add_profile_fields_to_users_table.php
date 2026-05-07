<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'admission_number')) {
                $table->string('admission_number')->nullable()->after('user_name');
            }
            if (!Schema::hasColumn('users', 'father_name')) {
                $table->string('father_name')->nullable()->after('admission_number');
            }
            if (!Schema::hasColumn('users', 'profile_picture')) {
                $table->string('profile_picture')->nullable()->after('father_name');
            }
            if (!Schema::hasColumn('users', 'class_id')) {
                $table->unsignedBigInteger('class_id')->nullable()->after('profile_picture');
            }
            if (!Schema::hasColumn('users', 'job_history')) {
                $table->text('job_history')->nullable()->after('class_id');
            }
            if (!Schema::hasColumn('users', 'education')) {
                $table->text('education')->nullable()->after('job_history');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $cols = ['admission_number', 'father_name', 'profile_picture', 'class_id', 'job_history', 'education'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
