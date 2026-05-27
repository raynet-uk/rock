<?php
/**
 * RAYNET-OS — Replace hardcoded Liverpool strings with dynamic settings
 * Run from public_html: php fix-hardcoded.php
 */

$base = getcwd();
$fixed = 0;

function fixFile(string $path, array $replacements): bool {
    if (!file_exists($path)) { echo "  SKIP (not found): $path\n"; return false; }
    $content = file_get_contents($path);
    $original = $content;
    foreach ($replacements as $old => $new) {
        $content = str_replace($old, $new, $content);
    }
    if ($content !== $original) {
        file_put_contents($path, $content);
        echo "  ✓ Fixed: $path\n";
        return true;
    }
    echo "  - Unchanged: $path\n";
    return false;
}

// ── layouts/app.blade.php ─────────────────────────────────────────────────
echo "\nlayouts/app.blade.php\n";
fixFile($base . '/resources/views/layouts/app.blade.php', [
    "'Liverpool RAYNET'" => "\\App\\Helpers\\RaynetSetting::groupName()",
    '"Liverpool RAYNET"' => "\\App\\Helpers\\RaynetSetting::groupName()",
    'Liverpool RAYNET' => "{{ \\App\\Helpers\\RaynetSetting::groupName() }}",
    "© {{ date('Y') }} {{ \\App\\Helpers\\RaynetSetting::groupName() }} (Group {{ \\App\\Helpers\\RaynetSetting::groupNumber() }}). All rights reserved." => "© {{ date('Y') }} {{ \\App\\Helpers\\RaynetSetting::footer() }}. All rights reserved.",
]);

// More targeted replacements for app.blade.php
$appPath = $base . '/resources/views/layouts/app.blade.php';
$app = file_get_contents($appPath);

// Footer copyright
$app = preg_replace(
    '/© \{\{ date\(\'Y\'\) \}\} Liverpool RAYNET \(Group 10\/ME\/179\)\. All rights reserved\./',
    "© {{ date('Y') }} {{ \\App\\Helpers\\RaynetSetting::footer() }}. All rights reserved.",
    $app
);
// Site name fallback
$app = str_replace(
    "Setting::get('site_name', 'Liverpool RAYNET')",
    "Setting::get('site_name', \\App\\Helpers\\RaynetSetting::groupName())",
    $app
);
// Broadcast bar
$app = str_replace(
    "'Notice from Liverpool RAYNET:'",
    "'Notice from ' . \\App\\Helpers\\RaynetSetting::groupName() . ':'",
    $app
);
file_put_contents($appPath, $app);
echo "  ✓ app.blade.php targeted fixes applied\n";

// ── layouts/admin.blade.php ───────────────────────────────────────────────
echo "\nlayouts/admin.blade.php\n";
$adminPath = $base . '/resources/views/layouts/admin.blade.php';
$admin = file_get_contents($adminPath);
$admin = str_replace(
    "Admin') — Liverpool RAYNET",
    "Admin') — {{ \\App\\Helpers\\RaynetSetting::groupName() }}",
    $admin
);
$admin = str_replace(
    '<div class="sb-site">Liverpool RAYNET</div>',
    '<div class="sb-site">{{ \\App\\Helpers\\RaynetSetting::groupName() }}</div>',
    $admin
);
$admin = str_replace(
    '<strong>Liverpool RAYNET</strong>',
    '<strong>{{ \\App\\Helpers\\RaynetSetting::groupName() }}</strong>',
    $admin
);
file_put_contents($adminPath, $admin);
echo "  ✓ admin.blade.php fixed\n";

// ── emails ────────────────────────────────────────────────────────────────
echo "\nEmails\n";

$emailFiles = [
    'resources/views/emails/magic-code.blade.php',
    'resources/views/emails/verify-email.blade.php',
    'resources/views/emails/reset-password.blade.php',
    'resources/views/emails/admin-notification.blade.php',
    'resources/views/emails/operator-briefing.blade.php',
    'resources/views/emails/support-request-submitted.blade.php',
    'resources/views/emails/layouts/lms.blade.php',
];

