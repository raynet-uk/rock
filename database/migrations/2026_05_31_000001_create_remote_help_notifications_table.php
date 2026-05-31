<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('remote_help_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('site_url');
            $table->string('site_name')->nullable();
            $table->string('code', 20);
            $table->string('group_name')->nullable();
            $table->timestamp('expires_at');
            $table->boolean('dismissed')->default(false);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('remote_help_notifications'); }
};
