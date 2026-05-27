<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('net_station_log', function (Blueprint $table) {
            $table->id();
            $table->string('callsign', 20);
            $table->string('name')->nullable();
            $table->string('signal_report', 10)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('checked_in_at')->useCurrent();
            $table->timestamp('checked_out_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('net_station_log');
    }
};
