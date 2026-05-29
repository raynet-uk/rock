@extends('layouts.app')
@section('title', 'RAYNET News')
@section('content')
<style>
.pub-hero{background:var(--navy);color:#fff;padding:3rem 0 2.5rem;}
.pub-hero-inner{max-width:900px;margin:0 auto;padding:0 1.5rem;}
.pub-eyebrow{font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.18em;color:rgba(255,255,255,.5);margin-bottom:.5rem;display:flex;align-items:center;gap:.5rem;}
.pub-eyebrow::before{content:'';width:16px;height:2px;background:var(--red);}
.pub-title{font-size:2rem;font-weight:bold;margin-bottom:.5rem;}
.pub-sub{font-size:1rem;color:rgba(255,255,255,.6);}
.pub-wrap{max-width:900px;margin:0 auto;padding:2rem 1.5rem 4rem;}
.current-card{background:#fff;border:1px solid var(--grey-mid);border-top:4px solid var(--red);display:grid;grid-template-columns:200px 1fr;gap:0;overflow:hidden;margin-bottom:2.5rem;box-shadow:0 4px 16px rgba(0,51,102,.08);}
@media(max-width:600px){.current-card{grid-template-columns:1fr;}}
.current-cover{background:var(--navy-faint);display:flex;align-items:center;justify-content:center;min-height:240px;padding:1.5rem;}
.current-cover img{max-width:100%;max-height:220px;object-fit:contain;box-shadow:0 4px 12px rgba(0,0,0,.15);}
.current-cover-placeholder{text-align:center;color:var(--text-muted);}
.current-cover-placeholder .icon{font-size:3rem;margin-bottom:.5rem;}
.current-body{padding:2rem;}
.current-badge{display:inline-block;background:var(--red);color:#fff;font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;padding:3px 10px;margin-bottom:.75rem;}
.current-edition{font-size:.85rem;color:var(--text-muted);margin-bottom:.35rem;}
.current-title{font-size:1.4rem;font-weight:bold;color:var(--navy);margin-bottom:.75rem;}
.current-desc{font-size:.9rem;color:var(--text-mid);line-height:1.6;margin-bottom:1.25rem;}
.btn-download{display:inline-flex;align-items:center;gap:.5rem;padding:.65rem 1.5rem;background:var(--red);color:#fff;font-size:.9rem;font-weight:bold;text-decoration:none;transition:background .15s;}
.btn-download:hover{background:#a00d25;}
.section-title{font-size:13px;font-weight:bold;text-transform:uppercase;letter-spacing:.12em;color:var(--navy);padding-bottom:.5rem;border-bottom:2px solid var(--navy);margin-bottom:1.25rem;display:flex;align-items:center;gap:.5rem;}
.section-title::before{content:'';width:3px;height:16px;background:var(--red);}
.archive-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem;}
.archive-card{background:#fff;border:1px solid var(--grey-mid);padding:1rem;transition:box-shadow .15s;}
.archive-card:hover{box-shadow:0 2px 8px rgba(0,51,102,.1);}
.archive-edition{font-size:11px;color:var(--text-muted);margin-bottom:.25rem;}
.archive-title{font-size:.9rem;font-weight:bold;color:var(--navy);margin-bottom:.5rem;}
.archive-date{font-size:11px;color:var(--text-muted);margin-bottom:.75rem;}
.archive-link{font-size:12px;font-weight:bold;color:var(--red);text-decoration:none;}
.archive-link:hover{text-decoration:underline;}
.empty-state{text-align:center;padding:3rem;color:var(--text-muted);}
</style>

<div class="pub-hero">
    <div class="pub-hero-inner">
        <div class="pub-eyebrow">Members · Publications</div>
        <div class="pub-title">📰 RAYNET News</div>
        <div class="pub-sub">The official magazine of RAYNET-UK — news, features and updates from across the network.</div>
    </div>
</div>

<div class="pub-wrap">
    @if($current)
    <div class="current-card">
        <div class="current-cover">
            @if($current->cover_url)
                <img src="{{ $current->cover_url }}" alt="{{ $current->title }}">
            @else
                <div class="current-cover-placeholder">
                    <div class="icon">📰</div>
                    <div style="font-size:.8rem;color:var(--text-muted);">RAYNET News</div>
                </div>
            @endif
        </div>
        <div class="current-body">
            <div class="current-badge">Current Edition</div>
            @if($current->edition)<div class="current-edition">{{ $current->edition }}</div>@endif
            <div class="current-title">{{ $current->title }}</div>
            @if($current->description)<div class="current-desc">{{ $current->description }}</div>@endif
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:1rem;">Published {{ $current->published_date->format('j F Y') }}</div>
            @if($current->file_url)
            <a href="{{ $current->file_url }}" target="_blank" class="btn-download">⬇ Download PDF</a>
            @endif
        </div>
    </div>
    @else
    <div class="empty-state">
        <div style="font-size:3rem;margin-bottom:.5rem;">📰</div>
        <div style="font-weight:bold;color:var(--navy);">No current edition</div>
        <div style="font-size:.875rem;margin-top:.25rem;">Check back soon.</div>
    </div>
    @endif

    @if($archive->count())
    <div class="section-title">Archive</div>
    <div class="archive-grid">
        @foreach($archive as $pub)
        <div class="archive-card">
            <div class="archive-edition">{{ $pub->edition }}</div>
            <div class="archive-title">{{ $pub->title }}</div>
            <div class="archive-date">{{ $pub->published_date->format('j M Y') }}</div>
            @if($pub->file_url)
            <a href="{{ $pub->file_url }}" target="_blank" class="archive-link">⬇ Download PDF</a>
            @endif
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
