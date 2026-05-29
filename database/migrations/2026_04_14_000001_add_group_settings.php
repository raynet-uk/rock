<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            // Group identity
            ['key' => 'group_name',       'value' => '',    'label' => 'Group Name',              'created_at' => now(), 'updated_at' => now()],
            ['key' => 'group_number',     'value' => '',    'label' => 'Group Number (e.g. 10/ME/179)', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'group_phone',      'value' => '',    'label' => 'Group Phone Number',              'created_at' => now(), 'updated_at' => now()],
            ['key' => 'group_callsign',   'value' => '',    'label' => 'Group Callsign',           'created_at' => now(), 'updated_at' => now()],
            ['key' => 'group_region',     'value' => '',    'label' => 'Region / Area',            'created_at' => now(), 'updated_at' => now()],
            ['key' => 'gc_name',          'value' => '',    'label' => 'Group Controller Name',    'created_at' => now(), 'updated_at' => now()],
            ['key' => 'gc_email',         'value' => '',    'label' => 'Group Controller Email',   'created_at' => now(), 'updated_at' => now()],
            ['key' => 'site_url',         'value' => '',    'label' => 'Site URL',                 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'support_request_email', 'value' => '', 'label' => 'Support Request Email', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'raynet_zone',      'value' => '',    'label' => 'RAYNET Zone',              'created_at' => now(), 'updated_at' => now()],
            ['key' => 'installed',        'value' => '0',   'label' => 'Installation Complete',    'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                $setting
            );
        }
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'group_name', 'group_number', 'group_callsign', 'group_region',
            'gc_name', 'gc_email', 'site_url', 'raynet_zone', 'installed',
        ])->delete();
    }
};
