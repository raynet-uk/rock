@extends('layouts.admin')
@section('title', 'Remote Access Panel — Admin')
@section('content')
<style>
:root{--navy:#003366;--red:#C8102E;--grey:#f2f2f2;--grey-mid:#dde2e8;--text:#001f40;--text-muted:#6b7f96;}
*{box-sizing:border-box;}
body{background:var(--grey);font-family:Arial,sans-serif;font-size:14px;color:var(--text);}
.rn-header{background:var(--navy);border-bottom:4px solid var(--red);padding:0 1.5rem;}
.rn-header-inner{max-width:700px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;padding:.75rem 0;}
.rn-logo{background:var(--red);padding:4px 10px;font-size:11px;font-weight:bold;color:#fff;letter-spacing:.1em;}
.rn-back{color:rgba(255,255,255,.8);text-decoration:none;font-size:12px;border:1px solid rgba(255,255,255,.25);padding:.3rem .8rem;}
.wrap{max-width:700px;margin:2rem auto;padding:0 1.5rem 4rem;}
.card{background:#fff;border:1px solid var(--grey-mid);border-top:3px solid var(--red);padding:1.5rem;}
.page-title{font-size:22px;font-weight:bold;color:var(--navy);margin-bottom:.25rem;}
.page-sub{font-size:13px;color:var(--text-muted);margin-bottom:1.5rem;}
.field{margin-bottom:1rem;}
.field label{display:block;font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:var(--text-muted);margin-bottom:.35rem;}
.field input{width:100%;padding:.5rem .75rem;border:1px solid var(--grey-mid);font-size:13px;outline:none;}
.btn{padding:.65rem 1.5rem;font-size:13px;font-weight:bold;cursor:pointer;border:none;background:var(--red);color:#fff;width:100%;letter-spacing:.05em;}
.warning{background:#fff8e1;border:1px solid #ffe082;border-left:3px solid #f9a825;padding:.75rem 1rem;font-size:12px;color:#7a5800;margin-bottom:1rem;}
</style>
<div class="rn-header">
    <div class="rn-header-inner">
        <div class="rn-logo">RAYNET — Liverpool Support</div>
        <a href="{{ route('admin.dashboard') }}" class="rn-back">← Admin</a>
    </div>
</div>
<div class="wrap">
    <div class="page-title">🔐 Remote Site Access</div>
    <div class="page-sub">Enter the site URL and support code provided by the group requesting help.</div>
    <div class="card">
        <div class="warning">⚠ This will log you in as the super admin on the remote site. Only use codes shared directly by the group controller.</div>
        <form method="POST" action="{{ route('admin.remote-help.access') }}">
            @csrf
            <div class="field">
                <label>Site URL</label>
                <input type="url" name="site_url" placeholder="https://raynet-grampian.net" required>
            </div>
            <div class="field">
                <label>Support Code</label>
                <input type="text" name="code" placeholder="XXXX-XXXX" required
                       style="font-family:monospace;font-size:1.2rem;letter-spacing:.2em;text-transform:uppercase"
                       oninput="this.value=this.value.toUpperCase()">
            </div>
            <button type="submit" class="btn">Connect to Remote Site →</button>
        </form>
    </div>
</div>
@endsection
