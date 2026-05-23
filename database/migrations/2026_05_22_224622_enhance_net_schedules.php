<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('net_schedules', function (Blueprint $table) {
            $table->string('band')->nullable()->after('frequency');
            $table->string('repeat_type')->default('weekly')->after('days_of_week');
            $table->date('repeat_anchor')->nullable()->after('repeat_type');
            $table->string('priority')->default('routine')->after('repeat_anchor');
            $table->text('announcement')->nullable()->after('description');
            $table->json('controller_slots')->nullable()->after('controller');
        });
    }
    public function down(): void {
        Schema::table('net_schedules', function (Blueprint $table) {
            $table->dropColumn(['band','repeat_type','repeat_anchor','priority','announcement','controller_slots']);
        });
    }
};
