<?php

namespace App\Console\Commands;

use App\Models\Resource;
use App\Models\User;
use App\Notifications\ResourcePendingNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class FetchResourceEmails extends Command
{
    protected $signature   = 'resources:fetch-emails';
    protected $description = 'Fetch emailed resources from all drive mailboxes';

    protected array $allowedExts = ['pdf','doc','docx','xls','xlsx','ppt','pptx','jpg','jpeg','png','gif','zip','txt','csv'];
    protected int $maxBytes = 20971520;

    protected array $driveRoles = [
        'public'    => [],
        'members'   => ['member','committee','admin','super-admin'],
        'committee' => ['committee','admin','super-admin'],
        'admin'     => ['admin','super-admin'],
    ];

    public function handle(): void
    {
        $this->processMailbox('docs@raynet-liverpool.net',           'public');
        $this->processMailbox('members-docs@raynet-liverpool.net',   'members');
        $this->processMailbox('committee-docs@raynet-liverpool.net', 'committee');
        $this->processMailbox('admin-docs@raynet-liverpool.net',     'admin');
    }

    protected function processMailbox(string $address, string $visibility): void
    {
        try {
            $host     = config('services.imap.host');
            $password = $this->getPasswordFor($address);
            $hostname = '{' . $host . ':993/imap/ssl/novalidate-cert}INBOX';
            $imap     = imap_open($hostname, $address, $password);

            if (!$imap) {
                Log::error("IMAP: Could not connect to {$address}: " . imap_last_error());
                return;
            }

            $emails = imap_search($imap, 'UNSEEN');
            if (!$emails) {
                $this->info("No new emails in {$address}");
                imap_close($imap);
                return;
            }

            foreach ($emails as $msgNum) {
                $this->processMessage($imap, $msgNum, $visibility, $address);
                imap_setflag_full($imap, (string)$msgNum, '\\Seen');
            }

            imap_close($imap);

        } catch (\Exception $e) {
            Log::error("IMAP fetch error for {$address}: " . $e->getMessage());
        }
    }

    protected function processMessage($imap, int $msgNum, string $visibility, string $toAddress): void
    {
        $header  = imap_headerinfo($imap, $msgNum);
        $rawFrom = strtolower($header->from[0]->mailbox . '@' . $header->from[0]->host);
        $replyTo = $rawFrom;
        $subject = imap_utf8($header->subject ?? 'Untitled Resource');
        $toBox   = strtolower($header->to[0]->mailbox ?? '');

        $callsign    = null;
        $user        = null;
        $uploader    = $rawFrom;
        $autoApprove = false;

        if (str_contains($toBox, '+')) {
            $tag  = strtoupper(explode('+', $toBox)[1]);
            $user = User::where('callsign', $tag)->first();
            if ($user) {
                $callsign = $tag;
                $uploader = $user->name . ' (' . $tag . ')';
                $replyTo  = $user->email;
            } else {
                $this->sendAutoResponse($rawFrom, 'callsign_not_found', ['callsign' => $tag, 'drive' => ucfirst($visibility), 'subject' => $subject]);
                $this->warn("Callsign {$tag} not found");
                return;
            }
        } else {
            $user = User::where('email', $rawFrom)->first();
            if ($user) {
                $callsign = $user->callsign;
                $uploader = $user->name . ($user->callsign ? ' (' . $user->callsign . ')' : '');
                $replyTo  = $user->email;
            }
        }

        if (!$user) {
            $this->sendAutoResponse($rawFrom, 'not_registered', ['drive' => ucfirst($visibility), 'subject' => $subject]);
            $this->warn("Unknown sender {$rawFrom}");
            return;
        }

        $requiredRoles = $this->driveRoles[$visibility] ?? [];
        if (!empty($requiredRoles) && !$user->hasRole($requiredRoles)) {
            $this->sendAutoResponse($replyTo, 'no_permission', ['name' => $user->name, 'drive' => ucfirst($visibility), 'subject' => $subject, 'address' => $toAddress]);
            $this->warn("User {$user->name} lacks role for {$visibility}");
            return;
        }

        $autoApprove = true;
        $structure   = imap_fetchstructure($imap, $msgNum);
        $parts       = $structure->parts ?? [];

        if (empty($parts)) {
            $this->sendAutoResponse($replyTo, 'no_attachment', ['name' => $user->name, 'drive' => ucfirst($visibility), 'subject' => $subject, 'address' => $toAddress]);
            $this->warn("No attachments from {$uploader}");
            return;
        }

        $saved       = 0;
        $skippedType = [];
        $skippedSize = [];

        foreach ($parts as $index => $part) {
            $filename = $this->extractFilename($part);
            if (!$filename) continue;

            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (!in_array($ext, $this->allowedExts)) {
                $skippedType[] = $filename;
                continue;
            }

            $data = imap_fetchbody($imap, $msgNum, (string)($index + 1));
            $data = match ($part->encoding) {
                3       => base64_decode($data),
                4       => quoted_printable_decode($data),
                default => $data,
            };

            if (strlen($data) > $this->maxBytes) {
                $skippedSize[] = $filename;
                continue;
            }

            $alreadyExists = Resource::where('original_name', $filename)
                ->where('visibility', $visibility)
                ->where('uploaded_by', $uploader)
                ->where('created_at', '>=', now()->subMinutes(10))
                ->exists();

            if ($alreadyExists) {
                $this->sendAutoResponse($replyTo, 'duplicate', ['name' => $user->name, 'filename' => $filename, 'drive' => ucfirst($visibility)]);
                $this->warn("Duplicate: {$filename}");
                continue;
            }

            $newName  = Str::uuid() . '.' . $ext;
            Storage::put("resources/{$visibility}/{$newName}", $data);

            $resource = Resource::create([
                'title'               => $subject,
                'filename'            => $newName,
                'original_name'       => $filename,
                'mime_type'           => $ext,
                'file_size'           => strlen($data),
                'visibility'          => $visibility,
                'category'            => null,
                'uploaded_by'         => $uploader,
                'uploaded_by_user_id' => $user->id,
                'source'              => 'email',
                'approved'            => $autoApprove,
            ]);

            if (!$autoApprove) {
                foreach (User::role(['admin','super-admin'])->get() as $admin) {
                    try { $admin->notify(new ResourcePendingNotification($resource)); } catch (\Exception $e) {}
                }
            }

            $saved++;
            $this->info("Saved: {$filename} ({$visibility}) from {$uploader}");
        }

        if (!empty($skippedType)) {
            $this->sendAutoResponse($replyTo, 'bad_filetype', ['name' => $user->name, 'files' => implode(', ', $skippedType), 'allowed' => implode(', ', $this->allowedExts), 'drive' => ucfirst($visibility)]);
        }

        if (!empty($skippedSize)) {
            $this->sendAutoResponse($replyTo, 'too_large', ['name' => $user->name, 'files' => implode(', ', $skippedSize), 'drive' => ucfirst($visibility)]);
        }

        if ($saved > 0) {
            $type = $autoApprove ? 'success_approved' : 'success_pending';
            $this->sendAutoResponse($replyTo, $type, ['name' => $user->name, 'count' => $saved, 'subject' => $subject, 'drive' => ucfirst($visibility), 'url' => config('app.url') . '/library']);
        } elseif ($saved === 0 && empty($skippedType) && empty($skippedSize)) {
            $this->sendAutoResponse($replyTo, 'no_attachment', ['name' => $user->name, 'drive' => ucfirst($visibility), 'subject' => $subject, 'address' => $toAddress]);
        }
    }

    protected function sendAutoResponse(string $to, string $type, array $data): void
    {
        $siteName = config('app.name', 'Liverpool RAYNET');
        $fromAddr = config('mail.from.address');
        $fromName = config('mail.from.name');
        $name     = $data['name']     ?? 'Member';
        $drive    = $data['drive']    ?? 'RAYNET Drive';
        $subject  = $data['subject']  ?? '';
        $address  = $data['address']  ?? '';
        $files    = $data['files']    ?? '';
        $allowed  = $data['allowed']  ?? '';
        $filename = $data['filename'] ?? '';
        $count    = $data['count']    ?? 1;
        $url      = $data['url']      ?? config('app.url') . '/library';
        $callsign = $data['callsign'] ?? 'unknown';

        // Status styles per type
        $styles = [
            'success_approved'   => ['icon'=>'&#9989;',  'label'=>'Upload Successful',    'band'=>'#e6f4ea', 'bandText'=>'#137333', 'boxBg'=>'#e6f4ea', 'boxBorder'=>'#34a853', 'boxText'=>'#137333'],
            'success_pending'    => ['icon'=>'&#9203;',  'label'=>'Pending Approval',     'band'=>'#fff8e1', 'bandText'=>'#f57f17', 'boxBg'=>'#fff8e1', 'boxBorder'=>'#fbbc04', 'boxText'=>'#b06000'],
            'not_registered'     => ['icon'=>'&#10060;', 'label'=>'Not Registered',       'band'=>'#fce8e6', 'bandText'=>'#c5221f', 'boxBg'=>'#fce8e6', 'boxBorder'=>'#ea4335', 'boxText'=>'#c5221f'],
            'callsign_not_found' => ['icon'=>'&#10060;', 'label'=>'Callsign Not Found',   'band'=>'#fce8e6', 'bandText'=>'#c5221f', 'boxBg'=>'#fce8e6', 'boxBorder'=>'#ea4335', 'boxText'=>'#c5221f'],
            'no_permission'      => ['icon'=>'&#128274;','label'=>'Access Denied',         'band'=>'#fce8e6', 'bandText'=>'#c5221f', 'boxBg'=>'#fce8e6', 'boxBorder'=>'#ea4335', 'boxText'=>'#c5221f'],
            'no_attachment'      => ['icon'=>'&#9888;',  'label'=>'No Attachment Found',   'band'=>'#fff8e1', 'bandText'=>'#f57f17', 'boxBg'=>'#fff8e1', 'boxBorder'=>'#fbbc04', 'boxText'=>'#b06000'],
            'bad_filetype'       => ['icon'=>'&#9888;',  'label'=>'File Type Not Allowed', 'band'=>'#fff8e1', 'bandText'=>'#f57f17', 'boxBg'=>'#fff8e1', 'boxBorder'=>'#fbbc04', 'boxText'=>'#b06000'],
            'too_large'          => ['icon'=>'&#9888;',  'label'=>'File Too Large',        'band'=>'#fff8e1', 'bandText'=>'#f57f17', 'boxBg'=>'#fff8e1', 'boxBorder'=>'#fbbc04', 'boxText'=>'#b06000'],
            'duplicate'          => ['icon'=>'&#9888;',  'label'=>'Duplicate Detected',    'band'=>'#fff8e1', 'bandText'=>'#f57f17', 'boxBg'=>'#fff8e1', 'boxBorder'=>'#fbbc04', 'boxText'=>'#b06000'],
        ];
        $style = $styles[$type] ?? $styles['no_attachment'];

        $templates = [
            'not_registered' => [
                'subject' => 'RAYNET Drive - Email address not recognised',
                'body'    => "Thank you for emailing the RAYNET Drive.\n\nUnfortunately your email address is not registered on the Liverpool RAYNET members portal. Only registered members can upload files via email.\n\nIf you believe this is an error, please contact your Group Controller or visit the website to register.\n\nDrive attempted: {$drive}\nOriginal subject: {$subject}",
            ],
            'callsign_not_found' => [
                'subject' => 'RAYNET Drive - Callsign not found',
                'body'    => "Thank you for emailing the RAYNET Drive.\n\nThe callsign '{$callsign}' in your upload address was not found in our system. Please check your personal upload address on the RAYNET Drive page.\n\nDrive: {$drive}\nOriginal subject: {$subject}",
            ],
            'no_permission' => [
                'subject' => "RAYNET Drive - Access denied for {$drive}",
                'body'    => "Hello {$name},\n\nYour file could not be uploaded because your account does not have permission to upload to the {$drive}.\n\nIf you believe you should have access, please contact your Group Controller.\n\nOriginal subject: {$subject}\nMailbox: {$address}",
            ],
            'no_attachment' => [
                'subject' => 'RAYNET Drive - No attachment found',
                'body'    => "Hello {$name},\n\nYour email was received but no valid attachment was found.\n\nPlease ensure you attach a file when emailing the RAYNET Drive.\nSupported formats: PDF, Word, Excel, PowerPoint, images (JPG/PNG), ZIP, text files.\n\nDrive: {$drive}\nMailbox: {$address}\nOriginal subject: {$subject}",
            ],
            'bad_filetype' => [
                'subject' => 'RAYNET Drive - File type not accepted',
                'body'    => "Hello {$name},\n\nThe following file(s) were rejected because the file type is not allowed:\n\n{$files}\n\nAllowed file types: {$allowed}\n\nPlease convert your file to a supported format and try again.\n\nDrive: {$drive}",
            ],
            'too_large' => [
                'subject' => 'RAYNET Drive - File too large',
                'body'    => "Hello {$name},\n\nThe following file(s) were rejected because they exceed the 20MB size limit:\n\n{$files}\n\nPlease compress or reduce the file size and try again.\n\nDrive: {$drive}",
            ],
            'duplicate' => [
                'subject' => 'RAYNET Drive - Duplicate file detected',
                'body'    => "Hello {$name},\n\nThe file '{$filename}' appears to have already been uploaded recently. To prevent duplicates, your file was not saved again.\n\nIf this was intentional, please wait 10 minutes and try again.\n\nDrive: {$drive}",
            ],
            'success_approved' => [
                'subject' => 'RAYNET Drive - File uploaded successfully',
                'body'    => "Hello {$name},\n\n" . ($count > 1 ? "{$count} files were" : "Your file was") . " successfully uploaded to the {$drive} and is now live.\n\nTitle: {$subject}\nDrive: {$drive}\n\nView it here: {$url}\n\nThank you for contributing to the RAYNET resource library.",
            ],
            'success_pending' => [
                'subject' => 'RAYNET Drive - File received, pending approval',
                'body'    => "Hello {$name},\n\nYour file has been received and is awaiting approval from an administrator.\n\nTitle: {$subject}\nDrive: {$drive}\n\nYou will receive another email once it has been approved.\n\nView the library: {$url}",
            ],
        ];

        if (!isset($templates[$type])) return;
        $tpl = $templates[$type];

        // Build info box content per type
        $infoBoxes = [
            'success_approved'   => ['title' => 'What happens next?',        'text'  => 'Your file is now live in the RAYNET Drive. Members can view and download it immediately.'],
            'success_pending'    => ['title' => 'What happens next?',        'text'  => 'An administrator will review your file shortly. You will receive another email once it is approved and live.'],
            'not_registered'     => ['title' => 'How to get access',         'text'  => 'Visit the Liverpool RAYNET website to register as a member. Once approved, you will be able to upload files via email.'],
            'callsign_not_found' => ['title' => 'How to fix this',           'text'  => 'Log in to the RAYNET Drive and check your personal upload addresses. Make sure you are using the correct callsign in the email address.'],
            'no_permission'      => ['title' => 'Why was I denied?',         'text'  => 'Each drive has role-based access. The ' . $drive . ' requires a specific role. Contact your Group Controller if you believe you should have access.'],
            'no_attachment'      => ['title' => 'Supported file types',      'text'  => 'PDF, Word (.doc/.docx), Excel (.xls/.xlsx), PowerPoint (.ppt/.pptx), Images (JPG, PNG, GIF), ZIP archives, Text files (.txt, .csv). Maximum size: 20MB.'],
            'bad_filetype'       => ['title' => 'Supported file types',      'text'  => 'Allowed: ' . $allowed . '. Please convert your file and try again.'],
            'too_large'          => ['title' => 'How to reduce file size',   'text'  => 'Try compressing images, using PDF compression tools, or splitting large documents into smaller files. Maximum allowed size is 20MB.'],
            'duplicate'          => ['title' => 'Why was this rejected?',    'text'  => 'The same file was uploaded within the last 10 minutes. If you intended to replace the file, please wait 10 minutes and try again.'],
        ];
        $infoBox = $infoBoxes[$type] ?? ['title' => '', 'text' => ''];

        $ctaMap = [
            'success_approved' => ['url' => config('app.url') . '/library', 'text' => 'View RAYNET Drive &rarr;'],
            'success_pending'  => ['url' => config('app.url') . '/library', 'text' => 'View RAYNET Drive &rarr;'],
            'not_registered'   => ['url' => config('app.url') . '/register', 'text' => 'Register Now &rarr;'],
            'no_permission'    => ['url' => config('app.url') . '/library', 'text' => 'View RAYNET Drive &rarr;'],
        ];
        $cta = $ctaMap[$type] ?? ['url' => null, 'text' => null];

        try {
            Mail::send('emails.drive.autoresponse', [
                'subject'        => $tpl['subject'],
                'bodyText'       => $tpl['body'],
                'drive'          => $drive,
                'statusIcon'     => $style['icon'],
                'statusLabel'    => $style['label'],
                'bandColour'     => $style['band'],
                'bandTextColour' => $style['bandText'],
                'boxBgColour'    => $style['boxBg'],
                'boxBorderColour'=> $style['boxBorder'],
                'boxTextColour'  => $style['boxText'],
                'infoTitle'      => $infoBox['title'],
                'infoText'       => $infoBox['text'],
                'ctaUrl'         => $cta['url'],
                'ctaText'        => $cta['text'],
            ], function ($msg) use ($to, $tpl, $fromAddr, $fromName, $siteName) {
                    $msg->to($to)->from($fromAddr, $fromName)->subject('[' . $siteName . '] ' . $tpl['subject']);
                }
            );
            $this->info("Autoresponse sent ({$type}) to {$to}");
            Log::info("Drive autoresponse: type={$type} to={$to}");
        } catch (\Exception $e) {
            Log::error("Autoresponse failed: " . $e->getMessage());
        }
    }

    protected function extractFilename(object $part): string
    {
        if (!empty($part->dparameters)) {
            foreach ($part->dparameters as $p) {
                if (strtolower($p->attribute) === 'filename') return $p->value;
            }
        }
        if (!empty($part->parameters)) {
            foreach ($part->parameters as $p) {
                if (strtolower($p->attribute) === 'name') return $p->value;
            }
        }
        return '';
    }

    protected function getPasswordFor(string $address): string
    {
        return match(true) {
            str_starts_with($address, 'committee-docs') => config('services.imap.committeefile_password'),
            str_starts_with($address, 'admin-docs')     => config('services.imap.adminfile_password'),
            str_starts_with($address, 'members-docs')   => config('services.imap.memberfile_password'),
            default                                      => config('services.imap.publicfile_password'),
        };
    }
}
