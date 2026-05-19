<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Helpers\AuditLogger;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TemporaryGuestController extends Controller
{
    public function index()
    {
        // Show anyone who has guest_expires_at set OR currently has a temporary role
        $roleUserIds = User::role(['temporary_guest', 'temporary_admin'])->pluck('id');

        $guests = User::where(function ($q) use ($roleUserIds) {
            $q->whereIn('id', $roleUserIds)
              ->orWhereNotNull('guest_expires_at');
        })
        ->orderByDesc('created_at')
        ->get();

        return view('admin.temporary-guests.index', compact('guests'));
    }

    public function create()
    {
        return view('admin.temporary-guests.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                    => ['required', 'string', 'max:255'],
            'email'                   => ['required', 'email', 'max:255', 'unique:users,email'],
            'callsign'                => ['nullable', 'string', 'max:20'],
            'expires_at'              => ['nullable', 'date', 'after:now'],
            'send_welcome'            => ['nullable', 'boolean'],
            'manual_password'         => ['nullable', 'string', 'min:8', 'confirmed'],
            'notes'                   => ['nullable', 'string', 'max:1000'],
            'access_level'            => ['nullable', 'string', 'in:guest,admin'],
        ]);

        $plainPassword = (! $request->boolean('send_welcome') && ! empty($validated['manual_password']))
            ? $validated['manual_password']
            : Str::random(24);

        $user = User::create([
            'name'              => $validated['name'],
            'email'             => $validated['email'],
            'callsign'          => strtoupper($validated['callsign'] ?? ''),
            'password'          => Hash::make($plainPassword),
            'email_verified_at' => now(),
            'guest_expires_at'  => $validated['expires_at'] ?? null,
            'notes'             => $validated['notes'] ?? null,
        ]);

        $role = ($validated['access_level'] ?? 'guest') === 'admin' ? 'temporary_admin' : 'temporary_guest';
        $user->assignRole($role);

        Log::info('Temporary guest created', [
            'guest_id'   => $user->id,
            'guest_name' => $user->name,
            'admin_id'   => auth()->id(),
        ]);
        AuditLogger::log('guest.created', $user, "Created temporary {$role} account for {$user->name}", [], [
            'email'       => $user->email,
            'expires_at'  => $user->guest_expires_at?->toDateTimeString(),
            'access_level'=> $role,
        ]);

        if ($request->boolean('send_welcome')) {
            Mail::to($user->email)->send(new \App\Mail\TemporaryGuestWelcome($user));
        }

        return redirect()
            ->route('admin.temporary-guests.index')
            ->with('success', "Temporary guest account created for {$user->name}.");
    }

    public function edit(User $user)
    {
        abort_unless($user->hasRole('temporary_guest') || $user->hasRole('temporary_admin') || $user->guest_expires_at, 404);
        return view('admin.temporary-guests.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        abort_unless($user->hasRole('temporary_guest') || $user->hasRole('temporary_admin') || $user->guest_expires_at, 404);

        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255', "unique:users,email,{$user->id}"],
            'callsign'   => ['nullable', 'string', 'max:20'],
            'expires_at' => ['nullable', 'date'],
            'notes'      => ['nullable', 'string', 'max:1000'],
        ]);

        $user->update([
            'name'             => $validated['name'],
            'email'            => $validated['email'],
            'callsign'         => strtoupper($validated['callsign'] ?? ''),
            'guest_expires_at' => $validated['expires_at'] ?? null,
            'notes'            => $validated['notes'] ?? null,
        ]);

        Log::info('Temporary guest updated', ['guest_id' => $user->id, 'admin_id' => auth()->id()]);
        AuditLogger::log('guest.updated', $user, "Updated temporary guest account for {$user->name}", [], [
            'expires_at' => $user->guest_expires_at?->toDateTimeString(),
        ]);

        return redirect()
            ->route('admin.temporary-guests.index')
            ->with('success', "{$user->name}'s guest account updated.");
    }

    public function destroy(User $user)
    {
        abort_unless($user->hasRole('temporary_guest') || $user->hasRole('temporary_admin') || $user->guest_expires_at, 404);
        $name = $user->name;

        // Clear the guest fields and remove role before deleting
        if ($user->hasRole('temporary_guest')) {
            $user->removeRole('temporary_guest');
        }
        $user->update(['guest_expires_at' => null]);
        $user->delete();

        Log::info('Temporary guest deleted', ['guest_name' => $name, 'admin_id' => auth()->id()]);
        AuditLogger::log('guest.deleted', null, "Deleted temporary guest account: {$name}");

        return redirect()
            ->route('admin.temporary-guests.index')
            ->with('success', "Guest account for {$name} deleted.");
    }

    public function disable(User $user)
    {
        abort_unless($user->hasRole('temporary_guest') || $user->hasRole('temporary_admin') || $user->guest_expires_at, 404);

        if ($user->hasRole('temporary_guest')) {
            $user->removeRole('temporary_guest');
        }
        $user->update(['guest_expires_at' => now()->subSecond()]);

        Log::info('Temporary guest disabled', ['guest_id' => $user->id, 'admin_id' => auth()->id()]);
        AuditLogger::log('guest.disabled', $user, "Manually disabled temporary guest access for {$user->name}");

        return redirect()
            ->route('admin.temporary-guests.index')
            ->with('success', "{$user->name}'s access has been revoked.");
    }

    public function reinstate(Request $request, User $user)
    {
        $validated = $request->validate([
            'expires_at' => ['nullable', 'date', 'after:now'],
        ]);

        if (! $user->hasRole('temporary_guest')) {
            $role = ($validated['access_level'] ?? 'guest') === 'admin' ? 'temporary_admin' : 'temporary_guest';
        $user->assignRole($role);
        }

        $user->update(['guest_expires_at' => $validated['expires_at'] ?? null]);

        Log::info('Temporary guest reinstated', ['guest_id' => $user->id, 'admin_id' => auth()->id()]);
        AuditLogger::log('guest.reinstated', $user, "Reinstated temporary guest access for {$user->name}", [], [
            'expires_at' => $user->guest_expires_at?->toDateTimeString(),
        ]);

        // Reset expiry notification flag so email fires again if they expire again
        $user->update(['guest_expiry_notified_at' => null]);

        // Send reinstatement email
        try {
            \Illuminate\Support\Facades\Mail::to($user->email)
                ->send(new \App\Mail\TemporaryGuestReinstated($user));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send guest reinstatement email', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }

        return redirect()
            ->route('admin.temporary-guests.index')
            ->with('success', "{$user->name} has been reinstated.");
    }
}
