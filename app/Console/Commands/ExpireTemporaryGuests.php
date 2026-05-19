<?php

namespace App\Console\Commands;

use App\Mail\TemporaryGuestExpired;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Helpers\AuditLogger;
use Illuminate\Support\Facades\Mail;

class ExpireTemporaryGuests extends Command
{
    protected $signature   = 'guests:expire';
    protected $description = 'Disable temporary guest accounts whose expiry date has passed';

    public function handle(): int
    {
        // Find guests who have expired but not yet been notified/deactivated
        $expired = User::role('temporary_guest')
            ->whereNotNull('guest_expires_at')
            ->where('guest_expires_at', '<=', now())
            ->whereNull('guest_expiry_notified_at')
            ->get();

        if ($expired->isEmpty()) {
            $this->info('No expired guest accounts found.');
            return self::SUCCESS;
        }

        foreach ($expired as $user) {
            // Mark as notified FIRST to prevent duplicate sends
            // even if the email or role removal fails
            $user->update(['guest_expiry_notified_at' => now()]);

            $user->removeRole('temporary_guest');

            try {
                Mail::to($user->email)->send(new TemporaryGuestExpired($user));
                $this->info("Email sent to: {$user->email}");
            } catch (\Throwable $e) {
                $this->warn("Email failed for {$user->email}: {$e->getMessage()}");
                Log::error('Failed to send guest expiry email', [
                    'user_id' => $user->id,
                    'error'   => $e->getMessage(),
                ]);
            }

            Log::info('Temporary guest account auto-expired', [
                'guest_id'    => $user->id,
                'guest_name'  => $user->name,
                'guest_email' => $user->email,
            ]);
            AuditLogger::log('guest.expired', $user, "Temporary guest access auto-expired for {$user->name}", [
                'expires_at' => $user->guest_expires_at?->toDateTimeString(),
            ], [
                'role' => 'removed',
            ]);

            $this->info("Expired: {$user->name} ({$user->email})");
        }

        $this->info("Done — {$expired->count()} account(s) expired.");
        return self::SUCCESS;
    }
}
