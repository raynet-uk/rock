<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class SpatieRoleSeeder extends Seeder
{
    public function run(): void
    {
        // Always clear cache before making changes
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ── 1. Roles ──────────────────────────────────────────────────────
        foreach (['super-admin', 'admin', 'committee', 'member', 'temporary_guest'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // ── 2. Permissions ────────────────────────────────────────────────
        $permissions = [
            // Users
            'manage users', 'view users', 'impersonate users',
            // Events
            'manage events', 'view events',
            // LMS / training
            'manage lms', 'view training',
            // Settings
            'manage settings', 'manage notifications',
            // Members area
            'view members', 'view dmr network',
            // Committee
            'view committee',
            'manage readiness', 'manage assets', 'manage networks',
            'manage exercises', 'manage actions', 'manage risks',
            'manage availability', 'view lrf report',
            'approve photos', 'feature photos',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // ── 3. Role → permission assignments ─────────────────────────────
        // super-admin bypasses all gates via Gate::before() — no perms needed here.

        Role::findByName('admin', 'web')->syncPermissions([
            'manage users', 'view users', 'impersonate users',
            'manage events', 'view events',
            'manage lms', 'view training',
            'manage settings', 'manage notifications',
            'view members', 'view dmr network',
            'view committee',
            'manage readiness', 'manage assets', 'manage networks',
            'manage exercises', 'manage actions', 'manage risks',
            'manage availability', 'view lrf report',
        ]);

        Role::findByName('committee', 'web')->syncPermissions([
            'view members', 'view training', 'view dmr network',
            'view committee',
            'manage readiness', 'manage assets', 'manage networks',
            'manage exercises', 'manage actions', 'manage risks',
            'manage availability', 'view lrf report',
        ]);

        Role::findByName('member', 'web')->syncPermissions([
            'view members', 'view training', 'view dmr network',
        ]);

        // ── 4. Migrate existing users ─────────────────────────────────────
        $this->command->info('Assigning Spatie roles to existing users…');
        $counts = ['super-admin' => 0, 'admin' => 0, 'committee' => 0, 'member' => 0, 'temporary_guest' => 0];

        User::chunk(100, function ($users) use (&$counts) {
            foreach ($users as $user) {
                // Wipe any existing Spatie roles (makes seeder idempotent)
                $user->syncRoles([]);

                $isSuperAdmin = (int)($user->getAttributes()['is_super_admin'] ?? 0) === 1;
                $isAdmin = (int)($user->getAttributes()['is_admin'] ?? 0) === 1;
                if ($isSuperAdmin) {
                    $user->assignRole('super-admin');
                    $counts['super-admin']++;
                } elseif ($isAdmin) {
                    $user->assignRole('admin');
                    $counts['admin']++;
                } elseif (($user->operator_title ?? '') === 'committee') {
                    $user->assignRole('committee');
                    $counts['committee']++;
                } else {
                    $user->assignRole('member');
                    $counts['member']++;
                }
            }
        });

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('Done:');
        foreach ($counts as $role => $n) {
            $this->command->line("  {$role}: {$n}");
        }
    }
}
