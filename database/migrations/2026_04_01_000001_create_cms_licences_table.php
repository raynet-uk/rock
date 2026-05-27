<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_licences', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();
            $table->string('group_name')->nullable();
            $table->string('group_number', 20)->nullable();
            $table->string('gc_name')->nullable();
            $table->string('gc_email')->nullable();
            $table->string('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('activated_at')->nullable();
            $table->string('activated_by_ip')->nullable();
            $table->string('activated_site_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_licences');
    }
};
