@extends('layouts.app')
@section('title', 'Gallery')
@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js" defer></script>
<style>
:root{--navy:#003366;--red:#C8102E;--white:#fff;--grey:#f2f5f9;--grey-mid:#dde2e8;--text:#001f40;--muted:#6b7f96;}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
.gal-wrap{max-width:1200px;margin:0 auto;padding:2rem 1rem 4rem;}
.gal-head{margin-bottom:2rem;text-align:center;}
.gal-eyebrow{font-size:.75rem;font-weight:bold;text-transform:uppercase;letter-spacing:.15em;color:var(--red);margin-bottom:.5rem;}
.gal-title{font-size:2rem;font-weight:bold;color:var(--navy);margin-bottom:.5rem;}
.gal-desc{font-size:.95rem;color:var(--muted);}
.gal-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1.2rem;}
.gal-card{background:#fff;border:1px solid var(--grey-mid);border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,51,102,.06);transition:transform .2s,box-shadow .2s;}
.gal-card:hover{transform:translateY(-3px);box-shadow:0 6px 20px rgba(0,51,102,.12);}
.gal-img-wrap{position:relative;padding-top:66%;overflow:hidden;background:#f0f0f0;cursor:pointer;}
.gal-img-wrap img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;transition:transform .3s;}
.gal-card:hover .gal-img-wrap img{transform:scale(1.04);}
.gal-card-body{padding:.9rem 1rem;}
.gal-caption{font-size:.88rem;color:var(--text);font-weight:500;margin-bottom:.4rem;line-height:1.4;}
.gal-meta{font-size:.75rem;color:var(--muted);display:flex;flex-wrap:wrap;gap:.5rem;}
.gal-chip{display:inline-flex;align-items:center;gap:.25rem;padding:.15rem .5rem;background:var(--grey);border-radius:999px;font-size:.72rem;font-weight:600;}
.gal-empty{text-align:center;padding:4rem 1rem;color:var(--muted);}
/* View toggles */
.view-toolbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem;}
.view-filters{display:flex;gap:.4rem;flex-wrap:wrap;}
.view-filter{padding:.35rem .85rem;border-radius:999px;font-size:.78rem;font-weight:600;border:1px solid var(--grey-mid);color:var(--muted);cursor:pointer;background:#fff;transition:all .15s;}
.view-filter:hover{border-color:var(--navy);color:var(--navy);}
.view-filter.active{background:var(--navy);color:#fff;border-color:var(--navy);}
.view-toggles{display:flex;gap:.3rem;}
.view-toggle{padding:.35rem .6rem;border-radius:6px;border:1px solid var(--grey-mid);background:#fff;cursor:pointer;font-size:.9rem;transition:all .15s;color:var(--muted);}
.view-toggle:hover{border-color:var(--navy);color:var(--navy);}
.view-toggle.active{background:var(--navy);color:#fff;border-color:var(--navy);}
/* Masonry */
.gal-grid-masonry{columns:3 280px;gap:1.2rem;}
.gal-grid-masonry .gal-card{break-inside:avoid;margin-bottom:1.2rem;}
.gal-grid-masonry .gal-img-wrap{padding-top:0;height:auto;}
.gal-grid-masonry .gal-img-wrap img{position:static;width:100%;height:auto;}
/* List view */
.gal-grid-list{display:flex;flex-direction:column;gap:.75rem;}
.gal-grid-list .gal-card{display:flex;flex-direction:row;border-radius:8px;}
.gal-grid-list .gal-img-wrap{padding-top:0;width:140px;min-width:140px;height:100px;flex-shrink:0;border-radius:8px 0 0 8px;}
.gal-grid-list .gal-img-wrap img{position:static;width:140px;height:100px;object-fit:cover;}
.gal-grid-list .gal-card-body{padding:.75rem 1rem;flex:1;display:flex;flex-direction:column;justify-content:center;}
@media(max-width:480px){.gal-grid-list .gal-img-wrap{width:90px;min-width:90px;height:70px;}.gal-grid-list .gal-img-wrap img{width:90px;height:70px;}}

/* Lightbox */
.lb-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.92);z-index:9999;align-items:center;justify-content:center;}
.lb-overlay.open{display:flex;}
.lb-container{display:flex;gap:0;max-width:1100px;width:95vw;max-height:90vh;background:#1a1a2e;border-radius:10px;overflow:hidden;position:relative;}
.lb-img-side{flex:1;min-width:0;display:flex;align-items:center;justify-content:center;background:#111;position:relative;min-height:300px;}
.lb-img-wrap{position:relative;width:100%;height:100%;}
.lb-img{max-width:100%;max-height:70vh;object-fit:contain;display:block;margin:auto;}
.lb-tag-dot{position:absolute;width:28px;height:28px;border-radius:50%;background:rgba(200,16,46,.85);border:2px solid #fff;transform:translate(-50%,-50%);cursor:pointer;display:flex;align-items:center;justify-content:center;}
.lb-tag-dot:hover .lb-tag-tip{display:block;}
.lb-tag-tip{display:none;position:absolute;bottom:calc(100% + 6px);left:50%;transform:translateX(-50%);background:#003366;color:#fff;font-size:.72rem;font-weight:bold;padding:.25rem .6rem;border-radius:4px;white-space:nowrap;z-index:10;}
.lb-info-side{width:280px;flex-shrink:0;padding:1.5rem;overflow-y:auto;display:flex;flex-direction:column;gap:1rem;background:#1a1a2e;color:#fff;}
.lb-title{font-size:1rem;font-weight:700;color:#fff;line-height:1.3;}
.lb-meta{font-size:.78rem;color:rgba(255,255,255,.55);display:flex;flex-direction:column;gap:.3rem;}
.lb-map{height:160px;border-radius:6px;overflow:hidden;background:#0d1117;}
.lb-tags{display:flex;flex-direction:column;gap:.4rem;}
.lb-tag-item{display:flex;align-items:center;gap:.5rem;font-size:.8rem;color:rgba(255,255,255,.8);padding:.3rem 0;border-bottom:1px solid rgba(255,255,255,.08);}
.lb-close{position:absolute;top:.75rem;right:.75rem;background:rgba(255,255,255,.1);border:none;color:#fff;font-size:1.2rem;cursor:pointer;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;z-index:10;}
.lb-close:hover{background:rgba(255,255,255,.2);}
.lb-nav{position:absolute;top:50%;transform:translateY(-50%);background:rgba(255,255,255,.08);border:none;color:#fff;font-size:1.4rem;cursor:pointer;padding:.5rem .7rem;border-radius:4px;z-index:10;}
.lb-prev{left:.5rem;}
.lb-next{right:.5rem;}
@media(max-width:700px){
    .lb-container{flex-direction:column;max-height:95vh;}
    .lb-info-side{width:100%;max-height:200px;}
    .lb-map{height:120px;}
}
</style>

<div class="gal-wrap">
    <div class="gal-head">
        <div class="gal-eyebrow">{{ \App\Helpers\RaynetSetting::groupName() }}</div>
        <h1 class="gal-title">📸 Gallery</h1>
        <p class="gal-desc">Photos from our operations, training exercises and events.</p>
    </div>

    {{-- View toolbar --}}
    <div class="view-toolbar">
        <div class="view-filters">
            <button class="view-filter active" onclick="filterPhotos('all', this)">All</button>
            <button class="view-filter" onclick="filterPhotos('featured', this)">⭐ Featured</button>
            <button class="view-filter" onclick="filterPhotos('location', this)">📍 With Location</button>
            <button class="view-filter" onclick="filterPhotos('tagged', this)">🏷 Tagged</button>
        </div>
        <div style="display:flex;align-items:center;gap:.75rem;">
            <span style="font-size:.78rem;color:var(--muted);">{{ $publicPhotos->total() }} photos</span>
            <div class="view-toggles">
                <button class="view-toggle active" onclick="setView('grid', this)" title="Grid">⊞ Grid</button>
                <button class="view-toggle" onclick="setView('masonry', this)" title="Masonry">▦ Masonry</button>
                <button class="view-toggle" onclick="setView('list', this)" title="List">☰ List</button>
                <button class="view-toggle" onclick="setView('map', this)" title="Map">🗺 Map</button>
            </div>
        </div>
    </div>

    @if($publicPhotos->isEmpty())
        <div class="gal-empty">
            <div style="font-size:3rem;margin-bottom:1rem;opacity:.3;">📷</div>
            <p>No photos yet. Check back soon.</p>
        </div>
    @else
        <div class="gal-grid" id="galGrid">
            @foreach($publicPhotos as $photo)
            <div class="gal-card" onclick="openLightbox({{ $loop->index }})" data-featured="{{ $photo->featured ? 1 : 0 }}" data-location="{{ $photo->location ? 1 : 0 }}" data-tagged="{{ $photo->tags->count() ? 1 : 0 }}">
                <div class="gal-img-wrap">
                    <img src="{{ $photo->thumbUrl() }}" alt="{{ $photo->caption ?? 'RAYNET photo' }}" loading="lazy">
                    @if($photo->featured)
                        <div style="position:absolute;top:.5rem;right:.5rem;background:#f59e0b;color:#fff;font-size:.7rem;font-weight:bold;padding:.2rem .5rem;border-radius:4px;">⭐ Featured</div>
                    @endif
                    @if($photo->tags && $photo->tags->count())
                        <div style="position:absolute;bottom:.5rem;left:.5rem;background:rgba(0,51,102,.8);color:#fff;font-size:.7rem;padding:.2rem .5rem;border-radius:4px;">🏷 {{ $photo->tags->count() }} tagged</div>
                    @endif
                </div>
                <div class="gal-card-body">
                    @if($photo->caption)<div class="gal-caption">{{ $photo->caption }}</div>@endif
                    <div class="gal-meta">
                        @if($photo->callsign)<span class="gal-chip">📻 {{ strtoupper($photo->callsign) }}</span>@endif
                        @if($photo->location)<span class="gal-chip">📍 {{ $photo->location }}</span>@endif
                        @if($photo->taken_at)<span class="gal-chip">📅 {{ $photo->taken_at->format('d M Y') }}</span>@endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div style="margin-top:2rem;display:flex;justify-content:center;">{{ $publicPhotos->links('pagination::tailwind') }}</div>
    @endif
</div>


@auth
@if($membersPhotos && $membersPhotos->isNotEmpty())
<div class="gal-wrap" style="padding-top:0;">
    <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1.5rem;padding:1rem 1.25rem;background:#e8eef5;border:1px solid #c7d9ef;border-left:4px solid #003366;border-radius:6px;">
        <span style="font-size:1.25rem;">🔒</span>
        <div>
            <div style="font-size:.9rem;font-weight:700;color:#003366;">Members Only Photos</div>
            <div style="font-size:.78rem;color:#6b7f96;">These photos are approved for members but awaiting admin approval before appearing publicly.</div>
        </div>
    </div>
    <div class="gal-grid">
        @foreach($membersPhotos as $photo)
        <div class="gal-card" onclick="openMembersLightbox({{ $loop->index }})">
            <div class="gal-img-wrap">
                <img src="{{ $photo->thumbUrl() }}" alt="{{ $photo->caption ?? 'RAYNET photo' }}" loading="lazy">
                <div style="position:absolute;top:.5rem;left:.5rem;background:rgba(0,51,102,.85);color:#fff;font-size:.68rem;font-weight:bold;padding:.2rem .5rem;border-radius:4px;">🔒 Members</div>
            </div>
            <div class="gal-card-body">
                @if($photo->caption)<div class="gal-caption">{{ $photo->caption }}</div>@endif
                <div class="gal-meta">
                    @if($photo->callsign)<span class="gal-chip">📻 {{ strtoupper($photo->callsign) }}</span>@endif
                    @if($photo->location)<span class="gal-chip">📍 {{ $photo->location }}</span>@endif
                    @if($photo->taken_at)<span class="gal-chip">📅 {{ $photo->taken_at->format('d M Y') }}</span>@endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <div style="margin-top:1.5rem;display:flex;justify-content:center;">{{ $membersPhotos->links() }}</div>
</div>

@php
$membersPhotosJson = $membersPhotos->map(function($p) {
    return [
        'url'      => $p->url(),
        'caption'  => $p->caption,
        'location' => $p->location,
        'callsign' => $p->callsign,
        'taken_at' => $p->taken_at?->format('d M Y'),
        'tags'     => $p->tags->map(fn($t) => ['callsign'=>$t->callsign,'name'=>$t->name,'x'=>(float)$t->x_pct,'y'=>(float)$t->y_pct])->values(),
        'lat'      => null,
        'lng'      => null,
    ];
})->values();
@endphp
<script>
var membersPhotosData = {!! json_encode($membersPhotosJson) !!};
var membersLbCurrent = 0;
function openMembersLightbox(i) {
    membersLbCurrent = i;
    // Reuse same lightbox but with members data
    var p = membersPhotosData[i];
    document.getElementById('lbImg').src = p.url;
    document.getElementById('lbCaption').textContent = (p.caption || '') + ' 🔒 Members Only';
    document.getElementById('lbTagsWrap').style.display = 'none';
    document.getElementById('lbMapWrap').style.display = 'none';
    document.getElementById('lbOverlay').classList.add('open');
    document.body.style.overflow = 'hidden';
}
</script>
@endif
@endauth

{{-- Map View --}}
<div id="galMapView" style="display:none;margin-bottom:2rem;">
    <div id="galMap" style="height:520px;border-radius:10px;border:1px solid var(--grey-mid);overflow:hidden;"></div>
</div>

{{-- Lightbox --}}
<div class="lb-overlay" id="lbOverlay" onclick="if(event.target===this)closeLb()">
    <div class="lb-container">
        <button class="lb-close" onclick="closeLb()">✕</button>
        <div class="lb-img-side">
            <button class="lb-nav lb-prev" onclick="navLb(-1)">‹</button>
            <div class="lb-img-wrap" id="lbImgWrap">
                <img class="lb-img" id="lbImg" src="" alt="">
                <div id="lbTagDots"></div>
            </div>
            <button class="lb-nav lb-next" onclick="navLb(1)">›</button>
        </div>
        <div class="lb-info-side">
            <div class="lb-title" id="lbCaption"></div>
            <div class="lb-meta" id="lbMeta"></div>
            <div id="lbMapWrap" style="display:none;">
                <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.4);margin-bottom:.4rem;">📍 Location</div>
                <div class="lb-map" id="lbMap"></div>
            </div>
            <div id="lbTagsWrap" style="display:none;">
                <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.4);margin-bottom:.4rem;">🏷 Tagged</div>
                <div class="lb-tags" id="lbTagsList"></div>
            </div>
        </div>
    </div>
</div>

<script>
@php
$photosJson = $publicPhotos->map(function($p) {
    $exif = $p->exif_data ? json_decode($p->exif_data, true) : [];
    $lat = $p->lat ? (float)$p->lat : null;
    $lng = $p->lng ? (float)$p->lng : null;
    if (!$lat && !empty($exif['GPSLatitude']) && !empty($exif['GPSLongitude'])) {
        $parseDeg = function($str) {
            $parts = array_map('trim', explode(',', $str));
            return (float)$parts[0] + ((float)($parts[1]??0))/60 + ((float)($parts[2]??0))/3600;
        };
        $lat = $parseDeg($exif['GPSLatitude']);
        $lng = $parseDeg($exif['GPSLongitude']);
    }
    return [
        'url'      => $p->url(),
        'thumb'    => $p->thumbUrl(),
        'caption'  => $p->caption,
        'location' => $p->location,
        'callsign' => $p->callsign,
        'taken_at' => $p->taken_at?->format('d M Y'),
        'featured' => $p->featured,
        'lat'      => $lat,
        'lng'      => $lng,
        'tags'     => $p->tags->map(fn($t) => ['callsign'=>$t->callsign,'name'=>$t->name,'x'=>(float)$t->x_pct,'y'=>(float)$t->y_pct])->values(),
    ];
})->values();
@endphp
var photosData = {!! json_encode($photosJson) !!};

var current = 0;
var lbMap = null;
var galMap = null;

function openLightbox(i) {
    current = i;
    renderLb();
    document.getElementById('lbOverlay').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeLb() {
    document.getElementById('lbOverlay').classList.remove('open');
    document.body.style.overflow = '';
    if (lbMap) { lbMap.remove(); lbMap = null; }
}
function navLb(dir) {
    if (lbMap) { lbMap.remove(); lbMap = null; }
    current = (current + dir + photosData.length) % photosData.length;
    renderLb();
}

function renderLb() {
    var p = photosData[current];
    document.getElementById('lbImg').src = p.url;

    // Caption
    document.getElementById('lbCaption').textContent = p.caption || '';

    // Meta
    var meta = [];
    if (p.callsign) meta.push('📻 ' + p.callsign.toUpperCase());
    if (p.taken_at) meta.push('📅 ' + p.taken_at);
    if (p.location)  meta.push('📍 ' + p.location);
    document.getElementById('lbMeta').innerHTML = meta.map(function(m){ return '<span>' + m + '</span>'; }).join('');

    // Tags on image
    var dotsEl = document.getElementById('lbTagDots');
    dotsEl.innerHTML = '';
    if (p.tags && p.tags.length) {
        p.tags.forEach(function(t) {
            var dot = document.createElement('div');
            dot.className = 'lb-tag-dot';
            dot.style.left = t.x + '%';
            dot.style.top  = t.y + '%';
            dot.innerHTML = '<div class="lb-tag-tip">' + (t.name || t.callsign) + '</div>';
            dotsEl.appendChild(dot);
        });
        var tw = document.getElementById('lbTagsWrap');
        var tl = document.getElementById('lbTagsList');
        tl.innerHTML = p.tags.map(function(t){
            return '<div class="lb-tag-item">📻 <strong>' + (t.callsign||'') + '</strong>' + (t.name ? ' · ' + t.name : '') + '</div>';
        }).join('');
        tw.style.display = 'block';
    } else {
        document.getElementById('lbTagsWrap').style.display = 'none';
    }

    // Map
    var mapWrap = document.getElementById('lbMapWrap');
    if (lbMap) { lbMap.remove(); lbMap = null; }

    if (p.lat && p.lng) {
        mapWrap.style.display = 'block';
        setTimeout(function() {
            lbMap = L.map('lbMap').setView([p.lat, p.lng], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(lbMap);
            L.marker([p.lat, p.lng]).addTo(lbMap);
        }, 50);
    } else if (p.location) {
        // Geocode text location via Nominatim
        mapWrap.style.display = 'block';
        document.getElementById('lbMap').innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:rgba(255,255,255,.4);font-size:.8rem;">Loading map…</div>';
        fetch('https://nominatim.openstreetmap.org/search?q=' + encodeURIComponent(p.location) + '&format=json&limit=1', {
            headers: {'Accept-Language': 'en'}
        })
        .then(function(r){ return r.json(); })
        .then(function(data) {
            if (data && data[0]) {
                var lat = parseFloat(data[0].lat);
                var lng = parseFloat(data[0].lon);
                document.getElementById('lbMap').innerHTML = '';
                lbMap = L.map('lbMap').setView([lat, lng], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap'
                }).addTo(lbMap);
                L.marker([lat, lng]).addTo(lbMap);
            } else {
                mapWrap.style.display = 'none';
            }
        })
        .catch(function(){ mapWrap.style.display = 'none'; });
    } else {
        mapWrap.style.display = 'none';
    }
}

document.addEventListener('keydown', function(e) {
    if (!document.getElementById('lbOverlay').classList.contains('open')) return;
    if (e.key === 'Escape') closeLb();
    if (e.key === 'ArrowLeft')  navLb(-1);
    if (e.key === 'ArrowRight') navLb(1);
});

// View switching
var currentView = localStorage.getItem('gal_view') || 'grid';
var titleMap = {grid:'Grid', masonry:'Masonry', list:'List', map:'Map'};
setView(currentView, document.querySelector('.view-toggle[title="' + (titleMap[currentView]||'Grid') + '"]'), true);

function setView(view, btn, silent) {
    currentView = view;
    if (!silent) localStorage.setItem('gal_view', view);
    var grid = document.getElementById('galGrid');
    var mapView = document.getElementById('galMapView');

    document.querySelectorAll('.view-toggle').forEach(function(b){ b.classList.remove('active'); });
    if (btn) btn.classList.add('active');

    if (view === 'map') {
        if (grid) grid.style.display = 'none';
        if (mapView) mapView.style.display = 'block';
        initGalMap();
    } else {
        if (grid) {
            grid.style.display = '';
            grid.className = view === 'masonry' ? 'gal-grid-masonry' : view === 'list' ? 'gal-grid-list' : 'gal-grid';
        }
        if (mapView) mapView.style.display = 'none';
    }
}

function initGalMap() {
    if (galMap) { setTimeout(function(){ galMap.invalidateSize(); }, 100); return; }

    galMap = L.map('galMap').setView([53.5, -2.5], 7);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(galMap);

    // Group photos by location
    var locationGroups = {};

    photosData.forEach(function(p, idx) {
        if (!p.lat || !p.lng) return;
        var key = p.lat.toFixed(4) + ',' + p.lng.toFixed(4);
        if (!locationGroups[key]) {
            locationGroups[key] = { lat: p.lat, lng: p.lng, photos: [] };
        }
        locationGroups[key].photos.push({ idx: idx, photo: p });
    });

    // Also geocode location text for photos without coordinates
    var geocodeQueue = [];
    photosData.forEach(function(p, idx) {
        if (!p.lat && !p.lng && p.location) {
            geocodeQueue.push({ idx: idx, photo: p });
        }
    });

    // Add markers for coordinate-based photos
    Object.values(locationGroups).forEach(function(group) {
        addMapMarker(group.lat, group.lng, group.photos);
    });

    // Geocode remaining locations sequentially
    var geocodeIndex = 0;
    function geocodeNext() {
        if (geocodeIndex >= geocodeQueue.length) return;
        var item = geocodeQueue[geocodeIndex++];
        fetch('https://nominatim.openstreetmap.org/search?q=' + encodeURIComponent(item.photo.location) + '&format=json&limit=1', {
            headers: {'Accept-Language': 'en'}
        }).then(function(r){ return r.json(); }).then(function(d) {
            if (d && d[0]) {
                var lat = parseFloat(d[0].lat);
                var lng = parseFloat(d[0].lon);
                var key = lat.toFixed(4) + ',' + lng.toFixed(4);
                if (!locationGroups[key]) {
                    locationGroups[key] = { lat: lat, lng: lng, photos: [] };
                    addMapMarker(lat, lng, [{ idx: item.idx, photo: item.photo }]);
                }
            }
            setTimeout(geocodeNext, 300);
        }).catch(function(){ setTimeout(geocodeNext, 300); });
    }
    geocodeNext();

    // Fit bounds if markers exist
    setTimeout(function() {
        var markers = [];
        Object.values(locationGroups).forEach(function(g) { markers.push([g.lat, g.lng]); });
        if (markers.length > 0) {
            try { galMap.fitBounds(markers, {padding: [30, 30], maxZoom: 13}); } catch(e) {}
        }
    }, 500);
}

function addMapMarker(lat, lng, photos) {
    var count = photos.length;
    var icon = L.divIcon({
        html: '<div style="background:#003366;color:#fff;border:2px solid #fff;border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:bold;box-shadow:0 2px 6px rgba(0,0,0,.3);">' + count + '</div>',
        className: '',
        iconSize: [32, 32],
        iconAnchor: [16, 16]
    });
    var marker = L.marker([lat, lng], { icon: icon }).addTo(galMap);

    // Build popup
    var cols = photos.length === 1 ? 1 : 2;
    var popupWidth = photos.length === 1 ? 180 : 280;
    var popupHtml = '<div style="width:' + popupWidth + 'px;">';
    if (photos[0].photo.location) {
        popupHtml += '<div style="font-size:11px;font-weight:700;color:#003366;margin-bottom:6px;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid #e5e7eb;padding-bottom:4px;">📍 ' + photos[0].photo.location + '</div>';
    }
    if (photos.length > 1) {
        popupHtml += '<div style="font-size:10px;color:#6b7f96;margin-bottom:6px;">' + photos.length + ' photos at this location</div>';
    }
    popupHtml += '<div style="display:grid;grid-template-columns:repeat(' + cols + ',1fr);gap:4px;max-height:240px;overflow-y:auto;">';
    photos.forEach(function(item) {
        popupHtml += '<div onclick="openLightbox(' + item.idx + ')" style="cursor:pointer;border-radius:4px;overflow:hidden;">';
        popupHtml += '<img src="' + item.photo.thumb + '" style="width:100%;aspect-ratio:4/3;object-fit:cover;display:block;">';
        if (item.photo.caption) popupHtml += '<div style="font-size:9px;color:#555;padding:2px 3px;line-height:1.3;background:#f9f9f9;">' + item.photo.caption.substring(0,40) + (item.photo.caption.length>40?'…':'') + '</div>';
        popupHtml += '</div>';
    });
    popupHtml += '</div>';
    if (photos.length > 4) {
        popupHtml += '<div style="text-align:center;font-size:10px;color:#6b7f96;margin-top:4px;">Scroll to see all ' + photos.length + ' photos</div>';
    }
    popupHtml += '</div>';

    marker.bindPopup(popupHtml, { maxWidth: popupWidth + 20, minWidth: popupWidth, maxHeight: 320 });
}

// Filtering
function filterPhotos(type, btn) {
    document.querySelectorAll('.view-filter').forEach(function(b){ b.classList.remove('active'); });
    btn.classList.add('active');
    document.querySelectorAll('.gal-card').forEach(function(card) {
        var show = true;
        if (type === 'featured')  show = card.dataset.featured === '1';
        if (type === 'location')  show = card.dataset.location === '1';
        if (type === 'tagged')    show = card.dataset.tagged   === '1';
        card.style.display = show ? '' : 'none';
    });
}
</script>
@endsection
