<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('net_log_history', function (Blueprint $table) {
            $table->id();
            $table->string('net_callsign', 30)->nullable();
            $table->string('net_name')->nullable();
            $table->string('frequency', 20)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->useCurrent();
            $table->json('stations');
            $table->integer('station_count')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('net_log_history');
    }
};
