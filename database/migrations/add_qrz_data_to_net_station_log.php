<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('net_station_log', function (Blueprint $table) {
            $table->json('qrz_data')->nullable()->after('notes');
            $table->boolean('is_registered')->default(false)->after('qrz_data');
            $table->string('photo_url')->nullable()->after('is_registered');
        });
    }
    public function down(): void {
        Schema::table('net_station_log', function (Blueprint $table) {
            $table->dropColumn(['qrz_data','is_registered','photo_url']);
        });
    }
};
