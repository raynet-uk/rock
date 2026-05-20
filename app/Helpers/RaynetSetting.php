<?php

namespace App\Helpers;

use App\Models\Setting;

class RaynetSetting
{
    protected static array $cache = [];

    public static function get(string $key, string $fallback = ''): string
    {
        if (!isset(static::$cache[$key])) {
            static::$cache[$key] = Setting::get($key, '') ?: $fallback;
        }
        return static::$cache[$key];
    }

    // Shorthand accessors used throughout views
    public static function groupName(): string
    {
        return static::get('group_name', config('app.name', 'RAYNET Group'));
    }

    public static function groupNumber(): string
    {
        return static::get('group_number', '');
    }

    public static function groupCallsign(): string
    {
        return static::get('group_callsign', '');
    }

    public static function groupRegion(): string
    {
        // Returns local area name (e.g. Merseyside) for display
        return static::get('group_area', static::get('group_region', ''));
    }

    public static function groupZone(): string
    {
        return static::get('group_region', '');
    }

    public static function gcName(): string
    {
        return static::get('gc_name', '');
    }

    public static function gcEmail(): string
    {
        return static::get('gc_email', static::get('support_request_email', ''));
    }

    public static function siteUrl(): string
    {
        return static::get('site_url', config('app.url', ''));
    }

    public static function siteName(): string
    {
        return static::get('site_name', static::groupName());
    }

    public static function supportEmail(): string
    {
        return static::get('support_request_email', static::gcEmail());
    }

    public static function footer(): string
    {
        $name   = static::groupName();
        $number = static::groupNumber();
        $region = static::groupRegion();

        $parts = [$name];
        if ($number) $parts[] = "Group {$number}";
        if ($region) $parts[] = $region;
        $parts[] = 'Affiliated to RAYNET-UK';

        return implode(' · ', $parts);
    }

    public static function isInstalled(): bool
    {
        return Setting::get('installed', '0') === '1';
    }
}
