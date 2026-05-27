<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Flags a user as a brand-new registration awaiting admin approval.
            // Completely separate from pending_callsign which is used for
            // callsign-change requests on already-active accounts.
            if (!\Illuminate\Support\Facades\Schema::hasColumn('users', 'registration_pending')) { $table->boolean('registration_pending')->default(false); }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('registration_pending');
        });
    }
};
