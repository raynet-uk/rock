@extends('layouts.app')
@section('title', 'Preview — ' . $resource->title)
@section('content')
<style>
    .prev-wrap{max-width:1100px;margin:0 auto;padding:1rem}
    .prev-bar{display:flex;align-items:center;gap:.75rem;margin-bottom:1rem;flex-wrap:wrap;background:white;border:1px solid var(--grey-mid);border-top:3px solid var(--navy);padding:.75rem 1rem}
    .prev-title{font-weight:700;color:var(--navy);flex:1;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .prev-meta{font-size:.8rem;color:var(--text-muted)}
    .prev-actions{display:flex;gap:.4rem;flex-shrink:0}
    .btn-prev{padding:.4rem .9rem;font-size:.85rem;font-weight:600;border:none;cursor:pointer;font-family:var(--font);display:inline-flex;align-items:center;gap:.35rem;text-decoration:none}
    .btn-prev.navy{background:var(--navy);color:white}
    .btn-prev.navy:hover{background:var(--navy-mid)}
    .btn-prev.ghost{background:white;color:var(--navy);border:1px solid var(--navy)}
    .btn-prev.ghost:hover{background:var(--navy-faint)}
    .prev-frame{background:white;border:1px solid var(--grey-mid);width:100%;min-height:80vh;position:relative}
    .prev-frame iframe{width:100%;height:80vh;border:none;display:block}
    .prev-frame img{max-width:100%;max-height:80vh;display:block;margin:0 auto;padding:1rem}
    .prev-text{padding:1.5rem;font-family:monospace;font-size:.85rem;white-space:pre-wrap;word-break:break-all;overflow-x:auto;max-height:80vh;overflow-y:auto;line-height:1.6;color:var(--text-light)}
    .prev-unsupported{padding:4rem;text-align:center}
    .prev-unsupported-icon{font-size:3rem;margin-bottom:1rem;opacity:.4}
    .prev-unsupported-text{font-size:1rem;color:var(--text-muted);margin-bottom:1.5rem}
    .prev-notice{background:#fffbeb;border:1px solid #fcd34d;padding:.6rem 1rem;font-size:.82rem;color:#92400e;margin-bottom:.5rem}
</style>

<div class="prev-wrap">
    <div class="prev-bar">
        <a href="{{ route('resources.index') }}" style="color:var(--navy);text-decoration:none;font-size:.9rem;flex-shrink:0">&#8592; Resources</a>
        <div>
            <div class="prev-title">{{ $resource->title }}</div>
            <div class="prev-meta">{{ $resource->original_name }} &middot; {{ $resource->file_size_formatted }} &middot; {{ $resource->created_at->format('d M Y') }}</div>
        </div>
        <div class="prev-actions">
            <a href="{{ route('resources.download', $resource) }}" class="btn-prev navy">&#11015; Download</a>
            @auth
            <form action="{{ route('resources.bookmark', $resource) }}" method="POST" style="display:inline">@csrf
                <button class="btn-prev ghost">&#11088; {{ $resource->isBookmarkedBy(auth()->id()) ? 'Bookmarked' : 'Bookmark' }}</button>
            </form>
            @endauth
        </div>
    </div>

    @if($type === 'office' && $resource->visibility === 'members')
    <div class="prev-notice">&#9888; Office preview requires the file to be publicly accessible. Members-only files cannot be previewed with Microsoft Office Online — download instead.</div>
    @endif

    <div class="prev-frame">
        @if($type === 'pdf')
            <iframe src="{{ route('resources.inline', $resource) }}" title="{{ $resource->title }}"></iframe>

        @elseif($type === 'office' && $resource->visibility === 'public')
            <iframe src="{{ $url }}" title="{{ $resource->title }}" frameborder="0"></iframe>

        @elseif($type === 'image')
            <img src="{{ route('resources.inline', $resource) }}" alt="{{ $resource->title }}">

        @elseif($type === 'text')
            <div class="prev-text">{{ $textContent }}</div>

        @else
            <div class="prev-unsupported">
                <div class="prev-unsupported-icon">&#128196;</div>
                <div class="prev-unsupported-text">This file type cannot be previewed in the browser.</div>
                <a href="{{ route('resources.download', $resource) }}" class="btn-prev navy">&#11015; Download File</a>
            </div>
        @endif
    </div>

    @if($resource->description)
    <div style="margin-top:.75rem;padding:.75rem 1rem;background:white;border:1px solid var(--grey-mid);font-size:.88rem;color:var(--text-muted)">
        {{ $resource->description }}
    </div>
    @endif
</div>
@endsection
