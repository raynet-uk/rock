<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pxr_reports', function (Blueprint $table) {
            $table->id();
            $table->string('exercise_name');
            $table->date('exercise_date');
            $table->string('exercise_type');
            $table->text('participants')->nullable();
            $table->dateTime('op_start')->nullable();
            $table->dateTime('op_end')->nullable();
            $table->string('location')->nullable();
            $table->string('author_name')->nullable();
            $table->string('author_callsign')->nullable();
            $table->string('group_name')->nullable();
            $table->string('distribution')->nullable();
            $table->text('primary_objective')->nullable();
            $table->text('jesip_principles_tested')->nullable();
            $table->text('chronology')->nullable();
            $table->text('jesip_colocation')->nullable();
            $table->text('jesip_communication')->nullable();
            $table->text('jesip_coordination')->nullable();
            $table->text('jesip_joint_risk')->nullable();
            $table->text('jesip_shared_awareness')->nullable();
            $table->text('rf_coverage')->nullable();
            $table->text('equipment_reliability')->nullable();
            $table->text('logistics')->nullable();
            $table->text('digital_performance')->nullable();
            $table->text('lessons')->nullable();
            $table->string('overall_grade')->nullable();
            $table->text('closing_statement')->nullable();
            $table->string('signed_by')->nullable();
            $table->date('signed_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pxr_reports');
    }
};
