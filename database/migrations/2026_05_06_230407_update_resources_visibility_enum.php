<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        // MySQL doesn't support modifying enums directly — use string then re-cast
        DB::statement("ALTER TABLE resources MODIFY COLUMN visibility ENUM('public','members','committee','admin') NOT NULL DEFAULT 'public'");
    }
    public function down(): void {
        DB::statement("ALTER TABLE resources MODIFY COLUMN visibility ENUM('public','members') NOT NULL DEFAULT 'public'");
    }
};
