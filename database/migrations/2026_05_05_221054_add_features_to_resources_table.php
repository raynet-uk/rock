<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('resources', function (Blueprint $table) {
            $table->string('tags')->nullable()->after('category');
            $table->boolean('pinned')->default(false)->after('tags');
            $table->boolean('featured')->default(false)->after('pinned');
            $table->timestamp('expires_at')->nullable()->after('featured');
            $table->unsignedBigInteger('download_count')->default(0)->after('expires_at');
            $table->string('version')->nullable()->after('download_count');
            $table->unsignedBigInteger('uploaded_by_user_id')->nullable()->after('uploaded_by');
        });
    }
    public function down(): void {
        Schema::table('resources', function (Blueprint $table) {
            $table->dropColumn(['tags','pinned','featured','expires_at','download_count','version','uploaded_by_user_id']);
        });
    }
};
