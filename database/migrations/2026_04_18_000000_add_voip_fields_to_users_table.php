<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('allstar_node',    100)->nullable()->after('aprs_ssid');
            $table->string('svxlink_network', 100)->nullable()->after('allstar_node');
            $table->string('raynet_voip',     100)->nullable()->after('svxlink_network');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['allstar_node', 'svxlink_network', 'raynet_voip']);
        });
    }
};
