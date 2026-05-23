<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('net_schedules', function (Blueprint $table) {
            $table->boolean('station_logging')->default(false)->after('auto_activate');
        });
    }
    public function down(): void {
        Schema::table('net_schedules', function (Blueprint $table) {
            $table->dropColumn('station_logging');
        });
    }
};
