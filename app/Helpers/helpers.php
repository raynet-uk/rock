<?php


/**
 * Render a PII value — blurred span when hidden, plain text when visible.
 * Usage in blade: {!! pii($user->name, $user->piiVisible()) !!}
 */
function pii(mixed $value, bool $visible, string $extra = ''): string {
    $safe = e((string) $value);
    if ($visible) return $safe;
    return '<span class="pii-redacted" ' . $extra . '>' . $safe . '</span>';
}
