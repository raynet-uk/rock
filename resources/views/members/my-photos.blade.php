@extends('layouts.app')
@section('title', 'My Photos')
@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<style>
:root{--navy:#003366;--red:#C8102E;--white:#fff;--grey:#f2f5f9;--grey-mid:#dde2e8;--text:#001f40;--muted:#6b7f96;}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
.wrap{max-width:1200px;margin:0 auto;padding:1.5rem 1rem 4rem;}
.page-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;}
.page-title{font-size:1.8rem;font-weight:bold;color:var(--navy);}
.eyebrow{font-size:.75rem;font-weight:bold;text-transform:uppercase;letter-spacing:.15em;color:var(--red);margin-bottom:.3rem;}
.upload-card{background:#fff;border:1px solid var(--grey-mid);border-top:3px solid var(--red);padding:1.5rem;border-radius:8px;margin-bottom:2rem;}
.upload-title{font-size:.85rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--navy);margin-bottom:1rem;display:flex;align-items:center;gap:.5rem;}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.75rem;}
@media(max-width:600px){.form-grid{grid-template-columns:1fr;}}
.form-label{display:block;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:.3rem;}
.form-input{width:100%;padding:.5rem .7rem;border:1px solid var(--grey-mid);border-radius:4px;font-size:.88rem;font-family:inherit;}
.form-input:focus{outline:none;border-color:var(--navy);}
.consent-row{display:flex;align-items:flex-start;gap:.6rem;margin-bottom:.85rem;}
.consent-row input{margin-top:3px;flex-shrink:0;width:15px;height:15px;accent-color:var(--navy);}
.consent-text{font-size:.8rem;color:var(--muted);line-height:1.5;}
.upload-btn{background:var(--red);color:#fff;border:none;padding:.65rem 1.5rem;border-radius:999px;font-size:.88rem;font-weight:700;cursor:pointer;}
.progress-wrap{display:none;margin-top:.85rem;}
.progress-label{font-size:.78rem;color:var(--muted);margin-bottom:.4rem;}
.progress-bar-track{background:var(--grey-mid);border-radius:999px;height:8px;overflow:hidden;}
.progress-bar-fill{height:100%;background:var(--red);width:0%;transition:width .2s;border-radius:999px;}
.success-banner{display:none;background:#d1fae5;border-left:3px solid #059669;padding:.65rem 1rem;border-radius:4px;font-size:.88rem;color:#065f46;font-weight:bold;margin-bottom:1rem;}
.photo-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1rem;}
@media(max-width:480px){.photo-grid{grid-template-columns:1fr 1fr;gap:.65rem;}}
.photo-card{background:#fff;border:1px solid var(--grey-mid);border-radius:8px;overflow:hidden;}
.photo-thumb{position:relative;aspect-ratio:4/3;overflow:hidden;background:#f0f0f0;}
.photo-thumb img{width:100%;height:100%;object-fit:cover;transition:transform .3s;}
.photo-card:hover .photo-thumb img{transform:scale(1.04);}
.badge{display:inline-block;padding:.15rem .45rem;border-radius:3px;font-size:.65rem;font-weight:bold;}
.badge-pending{background:#fef3c7;color:#92400e;}
.badge-approved{background:#d1fae5;color:#065f46;}
.badge-rejected{background:#fee2e2;color:#991b1b;}
.badge-featured{background:#fef9c3;color:#854d0e;}
.badge-public{background:#dbeafe;color:#1e40af;}
.photo-body{padding:.7rem .85rem;}
.photo-caption{font-size:.82rem;font-weight:600;color:var(--text);margin-bottom:.3rem;line-height:1.3;}
.photo-meta{font-size:.72rem;color:var(--muted);margin-bottom:.5rem;}
.photo-actions{display:flex;gap:.35rem;flex-wrap:wrap;}
.btn-sm{padding:.3rem .7rem;border-radius:4px;font-size:.72rem;font-weight:bold;border:none;cursor:pointer;}
.btn-red{background:#fee2e2;color:#991b1b;}
.btn-navy{background:#e8eef5;color:var(--navy);}
.empty-state{text-align:center;padding:3rem;color:var(--muted);}
.section-title{font-size:.82rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--navy);margin-bottom:1rem;padding-bottom:.5rem;border-bottom:2px solid var(--grey-mid);}
</style>

<div class="wrap">
    <div class="page-head">
        <div>
            <div class="eyebrow">Members Area</div>
            <h1 class="page-title">🖼️ My Photos</h1>
        </div>
        <a href="{{ route('gallery') }}" style="font-size:.82rem;color:var(--red);font-weight:bold;text-decoration:none;">View Gallery →</a>
    </div>

    {{-- Success / error --}}
    <div class="success-banner" id="photoSuccessBanner"></div>
    @if(session('photo_success'))
        <div class="success-banner" style="display:block;">✓ {{ session('photo_success') }}</div>
    @endif
    @if($errors->any())
        <div style="background:#fee2e2;border-left:3px solid #dc2626;padding:.65rem 1rem;border-radius:4px;font-size:.88rem;color:#991b1b;font-weight:bold;margin-bottom:1rem;">{{ $errors->first() }}</div>
    @endif

    {{-- Upload form --}}
    <div class="upload-card">
        <div class="upload-title">📤 Upload Photos</div>
        <form method="POST" action="{{ route('members.photos.store') }}" enctype="multipart/form-data" id="photoUploadForm">
            @csrf
            <div class="form-grid">
                <div>
                    <label class="form-label">Photos <span style="color:var(--red);">*</span></label>
                    <input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple class="form-input" style="padding:.35rem .5rem;" required>
                    <div style="font-size:.7rem;color:var(--muted);margin-top:.25rem;">Max 32MB each · JPG, PNG or WebP · Select multiple</div>
                </div>
                <div>
                    <label class="form-label">Caption</label>
                    <input type="text" name="caption" maxlength="500" placeholder="Describe the photo" class="form-input">
                </div>
                <div>
                    <label class="form-label">Location</label>
                    <div style="display:flex;gap:.4rem;">
                        <input type="text" name="location" id="uploadLocationText" maxlength="200" placeholder="Where was this taken?" class="form-input" style="flex:1;">
                        <button type="button" onclick="toggleUploadMap()" style="background:#e8eef5;color:var(--navy);border:1px solid var(--grey-mid);padding:.45rem .7rem;border-radius:4px;font-size:.82rem;cursor:pointer;white-space:nowrap;">📍 Map</button>
                    </div>
                    <input type="hidden" name="lat" id="uploadLat">
                    <input type="hidden" name="lng" id="uploadLng">
                    <div id="uploadMapWrap" style="display:none;margin-top:.5rem;border-radius:6px;overflow:hidden;border:1px solid var(--grey-mid);">
                        <div style="background:#e8eef5;padding:.35rem .75rem;font-size:.72rem;color:var(--navy);font-weight:600;">Click to place a pin for the photo location</div>
                        <div id="uploadMap" style="height:200px;"></div>
                    </div>
                </div>
                <div>
                    <label class="form-label">Date Taken</label>
                    <input type="date" name="taken_at" class="form-input">
                </div>
            </div>
            <label class="consent-row">
                <input type="checkbox" name="consent" value="1" required>
                <span class="consent-text">I consent to {{ \App\Helpers\RaynetSetting::groupName() }} storing and displaying this photo in the group gallery. I confirm I have the right to share this image and that it does not contain personal data of others without their consent.</span>
            </label>
            <button type="submit" class="upload-btn" id="photoUploadBtn">📤 Upload Photo</button>
            <div class="progress-wrap" id="uploadProgressWrap">
                <div class="progress-label" id="uploadProgressLabel">Uploading…</div>
                <div class="progress-bar-track"><div class="progress-bar-fill" id="uploadProgressBar"></div></div>
            </div>
        </form>
    </div>

    {{-- My photos grid --}}
    @php $myPhotos = ($myPhotos ?? collect())->load('tags'); @endphp
    @if($myPhotos->isNotEmpty())
        <div class="section-title">Your Uploads ({{ $myPhotos->count() }})</div>
        <div class="photo-grid">
            @foreach($myPhotos as $photo)
            <div class="photo-card">
                <div class="photo-thumb">
                    <img src="{{ $photo->thumbUrl() }}" alt="">
                    <div style="position:absolute;top:.4rem;left:.4rem;display:flex;flex-direction:column;gap:.2rem;">
                        @if($photo->status==='approved')
                            <span class="badge badge-approved">✓ Members</span>
                            @if($photo->public_status==='approved')
                                <span class="badge badge-public">🌐 Public</span>
                            @else
                                <span class="badge badge-pending">⏳ Awaiting public</span>
                            @endif
                        @elseif($photo->status==='draft')
                            <span class="badge" style="background:#fef3c7;color:#92400e;">📝 Draft — not yet submitted</span>
                        @elseif($photo->status==='rejected')
                            <span class="badge badge-rejected">✕ Rejected</span>
                        @else
                            <span class="badge badge-pending">⏳ Pending review</span>
                        @endif
                        @if($photo->featured)
                            <span class="badge badge-featured">⭐ Featured</span>
                        @endif
                    </div>
                </div>
                <div class="photo-body">
                    <div class="photo-caption">{{ $photo->caption ?: '(No caption)' }}</div>
                    <div class="photo-meta">
                        {{ $photo->created_at->format('d M Y') }}
                        {{ $photo->location ? '· 📍 ' . $photo->location : '' }}
                        {{ $photo->tags->count() ? '· 🏷 ' . $photo->tags->count() . ' tagged' : '' }}
                    </div>
                    @if($photo->status==='rejected' && $photo->admin_notes)
                        <div style="background:#fee2e2;border-left:2px solid #dc2626;padding:.3rem .5rem;font-size:.72rem;color:#991b1b;margin-bottom:.5rem;border-radius:0 3px 3px 0;">{{ $photo->admin_notes }}</div>
                    @endif
                    <div class="photo-actions">
                        @if($photo->status==='draft')
                            @if($photo->exif_data)
                                <button class="btn-sm btn-navy" data-exif-id="{{ $photo->id }}" data-exif='{{ $photo->exif_data }}' onclick="showExifFromBtn(this)">📷 EXIF</button>
                            @endif
                            <button onclick="openEditModal({{ $photo->id }}, '{{ addslashes($photo->caption ?? '') }}', '{{ addslashes($photo->location ?? '') }}')" class="btn-sm btn-navy">✎ Edit</button>
                            <button onclick="openTagger({{ $photo->id }}, '{{ $photo->url() }}')" class="btn-sm" style="background:#fef3c7;color:#92400e;">🏷 Tag</button>
                            <form method="POST" action="{{ route('members.photos.submit', $photo) }}" style="display:inline;" onsubmit="return confirm('Submit this photo for approval?')">
                                @csrf <button type="submit" class="btn-sm" style="background:#d1fae5;color:#065f46;">✓ Submit</button>
                            </form>
                            <form method="POST" action="{{ route('members.photos.destroy', $photo) }}" onsubmit="return confirm('Delete this photo?')" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-sm btn-red">🗑</button>
                            </form>
                        @else
                            @if($photo->exif_data)
                                <button class="btn-sm btn-navy" data-exif-id="{{ $photo->id }}" data-exif='{{ $photo->exif_data }}' onclick="showExifFromBtn(this)">📷 EXIF</button>
                            @endif
                            <button onclick="openEditModal({{ $photo->id }}, '{{ addslashes($photo->caption ?? '') }}', '{{ addslashes($photo->location ?? '') }}')" class="btn-sm btn-navy">↻ Rotate</button>
                            <button onclick="openTagger({{ $photo->id }}, '{{ $photo->url() }}')" class="btn-sm" style="background:#fef3c7;color:#92400e;">🏷 Tag</button>
                            <form method="POST" action="{{ route('members.photos.destroy', $photo) }}" onsubmit="return confirm('Delete this photo?')" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-sm btn-red">🗑</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <div style="font-size:3rem;opacity:.2;margin-bottom:.75rem;">📷</div>
            <p style="font-size:.95rem;">No photos uploaded yet.<br><span style="font-size:.85rem;color:var(--muted);">Use the form above to share your first photo.</span></p>
        </div>
    @endif
</div>

{{-- EXIF Modal --}}
<div id="exifModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:9999;align-items:center;justify-content:center;padding:1rem;" onclick="if(event.target===this)closeExif()">
    <div style="background:#fff;border-radius:10px;padding:1.5rem;max-width:480px;width:100%;max-height:80vh;overflow-y:auto;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
            <div style="font-size:1rem;font-weight:700;color:#003366;">📷 EXIF Data</div>
            <button onclick="closeExif()" style="background:none;border:none;font-size:1.2rem;cursor:pointer;color:#6b7f96;">✕</button>
        </div>
        <div id="exifContent" style="font-size:.82rem;"></div>
    </div>
</div>

{{-- Tagger Modal --}}
<div id="taggerModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:9999;align-items:center;justify-content:center;padding:1rem;" onclick="if(event.target===this)closeTagger()">
    <div style="background:#fff;border-radius:10px;max-width:700px;width:100%;max-height:90vh;overflow-y:auto;">
        <div style="background:#003366;padding:1rem 1.25rem;display:flex;align-items:center;justify-content:space-between;border-radius:10px 10px 0 0;">
            <div style="font-size:.95rem;font-weight:700;color:#fff;">🏷 Tag People in Photo</div>
            <button onclick="closeTagger()" style="background:none;border:none;color:rgba(255,255,255,.7);font-size:1.2rem;cursor:pointer;">✕</button>
        </div>
        <div style="padding:1.25rem;">
            <p style="font-size:.82rem;color:#6b7f96;margin-bottom:1rem;">Click on the photo to place a tag, then search by callsign.</p>
            <div style="position:relative;display:inline-block;width:100%;cursor:crosshair;" id="taggerImgWrap">
                <img id="taggerImg" src="" alt="" style="width:100%;display:block;border-radius:6px;">
                <div id="taggerDots"></div>
                <div id="taggerPendingDot" style="display:none;position:absolute;width:24px;height:24px;border-radius:50%;background:rgba(200,16,46,.7);border:2px dashed #fff;transform:translate(-50%,-50%);pointer-events:none;"></div>
            </div>
            <div id="taggerForm" style="display:none;margin-top:1rem;background:#f2f5f9;padding:1rem;border-radius:6px;">
                <div style="font-size:.8rem;font-weight:700;color:#003366;margin-bottom:.75rem;">Add tag</div>
                <div style="display:flex;gap:.5rem;align-items:flex-end;">
                    <div style="flex:1;">
                        <label style="display:block;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#6b7f96;margin-bottom:.3rem;">Callsign (members only)</label>
                        <input type="text" id="taggerCallsign" placeholder="e.g. M0ABC" style="width:100%;padding:.45rem .6rem;border:1px solid #dde2e8;border-radius:4px;font-size:.88rem;text-transform:uppercase;font-family:monospace;">
                    </div>
                    <button onclick="lookupAndTag()" style="background:#003366;color:#fff;border:none;padding:.45rem 1rem;border-radius:4px;font-size:.82rem;font-weight:bold;cursor:pointer;">🔍 Tag</button>
                    <button onclick="cancelTag()" style="background:#f0f0f0;color:#666;border:none;padding:.45rem .8rem;border-radius:4px;font-size:.82rem;cursor:pointer;">Cancel</button>
                </div>
                <div id="taggerLookupResult" style="margin-top:.5rem;font-size:.82rem;"></div>
            </div>
        </div>
    </div>
</div>

<script>
// Scroll to drafts after upload
if (sessionStorage.getItem('scrollToDrafts')) {
    sessionStorage.removeItem('scrollToDrafts');
    setTimeout(function() {
        var el = document.getElementById('draftReviewBanner') || document.getElementById('uploadsSection');
        if (el) el.scrollIntoView({behavior: 'smooth', block: 'start'});
    }, 300);
}

// Map
var uploadMapInstance = null, uploadMarker = null;
function toggleUploadMap() {
    var wrap = document.getElementById('uploadMapWrap');
    wrap.style.display = wrap.style.display === 'none' ? 'block' : 'none';
    if (wrap.style.display === 'block' && !uploadMapInstance) {
        uploadMapInstance = L.map('uploadMap').setView([53.4, -2.99], 10);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {attribution:'© OpenStreetMap'}).addTo(uploadMapInstance);
        uploadMapInstance.on('click', function(e) {
            if (uploadMarker) uploadMarker.remove();
            uploadMarker = L.marker(e.latlng).addTo(uploadMapInstance);
            document.getElementById('uploadLat').value = e.latlng.lat.toFixed(6);
            document.getElementById('uploadLng').value = e.latlng.lng.toFixed(6);
            fetch('https://nominatim.openstreetmap.org/reverse?lat='+e.latlng.lat+'&lon='+e.latlng.lng+'&format=json')
            .then(r=>r.json()).then(d=>{
                if(d&&d.address){var a=d.address,parts=[];
                if(a.road)parts.push(a.road);if(a.suburb)parts.push(a.suburb);
                if(a.city||a.town||a.village)parts.push(a.city||a.town||a.village);
                document.getElementById('uploadLocationText').value=parts.join(', ');}
            }).catch(()=>{});
        });
        setTimeout(()=>uploadMapInstance.invalidateSize(),100);
    }
}

// Upload XHR — sequential one at a time
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('photoUploadForm');
    if (!form) return;
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var fileInput = form.querySelector('input[type="file"]');
        if (!fileInput || !fileInput.files.length) { alert('Please select at least one photo.'); return; }
        var consent = form.querySelector('input[name="consent"]');
        if (!consent || !consent.checked) { alert('Please tick the consent checkbox.'); return; }

        var files = Array.from(fileInput.files);
        var total = files.length;
        var done = 0;
        var failed = 0;
        var btn = document.getElementById('photoUploadBtn');
        var wrap = document.getElementById('uploadProgressWrap');
        var bar = document.getElementById('uploadProgressBar');
        var label = document.getElementById('uploadProgressLabel');
        var banner = document.getElementById('photoSuccessBanner');

        btn.disabled = true;
        wrap.style.display = 'block';
        bar.style.background = 'var(--red)';

        var caption  = form.querySelector('[name="caption"]').value;
        var location = form.querySelector('[name="location"]').value;
        var taken_at = form.querySelector('[name="taken_at"]').value;
        var latVal   = document.getElementById('uploadLat').value;
        var lngVal   = document.getElementById('uploadLng').value;
        var csrf     = document.querySelector('meta[name="csrf-token"]').content;

        function uploadOne(index) {
            if (index >= total) {
                // All done
                bar.style.width = '100%';
                bar.style.background = failed > 0 ? '#f59e0b' : '#059669';
                var msg = done + ' of ' + total + ' photo' + (total>1?'s':'') + ' saved as draft. Review below then submit for approval when ready.';
                if (failed > 0) msg += ' ' + failed + ' failed.';
                label.textContent = '✓ ' + msg;
                banner.textContent = '✓ ' + msg;
                banner.style.display = 'block';
                btn.disabled = false;
                btn.textContent = '📤 Upload Photo';
                // Photos saved as drafts — scroll to review section
                form.reset();
                btn.disabled = false;
                btn.textContent = '📤 Upload More';
                wrap.style.display = 'none';
                setTimeout(function(){
                    // Reload to show new drafts, then scroll to them
                    sessionStorage.setItem('scrollToDrafts', '1');
                    window.location.reload();
                }, 1200);
                return;
            }

            var file = files[index];
            var pct = Math.round(index / total * 100);
            label.textContent = 'Uploading ' + (index+1) + ' of ' + total + ': ' + file.name;
            bar.style.width = pct + '%';

            var fd = new FormData();
            fd.append('photos[]', file);
            fd.append('_token', csrf);
            if (caption)  fd.append('caption', caption);
            if (location) fd.append('location', location);
            if (taken_at) fd.append('taken_at', taken_at);
            if (latVal)   fd.append('lat', latVal);
            if (lngVal)   fd.append('lng', lngVal);
            fd.append('consent', '1');

            var xhr = new XMLHttpRequest();
            xhr.open('POST', form.action);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.setRequestHeader('X-CSRF-TOKEN', csrf);

            xhr.upload.addEventListener('progress', function(ev) {
                if (ev.lengthComputable) {
                    var filePct = ev.loaded / ev.total;
                    var overall = (index + filePct) / total * 100;
                    bar.style.width = Math.round(overall) + '%';
                }
            });

            xhr.addEventListener('load', function() {
                try {
                    var resp = JSON.parse(xhr.responseText);
                    if (resp.success) { done++; } else { failed++; }
                } catch(err) { failed++; }
                uploadOne(index + 1);
            });

            xhr.addEventListener('error', function() {
                failed++;
                uploadOne(index + 1);
            });

            xhr.send(fd);
        }

        uploadOne(0);
    });
});

// EXIF
function showExifFromBtn(btn) {
    var data = JSON.parse(btn.dataset.exif || '{}');
    showExif(btn.dataset.exifId, data);
}
function showExif(id, data) {
    var labels = {Make:'Camera Make',Model:'Camera Model',DateTime:'Date/Time',DateTimeOriginal:'Date Taken',ExposureTime:'Exposure',FNumber:'F-Number',ISOSpeedRatings:'ISO',FocalLength:'Focal Length',Flash:'Flash',Software:'Software',ImageWidth:'Width',ImageLength:'Height'};
    var html = '<table style="width:100%;border-collapse:collapse;">';
    for (var k in data) { var l=labels[k]||k; html+='<tr style="border-bottom:1px solid #f0f0f0;"><td style="padding:.4rem .5rem;font-weight:600;color:#003366;width:45%;">'+l+'</td><td style="padding:.4rem .5rem;color:#2d4a6b;">'+data[k]+'</td></tr>'; }
    html += '</table>';
    document.getElementById('exifContent').innerHTML = html;
    document.getElementById('exifModal').style.display = 'flex';
}
function closeExif() { document.getElementById('exifModal').style.display = 'none'; }

// Tagger
var taggerPhotoId = null, taggerPendingX = null, taggerPendingY = null;
function openTagger(photoId, photoUrl) {
    taggerPhotoId = photoId;
    document.getElementById('taggerImg').src = photoUrl;
    document.getElementById('taggerDots').innerHTML = '';
    document.getElementById('taggerForm').style.display = 'none';
    document.getElementById('taggerPendingDot').style.display = 'none';
    document.getElementById('taggerCallsign').value = '';
    document.getElementById('taggerLookupResult').textContent = '';
    document.getElementById('taggerModal').style.display = 'flex';
}
function closeTagger() { document.getElementById('taggerModal').style.display = 'none'; }
document.addEventListener('DOMContentLoaded', function() {
    var wrap = document.getElementById('taggerImgWrap');
    if (!wrap) return;
    wrap.addEventListener('click', function(e) {
        if (e.target.closest('#taggerDots')) return;
        var rect = this.getBoundingClientRect();
        taggerPendingX = ((e.clientX-rect.left)/rect.width*100).toFixed(2);
        taggerPendingY = ((e.clientY-rect.top)/rect.height*100).toFixed(2);
        var dot = document.getElementById('taggerPendingDot');
        dot.style.left=taggerPendingX+'%'; dot.style.top=taggerPendingY+'%'; dot.style.display='block';
        document.getElementById('taggerForm').style.display='block';
        document.getElementById('taggerCallsign').focus();
    });
    document.getElementById('taggerCallsign').addEventListener('keydown', function(e) {
        if(e.key==='Enter') lookupAndTag(); this.value=this.value.toUpperCase();
    });
});
function cancelTag() { document.getElementById('taggerForm').style.display='none'; document.getElementById('taggerPendingDot').style.display='none'; taggerPendingX=null; taggerPendingY=null; }
function lookupAndTag() {
    var callsign = document.getElementById('taggerCallsign').value.trim().toUpperCase();
    if (!callsign || !taggerPendingX) return;
    var result = document.getElementById('taggerLookupResult');
    result.style.color='#2d4a6b'; result.textContent='⏳ Checking '+callsign+'…';
    fetch('/members/photos/'+taggerPhotoId+'/tags', {
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},
        body:JSON.stringify({callsign:callsign,x_pct:parseFloat(taggerPendingX),y_pct:parseFloat(taggerPendingY)})
    }).then(r=>r.json().then(data=>({status:r.status,data}))).then(res=>{
        if(res.data.success){
            var dot=document.createElement('div');
            dot.style.cssText='position:absolute;width:24px;height:24px;border-radius:50%;background:rgba(200,16,46,.85);border:2px solid #fff;transform:translate(-50%,-50%);cursor:default;';
            dot.style.left=taggerPendingX+'%'; dot.style.top=taggerPendingY+'%'; dot.title=res.data.name||res.data.callsign;
            document.getElementById('taggerDots').appendChild(dot);
            document.getElementById('taggerPendingDot').style.display='none';
            document.getElementById('taggerForm').style.display='none';
            document.getElementById('taggerCallsign').value='';
            taggerPendingX=null; taggerPendingY=null;
            result.style.color='#059669'; result.textContent='✓ Tagged '+( res.data.name||res.data.callsign)+' — notified by email.';
        } else { result.style.color='#dc2626'; result.textContent='✗ '+(res.data.message||'Could not add tag.'); }
    }).catch(()=>{ result.style.color='#dc2626'; result.textContent='✗ Failed. Please try again.'; });
}
document.addEventListener('keydown', function(e) {
    if(e.key==='Escape'){closeExif();closeTagger();}
});
</script>

{{-- Edit Photo Modal --}}
<div id="editPhotoModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:9999;align-items:center;justify-content:center;padding:1rem;" onclick="if(event.target===this)closeEditModal()">
    <div style="background:#fff;border-radius:10px;max-width:540px;width:100%;max-height:90vh;overflow-y:auto;">
        <div style="background:#003366;padding:1rem 1.25rem;display:flex;align-items:center;justify-content:space-between;border-radius:10px 10px 0 0;">
            <div style="font-size:.95rem;font-weight:700;color:#fff;">✎ Edit Photo</div>
            <button onclick="closeEditModal()" style="background:none;border:none;color:rgba(255,255,255,.7);font-size:1.2rem;cursor:pointer;">✕</button>
        </div>
        <div style="padding:1.25rem;">
            <div style="text-align:center;background:#f0f0f0;border-radius:6px;overflow:hidden;margin-bottom:1rem;">
                <img id="editPreviewImg" src="" alt="" style="max-width:100%;max-height:280px;object-fit:contain;display:block;margin:auto;">
                <div style="display:flex;justify-content:center;gap:.5rem;padding:.6rem;background:rgba(0,0,0,.04);">
                    <button type="button" onclick="rotatePhoto(-90)" style="background:#e8eef5;color:#003366;border:none;padding:.4rem .9rem;border-radius:4px;font-size:.82rem;font-weight:bold;cursor:pointer;">↺ Rotate Left</button>
                    <button type="button" onclick="rotatePhoto(90)" style="background:#e8eef5;color:#003366;border:none;padding:.4rem .9rem;border-radius:4px;font-size:.82rem;font-weight:bold;cursor:pointer;">↻ Rotate Right</button>
                    <span id="rotateStatus" style="font-size:.75rem;color:#6b7f96;line-height:2.5;"></span>
                </div>
            </div>
            <form id="editPhotoForm" method="POST">
                @csrf @method('PATCH')
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.85rem;">
                    <div>
                        <label style="display:block;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#6b7f96;margin-bottom:.3rem;">Caption</label>
                        <input type="text" name="caption" id="editCaption" maxlength="500" style="width:100%;padding:.5rem .7rem;border:1px solid #dde2e8;border-radius:4px;font-size:.88rem;">
                    </div>
                    <div>
                        <label style="display:block;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#6b7f96;margin-bottom:.3rem;">Location</label>
                        <input type="text" name="location" id="editLocation" maxlength="200" style="width:100%;padding:.5rem .7rem;border:1px solid #dde2e8;border-radius:4px;font-size:.88rem;">
                    </div>
                </div>
                <div style="display:flex;gap:.5rem;">
                    <button type="submit" style="flex:1;background:#003366;color:#fff;border:none;padding:.6rem;border-radius:999px;font-weight:bold;cursor:pointer;">Save Changes</button>
                    <button type="button" onclick="closeEditModal()" style="background:#f3f4f6;color:#6b7280;border:none;padding:.6rem 1rem;border-radius:999px;font-weight:bold;cursor:pointer;">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
var editPhotoId = null;
var currentRotation = 0;

function openEditModal(photoId, caption, location) {
    editPhotoId = photoId;
    currentRotation = 0;
    document.getElementById('editPhotoForm').action = '/members/photos/' + photoId;
    document.getElementById('editCaption').value = caption;
    document.getElementById('editLocation').value = location;
    document.getElementById('rotateStatus').textContent = '';
    // Load photo
    fetch('/members/photos/' + photoId + '/url', {
        headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'}
    }).then(function(r){ return r.json(); }).then(function(d){
        if (d.url) document.getElementById('editPreviewImg').src = d.url;
    }).catch(function(){});
    document.getElementById('editPhotoModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editPhotoModal').style.display = 'none';
    editPhotoId = null;
}

function rotatePhoto(degrees) {
    if (!editPhotoId) return;
    document.getElementById('rotateStatus').textContent = '⏳ Saving…';
    fetch('/members/photos/' + editPhotoId + '/rotate', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify({degrees: degrees})
    }).then(function(r){ return r.json(); }).then(function(d){
        if (d.success) {
            document.getElementById('rotateStatus').textContent = '✓ Rotated';
            // Reload image with cache bust
            var img = document.getElementById('editPreviewImg');
            var src = img.src.split('?')[0];
            img.src = src + '?t=' + Date.now();
            setTimeout(function(){ document.getElementById('rotateStatus').textContent = ''; }, 2000);
        } else {
            document.getElementById('rotateStatus').textContent = '✗ Failed';
        }
    }).catch(function(){ document.getElementById('rotateStatus').textContent = '✗ Error'; });
}
</script>
@endsection
