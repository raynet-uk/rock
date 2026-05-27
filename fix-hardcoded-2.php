<?php
/**
 * RAYNET-OS — Fix remaining hardcoded Liverpool strings
 * Run from public_html: php fix-hardcoded-2.php
 */

$base = getcwd();
$rs = '\App\Helpers\RaynetSetting';
$fixed = 0;

function fix(string $path, array $replacements): void {
    global $fixed;
    if (!file_exists($path)) { echo "  SKIP: $path\n"; return; }
    $c = file_get_contents($path);
    $orig = $c;
    foreach ($replacements as $old => $new) {
        $c = str_replace($old, $new, $c);
    }
    if ($c !== $orig) {
        file_put_contents($path, $c);
        echo "  ✓ $path\n";
        $fixed++;
    }
}

// ── layouts/app.blade.php ─────────────────────────────────────────────────
echo "\nlayouts/app.blade.php\n";
fix("$base/resources/views/layouts/app.blade.php", [
    'Affiliated to RAYNET-UK · Volunteer emergency communications for Merseyside.' =>
        'Affiliated to RAYNET-UK · Volunteer emergency communications for {{ \App\Helpers\RaynetSetting::groupRegion() }}.',
]);

// ── layouts/navigation.blade.php ─────────────────────────────────────────
echo "\nlayouts/navigation.blade.php\n";
fix("$base/resources/views/layouts/navigation.blade.php", [
    'Liverpool RAYNET' => '{{ \App\Helpers\RaynetSetting::groupName() }}',
]);

// ── layouts/guest.blade.php ───────────────────────────────────────────────
echo "\nlayouts/guest.blade.php\n";
fix("$base/resources/views/layouts/guest.blade.php", [
    'Member login – Liverpool RAYNET' =>
        'Member login – {{ \App\Helpers\RaynetSetting::groupName() }}',
]);

// ── partials/cookie-banner.blade.php ─────────────────────────────────────
echo "\npartials/cookie-banner.blade.php\n";
fix("$base/resources/views/partials/cookie-banner.blade.php", [
    '<div class="cm-sub">Liverpool RAYNET · raynet-liverpool.net</div>' =>
        '<div class="cm-sub">{{ \App\Helpers\RaynetSetting::groupName() }} · {{ \App\Helpers\RaynetSetting::siteUrl() }}</div>',
]);

// ── partials/alert-status-card.blade.php ─────────────────────────────────
echo "\npartials/alert-status-card.blade.php\n";
fix("$base/resources/views/partials/alert-status-card.blade.php", [
    'Liverpool RAYNET' => '{{ \App\Helpers\RaynetSetting::groupName() }}',
]);

// ── errors/403.blade.php ──────────────────────────────────────────────────
echo "\nerrors/403.blade.php\n";
fix("$base/resources/views/errors/403.blade.php", [
    'Access Restricted — Liverpool RAYNET' =>
        'Access Restricted — {{ \App\Helpers\RaynetSetting::groupName() }}',
    '<span class="brand-name">Liverpool RAYNET</span>' =>
        '<span class="brand-name">{{ \App\Helpers\RaynetSetting::groupName() }}</span>',
    'Liverpool RAYNET · Group 10/ME/179' =>
        '{{ \App\Helpers\RaynetSetting::groupName() }}',
    'Volunteer emergency communications for Merseyside' =>
        'Volunteer emergency communications for {{ \App\Helpers\RaynetSetting::groupRegion() }}',
]);

// ── errors/dmr-access-denied.blade.php ───────────────────────────────────
echo "\nerrors/dmr-access-denied.blade.php\n";
$dmrPath = "$base/resources/views/errors/dmr-access-denied.blade.php";
if (file_exists($dmrPath)) {
    $c = file_get_contents($dmrPath);
    $c = str_replace('Liverpool RAYNET DMR Network Dashboard',
        '{{ \App\Helpers\RaynetSetting::groupName() }} DMR Network Dashboard', $c);
    $c = preg_replace('/Zone \d+ · \w+/',
        '{{ \App\Helpers\RaynetSetting::groupRegion() }}', $c);
    file_put_contents($dmrPath, $c);
    echo "  ✓ $dmrPath\n";
}

// ── auth views ────────────────────────────────────────────────────────────
echo "\nAuth views\n";
$authViews = [
    'login.blade.php', 'register.blade.php', 'register-pending.blade.php',
    'forgot-password.blade.php', 'verify-email.blade.php', 'reset-password.blade.php',
];

