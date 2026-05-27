<?php
/**
 * RAYNET-OS — Nuclear hardcoded string replacement
 * Fixes ALL remaining Liverpool/Merseyside/Zone 10/Group 179 references
 * Run from public_html: php fix-all-hardcoded.php
 */

$base = getcwd();
$fixed = 0;

$GN  = "{{ \\App\\Helpers\\RaynetSetting::groupName() }}";
$GR  = "{{ \\App\\Helpers\\RaynetSetting::groupRegion() }}";
$GNO = "{{ \\App\\Helpers\\RaynetSetting::groupNumber() }}";
$GC  = "{{ \\App\\Helpers\\RaynetSetting::gcEmail() }}";
$GCN = "{{ \\App\\Helpers\\RaynetSetting::gcName() }}";
$URL = "{{ \\App\\Helpers\\RaynetSetting::siteUrl() }}";

// Walk all blade files
$dir = new RecursiveDirectoryIterator("$base/resources/views");
$iter = new RecursiveIteratorIterator($dir);

foreach ($iter as $file) {
    if (!str_ends_with($file->getPathname(), '.blade.php')) continue;

    // Skip install views (they intentionally have placeholder text)
    if (str_contains($file->getPathname(), '/install/')) continue;

    $path = $file->getPathname();
    $c = file_get_contents($path);
    $orig = $c;

    // ── Core replacements ──────────────────────────────────────────────────

    // Group name variants
    $c = str_replace('Liverpool RAYNET Group', "$GN Group", $c);
    $c = str_replace('Liverpool RAYNET', $GN, $c);
    $c = str_replace('LiverpoolRAYNET', $GN, $c);

    // Region
    $c = str_replace('Merseyside', $GR, $c);

    // Group number variants
    $c = str_replace('Group 10/ME/179', "Group $GNO", $c);
    $c = str_replace('10/ME/179', $GNO, $c);
    $c = str_replace('Group 179', "Group $GNO", $c);

    // Zone
    $c = str_replace('Zone 10', $GR, $c);

    // URLs
    $c = str_replace('https://raynet-liverpool.net', $URL, $c);
    $c = str_replace('raynet-liverpool.net', $URL, $c);
    $c = str_replace('Raynet-Liverpool.net', $URL, $c);

    // GC email
    $c = str_replace('GC.liverpool@raynet-uk.net', $GC, $c);
    $c = str_replace('gc@raynet-liverpool.net', $GC, $c);

    // GC name - specific Ian Jones reference
    $c = str_replace('<strong>Ian Jones</strong>', "<strong>$GCN</strong>", $c);

    // Don't double-replace already fixed strings
    // e.g. avoid replacing "Liverpool" inside already-replaced {{ }} blocks
    // (str_replace won't match those since they no longer contain "Liverpool")

    if ($c !== $orig) {
        file_put_contents($path, $c);
        $rel = str_replace("$base/resources/views/", '', $path);
        echo "  ✓ $rel\n";
        $fixed++;
    }
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Fixed $fixed files.\n";
echo "Run: php artisan view:clear && php artisan cache:clear\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
