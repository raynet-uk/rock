<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('photos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('filename');
            $table->string('original_filename');
            $table->string('caption', 500)->nullable();
            $table->string('location', 200)->nullable();
            $table->date('taken_at')->nullable();
            $table->string('callsign', 20)->nullable();
            $table->boolean('consent')->default(false);
            $table->enum('status', ['pending','approved','rejected'])->default('pending');
            $table->boolean('featured')->default(false);
            $table->string('admin_notes', 500)->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('photos'); }
};
