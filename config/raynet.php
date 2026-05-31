<?php

return [
    'primary_operator_callsign' => env('PRIMARY_OPERATOR_CALLSIGN', null),

    // Set DMR_ENABLED=true in .env to enable the DMR Network feature.
    // This feature is disabled by default.
    'dmr_enabled' => env('DMR_ENABLED', false),
    'remote_help_provider'     => env('REMOTE_HELP_PROVIDER', false),
    'remote_help_provider_url' => env('REMOTE_HELP_PROVIDER_URL', 'https://raynet-liverpool.net/admin/remote-help/notify'),
    'telegram_enabled'     => env('TELEGRAM_ENABLED', false),
];
