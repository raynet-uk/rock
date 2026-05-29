<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('remote_help_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique();
            $table->string('code', 12)->unique(); // short human-readable code
            $table->timestamp('expires_at');
            $table->boolean('used')->default(false);
            $table->string('created_by_name')->nullable();
            $table->string('created_by_email')->nullable();
            $table->timestamp('accessed_at')->nullable();
            $table->string('accessed_by_ip')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('remote_help_tokens'); }
};
