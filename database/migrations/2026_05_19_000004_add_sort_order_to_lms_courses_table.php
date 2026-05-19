<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lms_courses', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('id');
        });

        // Initialise sort_order based on created_at order
        $courses = DB::table('lms_courses')->orderBy('created_at')->pluck('id');
        foreach ($courses as $i => $id) {
            DB::table('lms_courses')->where('id', $id)->update(['sort_order' => $i]);
        }
    }

    public function down(): void
    {
        Schema::table('lms_courses', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
