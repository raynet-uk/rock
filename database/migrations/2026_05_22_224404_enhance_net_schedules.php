<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('net_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('net_schedules', 'band'))
                $table->string('band')->nullable()->after('frequency');
            if (!Schema::hasColumn('net_schedules', 'repeat_type'))
                $table->string('repeat_type')->default('weekly')->after('days_of_week');
            if (!Schema::hasColumn('net_schedules', 'repeat_anchor'))
                $table->date('repeat_anchor')->nullable()->after('repeat_type');
            if (!Schema::hasColumn('net_schedules', 'priority'))
                $table->string('priority')->default('routine')->after('repeat_anchor');
            if (!Schema::hasColumn('net_schedules', 'announcement'))
                $table->text('announcement')->nullable()->after('description');
            if (!Schema::hasColumn('net_schedules', 'controller_slots'))
                $table->json('controller_slots')->nullable()->after('controller');
        });
    }
    public function down(): void {
        Schema::table('net_schedules', function (Blueprint $table) {
            $table->dropColumn(['band','repeat_type','repeat_anchor','priority','announcement','controller_slots']);
        });
    }
};
