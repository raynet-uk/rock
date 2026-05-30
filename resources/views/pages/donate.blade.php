@extends('layouts.app')
@section('title', 'Support Us — ' . \App\Helpers\RaynetSetting::groupName())
@section('content')
<style>
.donate-hero { background: linear-gradient(135deg, var(--navy) 60%, #C8102E); color: #fff; padding: 4rem 0 3rem; text-align: center; }
.donate-hero-inner { max-width: 640px; margin: 0 auto; padding: 0 1.5rem; }
.donate-icon { font-size: 3.5rem; margin-bottom: 1rem; }
.donate-title { font-size: 2.2rem; font-weight: bold; margin-bottom: .5rem; }
.donate-sub { font-size: 1.1rem; color: rgba(255,255,255,.7); margin-bottom: 0; }
.donate-wrap { max-width: 640px; margin: 0 auto; padding: 2.5rem 1.5rem 4rem; }
.donate-card { background: #fff; border: 1px solid var(--grey-mid); border-top: 4px solid var(--red); padding: 2rem; text-align: center; box-shadow: 0 4px 20px rgba(0,51,102,.08); margin-bottom: 1.5rem; }
.donate-message { font-size: 1rem; color: var(--text-mid); line-height: 1.7; margin-bottom: 2rem; }
.donate-btn { display: inline-flex; align-items: center; gap: .6rem; padding: 1rem 2.5rem; background: var(--red); color: #fff; font-size: 1.1rem; font-weight: bold; text-decoration: none; border-radius: 4px; transition: background .15s; }
.donate-btn:hover { background: #a00d25; color: #fff; }
.donate-note { font-size: .8rem; color: var(--text-muted); margin-top: 1rem; }
.donate-info { background: var(--navy-faint); border: 1px solid rgba(0,51,102,.15); padding: 1.25rem 1.5rem; font-size: .875rem; color: var(--text-mid); line-height: 1.6; }
.donate-info ul { padding-left: 1.25rem; margin-top: .5rem; }
.donate-info li { margin-bottom: .3rem; }
</style>

<div class="donate-hero">
    <div class="donate-hero-inner">
        <div class="donate-icon">💙</div>
        <div class="donate-title">Support {{ \App\Helpers\RaynetSetting::groupName() }}</div>
        <div class="donate-sub">Help us keep volunteers trained, equipped and ready to serve the community.</div>
    </div>
</div>

<div class="donate-wrap">
    <div class="donate-card">
        @if($donationMessage)
        <div class="donate-message">{{ $donationMessage }}</div>
        @else
        <div class="donate-message">
            {{ \App\Helpers\RaynetSetting::groupName() }} is a volunteer-run emergency communications group. Your donation helps us maintain radio equipment, fund training exercises, and keep our operators ready to assist when it matters most.
        </div>
        @endif

        @if($donationUrl)
        <a href="{{ $donationUrl }}" target="_blank" rel="noopener" class="donate-btn">
            💳 Donate Now
        </a>
        <div class="donate-note">You'll be taken to our secure payment page. All donations are voluntary and greatly appreciated.</div>
        @else
        <p style="color:var(--text-muted);">Donation link coming soon.</p>
        @endif
    </div>

    <div class="donate-info">
        <strong style="color:var(--navy);">How your donation helps:</strong>
        <ul>
            <li>Maintaining and replacing radio equipment</li>
            <li>Funding training exercises and courses</li>
            <li>Covering insurance and membership costs</li>
            <li>Supporting deployment to community events</li>
        </ul>
        <div style="margin-top:.75rem;font-size:.8rem;color:var(--text-muted);">
            {{ \App\Helpers\RaynetSetting::groupName() }} is an affiliated group of RAYNET-UK, a voluntary organisation. Donations are used solely for group operations and equipment.
        </div>
    </div>
</div>
@endsection
