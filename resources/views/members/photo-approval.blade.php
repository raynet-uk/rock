@extends('layouts.app')
@section('title', 'Photo Approval')
@section('content')
<style>
:root{--navy:#003366;--red:#C8102E;--white:#fff;--grey:#f2f5f9;--grey-mid:#dde2e8;--text:#001f40;--muted:#6b7f96;}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
.wrap{max-width:1200px;margin:0 auto;padding:1.5rem 1rem 4rem;}
.page-head{margin-bottom:1.5rem;}
.eyebrow{font-size:.75rem;font-weight:bold;text-transform:uppercase;letter-spacing:.15em;color:var(--red);margin-bottom:.4rem;}
.page-title{font-size:1.8rem;font-weight:bold;color:var(--navy);}
.stat-row{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.5rem;}
@media(max-width:600px){.stat-row{grid-template-columns:1fr 1fr;}}
.stat-card{background:#fff;border:1px solid var(--grey-mid);border-top:3px solid var(--navy);padding:.85rem 1rem;}
.stat-label{font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);margin-bottom:.2rem;}
.stat-value{font-size:1.8rem;font-weight:bold;color:var(--navy);}
.filter-tabs{display:flex;gap:.5rem;margin-bottom:1.5rem;flex-wrap:wrap;}
.filter-tab{padding:.4rem 1rem;border-radius:999px;font-size:.82rem;font-weight:600;text-decoration:none;border:1px solid var(--grey-mid);color:var(--muted);transition:all .15s;}
.filter-tab.active{background:var(--navy);color:#fff;border-color:var(--navy);}
.photo-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:1rem;}
@media(max-width:600px){.photo-grid{grid-template-columns:1fr 1fr;gap:.65rem;}}
.photo-card{background:#fff;border:1px solid var(--grey-mid);border-radius:8px;overflow:hidden;}
.photo-img{width:100%;aspect-ratio:4/3;object-fit:cover;display:block;}
.photo-body{padding:.75rem;}
.photo-chip{display:inline-block;padding:.15rem .45rem;border-radius:3px;font-size:.68rem;font-weight:bold;margin-bottom:.4rem;}
.chip-pending{background:#fef3c7;color:#92400e;}
.chip-approved{background:#d1fae5;color:#065f46;}
.chip-rejected{background:#fee2e2;color:#991b1b;}
.chip-public{background:#dbeafe;color:#1e40af;}
.photo-actions{display:flex;flex-wrap:wrap;gap:.35rem;margin-top:.6rem;}
.btn{padding:.35rem .75rem;border-radius:4px;font-size:.75rem;font-weight:bold;border:none;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:.25rem;}
.btn-green{background:#d1fae5;color:#065f46;}
.btn-red{background:#fee2e2;color:#991b1b;}
.btn-amber{background:#fef3c7;color:#92400e;}
.btn-blue{background:#dbeafe;color:#1e40af;}
.btn-navy{background:#e8eef5;color:var(--navy);}
.alert{padding:.65rem 1rem;border-radius:6px;margin-bottom:1rem;font-size:.88rem;font-weight:bold;}
.alert-success{background:#d1fae5;border-left:3px solid #059669;color:#065f46;}
.two-level-note{background:#fffbeb;border:1px solid #fcd34d;border-left:3px solid #f59e0b;padding:.75rem 1rem;border-radius:6px;font-size:.82rem;color:#92400e;margin-bottom:1.5rem;line-height:1.6;}
</style>

<div class="wrap">
    <div class="page-head">
        <div class="eyebrow">Members Area</div>
        <h1 class="page-title">📸 Photo Approval</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success">✓ {{ session('success') }}</div>
    @endif

    <div class="two-level-note">
        <strong>Two-level approval:</strong> Level 1 (Members) — approved photos are visible to logged-in members.
        Level 2 (Public) — admin approval required before photos appear on the public gallery.
        @if($canFeature) You also have permission to <strong>feature photos</strong> on the homepage. @endif
    </div>

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
    </div>

    <div class="filter-tabs">
        <a href="?filter=pending"  class="filter-tab {{ $filter==='pending'  ? 'active' : '' }}">⏳ Pending ({{ $counts['pending'] }})</a>
        <a href="?filter=approved" class="filter-tab {{ $filter==='approved' ? 'active' : '' }}">✓ Approved</a>
        <a href="?filter=rejected" class="filter-tab {{ $filter==='rejected' ? 'active' : '' }}">✕ Rejected</a>
    </div>

    @if($photos->isEmpty())
        <div style="text-align:center;padding:3rem;color:var(--muted);background:#fff;border:1px solid var(--grey-mid);border-radius:8px;">
            <div style="font-size:2.5rem;margin-bottom:.75rem;opacity:.3;">📷</div>
            No photos in this category.
        </div>
    @else
    <div class="photo-grid">
        @foreach($photos as $photo)
        <div class="photo-card">
            <a href="{{ $photo->url() }}" target="_blank">
                <img src="{{ $photo->thumbUrl() }}" alt="" class="photo-img">
            </a>
            <div class="photo-body">
                <div style="display:flex;flex-wrap:wrap;gap:.25rem;margin-bottom:.4rem;">
                    <span class="photo-chip chip-{{ $photo->status }}">L1: {{ ucfirst($photo->status) }}</span>
                    @if($canPublicApprove)
                        <span class="photo-chip chip-public">L2: {{ ucfirst($photo->public_status) }}</span>
                    @endif
                    @if($photo->featured)<span class="photo-chip" style="background:#fef9c3;color:#854d0e;">⭐ Featured</span>@endif
                </div>
                <div style="font-size:.8rem;font-weight:600;color:var(--text);margin-bottom:.25rem;line-height:1.3;">{{ $photo->caption ?: '(No caption)' }}</div>
                <div style="font-size:.72rem;color:var(--muted);">
                    By {{ $photo->user?->name }}
                    @if($photo->user?->callsign) · {{ strtoupper($photo->user->callsign) }}@endif
                    · {{ $photo->created_at->format('d M Y') }}
                </div>
                @if($photo->tags && $photo->tags->count())
                <div style="margin-top:.3rem;display:flex;flex-wrap:wrap;gap:.2rem;">
                    @foreach($photo->tags as $tag)
                    <span style="background:#e8eef5;color:var(--navy);font-size:.65rem;padding:.1rem .35rem;border-radius:3px;font-weight:600;">🏷{{ strtoupper($tag->callsign) }}</span>
                    @endforeach
                </div>
                @endif
                <div class="photo-actions">
                    @if($photo->status !== 'approved')
                        <form method="POST" action="{{ route('members.photo-approval.approve', $photo) }}">
                            @csrf <button class="btn btn-green">✓ L1 Approve</button>
                        </form>
                    @endif
                    @if($canPublicApprove && $photo->status === 'approved' && $photo->public_status !== 'approved')
                        <form method="POST" action="{{ route('members.photo-approval.public-approve', $photo) }}">
                            @csrf <button class="btn btn-blue">🌐 L2 Public</button>
                        </form>
                    @endif
                    @if($canFeature && $photo->isApproved())
                        <form method="POST" action="{{ route('members.photo-approval.feature', $photo) }}">
                            @csrf <button class="btn btn-amber">{{ $photo->featured ? '★ Unfeature' : '⭐ Feature' }}</button>
                        </form>
                    @endif
                    @if($photo->status !== 'rejected')
                        <div x-data="{ open: false }">
                            <button class="btn btn-red" onclick="var f=document.getElementById('reject-form-{{ $photo->id }}');f.style.display=f.style.display==='none'?'block':'none'">✕ Reject</button>
                        </div>
                        <div class="reject-form-{{ $photo->id }}" style="display:none;width:100%;margin-top:.5rem;" id="reject-form-{{ $photo->id }}">
                            <form method="POST" action="{{ route('members.photo-approval.reject', $photo) }}">
                                @csrf
                                <textarea name="notes" placeholder="Reason for rejection (optional — will be shown to admin and sent to uploader)" style="width:100%;padding:.45rem .6rem;border:1px solid #dde2e8;border-radius:4px;font-size:.78rem;font-family:inherit;resize:vertical;min-height:60px;margin-bottom:.4rem;"></textarea>
                                <div style="display:flex;gap:.35rem;">
                                    <button type="submit" class="btn btn-red" style="flex:1;">✕ Confirm Reject</button>
                                    <button type="button" class="btn btn-navy" onclick="document.getElementById('reject-form-{{ $photo->id }}').style.display='none'">Cancel</button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <div style="margin-top:1.5rem;display:flex;justify-content:center;">{{ $photos->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