foreach ($authViews as $view) {
    $path = "$base/resources/views/auth/$view";
    if (!file_exists($path)) continue;
    $c = file_get_contents($path);
    $orig = $c;

    // Brand name
    $c = str_replace(
        '<div class="brand-name">Liverpool RAYNET</div>',
        '<div class="brand-name">{{ \App\Helpers\RaynetSetting::groupName() }}</div>',
        $c
    );
    // Chips
    $c = str_replace(
        '<span class="chip"><span class="chip-dot"></span>Liverpool RAYNET</span>',
        '<span class="chip"><span class="chip-dot"></span>{{ \App\Helpers\RaynetSetting::groupName() }}</span>',
        $c
    );
    $c = str_replace(
        '<span class="chip">📻 Liverpool RAYNET</span>',
        '<span class="chip">📻 {{ \App\Helpers\RaynetSetting::groupName() }}</span>',
        $c
    );
    $c = str_replace(
        '<span class="chip"><span class="chip-dot"></span>Zone 10 · Merseyside</span>',
        '<span class="chip"><span class="chip-dot"></span>{{ \App\Helpers\RaynetSetting::groupRegion() }}</span>',
        $c
    );
    $c = str_replace(
        '<span class="chip">Zone 10 · Merseyside</span>',
        '<span class="chip">{{ \App\Helpers\RaynetSetting::groupRegion() }}</span>',
        $c
    );
    $c = str_replace(
        '<span class="chip"><span class="chip-dot"></span>Group 179</span>',
        '@if(\App\Helpers\RaynetSetting::groupNumber())<span class="chip"><span class="chip-dot"></span>Group {{ \App\Helpers\RaynetSetting::groupNumber() }}</span>@endif',
        $c
    );
    $c = str_replace(
        '<span class="chip">Group 179</span>',
        '@if(\App\Helpers\RaynetSetting::groupNumber())<span class="chip">Group {{ \App\Helpers\RaynetSetting::groupNumber() }}</span>@endif',
        $c
    );
    // Portal title
    $c = str_replace(
        '<div class="right-title">Liverpool RAYNET Portal</div>',
        '<div class="right-title">{{ \App\Helpers\RaynetSetting::groupName() }} Portal</div>',
        $c
    );

    if ($c !== $orig) {
        file_put_contents($path, $c);
        echo "  ✓ auth/$view\n";
        $fixed++;
    }
}

// ── auth/oauth/authorize.blade.php ────────────────────────────────────────
echo "\nauth/oauth/authorize.blade.php\n";
fix("$base/resources/views/auth/oauth/authorize.blade.php", [
    'Authorise Application — Liverpool RAYNET' =>
        'Authorise Application — {{ \App\Helpers\RaynetSetting::groupName() }}',
    'src="/images/raynet-uk-liverpool-banner.png" alt="Liverpool RAYNET"' =>
        'src="{{ asset(\'images/raynet-uk-liverpool-banner.png\') }}" alt="{{ \App\Helpers\RaynetSetting::groupName() }}"',
    '<span class="connector-arrow">Liverpool RAYNET</span>' =>
        '<span class="connector-arrow">{{ \App\Helpers\RaynetSetting::groupName() }}</span>',
    'Authorised by <a href="/">Liverpool RAYNET</a>' =>
        'Authorised by <a href="/">{{ \App\Helpers\RaynetSetting::groupName() }}</a>',
]);

// ── emails ────────────────────────────────────────────────────────────────
echo "\nEmails\n";

// lms/enrolled
fix("$base/resources/views/emails/lms/enrolled.blade.php", [
    'Liverpool RAYNET Training Portal' =>
        '{{ \App\Helpers\RaynetSetting::groupName() }} Training Portal',
]);

// admin-notification
fix("$base/resources/views/emails/admin-notification.blade.php", [
    'Affiliated to RAYNET-UK · Volunteer emergency communications for Merseyside' =>
        'Affiliated to RAYNET-UK · {{ \App\Helpers\RaynetSetting::groupName() }}',
]);

// reset-password
fix("$base/resources/views/emails/reset-password.blade.php", [
    "Radio Amateurs' Emergency Network · Zone 10 · Merseyside" =>
        "Radio Amateurs' Emergency Network · {{ \\App\\Helpers\\RaynetSetting::groupRegion() }}",
]);

// lms layout
fix("$base/resources/views/emails/layouts/lms.blade.php", [
    'Volunteer emergency communications for Merseyside' =>
        'Volunteer emergency communications for {{ \App\Helpers\RaynetSetting::groupRegion() }}',
]);

// operator briefing - fix remaining Merseyside refs
$opPath = "$base/resources/views/emails/operator-briefing.blade.php";
if (file_exists($opPath)) {
    $c = file_get_contents($opPath);
    $c = preg_replace('/Zone \d+ · \w+/', '{{ \App\Helpers\RaynetSetting::groupRegion() }}', $c);
    $c = str_replace('Liverpool RAYNET', '{{ \App\Helpers\RaynetSetting::groupName() }}', $c);
    file_put_contents($opPath, $c);
    echo "  ✓ emails/operator-briefing.blade.php\n";
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Fixed $fixed files.\n";
echo "Run: php artisan view:clear && php artisan cache:clear\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