foreach ($emailFiles as $file) {
    $path = $base . '/' . $file;
    if (!file_exists($path)) continue;
    $content = file_get_contents($path);
    $original = $content;

    // Replace Liverpool RAYNET with dynamic group name in email HTML
    $content = str_replace('Liverpool RAYNET', "{{ \\App\\Helpers\\RaynetSetting::groupName() }}", $content);
    $content = str_replace('Group 10/ME/179', "{{ \\App\\Helpers\\RaynetSetting::groupNumber() }}", $content);
    $content = str_replace('raynet-liverpool.net', "{{ \\App\\Helpers\\RaynetSetting::siteUrl() }}", $content);
    $content = str_replace('GC.liverpool@raynet-uk.net', "{{ \\App\\Helpers\\RaynetSetting::gcEmail() }}", $content);
    $content = str_replace(
        'https://raynet-liverpool.net/images/raynet-uk-liverpool-banner.png',
        "{{ asset('images/raynet-uk-liverpool-banner.png') }}",
        $content
    );

    if ($content !== $original) {
        file_put_contents($path, $content);
        echo "  ✓ Fixed: $file\n";
    }
}

// ── Notifications (PHP files) ─────────────────────────────────────────────
echo "\nNotifications\n";

$notifFiles = [
    'app/Notifications/AdminNotificationEmail.php',
    'app/Notifications/ResetPasswordNotification.php',
    'app/Notifications/VerifyEmailNotification.php',
    'app/Http/Controllers/Auth/MagicCodeController.php',
];

foreach ($notifFiles as $file) {
    $path = $base . '/' . $file;
    if (!file_exists($path)) continue;
    $content = file_get_contents($path);
    $original = $content;

    $content = str_replace(
        "'Your Liverpool RAYNET sign-in code'",
        "'Your ' . \\App\\Helpers\\RaynetSetting::groupName() . ' sign-in code'",
        $content
    );
    $content = str_replace(
        "— Liverpool RAYNET",
        "— ' . \\App\\Helpers\\RaynetSetting::groupName()",
        $content
    );
    $content = str_replace(
        "'Reset your password — Liverpool RAYNET Members\\'Portal'",
        "'Reset your password — ' . \\App\\Helpers\\RaynetSetting::groupName() . ' Members Portal'",
        $content
    );
    $content = str_replace(
        "'Verify your email — Liverpool RAYNET Members Portal'",
        "'Verify your email — ' . \\App\\Helpers\\RaynetSetting::groupName() . ' Members Portal'",
        $content
    );
    $content = str_replace(
        "welcome to the Liverpool RAYNET members' portal.",
        "welcome to the ' . \\App\\Helpers\\RaynetSetting::groupName() . ' members portal.",
        $content
    );

    if ($content !== $original) {
        file_put_contents($path, $content);
        echo "  ✓ Fixed: $file\n";
    }
}

// ── Migration default email ───────────────────────────────────────────────
echo "\nMigration\n";
$migPath = $base . '/database/migrations/2026_03_08_000001_create_settings_table.php';
if (file_exists($migPath)) {
    $mig = file_get_contents($migPath);
    $mig = str_replace(
        "'value' => 'g4bds@raynet-liverpool.net'",
        "'value' => ''",
        $mig
    );
    file_put_contents($migPath, $mig);
    echo "  ✓ Migration default email cleared\n";
}

// ── SupportRequestController ──────────────────────────────────────────────
echo "\nSupportRequestController\n";
$srcPath = $base . '/app/Http/Controllers/SupportRequestController.php';
if (file_exists($srcPath)) {
    $src = file_get_contents($srcPath);
    $src = str_replace(
        "Setting::get('support_request_email', 'g4bds@raynet-liverpool.net')",
        "Setting::get('support_request_email', \\App\\Helpers\\RaynetSetting::gcEmail())",
        $src
    );
    file_put_contents($srcPath, $src);
    echo "  ✓ SupportRequestController fixed\n";
}

// ── OpsMapController User-Agent ───────────────────────────────────────────
echo "\nOpsMapController\n";
$opsPath = $base . '/app/Http/Controllers/OpsMapController.php';
if (file_exists($opsPath)) {
    $ops = file_get_contents($opsPath);
    $ops = str_replace(
        "'User-Agent' => 'Liverpool-RAYNET/1.0 (+https://raynet-liverpool.net)'",
        "'User-Agent' => 'RAYNET-CMS/1.0 (+' . \\App\\Helpers\\RaynetSetting::siteUrl() . ')'",
        $ops
    );
    $ops = str_replace(
        "'User-Agent' => 'Liverpool-RAYNET/1.0 (raynet-liverpool.net)'",
        "'User-Agent' => 'RAYNET-CMS/1.0 (' . \\App\\Helpers\\RaynetSetting::siteUrl() . ')'",
        $ops
    );
    $ops = str_replace(
        "'Referer'    => 'https://raynet-liverpool.net'",
        "'Referer'    => \\App\\Helpers\\RaynetSetting::siteUrl()",
        $ops
    );
    file_put_contents($opsPath, $ops);
    echo "  ✓ OpsMapController fixed\n";
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Done. Run: php artisan view:clear && php artisan cache:clear\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
