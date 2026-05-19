<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('resource_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('resource_id');
            $table->string('filename');
            $table->string('original_name');
            $table->string('version')->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->unsignedBigInteger('uploaded_by_user_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('resource_id');
        });
    }
    public function down(): void { Schema::dropIfExists('resource_versions'); }
};
