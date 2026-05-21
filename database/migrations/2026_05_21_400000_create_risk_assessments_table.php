<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('risk_assessments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('event_name');
            $table->string('location')->nullable();
            $table->date('event_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('finish_time')->nullable();
            $table->string('attendance')->nullable();
            $table->json('environment')->nullable();
            $table->json('event_type')->nullable();
            $table->json('other_agencies')->nullable();
            $table->string('operator_count')->nullable();
            $table->json('roles')->nullable();
            $table->json('communications')->nullable();
            $table->json('infrastructure')->nullable();
            $table->json('terrain')->nullable();
            $table->string('operator_movement')->nullable();
            $table->string('weather_exposure')->nullable();
            $table->string('road_exposure')->nullable();
            $table->string('access')->nullable();
            $table->string('deployment_duration')->nullable();
            $table->json('facilities')->nullable();
            $table->string('lone_working')->nullable();
            $table->string('under_18')->nullable();
            $table->string('night_operation')->nullable();
            $table->json('equipment')->nullable();
            $table->string('power_source')->nullable();
            $table->string('vehicles_operating')->nullable();
            $table->string('public_order')->nullable();
            $table->string('weather_contingency')->nullable();
            $table->json('fallback_comms')->nullable();
            $table->json('withdrawal_authority')->nullable();
            $table->string('notes')->nullable();
            $table->enum('rag_status', ['green','amber','red'])->nullable();
            $table->enum('status', ['draft','pending','approved','rejected'])->default('draft');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->integer('version')->default(1);
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('risk_assessments'); }
};
