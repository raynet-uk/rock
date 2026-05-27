<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'force_password_reset')) {
                $table->boolean('force_password_reset')->default(false);
            }
            if (! Schema::hasColumn('users', 'suspended_at')) {
                $table->timestamp('suspended_at')->nullable();
            }
            if (! Schema::hasColumn('users', 'suspension_message')) {
                $table->text('suspension_message')->nullable();
            }
            if (! Schema::hasColumn('users', 'admin_message')) {
                $table->text('admin_message')->nullable();
            }
            if (! Schema::hasColumn('users', 'dismissed_broadcast_id')) {
                $table->unsignedBigInteger('dismissed_broadcast_id')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'force_password_reset',
                'suspended_at',
                'suspension_message',
                'admin_message',
                'dismissed_broadcast_id',
            ]);
        });
    }
};
