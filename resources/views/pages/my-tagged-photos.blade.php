@extends('layouts.app')
@section('title', 'My Tagged Photos')
@section('content')
<style>
:root{--navy:#003366;--red:#C8102E;--white:#fff;--grey:#f2f5f9;--grey-mid:#dde2e8;--text:#001f40;--muted:#6b7f96;}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
.wrap{max-width:1000px;margin:0 auto;padding:2rem 1rem 4rem;}
.page-head{margin-bottom:2rem;}
.eyebrow{font-size:.75rem;font-weight:bold;text-transform:uppercase;letter-spacing:.15em;color:var(--red);margin-bottom:.4rem;}
.page-title{font-size:1.8rem;font-weight:bold;color:var(--navy);}
.photo-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:1.2rem;}
.photo-card{background:#fff;border:1px solid var(--grey-mid);border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,51,102,.06);}
.photo-img{width:100%;aspect-ratio:4/3;object-fit:cover;display:block;}
.photo-body{padding:.9rem 1rem;}
.photo-actions{display:flex;gap:.5rem;margin-top:.75rem;}
.btn{padding:.45rem 1rem;border-radius:999px;font-size:.8rem;font-weight:bold;border:none;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:.3rem;}
.btn-navy{background:#e8eef5;color:var(--navy);}
.btn-red{background:#fee2e2;color:#991b1b;}
.alert{padding:.65rem 1rem;border-radius:6px;margin-bottom:1rem;font-size:.88rem;font-weight:bold;}
.alert-success{background:#d1fae5;border-left:3px solid #059669;color:#065f46;}
</style>
<div class="wrap">
    <div class="page-head">
        <div class="eyebrow">Members Area</div>
        <h1 class="page-title">🏷 Photos I'm Tagged In</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success">✓ {{ session('success') }}</div>
    @endif

    @if($photos->isEmpty())
        <div style="text-align:center;padding:3rem;color:var(--muted);">
            <div style="font-size:3rem;margin-bottom:1rem;opacity:.3;">📷</div>
            You haven't been tagged in any photos yet.
        </div>
    @else
        <div class="photo-grid">
            @foreach($photos as $photo)
            @php $myTag = $tags->where('photo_id', $photo->id)->first(); @endphp
            <div class="photo-card">
                <img src="{{ $photo->thumbUrl() }}" alt="" class="photo-img">
                <div class="photo-body">
                    <div style="font-size:.88rem;font-weight:600;color:var(--text);margin-bottom:.3rem;">{{ $photo->caption ?: '(No caption)' }}</div>
                    <div style="font-size:.75rem;color:var(--muted);margin-bottom:.3rem;">
                        By {{ $photo->user?->name }} · {{ $photo->created_at->format('d M Y') }}
                        @if($photo->location) · 📍 {{ $photo->location }}@endif
                    </div>
                    <div style="font-size:.75rem;color:#059669;font-weight:600;">🏷 You are tagged as {{ strtoupper($myTag?->callsign ?? '') }}</div>
                    <div class="photo-actions">
                        <a href="{{ route('gallery') }}" class="btn btn-navy">👁 View in Gallery</a>
                        @if($myTag)
                        <form method="POST" action="{{ route('members.photos.tags.remove-self', $myTag) }}" onsubmit="return confirm('Remove your tag from this photo?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-red">✕ Remove Tag</button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
