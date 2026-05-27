<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class MergeOperatorsIntoUsers extends Command
{
    protected $signature   = 'raynet:merge-operators';
    protected $description = 'One-time migration: copy operators table data into matching users rows';

    public function handle(): void
    {
        $operators = DB::table('operators')->get();
        $matched   = 0;
        $unmatched = [];

        foreach ($operators as $op) {
            // Try to match by email first, then fall back to name
            $user = null;

            if (!empty($op->email)) {
                $user = User::where('email', $op->email)->first();
            }

            if (!$user && !empty($op->name)) {
                $user = User::where('name', $op->name)->first();
            }

            if ($user) {
                // Only fill if the user row doesn't already have a value
                $user->role      = $user->role      ?? $op->role      ?? null;
                $user->level     = $user->level     ?? $op->level     ?? null;
                $user->status    = $user->status    ?? $op->status    ?? null;
                $user->phone     = $user->phone     ?? $op->phone     ?? null;
                $user->joined_at = $user->joined_at ?? $op->joined_at ?? null;
                $user->notes     = $user->notes     ?? $op->notes     ?? null;
                // Use operator callsign if user has none
                if (empty($user->callsign) && !empty($op->callsign)) {
                    $user->callsign = strtoupper($op->callsign);
                }
                $user->save();
                $matched++;
                $this->line("  ✓ Merged: {$op->name}");
            } else {
                $unmatched[] = $op->name;
            }
        }

        $this->info("\nDone. Matched: {$matched}");

        if (count($unmatched)) {
            $this->warn("Unmatched operators (no user account found — create manually):");
            foreach ($unmatched as $name) {
                $this->line("  — {$name}");
            }
        }
    }
}
