@extends('layouts.app')
@section('title', 'My Albums')
@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<style>
:root{--navy:#003366;--red:#C8102E;--white:#fff;--grey:#f2f5f9;--grey-mid:#dde2e8;--text:#001f40;--muted:#6b7f96;}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
.wrap{max-width:1200px;margin:0 auto;padding:1.5rem 1rem 4rem;}
.page-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;}
.page-title{font-size:1.8rem;font-weight:bold;color:var(--navy);}
.eyebrow{font-size:.75rem;font-weight:bold;text-transform:uppercase;letter-spacing:.15em;color:var(--red);margin-bottom:.3rem;}
.section-title{font-size:.82rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--navy);margin-bottom:1rem;padding-bottom:.5rem;border-bottom:2px solid var(--grey-mid);display:flex;align-items:center;justify-content:space-between;}
.album-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1.2rem;margin-bottom:2rem;}
.album-card{background:#fff;border:1px solid var(--grey-mid);border-radius:10px;overflow:hidden;cursor:pointer;transition:transform .2s,box-shadow .2s;}
.album-card:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(0,51,102,.1);}
.album-cover{position:relative;aspect-ratio:16/9;background:#e8eef5;overflow:hidden;display:flex;align-items:center;justify-content:center;}
.album-cover img{width:100%;height:100%;object-fit:cover;}
.album-cover-placeholder{font-size:3rem;opacity:.3;}
.album-mosaic{display:grid;grid-template-columns:1fr 1fr;grid-template-rows:1fr 1fr;height:160px;gap:2px;}
.album-mosaic img{width:100%;height:100%;object-fit:cover;}
.album-mosaic .mosaic-main{grid-row:span 2;}
.album-body{padding:.85rem 1rem;}
.album-name{font-size:.95rem;font-weight:700;color:var(--text);margin-bottom:.25rem;}
.album-meta{font-size:.75rem;color:var(--muted);display:flex;gap:.5rem;align-items:center;}
.badge{display:inline-block;padding:.15rem .45rem;border-radius:3px;font-size:.68rem;font-weight:bold;}
.badge-draft{background:#f3f4f6;color:#6b7280;}
.badge-pending{background:#fef3c7;color:#92400e;}
.badge-approved{background:#d1fae5;color:#065f46;}
.badge-rejected{background:#fee2e2;color:#991b1b;}
.btn{padding:.45rem 1rem;border-radius:999px;font-size:.82rem;font-weight:bold;border:none;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:.3rem;transition:all .15s;}
.btn-primary{background:var(--red);color:#fff;}
.btn-navy{background:#e8eef5;color:var(--navy);}
.btn-green{background:#d1fae5;color:#065f46;}
.btn-amber{background:#fef3c7;color:#92400e;}
.btn-sm{padding:.3rem .7rem;border-radius:4px;font-size:.75rem;}
.alert{padding:.65rem 1rem;border-radius:6px;margin-bottom:1rem;font-size:.88rem;font-weight:bold;}
.alert-success{background:#d1fae5;border-left:3px solid #059669;color:#065f46;}
.alert-error{background:#fee2e2;border-left:3px solid #dc2626;color:#991b1b;}
/* Draft photos strip */
.draft-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:.75rem;margin-bottom:1.5rem;}
.draft-card{background:#fff;border:1px solid var(--grey-mid);border-radius:8px;overflow:hidden;position:relative;}
.draft-thumb{aspect-ratio:4/3;overflow:hidden;background:#f0f0f0;}
.draft-thumb img{width:100%;height:100%;object-fit:cover;}
.draft-actions{padding:.5rem .6rem;display:flex;gap:.3rem;flex-wrap:wrap;}
/* Album modal */
.modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:9999;align-items:center;justify-content:center;padding:1rem;}
.modal.open{display:flex;}
.modal-box{background:#fff;border-radius:10px;max-width:900px;width:100%;max-height:90vh;overflow-y:auto;}
.modal-head{background:#003366;padding:1rem 1.25rem;display:flex;align-items:center;justify-content:space-between;border-radius:10px 10px 0 0;}
.modal-head-title{font-size:.95rem;font-weight:700;color:#fff;}
.modal-body{padding:1.25rem;}
.album-photo-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:.65rem;}
.album-photo-item{position:relative;aspect-ratio:4/3;border-radius:6px;overflow:hidden;background:#f0f0f0;cursor:pointer;border:2px solid transparent;}
.album-photo-item.is-cover{border-color:#f59e0b;}
.album-photo-item img{width:100%;height:100%;object-fit:cover;}
.album-photo-item .overlay{position:absolute;inset:0;background:rgba(0,0,0,.5);display:none;align-items:center;justify-content:center;gap:.3rem;}
.album-photo-item:hover .overlay{display:flex;}
</style>

<div class="wrap">
    <div class="page-head">
        <div>
            <div class="eyebrow">Members Area</div>
            <h1 class="page-title">📚 My Albums</h1>
        </div>
        <button class="btn btn-primary" onclick="document.getElementById('newAlbumModal').classList.add('open')">+ New Album</button>
    </div>

    @if(session('success'))
        <div class="alert alert-success">✓ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">✗ {{ session('error') }}</div>
    @endif

    {{-- Albums --}}
    @if($albums->isNotEmpty())
    <div class="section-title">
        <span>Your Albums ({{ $albums->count() }})</span>
    </div>
    <div class="album-grid">
        @foreach($albums as $album)
        <div class="album-card" onclick="openAlbum({{ $album->id }}, '{{ addslashes($album->name) }}', '{{ addslashes($album->description ?? '') }}', '{{ $album->status }}')">
            <div class="album-cover">
                @if($album->photos_count > 0)
                    @php $albumPhotos = $album->photos()->take(4)->get(); @endphp
                    @if($albumPhotos->count() >= 2)
                    <div class="album-mosaic">
                        <img src="{{ $albumPhotos[0]->thumbUrl() }}" alt="" class="mosaic-main">
                        @foreach($albumPhotos->skip(1) as $ap)
                            <img src="{{ $ap->thumbUrl() }}" alt="">
                        @endforeach
                    </div>
                    @else
                    <img src="{{ $albumPhotos[0]->thumbUrl() }}" alt="">
                    @endif
                @else
                    <div class="album-cover-placeholder">📷</div>
                @endif
                <div style="position:absolute;top:.5rem;right:.5rem;">
                    <span class="badge badge-{{ $album->status }}">{{ ucfirst($album->status) }}</span>
                </div>
            </div>
            <div class="album-body">
                <div class="album-name">{{ $album->name }}</div>
                @if($album->description)<div style="font-size:.78rem;color:var(--muted);margin-bottom:.4rem;line-height:1.4;">{{ Str::limit($album->description, 80) }}</div>@endif
                <div class="album-meta">
                    <span>{{ $album->photos_count }} photo{{ $album->photos_count != 1 ? 's' : '' }}</span>
                    <span>·</span>
                    <span>{{ $album->created_at->format('d M Y') }}</span>
                </div>
                @if($album->status === 'draft' && $album->photos_count > 0)
                <div style="margin-top:.65rem;" onclick="event.stopPropagation()">
                    <form method="POST" action="{{ route('members.albums.submit', $album) }}" style="display:inline;" onsubmit="return confirm('Submit \'{{ addslashes($album->name) }}\' for approval? This will notify the approval team.')">
                        @csrf
                        <button type="submit" class="btn btn-green btn-sm">✓ Submit for Approval</button>
                    </form>
                    <form method="POST" action="{{ route('members.albums.destroy', $album) }}" style="display:inline;" onsubmit="return confirm('Delete this album?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm" style="background:#fee2e2;color:#991b1b;">🗑</button>
                    </form>
                </div>
                @elseif($album->status === 'pending')
                <div style="margin-top:.5rem;font-size:.75rem;color:#92400e;">⏳ Awaiting approval</div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Unassigned draft photos --}}
    @if($drafts->isNotEmpty())
    <div class="section-title">
        <span>Unassigned Drafts ({{ $drafts->count() }})</span>
        <form method="POST" action="{{ route('members.albums.submit-unassigned') }}" onsubmit="return confirm('Submit all {{ $drafts->count() }} unassigned photo(s) for approval?')">
            @csrf
            <button type="submit" class="btn btn-amber btn-sm">📤 Submit All for Approval</button>
        </form>
    </div>
    <div class="draft-grid">
        @foreach($drafts as $photo)
        <div class="draft-card" id="draft-{{ $photo->id }}">
            <div class="draft-thumb">
                <img src="{{ $photo->thumbUrl() }}" alt="">
            </div>
            <div class="draft-actions">
                <select class="btn btn-sm btn-navy" style="padding:.25rem .4rem;font-size:.7rem;" onchange="assignToAlbum({{ $photo->id }}, this.value); this.value=''">
                    <option value="">+ Add to album</option>
                    @foreach($albums->where('status','draft') as $al)
                    <option value="{{ $al->id }}">{{ $al->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="padding:0 .6rem .5rem;font-size:.7rem;color:var(--muted);">{{ $photo->caption ?: $photo->original_filename }}</div>
        </div>
        @endforeach
    </div>
    @endif

    @if($albums->isEmpty() && $drafts->isEmpty())
    <div style="text-align:center;padding:4rem;color:var(--muted);">
        <div style="font-size:3rem;opacity:.2;margin-bottom:.75rem;">📚</div>
        <p>No albums or drafts yet. Go to <a href="{{ route('members.my-photos') }}" style="color:var(--red);">My Photos</a> to upload some.</p>
    </div>
    @endif
</div>

{{-- New Album Modal --}}
<div class="modal" id="newAlbumModal" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal-box" style="max-width:480px;">
        <div class="modal-head">
            <div class="modal-head-title">📚 New Album</div>
            <button onclick="document.getElementById('newAlbumModal').classList.remove('open')" style="background:none;border:none;color:rgba(255,255,255,.7);font-size:1.2rem;cursor:pointer;">✕</button>
        </div>
        <div class="modal-body">
            <form id="newAlbumForm">
                @csrf
                <div style="margin-bottom:.85rem;">
                    <label style="display:block;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:.3rem;">Album Name *</label>
                    <input type="text" id="newAlbumName" maxlength="200" placeholder="e.g. Exercise Mersey 2026" style="width:100%;padding:.5rem .7rem;border:1px solid var(--grey-mid);border-radius:4px;font-size:.9rem;">
                </div>
                <div style="margin-bottom:1rem;">
                    <label style="display:block;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:.3rem;">Description</label>
                    <textarea id="newAlbumDesc" maxlength="500" placeholder="Optional description" style="width:100%;padding:.5rem .7rem;border:1px solid var(--grey-mid);border-radius:4px;font-size:.9rem;resize:vertical;min-height:70px;font-family:inherit;"></textarea>
                </div>
                <button type="button" onclick="createAlbum()" class="btn btn-primary" style="width:100%;justify-content:center;">Create Album</button>
            </form>
        </div>
    </div>
</div>

{{-- Album Detail Modal --}}
<div class="modal" id="albumModal" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal-box">
        <div class="modal-head">
            <div class="modal-head-title" id="albumModalTitle">Album</div>
            <button onclick="document.getElementById('albumModal').classList.remove('open')" style="background:none;border:none;color:rgba(255,255,255,.7);font-size:1.2rem;cursor:pointer;">✕</button>
        </div>
        <div class="modal-body">
            <div id="albumModalContent" style="font-size:.85rem;color:var(--muted);text-align:center;padding:2rem;">Loading…</div>
        </div>
    </div>
</div>

<script>
var csrfToken = document.querySelector('meta[name="csrf-token"]').content;

function createAlbum() {
    var name = document.getElementById('newAlbumName').value.trim();
    if (!name) { alert('Please enter an album name.'); return; }
    fetch('/members/albums', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},
        body: JSON.stringify({name: name, description: document.getElementById('newAlbumDesc').value})
    }).then(r=>r.json()).then(d=>{
        if (d.success) window.location.reload();
    });
}

function assignToAlbum(photoId, albumId) {
    if (!albumId) return;
    fetch('/members/albums/' + albumId + '/assign', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},
        body: JSON.stringify({photo_id: photoId})
    }).then(r=>r.json()).then(d=>{
        if (d.success) {
            var el = document.getElementById('draft-' + photoId);
            if (el) { el.style.opacity='.4'; el.style.pointerEvents='none'; }
        }
    });
}

function openAlbum(albumId, name, desc, status) {
    document.getElementById('albumModalTitle').textContent = '📚 ' + name;
    document.getElementById('albumModal').classList.add('open');
    document.getElementById('albumModalContent').innerHTML = '<div style="text-align:center;padding:2rem;color:var(--muted);">Loading…</div>';

    fetch('/members/albums/' + albumId + '/photos', {
        headers: {'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}
    }).then(r=>r.json()).then(function(photos) {
        var html = '';
        if (desc) html += '<p style="font-size:.85rem;color:var(--muted);margin-bottom:1rem;">' + desc + '</p>';
        if (photos.length === 0) {
            html += '<div style="text-align:center;padding:2rem;color:var(--muted);">No photos in this album yet. Assign some from your unassigned drafts.</div>';
        } else {
            html += '<div class="album-photo-grid">';
            photos.forEach(function(p) {
                html += '<div class="album-photo-item' + (p.is_cover ? ' is-cover' : '') + '">';
                html += '<img src="' + p.thumb + '" alt="">';
                html += '<div class="overlay">';
                if (!p.is_cover && status === 'draft') {
                    html += '<button onclick="setCover(' + albumId + ',' + p.id + ')" style="background:#f59e0b;color:#fff;border:none;padding:.25rem .5rem;border-radius:3px;font-size:.7rem;cursor:pointer;">⭐ Cover</button>';
                    html += '<button onclick="removeFromAlbum(' + albumId + ',' + p.id + ')" style="background:#fee2e2;color:#991b1b;border:none;padding:.25rem .5rem;border-radius:3px;font-size:.7rem;cursor:pointer;">✕ Remove</button>';
                }
                html += '</div>';
                if (p.is_cover) html += '<div style="position:absolute;bottom:.3rem;left:.3rem;background:#f59e0b;color:#fff;font-size:.62rem;padding:.1rem .35rem;border-radius:3px;font-weight:bold;">⭐ Cover</div>';
                html += '</div>';
            });
            html += '</div>';
        }
        document.getElementById('albumModalContent').innerHTML = html;
    });
}

function setCover(albumId, photoId) {
    fetch('/members/albums/' + albumId + '/cover', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},
        body: JSON.stringify({photo_id: photoId})
    }).then(r=>r.json()).then(d=>{ if(d.success) window.location.reload(); });
}

function removeFromAlbum(albumId, photoId) {
    if (!confirm('Remove this photo from the album?')) return;
    fetch('/members/albums/' + albumId + '/remove-photo', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},
        body: JSON.stringify({photo_id: photoId})
    }).then(r=>r.json()).then(d=>{ if(d.success) window.location.reload(); });
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.getElementById('newAlbumModal').classList.remove('open');
        document.getElementById('albumModal').classList.remove('open');
    }
});
</script>
@endsection
