<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\EventRsvp;

class Event extends Model
{
    protected $table = 'events';

    protected $fillable = [
        'title',
        'slug',
        'location',
        'starts_at',
        'ends_at',
        'category',
        'description',
        'members_description',
        'event_type_id',
        'is_public',
        'is_sample',
        // Map pin — 2026_add_map_fields_to_events_table
        'event_lat',
        'event_lng',
        // Site boundary polygon — 2026_add_map_fields_to_events_table
        'event_polygon',
        // Polygon display name — 2026_add_map_names_to_events_table
        'event_polygon_name',
        // Route (walk, race course etc.) — 2026_add_event_route_to_events_table
        'event_route',
        // Route display name — 2026_add_map_names_to_events_table
        'event_route_name',
        // Points of interest — 2026_add_event_pois_to_events_table
        'event_pois',
        'is_private',
        'supporting_group',
    ];

    protected $casts = [
        'starts_at'     => 'datetime',
        'ends_at'       => 'datetime',
        'is_public'     => 'boolean',
        'is_sample'     => 'boolean',
        'event_lat'     => 'float',
        'event_lng'     => 'float',
        'event_polygon' => 'array',  // GeoJSON Polygon/MultiPolygon geometry
        'event_route'   => 'array',  // GeoJSON LineString/MultiLineString geometry
        'event_pois'    => 'array',  // Array of POI objects
        'is_private'    => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function type()
    {
        return $this->belongsTo(EventType::class, 'event_type_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EventDocument::class)
                    ->orderBy('sort_order')
                    ->orderBy('created_at');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(EventAssignment::class);
    }

    public function rsvps(): HasMany
    {
    return $this->hasMany(EventRsvp::class);
    }
    // ── Display helpers ───────────────────────────────────────────────────────

    /**
     * Human-readable date string, e.g. "Sun 23 Nov 2025, 08:30"
     */
    public function displayDate(): string
    {
        if (! $this->starts_at) return '';
        return $this->starts_at->format('D j M Y, H:i');
    }

    /**
     * Public-facing event URL.
     */
    public function url(): string
    {
        if (! $this->starts_at || ! $this->slug) return '#';

        return route('events.show', [
            'year'  => $this->starts_at->format('Y'),
            'month' => $this->starts_at->format('m'),
            'slug'  => $this->slug,
        ]);
    }

    // ── Map / location helpers ────────────────────────────────────────────────

    /**
     * Whether a centre pin has been placed for this event.
     */
    public function hasLocation(): bool
    {
        return $this->event_lat !== null && $this->event_lng !== null;
    }

    /**
     * Whether a site boundary polygon has been drawn.
     */
    public function hasPolygon(): bool
    {
        return ! empty($this->event_polygon);
    }

    /**
     * Whether a route (walk, race course etc.) has been drawn.
     */
    public function hasRoute(): bool
    {
        return ! empty($this->event_route);
    }

    /**
     * Whether any points of interest have been placed.
     */
    public function hasPois(): bool
    {
        return ! empty($this->event_pois);
    }

    /**
     * Return the centre pin as a [lat, lng] array for Leaflet.
     * Falls back to Liverpool city centre if no pin is set.
     */
    public function mapCentre(): array
    {
        return [
            $this->event_lat ?? 53.4084,
            $this->event_lng ?? -2.9916,
        ];
    }

    /**
     * Wrap the polygon geometry in a GeoJSON Feature string, safe for
     * embedding directly in a <script> block with {!! !!}.
     *
     * Usage:
     *   const SITE_POLY = {!! $event->polygonFeatureJson() !!};
     */
    public function polygonFeatureJson(): string
    {
        if (! $this->hasPolygon()) return 'null';

        return json_encode([
            'type'       => 'Feature',
            'geometry'   => $this->event_polygon,
            'properties' => ['name' => $this->title],
        ]);
    }

    /**
     * Wrap the route geometry in a GeoJSON Feature string.
     *
     * Usage:
     *   const EVENT_ROUTE = {!! $event->routeFeatureJson() !!};
     */
    public function routeFeatureJson(): string
    {
        if (! $this->hasRoute()) return 'null';

        return json_encode([
            'type'       => 'Feature',
            'geometry'   => $this->event_route,
            'properties' => ['name' => $this->title . ' Route'],
        ]);
    }
}