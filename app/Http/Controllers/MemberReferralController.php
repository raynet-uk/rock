<?php

namespace App\Http\Controllers;

use App\Services\QRZLookup;
use App\Models\Referral;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MemberReferralController extends Controller
{
    public function show()
    {
        return view('members.refer', [
            'groupName'   => \App\Helpers\RaynetSetting::groupName(),
            'groupRegion' => \App\Helpers\RaynetSetting::groupRegion(),
        ]);
    }

    public function lookup(string $callsign, QRZLookup $qrz)
    {
        $data = $qrz->lookup($callsign);
        return response()->json($data ?? []);
    }

    public function send(Request $request)
    {
        $request->validate([
            'callsign'     => ['nullable', 'string', 'max:20'],
            'is_operator'  => ['nullable', 'in:0,1'],
            'email'    => ['required', 'email', 'max:255'],
            'name'     => ['nullable', 'string', 'max:100'],
        ]);

        $referrer  = auth()->user();
        $callsign   = strtoupper(trim($request->callsign ?? ''));
        $isOperator = $request->input('is_operator', '1') === '1';
        $email     = trim($request->email);
        $name      = trim($request->name) ?: $callsign;
        $groupName = \App\Helpers\RaynetSetting::groupName();
        $groupRegion = \App\Helpers\RaynetSetting::groupRegion();
        $gcEmail   = \App\Models\Setting::get('gc_email', '');
        $joinUrl   = route('member-application');

        try {
            $pdfPath = storage_path('app/forms/reg02_blank.pdf');

            Mail::send(
                'emails.referral-invite',
                compact('name', 'callsign', 'email', 'referrer', 'groupName', 'groupRegion', 'gcEmail', 'joinUrl', 'isOperator'),
                function ($m) use ($email, $name, $groupName, $pdfPath) {
                    $m->to($email, $name)
                      ->subject("You've been invited to join {$groupName}");
                    if (file_exists($pdfPath)) {
                        $m->attach($pdfPath, ['as' => 'RAYNET-REG02-Application.pdf', 'mime' => 'application/pdf']);
                    }
                }
            );

            Log::info("Referral invite sent to {$callsign} ({$email}) by {$referrer->name}");
            Referral::create([
                'referrer_id' => $referrer->id,
                'callsign'    => $callsign,
                'email'       => $email,
                'name'        => $name,
                'sent_at'     => now(),
            ]);

            return back()->with('refer_success', "Invite sent to {$name} ({$callsign}) at {$email}.");

        } catch (\Throwable $e) {
            Log::error("Referral invite failed: " . $e->getMessage());
            return back()->withErrors(['email' => 'Failed to send invite. Please try again.'])->withInput();
        }
    }
}
