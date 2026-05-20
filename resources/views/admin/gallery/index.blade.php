@extends('layouts.admin')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
@section('title', 'Gallery Management')
@section('content')
<style>
:root{--navy:#003366;--red:#C8102E;--grey:#f2f5f9;--grey-mid:#dde2e8;--text:#001f40;--muted:#6b7f96;--amber:#f59e0b;}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
.wrap{max-width:1340px;margin:0 auto;padding:1.5rem 1.5rem 4rem;}
.page-head{margin-bottom:1.5rem;}
.page-eyebrow{font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.15em;color:var(--red);margin-bottom:.3rem;}
.page-title{font-size:1.8rem;font-weight:bold;color:var(--navy);}
.stat-row{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem;}
.stat-card{background:#fff;border:1px solid var(--grey-mid);border-top:3px solid var(--navy);padding:1rem 1.2rem;}
.stat-label{font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.12em;color:var(--muted);margin-bottom:.3rem;}
.stat-value{font-size:2rem;font-weight:bold;color:var(--navy);}
.filter-tabs{display:flex;gap:.5rem;margin-bottom:1.5rem;flex-wrap:wrap;}
.filter-tab{padding:.45rem 1.1rem;border-radius:999px;font-size:.82rem;font-weight:600;text-decoration:none;border:1px solid var(--grey-mid);color:var(--muted);transition:all .15s;}
.filter-tab:hover{border-color:var(--navy);color:var(--navy);}
.filter-tab.active{background:var(--navy);color:#fff;border-color:var(--navy);}
.photo-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:1.2rem;}
.photo-card{background:#fff;border:1px solid var(--grey-mid);border-radius:8px;overflow:hidden;}
.photo-img{width:100%;aspect-ratio:4/3;object-fit:cover;display:block;}
.photo-body{padding:.85rem;}
.photo-meta{font-size:.78rem;color:var(--muted);margin-bottom:.6rem;display:flex;flex-wrap:wrap;gap:.4rem;}
.photo-chip{padding:.15rem .5rem;border-radius:4px;font-size:.72rem;font-weight:bold;}
.chip-pending{background:#fef3c7;color:#92400e;}
.chip-approved{background:#d1fae5;color:#065f46;}
.chip-rejected{background:#fee2e2;color:#991b1b;}
.chip-featured{background:#fef9c3;color:#854d0e;}
.photo-actions{display:flex;flex-wrap:wrap;gap:.4rem;margin-top:.75rem;}
.btn-sm{padding:.35rem .8rem;border-radius:4px;font-size:.75rem;font-weight:bold;border:none;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;}
.btn-green{background:#d1fae5;color:#065f46;}
.btn-green:hover{background:#a7f3d0;}
.btn-red{background:#fee2e2;color:#991b1b;}
.btn-red:hover{background:#fecaca;}
.btn-amber{background:#fef3c7;color:#92400e;}
.btn-amber:hover{background:#fde68a;}
.btn-navy{background:#e8eef5;color:var(--navy);}
.btn-navy:hover{background:#c7d9ef;}
.edit-form{margin-top:.75rem;border-top:1px solid var(--grey-mid);padding-top:.75rem;display:none;}
.edit-form.open{display:block;}
.ef-input{width:100%;padding:.45rem .6rem;border:1px solid var(--grey-mid);border-radius:4px;font-size:.82rem;margin-bottom:.5rem;font-family:inherit;}
.alert-success{background:#d1fae5;border:1px solid #6ee7b7;border-left:3px solid #059669;padding:.65rem 1rem;margin-bottom:1rem;font-size:.88rem;color:#065f46;font-weight:bold;}
</style>

<div class="wrap">
    <div class="page-head">
        <div class="page-eyebrow">Admin Panel</div>
        <h1 class="page-title">📸 Gallery Management</h1>
    </div>

    @if(session('success'))
        <div class="alert-success">✓ {{ session('success') }}</div>
    @endif

    <div class="stat-row">
        <div class="stat-card" style="border-top-color:#f59e0b;">
            <div class="stat-label">Pending</div>
            <div class="stat-value" style="color:#f59e0b;">{{ $counts['pending'] }}</div>
        </div>
        <div class="stat-card" style="border-top-color:#059669;">
            <div class="stat-label">Approved</div>
            <div class="stat-value" style="color:#059669;">{{ $counts['approved'] }}</div>
        </div>
        <div class="stat-card" style="border-top-color:var(--red);">
            <div class="stat-label">Rejected</div>
            <div class="stat-value" style="color:var(--red);">{{ $counts['rejected'] }}</div>
        </div>
        <div class="stat-card" style="border-top-color:#854d0e;">
            <div class="stat-label">Featured</div>
            <div class="stat-value" style="color:#854d0e;">{{ $counts['featured'] }}</div>
        </div>
    </div>

    <div class="filter-tabs">
        <a href="?status=pending"  class="filter-tab {{ $filter==='pending'  ? 'active' : '' }}">⏳ Pending ({{ $counts['pending'] }})</a>
        <a href="?status=approved" class="filter-tab {{ $filter==='approved' ? 'active' : '' }}">✓ Approved</a>
        <a href="?status=rejected" class="filter-tab {{ $filter==='rejected' ? 'active' : '' }}">✕ Rejected</a>
        <a href="?status=all"      class="filter-tab {{ $filter==='all'      ? 'active' : '' }}">All</a>
    </div>

    @if($photos->isEmpty())
        <div style="text-align:center;padding:3rem;color:var(--muted);background:#fff;border:1px solid var(--grey-mid);border-radius:8px;">
            No photos in this category.
        </div>
    @else
    <div class="photo-grid">
        @foreach($photos as $photo)
        <div class="photo-card">
            <img src="{{ $photo->thumbUrl() }}" alt="" class="photo-img">
            <div class="photo-body">
                <div class="photo-meta">
                    <span class="photo-chip chip-{{ $photo->status }}">{{ ucfirst($photo->status) }}</span>
                    @if($photo->featured)<span class="photo-chip chip-featured">⭐ Featured</span>@endif
                    @if($photo->callsign)<span class="photo-chip" style="background:#e8eef5;color:var(--navy);">{{ strtoupper($photo->callsign) }}</span>@endif
                </div>
                <div style="font-size:.85rem;font-weight:600;color:var(--text);margin-bottom:.3rem;">{{ $photo->caption ?: '(No caption)' }}</div>
                <div style="font-size:.75rem;color:var(--muted);">
                    {{ $photo->user?->name }} · {{ $photo->created_at->format('d M Y') }}
                    @if($photo->location) · 📍 {{ $photo->location }}@endif
                </div>
                @if($photo->tags && $photo->tags->count())
                <div style="margin-top:.4rem;display:flex;flex-wrap:wrap;gap:.3rem;">
                    @foreach($photo->tags as $tag)
                    <span style="background:#e8eef5;color:var(--navy);font-size:.7rem;padding:.15rem .45rem;border-radius:3px;font-weight:600;display:inline-flex;align-items:center;gap:.25rem;">
                        🏷 {{ strtoupper($tag->callsign) }}
                        @if($tag->name && $tag->name !== $tag->callsign)
                            <span style="font-weight:400;opacity:.7;">{{ $tag->name }}</span>
                        @endif
                        @if($tag->user_id)
                            <span style="background:#059669;color:#fff;font-size:.62rem;padding:.1rem .3rem;border-radius:2px;">member</span>
                        @endif
                    </span>
                    @endforeach
                </div>
                @endif
                <div class="photo-actions">
                    @if($photo->status !== 'approved')
                        <form method="POST" action="{{ route('admin.super.admin.gallery.approve', $photo) }}" style="display:inline;">
                            @csrf <button class="btn-sm btn-green">✓ Approve</button>
                        </form>
                    @endif
                    @if($photo->status !== 'rejected')
                        <form method="POST" action="{{ route('admin.super.admin.gallery.reject', $photo) }}" style="display:inline;">
                            @csrf <button class="btn-sm btn-red">✕ Reject</button>
                        </form>
                    @endif
                    @if($photo->isApproved())
                        <form method="POST" action="{{ route('admin.super.admin.gallery.feature', $photo) }}" style="display:inline;">
                            @csrf <button class="btn-sm btn-amber">{{ $photo->featured ? '★ Unfeature' : '⭐ Feature' }}</button>
                        </form>
                    @endif
                    <button class="btn-sm btn-navy" onclick="this.closest('.photo-card').querySelector('.edit-form').classList.toggle('open')">✎ Edit</button>
                    <form method="POST" action="{{ route('admin.super.admin.gallery.destroy', $photo) }}" style="display:inline;" onsubmit="return confirm('Delete this photo permanently?')">
                        @csrf @method('DELETE') <button class="btn-sm btn-red">🗑</button>
                    </form>
                    <a href="{{ $photo->url() }}" target="_blank" class="btn-sm btn-navy">🔍 Full</a>
                    <button class="btn-sm btn-navy" onclick="openDetails({{ $photo->id }}, {{ json_encode($photo->location) }}, {{ json_encode($photo->tags->map(fn($t)=>['id'=>$t->id,'callsign'=>$t->callsign,'name'=>$t->name,'x'=>(float)$t->x_pct,'y'=>(float)$t->y_pct])->values()) }}, {{ json_encode($photo->url()) }})">🗺 Details</button>
                </div>
                <div class="edit-form">
                    <form method="POST" action="{{ route('admin.super.admin.gallery.update', $photo) }}">
                        @csrf @method('PATCH')
                        <input type="text" name="caption" class="ef-input" value="{{ $photo->caption }}" placeholder="Caption">
                        <input type="text" name="location" class="ef-input" value="{{ $photo->location }}" placeholder="Location">
                        <div style="display:flex;gap:.4rem;margin-bottom:.5rem;">
                            <button type="button" onclick="this.closest('.edit-form').querySelector('.ef-map').classList.toggle('open')" style="font-size:.72rem;background:#e8eef5;color:var(--navy);border:none;padding:.3rem .6rem;border-radius:4px;cursor:pointer;">📍 Edit on Map</button>
                            <button type="button" onclick="this.closest('form').querySelector('[name=location]').value=''" style="font-size:.72rem;background:#fee2e2;color:#991b1b;border:none;padding:.3rem .6rem;border-radius:4px;cursor:pointer;">✕ Clear Location</button>
                        </div>
                        <div class="ef-map" style="height:160px;border-radius:4px;overflow:hidden;margin-bottom:.5rem;display:none;" data-photo-id="{{ $photo->id }}"></div>
                        <input type="text" name="admin_notes" class="ef-input" value="{{ $photo->admin_notes }}" placeholder="Admin notes (internal)">
                        <button type="submit" class="btn-sm btn-navy" style="width:100%;justify-content:center;">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <div style="margin-top:1.5rem;">{{ $photos->withQueryString()->links() }}</div>
    @endif
</div>

{{-- Details Modal --}}
<div id="detailsModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:9999;align-items:center;justify-content:center;padding:1rem;" onclick="if(event.target===this)closeDetails()">
    <div style="background:#1a1a2e;border-radius:10px;max-width:800px;width:100%;max-height:90vh;overflow-y:auto;box-shadow:0 8px 32px rgba(0,0,0,.4);">
        <div style="background:#003366;padding:1rem 1.25rem;display:flex;align-items:center;justify-content:space-between;border-radius:10px 10px 0 0;border-bottom:3px solid #C8102E;">
            <div style="font-size:.95rem;font-weight:700;color:#fff;">🗺 Photo Details — Tags & Location</div>
            <button onclick="closeDetails()" style="background:none;border:none;color:rgba(255,255,255,.7);font-size:1.2rem;cursor:pointer;">✕</button>
        </div>
        <div style="display:grid;grid-template-columns:1fr 280px;gap:0;">
            <div style="position:relative;background:#111;">
                <img id="detailsImg" src="" alt="" style="width:100%;max-height:500px;object-fit:contain;display:block;">
                <div id="detailsDots"></div>
            </div>
            <div style="padding:1.25rem;background:#222;display:flex;flex-direction:column;gap:1rem;">
                <div>
                    <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.4);margin-bottom:.5rem;">📍 Location</div>
                    <div id="detailsLocation" style="font-size:.85rem;color:rgba(255,255,255,.8);margin-bottom:.5rem;"></div>
                    <div id="detailsMapEl" style="height:150px;border-radius:6px;overflow:hidden;"></div>
                </div>
                <div>
                    <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.4);margin-bottom:.5rem;">🏷 Tags</div>
                    <div id="detailsTagsList" style="display:flex;flex-direction:column;gap:.4rem;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var detailsMap = null;
function openDetails(photoId, location, tags, imgUrl) {
    document.getElementById('detailsImg').src = imgUrl;
    document.getElementById('detailsLocation').textContent = location || 'No location set';

    // Tags
    var tl = document.getElementById('detailsTagsList');
    tl.innerHTML = '';
    var dots = document.getElementById('detailsDots');
    dots.innerHTML = '';

    tags.forEach(function(t) {
        var item = document.createElement('div');
        item.style.cssText = 'display:flex;align-items:center;justify-content:space-between;background:rgba(255,255,255,.07);padding:.4rem .6rem;border-radius:4px;';
        item.innerHTML = '<span style="font-size:.8rem;color:#fff;font-weight:600;">📻 ' + t.callsign + (t.name ? ' · <span style='font-weight:400;opacity:.7;'>' + t.name + '</span>' : '') + '</span>';
        var removeBtn = document.createElement('form');
        removeBtn.method = 'POST';
        removeBtn.action = '/admin/super/gallery/' + photoId + '/tags/' + t.id;
        removeBtn.style.cssText = 'display:inline;margin:0;';
        removeBtn.innerHTML = '<input type="hidden" name="_token" value="' + document.querySelector('meta[name="csrf-token"]').content + '"><input type="hidden" name="_method" value="DELETE"><button type="submit" style="background:#fee2e2;color:#991b1b;border:none;padding:.2rem .5rem;border-radius:3px;font-size:.7rem;font-weight:bold;cursor:pointer;" onclick="return confirm('Remove tag?')">✕</button>';
        item.appendChild(removeBtn);
        tl.appendChild(item);

        // Dot on image
        var dot = document.createElement('div');
        dot.style.cssText = 'position:absolute;width:22px;height:22px;border-radius:50%;background:rgba(200,16,46,.85);border:2px solid #fff;transform:translate(-50%,-50%);cursor:default;';
        dot.style.left = t.x + '%';
        dot.style.top  = t.y + '%';
        dot.title = t.name || t.callsign;
        dots.appendChild(dot);
    });

    if (tags.length === 0) {
        tl.innerHTML = '<div style="font-size:.8rem;color:rgba(255,255,255,.4);">No tags on this photo.</div>';
    }

    document.getElementById('detailsModal').style.display = 'flex';

    // Map
    if (detailsMap) { detailsMap.remove(); detailsMap = null; }
    if (location) {
        setTimeout(function() {
            fetch('https://nominatim.openstreetmap.org/search?q=' + encodeURIComponent(location) + '&format=json&limit=1')
            .then(function(r){return r.json();})
            .then(function(d) {
                if (d && d[0]) {
                    detailsMap = L.map('detailsMapEl').setView([parseFloat(d[0].lat), parseFloat(d[0].lon)], 13);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {attribution:'© OpenStreetMap'}).addTo(detailsMap);
                    L.marker([parseFloat(d[0].lat), parseFloat(d[0].lon)]).addTo(detailsMap);
                }
            }).catch(function(){});
        }, 100);
    } else {
        document.getElementById('detailsMapEl').innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:rgba(255,255,255,.3);font-size:.8rem;">No location data</div>';
    }
}
function closeDetails() {
    document.getElementById('detailsModal').style.display = 'none';
    if (detailsMap) { detailsMap.remove(); detailsMap = null; }
}
</script>
@endsection
