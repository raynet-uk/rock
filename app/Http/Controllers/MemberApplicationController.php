<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Models\MemberApplication;
use Illuminate\Support\Str;

class MemberApplicationController extends Controller
{
    public function show()
    {
        return view('pages.member-application');
    }

    public function generateSignToken()
    {
        $token = Str::uuid()->toString();
        Cache::put('sig_session_' . $token, ['created_at' => now()->toISOString()], now()->addMinutes(15));
        return response()->json(['token' => $token]);
    }

    public function signPage(string $token)
    {
        if (!Cache::has('sig_session_' . $token) && !Cache::has('sig_result_' . $token)) {
            abort(404, 'Signing link has expired or is invalid.');
        }
        $already = Cache::has('sig_result_' . $token);
        return view('pages.signature-pad', compact('token', 'already'));
    }

    public function signSubmit(Request $request, string $token)
    {
        $request->validate(['signature' => ['required', 'string']]);
        if (!Cache::has('sig_session_' . $token) && !Cache::has('sig_result_' . $token)) {
            return response()->json(['ok' => false, 'error' => 'Token expired'], 422);
        }
        Cache::put('sig_result_' . $token, [
            'signature' => $request->signature,
            'signed_at' => now()->toISOString(),
        ], now()->addMinutes(30));
        Cache::forget('sig_session_' . $token);
        return response()->json(['ok' => true]);
    }

    public function signStatus(string $token)
    {
        $result = Cache::get('sig_result_' . $token);
        if ($result) {
            return response()->json(['ok' => true, 'signature' => $result['signature'], 'signed_at' => $result['signed_at']]);
        }
        return response()->json(['ok' => false]);
    }

