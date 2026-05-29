<?php

return [
    'primary_operator_callsign' => env('PRIMARY_OPERATOR_CALLSIGN', null),

    // Set DMR_ENABLED=true in .env to enable the DMR Network feature.
    // This feature is disabled by default.
    'dmr_enabled' => env('DMR_ENABLED', false),
    'remote_help_provider' => env('REMOTE_HELP_PROVIDER', false),
];
