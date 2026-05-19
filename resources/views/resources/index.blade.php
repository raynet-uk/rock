@extends('layouts.app')
@section('title', 'RAYNET Drive')
@section('content')
<style>
/* ── DRIVE SHELL ── */
.drive-shell{display:flex;height:calc(100vh - 60px);overflow:hidden;background:#f8f9fa}
.drive-sidebar{width:220px;flex-shrink:0;background:white;border-right:1px solid #e0e0e0;overflow-y:auto;padding:.5rem 0}
.drive-main{flex:1;display:flex;flex-direction:column;overflow:hidden}
.drive-toolbar{display:flex;align-items:center;gap:.5rem;padding:.6rem 1rem;background:white;border-bottom:1px solid #e0e0e0;flex-shrink:0;flex-wrap:wrap}
.drive-content{flex:1;overflow-y:auto;padding:1rem}

/* ── SIDEBAR ── */
.sidebar-upload-btn{display:flex;align-items:center;gap:.5rem;margin:.5rem .75rem 1rem;padding:.6rem 1.2rem;background:white;border:1px solid #dadce0;border-radius:24px;font-size:.88rem;font-weight:500;color:#3c4043;cursor:pointer;box-shadow:0 1px 3px rgba(0,0,0,.1);font-family:var(--font);transition:box-shadow .2s}
.sidebar-upload-btn:hover{box-shadow:0 2px 6px rgba(0,0,0,.15)}
.sidebar-section{padding:.25rem 0}
.sidebar-section-label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--text-muted);padding:.4rem .75rem .2rem}
.sidebar-item{display:flex;align-items:center;gap:.6rem;padding:.45rem .75rem;font-size:.88rem;color:#3c4043;cursor:pointer;border-radius:0 24px 24px 0;margin-right:.5rem;transition:background .15s;text-decoration:none;border:none;background:none;width:100%;text-align:left;font-family:var(--font)}
.sidebar-item:hover{background:#f1f3f4}
.sidebar-item.active{background:#e8f0fe;color:#1967d2;font-weight:600}
.sidebar-item .si-icon{font-size:1rem;width:20px;text-align:center;flex-shrink:0}
.sidebar-item .si-count{margin-left:auto;font-size:11px;background:#e0e0e0;border-radius:999px;padding:1px 6px;color:#5f6368}
.sidebar-divider{height:1px;background:#e0e0e0;margin:.4rem .75rem}
.drive-chip{display:inline-flex;align-items:center;gap:.3rem;padding:3px 10px;border-radius:4px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em}
.drive-chip.public{background:#e8f5e9;color:#2e7d32}
.drive-chip.members{background:#e3f2fd;color:#1565c0}
.drive-chip.committee{background:#fff3e0;color:#e65100}
.drive-chip.admin{background:#fce4ec;color:#880e4f}

/* ── TOOLBAR ── */
.drive-search{flex:1;min-width:200px;max-width:500px;display:flex;align-items:center;background:#f1f3f4;border-radius:24px;padding:.4rem .85rem;gap:.4rem;border:2px solid transparent;transition:all .2s}
.drive-search:focus-within{background:white;border-color:#1a73e8;box-shadow:0 2px 8px rgba(0,0,0,.1)}
.drive-search input{flex:1;border:none;background:none;outline:none;font-size:.9rem;font-family:var(--font);color:#3c4043}
.view-toggle{display:flex;border:1px solid #dadce0;border-radius:4px;overflow:hidden}
.view-btn{padding:.35rem .6rem;background:white;border:none;cursor:pointer;font-size:.9rem;color:#5f6368;transition:background .15s}
.view-btn.active,.view-btn:hover{background:#e8f0fe;color:#1967d2}
.sort-select{border:1px solid #dadce0;padding:.35rem .6rem;font-size:.82rem;font-family:var(--font);color:#3c4043;background:white;border-radius:4px;cursor:pointer}

/* ── PENDING BANNER ── */
.pending-banner{background:#fff8e1;border:1px solid #ffe082;border-radius:8px;padding:.75rem 1rem;margin-bottom:1rem;display:flex;align-items:center;gap:.75rem}
.pending-banner-icon{font-size:1.2rem}
.pending-banner-text{flex:1;font-size:.88rem;color:#f57f17;font-weight:500}
.pending-item{display:flex;align-items:center;justify-content:space-between;padding:.5rem .75rem;background:white;border:1px solid #ffe082;border-radius:6px;margin-top:.4rem;gap:.75rem;flex-wrap:wrap}
.pending-item-info{flex:1;min-width:0}
.pending-item-name{font-size:.88rem;font-weight:600;color:#3c4043}
.pending-item-meta{font-size:.78rem;color:#80868b;display:flex;gap:.4rem;flex-wrap:wrap;align-items:center;margin-top:2px}
.pending-approve-form{display:flex;gap:.3rem;align-items:center;flex-wrap:wrap}
.pending-approve-form select{border:1px solid #dadce0;padding:.25rem .5rem;font-size:.78rem;font-family:var(--font);border-radius:4px}

/* ── SECTION HEADERS ── */
.drive-section{margin-bottom:1.5rem}
.drive-section-header{display:flex;align-items:center;gap:.5rem;margin-bottom:.75rem;padding-bottom:.4rem;border-bottom:1px solid #e0e0e0}
.drive-section-title{font-size:.95rem;font-weight:600;color:#3c4043}
.drive-section-count{font-size:.78rem;color:#80868b;margin-left:auto}
.drive-locked-banner{display:flex;align-items:center;gap:.75rem;padding:.75rem 1rem;background:#f8f9fa;border:1px dashed #dadce0;border-radius:8px;color:#80868b;font-size:.88rem}

/* ── CATEGORY ── */
.cat-header{display:flex;align-items:center;gap:.5rem;padding:.3rem .5rem;margin-bottom:2px;cursor:pointer}
.cat-title{font-size:.82rem;font-weight:600;color:#5f6368;text-transform:uppercase;letter-spacing:.06em}
.cat-count{font-size:.75rem;color:#9aa0a6;margin-left:auto}
.follow-btn{font-size:10px;padding:2px 8px;border:1px solid #dadce0;border-radius:999px;background:white;cursor:pointer;font-family:var(--font);color:#5f6368;transition:all .15s}
.follow-btn.following{background:#e8f0fe;color:#1967d2;border-color:#1967d2}

/* ── GRID VIEW ── */
.file-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:.6rem;margin-bottom:.75rem}
.file-card{background:white;border:1px solid #e0e0e0;border-radius:8px;padding:.75rem;cursor:pointer;transition:box-shadow .15s,border-color .15s;position:relative;display:flex;flex-direction:column;gap:.4rem}
.file-card:hover{box-shadow:0 2px 8px rgba(0,0,0,.12);border-color:#1a73e8}
.file-card.pinned-card{border-color:#fbbc04;background:#fffde7}
.file-card-icon{font-size:2.2rem;text-align:center}
.file-card-name{font-size:.8rem;font-weight:600;color:#3c4043;line-height:1.3;word-break:break-word;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.file-card-meta{font-size:.72rem;color:#9aa0a6}
.file-card-badges{display:flex;gap:3px;flex-wrap:wrap}
.file-card-actions{display:flex;gap:3px;margin-top:auto;opacity:0;transition:opacity .15s}
.file-card:hover .file-card-actions{opacity:1}
.fc-btn{padding:3px 6px;font-size:.72rem;border:none;border-radius:4px;cursor:pointer;font-family:var(--font);font-weight:600}
.fc-btn.dl{background:#1a73e8;color:white}
.fc-btn.prev{background:#f1f3f4;color:#3c4043}
.fc-btn.bm{background:#f1f3f4;color:#3c4043}
.fc-btn.del{background:#fce8e6;color:#c5221f}
.fc-btn.ed{background:#f1f3f4;color:#3c4043}

/* ── LIST VIEW ── */
.file-list{border:1px solid #e0e0e0;border-radius:8px;overflow:hidden;background:white;margin-bottom:.75rem}
.file-list-header{display:grid;grid-template-columns:2fr 80px 100px 80px 160px;gap:.5rem;padding:.5rem .75rem;background:#f8f9fa;border-bottom:1px solid #e0e0e0;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#80868b}
.file-list-row{display:grid;grid-template-columns:2fr 80px 100px 80px 160px;gap:.5rem;padding:.55rem .75rem;border-bottom:1px solid #f1f3f4;align-items:center;transition:background .1s;cursor:pointer}
.file-list-row:last-child{border-bottom:none}
.file-list-row:hover{background:#f8f9fa}
.file-list-row.pinned-row{background:#fffde7}
.fln{display:flex;align-items:center;gap:.5rem;min-width:0}
.fln-icon{font-size:1.1rem;flex-shrink:0}
.fln-info{min-width:0}
.fln-title{font-size:.88rem;font-weight:500;color:#3c4043;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.fln-desc{font-size:.75rem;color:#9aa0a6;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.fln-badges{display:flex;gap:3px;margin-top:2px}
.fl-size{font-size:.8rem;color:#80868b;font-family:monospace}
.fl-dl{font-size:.8rem;color:#80868b;text-align:center}
.fl-date{font-size:.8rem;color:#80868b}
.fl-actions{display:flex;gap:3px;justify-content:flex-end;opacity:0;transition:opacity .15s}
.file-list-row:hover .fl-actions{opacity:1}

/* ── BADGES ── */
.fbadge{font-size:10px;font-weight:700;padding:1px 5px;border-radius:3px}
.fbadge.new{background:#e6f4ea;color:#137333}
.fbadge.pin{background:#fef7e0;color:#b06000}
.fbadge.src{background:#fce8e6;color:#c5221f}
.fbadge.ver{background:#e8f0fe;color:#1967d2}

/* ── RECENT ── */
.recent-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:.5rem;margin-bottom:1.5rem}
.recent-card{display:flex;align-items:center;gap:.5rem;padding:.5rem .75rem;background:white;border:1px solid #e0e0e0;border-radius:8px;cursor:pointer;transition:box-shadow .15s;text-decoration:none}
.recent-card:hover{box-shadow:0 2px 6px rgba(0,0,0,.1)}
.recent-icon{font-size:1.2rem;flex-shrink:0}
.recent-name{font-size:.78rem;font-weight:500;color:#3c4043;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}

/* ── STARRED ── */
.starred-empty{text-align:center;padding:2rem;color:#80868b;font-size:.88rem}

/* ── MODALS ── */
.modal-bg{display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:1000;align-items:center;justify-content:center}
.modal-bg.open{display:flex}
.modal{background:white;width:100%;max-width:540px;max-height:90vh;overflow-y:auto;border-radius:8px;box-shadow:0 8px 32px rgba(0,0,0,.2)}
.modal-header{display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;border-bottom:1px solid #e0e0e0}
.modal-title{font-size:1rem;font-weight:600;color:#3c4043}
.modal-close{background:none;border:none;font-size:1.2rem;cursor:pointer;color:#80868b;padding:.2rem;border-radius:4px}
.modal-close:hover{background:#f1f3f4}
.modal-body{padding:1.25rem;display:flex;flex-direction:column;gap:.75rem}
.modal-footer{padding:.75rem 1.25rem;border-top:1px solid #e0e0e0;display:flex;justify-content:flex-end;gap:.5rem}
.mfield{display:flex;flex-direction:column;gap:4px}
.mfield label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#80868b}
.mfield input,.mfield select,.mfield textarea{border:1px solid #dadce0;padding:.5rem .75rem;font-size:.9rem;font-family:var(--font);color:#3c4043;border-radius:4px;outline:none;transition:border-color .15s}
.mfield input:focus,.mfield select:focus{border-color:#1a73e8}
.mgrid{display:grid;grid-template-columns:1fr 1fr;gap:.75rem}
.mgrid .full{grid-column:1/-1}
.drop-zone{border:2px dashed #dadce0;border-radius:8px;padding:1.5rem;text-align:center;cursor:pointer;transition:all .2s;background:#f8f9fa}
.drop-zone:hover,.drop-zone.drag{border-color:#1a73e8;background:#e8f0fe}
.drop-name{font-size:.85rem;font-weight:600;color:#1967d2;margin-top:.4rem}

/* ── BUTTONS ── */
.btn-drive{padding:.5rem 1.1rem;border-radius:4px;font-size:.88rem;font-weight:600;border:none;cursor:pointer;font-family:var(--font);display:inline-flex;align-items:center;gap:.35rem;transition:all .15s}
.btn-drive.primary{background:#1a73e8;color:white}.btn-drive.primary:hover{background:#1557b0}
.btn-drive.secondary{background:white;color:#1a73e8;border:1px solid #dadce0}.btn-drive.secondary:hover{background:#e8f0fe}
.btn-drive.danger{background:#c5221f;color:white}.btn-drive.danger:hover{background:#a50e0e}
.btn-drive.sm{padding:.3rem .7rem;font-size:.78rem}

/* ── UPLOAD PROGRESS ── */
.upload-progress{display:none;position:fixed;bottom:1.5rem;right:1.5rem;background:white;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,.2);padding:1rem 1.25rem;min-width:280px;z-index:2000}
.upload-progress.show{display:block}
.up-title{font-size:.88rem;font-weight:600;color:#3c4043;margin-bottom:.5rem}
.up-bar-bg{background:#e0e0e0;border-radius:999px;height:6px}
.up-bar{background:#1a73e8;height:6px;border-radius:999px;width:0%;transition:width .3s}
.up-status{font-size:.78rem;color:#80868b;margin-top:.25rem}

/* ── PERSONAL ADDRESSES ── */
.addr-box{background:#e8f0fe;border-radius:8px;padding:.75rem 1rem;margin-bottom:1rem;font-size:.82rem}
.addr-box-title{font-weight:700;color:#1565c0;margin-bottom:.4rem;font-size:.85rem}
.addr-row{display:flex;align-items:center;gap:.4rem;margin:.2rem 0;flex-wrap:wrap}
.addr-code{background:white;border:1px solid #c5d5f5;border-radius:4px;padding:2px 8px;font-family:monospace;color:#1967d2;font-size:.8rem}
.addr-copy{font-size:10px;padding:2px 6px;border:1px solid #dadce0;border-radius:4px;background:white;cursor:pointer;font-family:var(--font);color:#5f6368}

/* ── RESPONSIVE ── */
@media(max-width:768px){
    .drive-sidebar{display:none}
    .file-list-header,.file-list-row{grid-template-columns:1fr 80px 120px}
    .file-list-header .hide-sm,.file-list-row .hide-sm{display:none}
    .file-grid{grid-template-columns:repeat(auto-fill,minmax(130px,1fr))}
}
</style>

<div class="drive-shell">

{{-- ── SIDEBAR ── --}}
<div class="drive-sidebar">
    @auth
    <button onclick="openUpload()" class="sidebar-upload-btn">
        <span style="font-size:1.1rem">&#43;</span> New Upload
    </button>
    @endauth

    <div class="sidebar-section">
        <button onclick="setView('recent')" class="sidebar-item {{ ($driveFocus??'public')==='recent'?'active':'' }}">
            <span class="si-icon">&#128336;</span> Recent
        </button>
        @auth
        <button onclick="setView('starred')" class="sidebar-item {{ ($driveFocus??'public')==='starred'?'active':'' }}">
            <span class="si-icon">&#11088;</span> Starred
            @if(isset($bookmarked) && $bookmarked->count())
            <span class="si-count">{{ $bookmarked->count() }}</span>
            @endif
        </button>
        @endauth
    </div>

    <div class="sidebar-divider"></div>
    <div class="sidebar-section">
        <div class="sidebar-section-label">Drives</div>

        @foreach($allowedVisibilities as $vis)
        @php
            $icons = ['public'=>'&#127760;','members'=>'&#128274;','committee'=>'&#128203;','admin'=>'&#9881;'];
            $names = ['public'=>'Public Library','members'=>'Members Drive','committee'=>'Committee Drive','admin'=>'Admin Drive'];
            $count = isset($drives[$vis]) ? $drives[$vis]->flatten()->count() : 0;
        @endphp
        <button onclick="setView('{{ $vis }}')" class="sidebar-item {{ ($driveFocus??'public')===$vis?'active':'' }}">
            <span class="si-icon">{!! $icons[$vis] !!}</span>
            {{ $names[$vis] }}
            @if($count)<span class="si-count">{{ $count }}</span>@endif
        </button>
        @endforeach

        @guest
        <div style="padding:.4rem .75rem;font-size:.78rem;color:#9aa0a6;font-style:italic">
            &#128274; Log in to see more drives
        </div>
        @endguest
    </div>

    @if(isset($pending) && $pending->count())
    <div class="sidebar-divider"></div>
    <div class="sidebar-section">
        <button onclick="setView('pending')" class="sidebar-item {{ ($driveFocus??'')==='pending'?'active':'' }}" style="color:#f57f17">
            <span class="si-icon">&#9203;</span> Pending
            <span class="si-count" style="background:#fff8e1;color:#f57f17">{{ $pending->count() }}</span>
        </button>
    </div>
    @endif
</div>

{{-- ── MAIN AREA ── --}}
<div class="drive-main">

    {{-- Toolbar --}}
    <div class="drive-toolbar">
        <div class="drive-search">
            <span style="color:#9aa0a6">&#128269;</span>
            <input type="text" id="driveSearch" placeholder="Search RAYNET Drive..." oninput="filterFiles()">
        </div>
        <div class="view-toggle">
            <button class="view-btn active" id="gridBtn" onclick="switchLayout('grid')" title="Grid view">&#9783;</button>
            <button class="view-btn" id="listBtn" onclick="switchLayout('list')" title="List view">&#9776;</button>
        </div>
        <select class="sort-select" onchange="window.location='?drive={{ $driveFocus }}&sort='+this.value">
            <option value="date" {{ $sort==='date'?'selected':'' }}>Date</option>
            <option value="name" {{ $sort==='name'?'selected':'' }}>Name</option>
            <option value="size" {{ $sort==='size'?'selected':'' }}>Size</option>
            <option value="downloads" {{ $sort==='downloads'?'selected':'' }}>Downloads</option>
        </select>
        @if($allTags->count())
        <div style="display:flex;gap:.3rem;flex-wrap:wrap">
            @foreach($allTags as $t)
            <button onclick="window.location='?drive={{ $driveFocus }}&tag={{ urlencode($t) }}'" class="btn-drive sm secondary {{ $tag===$t?'primary':'' }}">{{ $t }}</button>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Content --}}
    <div class="drive-content" id="driveContent">

        {{-- Alerts --}}
        @if(session('success'))<div style="background:#e6f4ea;border:1px solid #a8d5b1;color:#137333;padding:.6rem 1rem;border-radius:6px;margin-bottom:.75rem;font-size:.88rem">&#9989; {{ session('success') }}</div>@endif
        @if(session('error'))<div style="background:#fce8e6;border:1px solid #f5b8b0;color:#c5221f;padding:.6rem 1rem;border-radius:6px;margin-bottom:.75rem;font-size:.88rem">&#10060; {{ session('error') }}</div>@endif

        {{-- Personal email addresses --}}
        @auth
        @php $callsign = auth()->user()->callsign ?? null; @endphp
        @if($callsign)
        <div class="addr-box">
            <div class="addr-box-title">&#128236; Your personal upload addresses</div>
            <div class="addr-row">
                <span class="drive-chip public">Public</span>
                <code class="addr-code">docs+{{ strtoupper($callsign) }}@raynet-liverpool.net</code>
                <button class="addr-copy" onclick="navigator.clipboard.writeText('docs+{{ strtoupper($callsign) }}@raynet-liverpool.net');this.textContent='Copied!';setTimeout(()=>this.textContent='Copy',1500)">Copy</button>
            </div>
            @if(in_array('members', $allowedVisibilities))
            <div class="addr-row">
                <span class="drive-chip members">Members</span>
                <code class="addr-code">members-docs+{{ strtoupper($callsign) }}@raynet-liverpool.net</code>
                <button class="addr-copy" onclick="navigator.clipboard.writeText('members-docs+{{ strtoupper($callsign) }}@raynet-liverpool.net');this.textContent='Copied!';setTimeout(()=>this.textContent='Copy',1500)">Copy</button>
            </div>
            @endif
            @if(in_array('committee', $allowedVisibilities))
            <div class="addr-row">
                <span class="drive-chip committee">Committee</span>
                <code class="addr-code">committee-docs+{{ strtoupper($callsign) }}@raynet-liverpool.net</code>
                <button class="addr-copy" onclick="navigator.clipboard.writeText('committee-docs+{{ strtoupper($callsign) }}@raynet-liverpool.net');this.textContent='Copied!';setTimeout(()=>this.textContent='Copy',1500)">Copy</button>
            </div>
            @endif
            @if(in_array('admin', $allowedVisibilities))
            <div class="addr-row">
                <span class="drive-chip admin">Admin</span>
                <code class="addr-code">admin-docs+{{ strtoupper($callsign) }}@raynet-liverpool.net</code>
                <button class="addr-copy" onclick="navigator.clipboard.writeText('admin-docs+{{ strtoupper($callsign) }}@raynet-liverpool.net');this.textContent='Copied!';setTimeout(()=>this.textContent='Copy',1500)">Copy</button>
            </div>
            @endif
        </div>
        @endif
        @endauth

        {{-- PENDING VIEW --}}
        <div id="view-pending" class="drive-view" style="display:none">
            @if(isset($pending) && $pending->count())
            <div class="pending-banner">
                <div class="pending-banner-icon">&#9203;</div>
                <div style="flex:1">
                    <div class="pending-banner-text">{{ $pending->count() }} file{{ $pending->count()>1?'s':'' }} awaiting approval</div>
                    @foreach($pending as $p)
                    <div class="pending-item">
                        <div class="pending-item-info">
                            <div class="pending-item-name">{{ $p->title ?: $p->original_name }}</div>
                            <div class="pending-item-meta">
                                <span class="drive-chip {{ $p->visibility }}">{{ $p->visibility }}</span>
                                <span>{{ $p->file_size_formatted }}</span>
                                <span>from {{ $p->uploaded_by }}</span>
                                <span>{{ $p->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        <div class="pending-approve-form">
                            <form action="{{ route('resources.approve', $p) }}" method="POST" style="display:flex;gap:.3rem;align-items:center">
                                @csrf @method('PATCH')
                                <select name="category" style="border:1px solid #dadce0;padding:.25rem .5rem;font-size:.78rem;font-family:var(--font);border-radius:4px">
                                    <option value="">Category...</option>
                                    <option>Training</option><option>Forms</option><option>Callout</option>
                                    <option>Equipment</option><option>Procedures</option><option>General</option>
                                </select>
                                <select name="visibility" style="border:1px solid #dadce0;padding:.25rem .5rem;font-size:.78rem;font-family:var(--font);border-radius:4px">
                                    <option value="{{ $p->visibility }}">Keep: {{ ucfirst($p->visibility) }}</option>
                                    <option value="public">Public</option>
                                    <option value="members">Members</option>
                                    <option value="committee">Committee</option>
                                    <option value="admin">Admin</option>
                                </select>
                                <button class="btn-drive sm primary">&#10003; Approve</button>
                            </form>
                            <form action="{{ route('resources.destroy', $p) }}" method="POST" onsubmit="return confirm('Delete?')">
                                @csrf @method('DELETE')
                                <button class="btn-drive sm danger">&#10005;</button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @else
            <div style="text-align:center;padding:3rem;color:#80868b">No files pending approval.</div>
            @endif
        </div>

        {{-- RECENT VIEW --}}
        <div id="view-recent" class="drive-view" style="display:none">
            <div class="drive-section">
                <div class="drive-section-header">
                    <span style="font-size:1rem">&#128336;</span>
                    <span class="drive-section-title">Recently Added</span>
                </div>
                @if($recent->count())
                <div class="recent-grid">
                    @foreach($recent as $r)
                    <a href="{{ route('resources.preview', $r) }}" class="recent-card" title="{{ $r->title }}">
                        <span class="recent-icon">@php echo rIcon($r->mime_type); @endphp</span>
                        <div>
                            <div class="recent-name">{{ $r->title }}</div>
                            <div style="font-size:.72rem;color:#9aa0a6">{{ $r->created_at->diffForHumans() }}</div>
                        </div>
                    </a>
                    @endforeach
                </div>
                @else
                <div style="color:#80868b;font-size:.88rem;padding:1rem">No recent files.</div>
                @endif
            </div>
        </div>

        {{-- STARRED VIEW --}}
        @auth
        <div id="view-starred" class="drive-view" style="display:none">
            <div class="drive-section">
                <div class="drive-section-header">
                    <span style="font-size:1rem">&#11088;</span>
                    <span class="drive-section-title">Starred Files</span>
                </div>
                @if(isset($bookmarked) && $bookmarked->count())
                <div id="starred-grid" class="file-grid">
                    @foreach($bookmarked as $resource)
                    @php $bookmarkedIds = $bookmarked->pluck('id')->toArray(); @endphp
                    <div class="file-card file-item"
                         data-name="{{ strtolower($resource->title.' '.$resource->original_name) }}"
                         data-cat="{{ $resource->category ?: 'General' }}">
                        <div class="file-card-icon">@php echo rIcon($resource->mime_type); @endphp</div>
                        <div class="file-card-name">{{ $resource->title }}</div>
                        <div class="file-card-meta">{{ $resource->file_size_formatted }} &middot; {{ $resource->created_at->format('d M Y') }}</div>
                        <div class="file-card-actions">
                            <a href="{{ route('resources.preview', $resource) }}" class="fc-btn prev">&#128065;</a>
                            <a href="{{ route('resources.download', $resource) }}" class="fc-btn dl">&#11015;</a>
                            <form action="{{ route('resources.bookmark', $resource) }}" method="POST" style="display:inline">@csrf
                                <button class="fc-btn del" title="Remove star">&#11088;</button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="starred-empty">&#11088; Star files to find them quickly here.</div>
                @endif
            </div>
        </div>
        @endauth

        {{-- DRIVE VIEWS --}}
        @foreach($allowedVisibilities as $vis)
        @php
            $driveNames = ['public'=>'Public Library','members'=>'Members Drive','committee'=>'Committee Drive','admin'=>'Admin Drive'];
            $driveIcons = ['public'=>'&#127760;','members'=>'&#128274;','committee'=>'&#128203;','admin'=>'&#9881;'];
            $driveFiles = $drives[$vis] ?? collect();
        @endphp
        <div id="view-{{ $vis }}" class="drive-view" style="display:none">
            <div class="drive-section">
                <div class="drive-section-header">
                    <span style="font-size:1rem">{!! $driveIcons[$vis] !!}</span>
                    <span class="drive-section-title">{{ $driveNames[$vis] }}</span>
                    <span class="drive-section-count">{{ $driveFiles->flatten()->count() }} files</span>
                </div>

                @if($driveFiles->isEmpty())
                    <div style="text-align:center;padding:3rem;color:#80868b">
                        <div style="font-size:3rem;opacity:.3;margin-bottom:.5rem">{!! $driveIcons[$vis] !!}</div>
                        <div>No files in this drive yet.</div>
                        @auth @if(auth()->user()->isCommittee())
                        <button onclick="openUpload('{{ $vis }}')" class="btn-drive primary" style="margin-top:1rem">&#11014; Upload First File</button>
                        @endif @endauth
                    </div>
                @else
                    @foreach($driveFiles as $category => $files)
                    @php $cat = $category ?: 'General'; $isFollowing = in_array($cat, $followers ?? []); @endphp
                    <div class="cat-section" data-cat="{{ $cat }}">
                        <div class="cat-header">
                            <span style="font-size:.9rem">&#128193;</span>
                            <span class="cat-title">{{ $cat }}</span>
                            <span class="cat-count">{{ $files->count() }} file{{ $files->count()>1?'s':'' }}</span>
                            @auth
                            <form action="{{ route('resources.follow-category') }}" method="POST" style="display:inline">
                                @csrf<input type="hidden" name="category" value="{{ $cat }}">
                                <button class="follow-btn {{ $isFollowing?'following':'' }}">{!! $isFollowing?'&#128276; Following':'&#128277; Follow' !!}</button>
                            </form>
                            @endauth
                        </div>

                        {{-- Grid --}}
                        <div class="file-grid layout-grid" id="grid-{{ $vis }}-{{ Str::slug($cat) }}">
                            @foreach($files as $resource)
                            @php $bookmarkedIds = isset($bookmarked) ? $bookmarked->pluck('id')->toArray() : []; @endphp
                            <div class="file-card {{ $resource->pinned?'pinned-card':'' }} file-item"
                                 data-name="{{ strtolower($resource->title.' '.$resource->original_name.' '.$resource->tags) }}"
                                 data-cat="{{ $cat }}">
                                <div class="file-card-icon">@php echo rIcon($resource->mime_type); @endphp</div>
                                <div class="file-card-name">{{ $resource->title }}</div>
                                <div class="file-card-meta">{{ $resource->file_size_formatted }} &middot; {{ $resource->created_at->format('d M Y') }}</div>
                                <div class="file-card-badges">
                                    @if($resource->pinned)<span class="fbadge pin">&#128204;</span>@endif
                                    @if($resource->isNew())<span class="fbadge new">New</span>@endif
                                    @if($resource->version)<span class="fbadge ver">{{ $resource->version }}</span>@endif
                                    @if($resource->source==='email')<span class="fbadge src">Email</span>@endif
                                </div>
                                <div class="file-card-actions">
                                    <a href="{{ route('resources.preview', $resource) }}" class="fc-btn prev" title="Preview">&#128065;</a>
                                    <a href="{{ route('resources.download', $resource) }}" class="fc-btn dl" title="Download">&#11015;</a>
                                    @auth
                                    <form action="{{ route('resources.bookmark', $resource) }}" method="POST" style="display:inline">@csrf
                                        <button class="fc-btn bm {{ in_array($resource->id,$bookmarkedIds)?'del':'' }}" title="Star">&#11088;</button>
                                    </form>
                                    @if(auth()->user()->isCommittee())
                                    <button onclick="openEdit({{ $resource->id }},'{{ addslashes($resource->title) }}','{{ addslashes($resource->description) }}','{{ $resource->category }}','{{ $resource->visibility }}','{{ $resource->tags }}','{{ $resource->version }}','{{ $resource->expires_at?$resource->expires_at->format('Y-m-d'):'' }}',{{ $resource->pinned?'true':'false' }})" class="fc-btn ed" title="Edit">&#9998;</button>
                                    <form action="{{ route('resources.destroy', $resource) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')
                                        <button class="fc-btn del" title="Delete">&#10005;</button>
                                    </form>
                                    @endif
                                    @endauth
                                </div>
                            </div>
                            @endforeach
                        </div>

                        {{-- List --}}
                        <div class="file-list layout-list" id="list-{{ $vis }}-{{ Str::slug($cat) }}" style="display:none">
                            <div class="file-list-header">
                                <div>Name</div>
                                <div>Size</div>
                                <div class="hide-sm">Downloads</div>
                                <div class="hide-sm">Added</div>
                                <div></div>
                            </div>
                            @foreach($files as $resource)
                            @php $bookmarkedIds = isset($bookmarked) ? $bookmarked->pluck('id')->toArray() : []; @endphp
                            <div class="file-list-row {{ $resource->pinned?'pinned-row':'' }} file-item"
                                 data-name="{{ strtolower($resource->title.' '.$resource->original_name.' '.$resource->tags) }}"
                                 data-cat="{{ $cat }}">
                                <div class="fln">
                                    <span class="fln-icon">@php echo rIcon($resource->mime_type); @endphp</span>
                                    <div class="fln-info">
                                        <div class="fln-title">{{ $resource->title }}</div>
                                        @if($resource->description)<div class="fln-desc">{{ $resource->description }}</div>@endif
                                        <div class="fln-badges">
                                            @if($resource->pinned)<span class="fbadge pin">&#128204;</span>@endif
                                            @if($resource->isNew())<span class="fbadge new">New</span>@endif
                                            @if($resource->version)<span class="fbadge ver">{{ $resource->version }}</span>@endif
                                            @if($resource->source==='email')<span class="fbadge src">Email</span>@endif
                                        </div>
                                    </div>
                                </div>
                                <div class="fl-size">{{ $resource->file_size_formatted }}</div>
                                <div class="fl-dl hide-sm">{{ $resource->download_count }}</div>
                                <div class="fl-date hide-sm">{{ $resource->created_at->format('d M Y') }}</div>
                                <div class="fl-actions">
                                    <a href="{{ route('resources.preview', $resource) }}" class="btn-drive sm secondary" title="Preview">&#128065;</a>
                                    <a href="{{ route('resources.download', $resource) }}" class="btn-drive sm primary" title="Download">&#11015;</a>
                                    @auth
                                    <form action="{{ route('resources.bookmark', $resource) }}" method="POST" style="display:inline">@csrf
                                        <button class="btn-drive sm {{ in_array($resource->id,$bookmarkedIds)?'danger':'secondary' }}" title="Star">&#11088;</button>
                                    </form>
                                    @if(auth()->user()->isCommittee())
                                    <button onclick="openEdit({{ $resource->id }},'{{ addslashes($resource->title) }}','{{ addslashes($resource->description) }}','{{ $resource->category }}','{{ $resource->visibility }}','{{ $resource->tags }}','{{ $resource->version }}','{{ $resource->expires_at?$resource->expires_at->format('Y-m-d'):'' }}',{{ $resource->pinned?'true':'false' }})" class="btn-drive sm secondary">&#9998;</button>
                                    <form action="{{ route('resources.destroy', $resource) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')
                                        <button class="btn-drive sm danger">&#10005;</button>
                                    </form>
                                    @endif
                                    @endauth
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                @endif
            </div>
        </div>
        @endforeach

        {{-- LOCKED DRIVES for guests --}}
        @guest
        @foreach(['members','committee','admin'] as $lockedVis)
        @php $ln = ['members'=>'Members Drive','committee'=>'Committee Drive','admin'=>'Admin Drive']; $li = ['members'=>'&#128274;','committee'=>'&#128203;','admin'=>'&#9881;']; @endphp
        <div id="view-{{ $lockedVis }}" class="drive-view" style="display:none">
            <div class="drive-locked-banner">
                <span style="font-size:1.5rem">{!! $li[$lockedVis] !!}</span>
                <div>
                    <div style="font-weight:600;color:#3c4043;margin-bottom:2px">{{ $ln[$lockedVis] }}</div>
                    <div>You need to <a href="{{ route('login') }}" style="color:#1a73e8;font-weight:600">log in</a> to access this drive.</div>
                </div>
            </div>
        </div>
        @endforeach
        @endguest

    </div>{{-- /drive-content --}}
</div>{{-- /drive-main --}}
</div>{{-- /drive-shell --}}

{{-- Upload Modal --}}
@auth @if(auth()->user()->isCommittee())
<div class="modal-bg" id="uploadModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">&#11014; Upload to RAYNET Drive</span>
            <button class="modal-close" onclick="closeUpload()">&#10005;</button>
        </div>
        <form action="{{ route('resources.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
            @csrf
            <div class="modal-body">
                <div class="mgrid">
                    <div class="mfield full"><label>Title *</label><input type="text" name="title" required placeholder="Document title"></div>
                    <div class="mfield"><label>Drive *</label>
                        <select name="visibility" id="uploadDrive" required>
                            @foreach($allowedVisibilities as $vis)
                            @php $vn = ['public'=>'&#127760; Public Library','members'=>'&#128274; Members Drive','committee'=>'&#128203; Committee Drive','admin'=>'&#9881; Admin Drive']; @endphp
                            <option value="{{ $vis }}">{!! $vn[$vis] !!}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mfield"><label>Category</label>
                        <input type="text" name="category" list="cat-list" placeholder="e.g. Training, Forms">
                        <datalist id="cat-list"><option value="Training"><option value="Forms"><option value="Callout"><option value="Equipment"><option value="Procedures"><option value="General"></datalist>
                    </div>
                    <div class="mfield full"><label>Description</label><input type="text" name="description" placeholder="Brief description"></div>
                    <div class="mfield"><label>Tags <span style="font-weight:400">(comma separated)</span></label><input type="text" name="tags" placeholder="e.g. emergency, dmr"></div>
                    <div class="mfield"><label>Version</label><input type="text" name="version" placeholder="e.g. v1.0"></div>
                    <div class="mfield"><label>Expires</label><input type="date" name="expires_at"></div>
                    <div class="mfield" style="flex-direction:row;align-items:center;gap:.5rem">
                        <input type="checkbox" name="pinned" value="1" id="pinCheck" style="width:16px;height:16px">
                        <label for="pinCheck" style="font-size:.88rem;text-transform:none;letter-spacing:0;cursor:pointer">&#128204; Pin to top</label>
                    </div>
                    <div class="mfield full"><label>File *</label>
                        <div class="drop-zone" id="dropZone" onclick="document.getElementById('fileInput').click()">
                            <div style="font-size:2rem">&#128206;</div>
                            <div style="font-size:.9rem;color:#80868b"><strong style="color:#1a73e8">Click to browse</strong> or drag &amp; drop</div>
                            <div style="font-size:.8rem;color:#9aa0a6">PDF, Word, Excel, PowerPoint, Images, ZIP &mdash; max 20MB</div>
                            <div class="drop-name" id="dropName"></div>
                        </div>
                        <input type="file" id="fileInput" name="file" required accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.zip,.txt,.csv" onchange="document.getElementById('dropName').textContent=this.files[0]?.name||''">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeUpload()" class="btn-drive secondary">Cancel</button>
                <button type="submit" class="btn-drive primary">&#11014; Upload</button>
            </div>
        </form>
    </div>
</div>
@endif @endauth

{{-- Edit Modal --}}
<div class="modal-bg" id="editModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">&#9998; Edit Resource</span>
            <button class="modal-close" onclick="closeEdit()">&#10005;</button>
        </div>
        <form id="editForm" method="POST">
            @csrf @method('PATCH')
            <div class="modal-body">
                <div class="mgrid">
                    <div class="mfield full"><label>Title *</label><input type="text" name="title" id="edit-title" required></div>
                    <div class="mfield full"><label>Description</label><input type="text" name="description" id="edit-description"></div>
                    <div class="mfield"><label>Category</label><input type="text" name="category" id="edit-category" list="cat-list"></div>
                    <div class="mfield"><label>Drive</label>
                        <select name="visibility" id="edit-visibility">
                            @foreach($allowedVisibilities as $vis)
                            <option value="{{ $vis }}">{{ ucfirst($vis) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mfield"><label>Tags</label><input type="text" name="tags" id="edit-tags"></div>
                    <div class="mfield"><label>Version</label><input type="text" name="version" id="edit-version"></div>
                    <div class="mfield full"><label>Expires</label><input type="date" name="expires_at" id="edit-expires"></div>
                    <div class="mfield" style="flex-direction:row;align-items:center;gap:.5rem">
                        <input type="checkbox" name="pinned" value="1" id="edit-pinned" style="width:16px;height:16px">
                        <label for="edit-pinned" style="font-size:.88rem;text-transform:none;letter-spacing:0;cursor:pointer">&#128204; Pin to top</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeEdit()" class="btn-drive secondary">Cancel</button>
                <button type="submit" class="btn-drive primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

{{-- Version Modal --}}
<div class="modal-bg" id="versionModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">&#128196;+ Upload New Version</span>
            <button class="modal-close" onclick="closeVersion()">&#10005;</button>
        </div>
        <form id="versionForm" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                <p id="versionTitle" style="font-weight:600;color:#3c4043;font-size:.9rem"></p>
                <div class="mfield"><label>New File *</label><input type="file" name="file" required accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.zip,.txt,.csv"></div>
                <div class="mfield"><label>Version Label</label><input type="text" name="version" placeholder="e.g. v2.0"></div>
                <div class="mfield"><label>Change Notes</label><input type="text" name="notes" placeholder="What changed?"></div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeVersion()" class="btn-drive secondary">Cancel</button>
                <button type="submit" class="btn-drive primary">Upload Version</button>
            </div>
        </form>
    </div>
</div>

@php
function rIcon(?string $mime): string {
    $map=['pdf'=>'&#128213;','doc'=>'&#128216;','docx'=>'&#128216;','xls'=>'&#128202;','xlsx'=>'&#128202;','ppt'=>'&#128190;','pptx'=>'&#128190;','jpg'=>'&#128247;','jpeg'=>'&#128247;','png'=>'&#128247;','gif'=>'&#128247;','zip'=>'&#128230;','txt'=>'&#128203;','csv'=>'&#128202;'];
    return $map[strtolower($mime??'')] ?? '&#128196;';
}
@endphp

<script>
// Current view
let currentView = '{{ $driveFocus }}';
let currentLayout = localStorage.getItem('driveLayout') || 'grid';

document.addEventListener('DOMContentLoaded', function() {
    applyLayout(currentLayout);
    showView(currentView);
});

function setView(view) {
    currentView = view;
    // Update URL without reload
    const url = new URL(window.location);
    url.searchParams.set('drive', view);
    window.history.pushState({}, '', url);
    showView(view);
    // Update sidebar active state
    document.querySelectorAll('.sidebar-item').forEach(i => i.classList.remove('active'));
    event.currentTarget.classList.add('active');
}

function showView(view) {
    document.querySelectorAll('.drive-view').forEach(v => v.style.display = 'none');
    const el = document.getElementById('view-' + view);
    if (el) el.style.display = 'block';
}

function switchLayout(mode) {
    currentLayout = mode;
    localStorage.setItem('driveLayout', mode);
    applyLayout(mode);
    document.getElementById('gridBtn').classList.toggle('active', mode === 'grid');
    document.getElementById('listBtn').classList.toggle('active', mode === 'list');
}

function applyLayout(mode) {
    document.querySelectorAll('.layout-grid').forEach(el => el.style.display = mode === 'grid' ? 'grid' : 'none');
    document.querySelectorAll('.layout-list').forEach(el => el.style.display = mode === 'list' ? 'block' : 'none');
}

function filterFiles() {
    const q = document.getElementById('driveSearch').value.toLowerCase();
    document.querySelectorAll('.file-item').forEach(el => {
        el.style.display = !q || (el.dataset.name || '').includes(q) ? '' : 'none';
    });
}

// Upload modal
function openUpload(drive) {
    const sel = document.getElementById('uploadDrive');
    if (sel && drive) sel.value = drive;
    document.getElementById('uploadModal').classList.add('open');
}
function closeUpload() { document.getElementById('uploadModal').classList.remove('open'); }

// Edit modal
function openEdit(id, title, desc, cat, vis, tags, ver, exp, pinned) {
    document.getElementById('editForm').action = '/library/' + id;
    document.getElementById('edit-title').value = title;
    document.getElementById('edit-description').value = desc;
    document.getElementById('edit-category').value = cat;
    document.getElementById('edit-visibility').value = vis;
    document.getElementById('edit-tags').value = tags;
    document.getElementById('edit-version').value = ver;
    document.getElementById('edit-expires').value = exp;
    document.getElementById('edit-pinned').checked = pinned;
    document.getElementById('editModal').classList.add('open');
}
function closeEdit() { document.getElementById('editModal').classList.remove('open'); }

// Version modal
function openVersion(id, title) {
    document.getElementById('versionForm').action = '/library/' + id + '/new-version';
    document.getElementById('versionTitle').textContent = title;
    document.getElementById('versionModal').classList.add('open');
}
function closeVersion() { document.getElementById('versionModal').classList.remove('open'); }

// Close modals on background click
document.querySelectorAll('.modal-bg').forEach(bg => bg.addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('open');
}));

// Drag and drop upload
const dz = document.getElementById('dropZone');
const fi = document.getElementById('fileInput');
if (dz && fi) {
    ['dragenter','dragover'].forEach(e => dz.addEventListener(e, ev => { ev.preventDefault(); dz.classList.add('drag'); }));
    ['dragleave','drop'].forEach(e => dz.addEventListener(e, ev => { ev.preventDefault(); dz.classList.remove('drag'); }));
    dz.addEventListener('drop', ev => {
        const f = ev.dataTransfer.files[0];
        if (f) { const dt = new DataTransfer(); dt.items.add(f); fi.files = dt.files; document.getElementById('dropName').textContent = f.name; }
    });
}
</script>
@endsection
