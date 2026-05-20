<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('photos', function (Blueprint $table) {
            $table->text('exif_data')->nullable()->after('admin_notes');
        });
    }
    public function down(): void {
        Schema::table('photos', function (Blueprint $table) {
            $table->dropColumn('exif_data');
        });
    }
};
