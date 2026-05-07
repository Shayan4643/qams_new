<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            if (!Schema::hasColumn('classes', 'code')) {
                $table->string('code', 30)->nullable()->after('name');
            }
        });

        // Generate unique codes for existing rows
        $classes = DB::table('classes')->whereNull('code')->orWhere('code', '=', '')->get();
        foreach ($classes as $class) {
            DB::table('classes')->where('id', $class->id)->update(['code' => 'CLASS-' . $class->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            if (Schema::hasColumn('classes', 'code')) {
                $table->dropColumn('code');
            }
        });
    }
};
