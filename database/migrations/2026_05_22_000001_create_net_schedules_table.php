<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('net_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('callsign', 30);
            $table->string('frequency', 30)->nullable();
            $table->string('controller', 30)->nullable();
            $table->text('description')->nullable();
            $table->json('days_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('auto_activate')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('net_schedules'); }
};
