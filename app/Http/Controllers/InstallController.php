<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class InstallController extends Controller
{
    public function index()
    {
        if ($this->isInstalled()) return redirect('/');
        return view('install.index', ['step' => 'index', 'groupName' => '']);
    }

    public function step1()
    {
        if ($this->isInstalled()) return redirect('/');
        return view('install.index', ['step' => 'step1', 'groupName' => '']);
    }

    public function step1Post(Request $request)
    {
        $request->validate([
            'licence_key'           => ['required', 'string', 'max:64'],
            'group_name'            => ['required', 'string', 'max:80'],
            'group_number'          => ['nullable', 'string', 'max:20'],
            'group_callsign'        => ['nullable', 'string', 'max:20'],
            'group_region'          => ['nullable', 'string', 'max:80'],
            'gc_name'               => ['required', 'string', 'max:80'],
            'gc_email'              => ['required', 'email', 'max:120'],
            'support_request_email' => ['required', 'email', 'max:120'],
            'site_url'              => ['required', 'url', 'max:120'],
            'raynet_zone'           => ['nullable', 'string', 'max:20'],
            'mail_host'             => ['required', 'string', 'max:120'],
            'mail_port'             => ['required', 'in:25,465,587'],
            'mail_username'         => ['required', 'string', 'max:120'],
            'mail_password'         => ['required', 'string', 'max:255'],
            'mail_from_address'     => ['required', 'email', 'max:120'],
            'mail_from_name'        => ['nullable', 'string', 'max:80'],
            'qrz_username'              => ['nullable', 'string', 'max:30'],
            'qrz_password'              => ['nullable', 'string', 'max:120'],
            'site_name'                 => ['nullable', 'string', 'max:80'],
            'site_tagline'              => ['nullable', 'string', 'max:120'],
            'group_phone'               => ['nullable', 'string', 'max:20'],
            'group_area'                => ['nullable', 'string', 'max:80'],
            'registration_notify_email' => ['nullable', 'email', 'max:120'],
            'install_site_logo'         => ['nullable', 'image', 'max:2048'],
        ]);

        // ── Validate licence key against raynet-liverpool.net ─────────────
        $licenceKey = trim($request->input('licence_key'));
        $licenceValid = false;

        try {
            $response = Http::timeout(10)->post('https://command.nathandillon.co.uk/api/cms/validate-licence', [
                'key'      => $licenceKey,
                'site_url' => $request->input('site_url'),
            ]);

            if ($response->successful() && $response->json('valid')) {
                $licenceValid = true;
            } else {
                $message = $response->json('message', 'Invalid or already used licence key.');
                return back()
                    ->withInput()
                    ->withErrors(['licence_key' => $message]);
            }
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->withErrors(['licence_key' => 'Could not connect to the ROCK licence server. Please check your internet connection and try again. If the problem persists, contact RAYNET Liverpool.']);
        }

        // ── Save settings ─────────────────────────────────────────────────
        $fields = [
            'group_name', 'group_number', 'group_callsign', 'group_region',
            'gc_name', 'gc_email', 'support_request_email', 'site_url', 'raynet_zone',
            'group_phone', 'group_area', 'site_tagline', 'registration_notify_email',
        ];

        foreach ($fields as $field) {
            Setting::set($field, $request->input($field, ''));
        }

        Setting::set('site_name', $request->input('site_name') ?: $request->input('group_name'));
        Setting::set('cms_licence_key', $licenceKey);

        // ── Handle logo upload ────────────────────────────────────────────
        if ($request->hasFile('install_site_logo') && $request->file('install_site_logo')->isValid()) {
            try {
                $path = $request->file('install_site_logo')->store('logos', 'public');
                Setting::set('site_logo_path', $path);
            } catch (\Throwable $e) {}
        }

        // If registration_notify_email empty, default to gc_email
        if (!$request->input('registration_notify_email')) {
            Setting::set('registration_notify_email', $request->input('gc_email', ''));
        }

        // ── Write mail config to .env ──────────────────────────────────────
        $envPath = base_path('.env');
        if (file_exists($envPath)) {
            $encryption = $request->input('mail_port') === '587' ? 'tls' : 'ssl';
            $fromName   = $request->input('mail_from_name') ?: $request->input('group_name');
            $envUpdates = [
                'MAIL_HOST'         => $request->input('mail_host'),
                'MAIL_PORT'         => $request->input('mail_port'),
                'MAIL_USERNAME'     => $request->input('mail_username'),
                'MAIL_PASSWORD'     => $request->input('mail_password'),
                'MAIL_ENCRYPTION'   => $encryption,
                'MAIL_FROM_ADDRESS' => $request->input('mail_from_address'),
                'MAIL_FROM_NAME'    => '"' . $fromName . '"',
                'APP_URL'           => $request->input('site_url'),
                'QRZ_USERNAME'      => $request->input('qrz_username', ''),
                'QRZ_PASSWORD'      => $request->input('qrz_password', ''),
            ];
            $env = file_get_contents($envPath);
            foreach ($envUpdates as $key => $value) {
                if (preg_match('/^' . $key . '=/m', $env)) {
                    $env = preg_replace('/^' . $key . '=.*/m', $key . '=' . $value, $env);
                } else {
                    $env .= "
" . $key . '=' . $value;
                }
            }
            file_put_contents($envPath, $env);

            // Clear config cache so new .env values take effect immediately
            try {
                Artisan::call('config:clear');
            } catch (\Throwable $e) {}
        }

        // Insert into CmsLicence table so Command Centre API auth works
        \App\Models\CmsLicence::firstOrCreate(
            ['key' => $licenceKey],
            [
                'group_name'   => $request->input('group_name'),
                'group_number' => $request->input('group_number', ''),
                'gc_name'      => $request->input('gc_name', ''),
                'gc_email'     => $request->input('gc_email', ''),
                'is_active'    => true,
            ]
        );

        return redirect()->route('install.step2');
    }

    public function step2()
    {
        if ($this->isInstalled()) return redirect('/');
        return view('install.index', ['step' => 'step2', 'groupName' => '']);
    }

    public function step2Post(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'callsign' => ['required', 'string', 'max:15'],
            'email'    => ['required', 'email', 'max:120', 'unique:users,email'],
            'password' => ['required', 'string', 'min:10', 'confirmed'],
        ]);

        $user = User::create([
            'name'              => $request->name,
            'callsign'          => strtoupper($request->callsign),
            'email'             => $request->email,
            'password'          => Hash::make($request->password),
            'email_verified_at' => now(),
            'is_admin'          => true,
            'is_super_admin'    => true,
        ]);

        // Seed Spatie roles and assign super-admin
        try {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            foreach (['super-admin', 'admin', 'committee', 'member'] as $role) {
                \Spatie\Permission\Models\Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
            }
            $user->assignRole('super-admin');
        } catch (\Throwable $e) {
            // Fallback
        }

        session(['install_user_id' => $user->id]);

        return redirect()->route('install.step3');
    }

    public function step3()
    {
        if ($this->isInstalled()) return redirect('/');
        return view('install.index', [
            'step'      => 'step3',
            'groupName' => Setting::get('group_name'),
        ]);
    }

    public function complete(Request $request)
    {
        Setting::set('installed', '1');

        // Register queue worker cron
        $cronLine = '* * * * * cd ' . base_path() . ' && php artisan queue:work --stop-when-empty --quiet';
        $existing = shell_exec('crontab -l 2>/dev/null') ?? '';
        if (!str_contains($existing, 'queue:work')) {
            $new = trim($existing) . "\n" . $cronLine . "\n";
            file_put_contents('/tmp/raynet_cron', $new);
            shell_exec('crontab /tmp/raynet_cron');
            @unlink('/tmp/raynet_cron');
        }

        Artisan::call('route:clear');
        Artisan::call('view:clear');
        Artisan::call('cache:clear');

        // Auto-login the installer user
        $userId = session('install_user_id');
        if ($userId) {
            $user = User::find($userId);
            if ($user) {
                Auth::login($user, true);
                session()->forget('install_user_id');
                return redirect()->route('install.welcome');
            }
        }

        return redirect()->route('admin.login')
            ->with('success', 'Installation complete! Log in with your admin account.');
    }

    protected function isInstalled(): bool
    {
        try {
            return Setting::get('installed', '0') === '1';
        } catch (\Throwable $e) {
            return false;
        }
    }
}