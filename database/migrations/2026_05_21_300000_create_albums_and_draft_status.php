<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('albums', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name', 200);
            $table->string('description', 500)->nullable();
            $table->string('cover_photo_id')->nullable();
            $table->enum('status', ['draft','pending','approved','rejected'])->default('draft');
            $table->timestamps();
        });
        Schema::table('photos', function (Blueprint $table) {
            $table->unsignedBigInteger('album_id')->nullable()->after('user_id');
        });
        // Add draft to photos status enum
        \DB::statement("ALTER TABLE photos MODIFY COLUMN status ENUM('draft','pending','approved','rejected') DEFAULT 'draft'");
    }
    public function down(): void {
        Schema::dropIfExists('albums');
        Schema::table('photos', function (Blueprint $table) {
            $table->dropColumn('album_id');
        });
    }
};
