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

    @if($photos->isEmpty())
        <div class="gal-empty">
            <div style="font-size:3rem;margin-bottom:1rem;opacity:.3;">📷</div>
            <p>No photos yet. Check back soon.</p>
        </div>
    @else
        <div class="gal-grid">
            @foreach($photos as $photo)
            <div class="gal-card" onclick="openLightbox({{ $loop->index }})">
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
        <div style="margin-top:2rem;display:flex;justify-content:center;">{{ $photos->links() }}</div>
    @endif
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
$photosJson = $photos->map(function($p) {
    $exif = $p->exif_data ? json_decode($p->exif_data, true) : [];
    $lat = null; $lng = null;
    if (!empty($exif['GPSLatitude']) && !empty($exif['GPSLongitude'])) {
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
</script>
@endsection
