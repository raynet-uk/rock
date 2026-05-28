<?php

return [
    'primary_operator_callsign' => env('PRIMARY_OPERATOR_CALLSIGN', null),

    // Set DMR_ENABLED=true in .env to enable the DMR Network feature.
    // This is a Liverpool-specific feature and is disabled by default.
    'dmr_enabled' => env('DMR_ENABLED', false),
];
