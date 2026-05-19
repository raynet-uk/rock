<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'callsign' => ['nullable', 'string', 'max:12', 'unique:' . User::class . ',callsign'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Base approval requirement from global setting
        $approvalRequired = Setting::registrationApprovalRequired();

        // RAYNET email auto-approval — bypass the queue for @raynet-uk.net addresses
        // if the admin has enabled this setting
        if ($approvalRequired) {
            $raynetAutoApproval = filter_var(
                Setting::get('raynet_email_auto_approval', true),
                FILTER_VALIDATE_BOOLEAN
            );

           // Check against built-in domain + any admin-configured extra domains
$trustedDomains  = ['raynet-uk.net'];
$extraDomains    = json_decode(\App\Models\Setting::get('auto_approve_domains', '[]'), true) ?? [];
$trustedDomains  = array_merge($trustedDomains, $extraDomains);

$emailDomain     = strtolower(substr(strrchr($request->email, '@'), 1));
$isRaynetEmail   = in_array($emailDomain, $trustedDomains);

            if ($raynetAutoApproval && $isRaynetEmail) {
                $approvalRequired = false;
            }
        }

        $user = User::create([
            'name'                 => $request->name,
            'email'                => $request->email,
            'password'             => Hash::make($request->password),
            'callsign'             => $request->callsign ? strtoupper($request->callsign) : null,
            'status'               => $approvalRequired ? null    : 'Active',
            'registration_pending' => $approvalRequired ? true    : false,
        ]);

        // Always send the verification email immediately on registration.
        // If approval is required, the user can verify their email while
        // they wait — but they won't be able to access the portal until
        // an admin approves their account.
        $user->sendEmailVerificationNotification();
        
        // Notify admin of new registration
try {
    $notifyEmail = Setting::get('registration_notify_email', '');
    if ($notifyEmail) {
        \Illuminate\Support\Facades\Notification::route('mail', $notifyEmail)
            ->notify(new \App\Notifications\NewRegistrationAdminNotification($user));
    }
} catch (\Throwable $e) {
    \Log::error('NewRegistrationAdminNotification failed: ' . $e->getMessage());
}
        
        if ($approvalRequired) {
            // Hold them at the pending page — don't log in
            return redirect()->route('register.pending');
        }

        // Approval not required (either globally off, or bypassed via RAYNET auto-approve)
        Auth::login($user);

        return redirect()->route('members');
    }
}