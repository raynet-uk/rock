<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('raynet_publications', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['news', 'checkpoint']); // RAYNET News or Checkpoint
            $table->string('title');
            $table->string('edition')->nullable();         // e.g. "Spring 2026", "Issue 47"
            $table->date('published_date');
            $table->text('description')->nullable();
            $table->string('file_path')->nullable();       // uploaded PDF
            $table->string('cover_image_path')->nullable();// optional cover thumbnail
            $table->string('external_url')->nullable();    // or link to external PDF
            $table->boolean('is_current')->default(false); // featured/current edition
            $table->boolean('is_published')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('raynet_publications'); }
};
