<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MemberApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class MemberApplicationAdminController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');
        $applications = MemberApplication::query()
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'pending'  => MemberApplication::where('status', 'pending')->count(),
            'approved' => MemberApplication::where('status', 'approved')->count(),
            'rejected' => MemberApplication::where('status', 'rejected')->count(),
            'all'      => MemberApplication::count(),
        ];

        return view('admin.member-applications.index', compact('applications', 'status', 'counts'));
    }

    public function show(MemberApplication $application)
    {
        return view('admin.member-applications.show', compact('application'));
    }

    public function convert(MemberApplication $application)
    {
        if ($application->status === 'approved') {
            return back()->with('error', 'This application has already been converted.');
        }

        // Check email not already registered
        if (User::where('email', $application->email)->exists()) {
            return back()->with('error', 'A user with this email address already exists.');
        }

        // Generate invite token
        $token = Str::random(64);

        // Update application
        $application->update([
            'status'         => 'approved',
            'invite_token'   => $token,
            'invite_sent_at' => now(),
        ]);

        // Send invite email
        $inviteUrl  = route('member-application.accept-invite', ['token' => $token]);
        $groupName  = \App\Models\Setting::get('group_name', 'RAYNET Group');
        $applicantName = $application->forenames;

        Mail::send([], [], function ($m) use ($application, $inviteUrl, $groupName, $applicantName) {
            $m->to($application->email)
              ->subject('Welcome to ' . $groupName . ' — Set up your account')
              ->html(
                  '<p>Dear ' . e($applicantName) . ',</p>'
                  . '<p>Your application to join <strong>' . e($groupName) . '</strong> has been approved! '
                  . 'You can now set up your online account by clicking the link below.</p>'
                  . '<p style="margin:24px 0">'
                  . '<a href="' . e($inviteUrl) . '" style="background:#1a7a3c;color:#fff;padding:12px 24px;text-decoration:none;border-radius:4px;font-weight:bold">Set Up My Account</a>'
                  . '</p>'
                  . '<p>This link will expire in 7 days. If you did not apply to join ' . e($groupName) . ', please ignore this email.</p>'
                  . '<p>73 de ' . e($groupName) . '</p>'
                  . '<hr><p style="font-size:11px;color:#666">If the button above does not work, copy and paste this link into your browser:<br>'
                  . '<a href="' . e($inviteUrl) . '">' . e($inviteUrl) . '</a></p>'
              );
        });

        return back()->with('success', 'Application approved and invite email sent to ' . $application->email);
    }

    public function reject(MemberApplication $application)
    {
        $application->update(['status' => 'rejected']);
        return back()->with('success', 'Application marked as rejected.');
    }

    public function destroy(MemberApplication $application)
    {
        $application->delete();
        return redirect()->route('admin.member-applications.index')->with('success', 'Application deleted.');
    }


    public function downloadPdf(MemberApplication $application)
    {
        if (!$application->pdf_path || !\Illuminate\Support\Facades\Storage::disk('local')->exists($application->pdf_path)) {
            return back()->with('error', 'PDF not found.');
        }
        $filename = 'REG-02_' . strtoupper($application->surname) . '_' . $application->forenames . '_' . $application->created_at->format('Ymd') . '.pdf';
        return response()->streamDownload(function () use ($application) {
            echo \Illuminate\Support\Facades\Storage::disk('local')->get($application->pdf_path);
        }, $filename, ['Content-Type' => 'application/pdf']);
    }

    public function downloadDoc(MemberApplication $application, string $type)
    {
        $path = $type === 'a' ? $application->doc_a_file : $application->doc_b_file;
        if (!$path || !\Illuminate\Support\Facades\Storage::disk('local')->exists($path)) {
            return back()->with('error', 'Document not found.');
        }
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $filename = 'DOC_' . strtoupper($type) . '_' . strtoupper($application->surname) . '.' . $ext;
        $mime = in_array(strtolower($ext), ['jpg','jpeg','png']) ? 'image/' . $ext : 'application/pdf';
        return response()->streamDownload(function () use ($path) {
            echo \Illuminate\Support\Facades\Storage::disk('local')->get($path);
        }, $filename, ['Content-Type' => $mime]);
    }
}
