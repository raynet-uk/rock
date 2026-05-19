<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('resource_followers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('category');
            $table->enum('visibility', ['public','members','both'])->default('both');
            $table->timestamps();
            $table->unique(['user_id','category']);
        });
    }
    public function down(): void { Schema::dropIfExists('resource_followers'); }
};
