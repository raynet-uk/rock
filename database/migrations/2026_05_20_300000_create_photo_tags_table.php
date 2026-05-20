<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('photo_tags', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('photo_id');
            $table->unsignedBigInteger('user_id')->nullable(); // null if not a site member
            $table->string('callsign', 20)->nullable();
            $table->string('name', 100)->nullable();
            $table->decimal('x_pct', 5, 2); // % from left
            $table->decimal('y_pct', 5, 2); // % from top
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('photo_tags'); }
};
