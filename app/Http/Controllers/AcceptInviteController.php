<?php

namespace App\Http\Controllers;

use App\Models\MemberApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AcceptInviteController extends Controller
{
    public function show(string $token)
    {
        $application = MemberApplication::where('invite_token', $token)
            ->where('status', 'approved')
            ->whereNull('converted_user_id')
            ->where('invite_sent_at', '>=', now()->subDays(7))
            ->firstOrFail();

        return view('pages.accept-invite', compact('application', 'token'));
    }

    public function submit(Request $request, string $token)
    {
        $application = MemberApplication::where('invite_token', $token)
            ->where('status', 'approved')
            ->whereNull('converted_user_id')
            ->where('invite_sent_at', '>=', now()->subDays(7))
            ->firstOrFail();

        $request->validate([
            'callsign' => ['required', 'string', 'max:20', 'unique:users,callsign'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Create the user
        $user = User::create([
            'name'     => $application->forenames . ' ' . $application->surname,
            'email'    => $application->email,
            'callsign' => strtoupper($request->callsign),
            'password' => Hash::make($request->password),
        ]);

        // Link application to user
        $application->update([
            'converted_user_id' => $user->id,
            'invite_token'      => null,
        ]);

        return redirect()->route('login')->with('success', 'Your account has been created! You can now log in.');
    }
}
