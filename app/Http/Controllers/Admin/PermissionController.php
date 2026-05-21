<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class PermissionController extends Controller
{
    public function index()
    {
        $roles       = Role::with('permissions')->orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get();

        // Group permissions by category (everything before the first space)
        $grouped = $permissions->groupBy(function ($p) {
            $parts = explode(' ', $p->name, 2);
            return ucfirst($parts[0]);
        })->sortKeys();

        return view('admin.super.permissions', compact('roles', 'permissions', 'grouped'));
    }

    /**
     * Sync all permissions for a single role.
     */
    public function updateRole(Request $request, Role $role): RedirectResponse
    {
        // Prevent editing super-admin via this UI — it bypasses via Gate::before
        if ($role->name === 'super-admin') {
            return back()->with('error', 'super-admin permissions are managed via Gate::before and cannot be edited here.');
        }

        $permissionIds = $request->input('permissions', []);

        $permissions = Permission::whereIn('id', $permissionIds)->get();
        $role->syncPermissions($permissions);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return back()->with('success', "Permissions updated for {$role->name}.");
    }

    /**
     * Create a new permission.
     */
    public function createPermission(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:permissions,name'],
        ]);

        Permission::create(['name' => trim($request->name), 'guard_name' => 'web']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return back()->with('success', "Permission \"{$request->name}\" created.");
    }

    /**
     * Delete a permission (removes from all roles too).
     */
    public function toggleUserPermission(Request $request): RedirectResponse {
        $request->validate([
            'user_id'    => ['required','integer'],
            'permission' => ['required','string'],
        ]);
        $user = User::findOrFail($request->user_id);
        $perm = $request->permission;
        if ($user->hasDirectPermission($perm)) {
            $user->revokePermissionTo($perm);
            $msg = $perm . " revoked from {$user->name}.";
        } else {
            $user->givePermissionTo($perm);
            $msg = $perm . " granted to {$user->name}.";
            // Email the user
            if ($user->email) {
                try {
                    $grantedBy = auth()->user();
                    $groupName = \App\Helpers\RaynetSetting::groupName();
                    Mail::send('emails.photo-permission-granted', [
                        'user'       => $user,
                        'grantedBy'  => $grantedBy,
                        'permission' => $perm,
                        'groupName'  => $groupName,
                    ], function($m) use ($user, $groupName, $perm) {
                        $m->to($user->email, $user->name)
                          ->subject('Photo permission granted — ' . $groupName);
                    });
                } catch (\Throwable $e) {}
            }
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        return back()->with('success', $msg);
    }

    public function deletePermission(Permission $permission): RedirectResponse
    {
        $permission->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return back()->with('success', "Permission \"{$permission->name}\" deleted.");
    }
}