    public function submit(Request $request)
    {
        $data = $request->validate([
            'callsign'            => ['nullable', 'string', 'max:20'],
            'title'               => ['nullable', 'string', 'max:20'],
            'surname'             => ['required', 'string', 'max:100'],
            'forenames'           => ['required', 'string', 'max:100'],
            'known_as'            => ['nullable', 'string', 'max:100'],
            'dob'                 => ['required', 'date'],
            'home_tel'            => ['nullable', 'string', 'max:30'],
            'home_tel_ex'         => ['nullable', 'boolean'],
            'mobile'              => ['nullable', 'string', 'max:30'],
            'mobile_ex'           => ['nullable', 'boolean'],
            'nationality'         => ['nullable', 'string', 'max:80'],
            'former_nationality'  => ['nullable', 'string', 'max:80'],
            'place_of_birth'      => ['nullable', 'string', 'max:100'],
            'email'               => ['required', 'email', 'max:255'],
            'address'             => ['required', 'string', 'max:500'],
            'doc_a_type'          => ['nullable', 'string', 'max:200'],
            'doc_a_date'          => ['nullable', 'string', 'max:30'],
            'doc_a_ref'           => ['nullable', 'string', 'max:100'],
            'doc_a_file'          => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'doc_b_type'          => ['nullable', 'string', 'max:200'],
            'doc_b_date'          => ['nullable', 'string', 'max:30'],
            'doc_b_ref'           => ['nullable', 'string', 'max:100'],
            'doc_b_file'          => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'criminal_1'          => ['nullable', 'in:yes,no'],
            'criminal_1_detail'   => ['nullable', 'string', 'max:500'],
            'criminal_2'          => ['nullable', 'in:yes,no'],
            'criminal_2_detail'   => ['nullable', 'string', 'max:500'],
            'criminal_3'          => ['nullable', 'in:yes,no'],
            'criminal_3_detail'   => ['nullable', 'string', 'max:500'],
            'comms_national_email'=> ['nullable', 'boolean'],
            'comms_group_email'   => ['nullable', 'boolean'],
            'comms_national_tel'  => ['nullable', 'boolean'],
            'comms_group_tel'     => ['nullable', 'boolean'],
            'comms_national_sms'  => ['nullable', 'boolean'],
            'comms_group_sms'     => ['nullable', 'boolean'],
            'comms_national_post' => ['nullable', 'boolean'],
            'comms_group_post'    => ['nullable', 'boolean'],
            'signature'           => ['nullable', 'string'],
            'sig_token'           => ['nullable', 'string'],
        ]);

        // Resolve signature
        $signature = null;
        if (!empty($data['signature']) && str_starts_with($data['signature'], 'data:image/')) {
            $signature = $data['signature'];
        } elseif (!empty($data['sig_token'])) {
            $cached = Cache::get('sig_result_' . $data['sig_token']);
            if ($cached) {
                $signature = $cached['signature'];
                Cache::forget('sig_result_' . $data['sig_token']);
            }
        }

        // Handle document uploads
        $docAPath = null;
        $docBPath = null;
        if ($request->hasFile('doc_a_file')) {
            $docAPath = $request->file('doc_a_file')->store('member-application-docs', 'local');
        }
        if ($request->hasFile('doc_b_file')) {
            $docBPath = $request->file('doc_b_file')->store('member-application-docs', 'local');
        }

        $groupName = Setting::get('group_name', 'RAYNET Group');

        // Generate PDF and store it
        $pdfContent  = $this->generatePdf($data, $signature, $groupName);
        $pdfFilename = 'REG-02_' . strtoupper(preg_replace('/\s+/', '_', trim($data['surname'] . '_' . $data['forenames']))) . '_' . date('Ymd') . '.pdf';
        $pdfStorePath = 'member-application-pdfs/' . $pdfFilename;
        Storage::disk('local')->put($pdfStorePath, $pdfContent);

        // Save application
        MemberApplication::create([
            'callsign'            => $data['callsign'] ?? null,
            'title'               => $data['title'] ?? null,
            'surname'             => $data['surname'],
            'forenames'           => $data['forenames'],
            'known_as'            => $data['known_as'] ?? null,
            'dob'                 => $data['dob'],
            'email'               => $data['email'],
            'home_tel'            => $data['home_tel'] ?? null,
            'home_tel_ex'         => !empty($data['home_tel_ex']),
            'mobile'              => $data['mobile'] ?? null,
            'mobile_ex'           => !empty($data['mobile_ex']),
            'nationality'         => $data['nationality'] ?? null,
            'former_nationality'  => $data['former_nationality'] ?? null,
            'place_of_birth'      => $data['place_of_birth'] ?? null,
            'address'             => $data['address'],
            'doc_a_type'          => $data['doc_a_type'] ?? null,
            'doc_a_date'          => $data['doc_a_date'] ?? null,
            'doc_a_ref'           => $data['doc_a_ref'] ?? null,
            'doc_a_file'          => $docAPath,
            'doc_b_type'          => $data['doc_b_type'] ?? null,
            'doc_b_date'          => $data['doc_b_date'] ?? null,
            'doc_b_ref'           => $data['doc_b_ref'] ?? null,
            'doc_b_file'          => $docBPath,
            'criminal_1'          => $data['criminal_1'] ?? 'no',
            'criminal_1_detail'   => $data['criminal_1_detail'] ?? null,
            'criminal_2'          => $data['criminal_2'] ?? 'no',
            'criminal_2_detail'   => $data['criminal_2_detail'] ?? null,
            'criminal_3'          => $data['criminal_3'] ?? 'no',
            'criminal_3_detail'   => $data['criminal_3_detail'] ?? null,
            'comms_national_email'=> !empty($data['comms_national_email']),
            'comms_group_email'   => !empty($data['comms_group_email']),
            'comms_national_tel'  => !empty($data['comms_national_tel']),
            'comms_group_tel'     => !empty($data['comms_group_tel']),
            'comms_national_sms'  => !empty($data['comms_national_sms']),
            'comms_group_sms'     => !empty($data['comms_group_sms']),
            'comms_national_post' => !empty($data['comms_national_post']),
            'comms_group_post'    => !empty($data['comms_group_post']),
            'signature_data'      => $signature,
            'pdf_path'            => $pdfStorePath,
        ]);

        $gcEmail = Setting::get('gc_email');

        // Email to GC with PDF attached
        Mail::send([], [], function ($m) use ($gcEmail, $pdfContent, $pdfFilename, $data, $groupName, $signature) {
            $m->to($gcEmail)
              ->subject('New Member Application — ' . $data['forenames'] . ' ' . $data['surname'])
              ->html(
                  '<p>A new member application has been submitted via the ' . e($groupName) . ' website.</p>'
                  . '<p><strong>Name:</strong> ' . e($data['forenames'] . ' ' . $data['surname']) . '<br>'
                  . '<strong>Callsign:</strong> ' . e($data['callsign'] ?? '—') . '<br>'
                  . '<strong>DOB:</strong> ' . e($data['dob']) . '<br>'
                  . '<strong>Email:</strong> ' . e($data['email']) . '</p>'
                  . '<p>The completed REG-02 form is attached as a PDF.</p>'
                  . (!empty($signature) ? '<p>&#x2713; Digital signature captured.</p>' : '<p>&#x26A0; No digital signature — requires handwritten signature.</p>')
              )
              ->attachData($pdfContent, $pdfFilename, ['mime' => 'application/pdf']);
        });

        // Confirmation to applicant
        Mail::send([], [], function ($m) use ($data, $groupName) {
            $m->to($data['email'])
              ->subject('Your RAYNET Application — ' . $groupName)
              ->html(
                  '<p>Dear ' . e($data['forenames']) . ',</p>'
                  . '<p>Thank you for submitting your application to join ' . e($groupName) . '. '
                  . 'Your REG-02 form has been sent to the Group Controller for processing.</p>'
                  . '<p>73 de ' . e($groupName) . '</p>'
              );
        });

        return redirect()->route('member-application.success');
    }

    private function generatePdf(array $data, ?string $signature, string $groupName): string
    {
        $blankPdf  = storage_path('app/forms/reg02_blank.pdf');
        $outputPdf = tempnam(sys_get_temp_dir(), 'reg02_') . '.pdf';
        $jsonFile  = tempnam(sys_get_temp_dir(), 'reg02_') . '.json';

        $payload = [
            'blank_pdf' => $blankPdf,
            'data'      => $data,
            'signature' => $signature,
        ];

        file_put_contents($jsonFile, json_encode($payload));

        $script = app_path('Libraries/fill_reg02.py');
        $cmd    = escapeshellcmd('/usr/bin/python3 ' . escapeshellarg($script) . ' ' . escapeshellarg($jsonFile) . ' ' . escapeshellarg($outputPdf));
        $output = shell_exec($cmd . ' 2>&1');

        @unlink($jsonFile);

        if (!file_exists($outputPdf) || filesize($outputPdf) === 0) {
            @unlink($outputPdf);
            throw new \RuntimeException('PDF generation failed: ' . ($output ?? 'no output'));
        }

        $content = file_get_contents($outputPdf);
        @unlink($outputPdf);
        return $content;
    }

    public function success()
    {
        return view('pages.member-application-success');
    }
}
