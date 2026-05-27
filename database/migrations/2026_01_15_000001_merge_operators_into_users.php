<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->nullable();        // e.g. "Net Controller", "Operator"
            $table->integer('level')->nullable();          // operator training level
            $table->string('status')->nullable();         // Active / Inactive / Standby
            $table->string('phone')->nullable();
            $table->date('joined_at')->nullable();
            $table->text('notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'level', 'status', 'phone', 'joined_at', 'notes']);
        });
    }
};
