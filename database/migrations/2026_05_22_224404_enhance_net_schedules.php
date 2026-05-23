<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('net_schedules', function (Blueprint $table) {
            $table->string('band')->nullable()->after('frequency');          // hf/vhf/uhf/shf
            $table->string('repeat_type')->default('weekly')->after('days_of_week'); // weekly/fortnightly/monthly
            $table->date('repeat_anchor')->nullable()->after('repeat_type'); // reference date for fortnightly/monthly
            $table->string('priority')->default('routine')->after('repeat_anchor'); // routine/urgent/emergency
            $table->text('announcement')->nullable()->after('description');  // pre-net message
            $table->json('controller_slots')->nullable()->after('controller'); // [{callsign,from,to}]
        });
    }
    public function down(): void {
        Schema::table('net_schedules', function (Blueprint $table) {
            $table->dropColumn(['band','repeat_type','repeat_anchor','priority','announcement','controller_slots']);
        });
    }
};
