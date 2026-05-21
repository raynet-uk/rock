<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('photos', function (Blueprint $table) {
            // status = member-level approval (visible in members area)
            // public_status = public-facing approval (visible on gallery page)
            $table->enum('public_status', ['pending','approved','rejected'])->default('pending')->after('status');
            $table->unsignedBigInteger('approved_by')->nullable()->after('public_status');
            $table->unsignedBigInteger('public_approved_by')->nullable()->after('approved_by');
        });
    }
    public function down(): void {
        Schema::table('photos', function (Blueprint $table) {
            $table->dropColumn(['public_status','approved_by','public_approved_by']);
        });
    }
};
