{{-- resources/views/admin/modules/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Module Manager')
@section('content')

@php
    $activeCount = collect($modules)->where('enabled', true)->count();
    $total       = count($modules);
@endphp

<div class="mm">

    {{-- ── Title row ── --}}
    <div class="mm-titlerow">
        <h1 class="mm-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
            Module Manager
        </h1>
        <div class="mm-titlerow__right">
            <form action="{{ route('admin.modules.refresh-updates') }}" method="POST" style="display:inline">
                @csrf
                <button type="submit" class="mm-btn-ghost">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                    Check for updates
                </button>
            </form>
            <button class="mm-btn-secondary" onclick="openRegistry()">
                🔌 Browse Modules
            </button>
            <button class="mm-btn-primary" onclick="document.getElementById('mm-upload-panel').classList.toggle('mm-hidden')">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Upload Module
            </button>
        </div>
    </div>

    {{-- ── Upload panel ── --}}
    <div id="mm-upload-panel" class="mm-upload-panel mm-hidden">
        <div class="mm-upload-panel__head">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            Install module from ZIP
        </div>
        <form action="{{ route('admin.modules.upload') }}" method="POST" enctype="multipart/form-data" class="mm-upload-form">
            @csrf
            <div class="mm-upload-drop" id="mm-drop-zone">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" opacity=".4"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                <p class="mm-upload-drop__text">Drag &amp; drop a <strong>.zip</strong> here, or <label for="mm-file-input" class="mm-upload-browse">browse</label></p>
                <p class="mm-upload-drop__hint" id="mm-file-name">Max 20 MB &nbsp;·&nbsp; Must contain a valid <code>module.json</code></p>
                <input type="file" name="module_zip" id="mm-file-input" accept=".zip" style="display:none" onchange="document.getElementById('mm-file-name').textContent = this.files[0]?.name ?? 'No file chosen'">
            </div>
            @error('module_zip')
                <div class="mm-upload-err">{{ $message }}</div>
            @enderror
            <div class="mm-upload-actions">
                <button type="submit" class="mm-btn-primary">Install Now</button>
                <button type="button" class="mm-btn-ghost" onclick="document.getElementById('mm-upload-panel').classList.add('mm-hidden')">Cancel</button>
            </div>
        </form>
    </div>

    {{-- ── Notices ── --}}
    @if(session('success'))
        <div class="mm-notice mm-notice--ok">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            {!! session('success') !!}
        </div>
    @endif
    @if(session('error'))
        <div class="mm-notice mm-notice--err">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            {!! session('error') !!}
        </div>
    @endif
    @if($updateCount > 0)
        <div class="mm-notice mm-notice--warn">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
            <strong>{{ $updateCount }} update{{ $updateCount > 1 ? 's' : '' }} available</strong> — apply them from the table below.
        </div>
    @endif

    {{-- ── Filter tabs ── --}}
    <div class="mm-tabs">
        <a href="#" class="mm-tab mm-tab--active" data-filter="all">All <span class="mm-tab__count">{{ $total }}</span></a>
        <a href="#" class="mm-tab" data-filter="active">Active <span class="mm-tab__count">{{ $activeCount }}</span></a>
        <a href="#" class="mm-tab" data-filter="inactive">Inactive <span class="mm-tab__count">{{ $total - $activeCount }}</span></a>
        @if($updateCount > 0)
        <a href="#" class="mm-tab" data-filter="update">Update Available <span class="mm-tab__count mm-tab__count--warn">{{ $updateCount }}</span></a>
        @endif
    </div>

    {{-- ── Module table ── --}}
    @if($total === 0)
        <div class="mm-empty">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" opacity=".25"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
            <p>No modules installed yet.</p>
        </div>
    @else
        <div class="mm-table-wrap">
            <table class="mm-table">
                <thead>
                    <tr>
                        <th class="mm-th-module">Module</th>
                        <th class="mm-th-desc">Description</th>
                        <th class="mm-th-ver">Version</th>
                        <th class="mm-th-author">Author</th>
                        <th class="mm-th-tags">Tags</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($modules as $alias => $mod)

                    {{-- ═══════════════════════════════════════════
                         CORE ROW — special locked rendering
                    ═══════════════════════════════════════════ --}}
                    @if($mod['is_core'])
                    <tr class="mm-row mm-row--core" data-state="active" data-update="false">

                        <td class="mm-td-module">
                            <div class="mm-mod-name">
                                <span class="mm-core-lock" title="Core — cannot be disabled">🔒</span>
                                {{ $mod['name'] }}
                                <span class="mm-pill mm-pill--core">Always Active</span>
                            </div>
                            <div class="mm-mod-alias">{{ $alias }}</div>

                            {{-- Core action links --}}
                            <div class="mm-actions">
                                @if(\Illuminate\Support\Facades\Route::has('admin.core.health'))
                                    <a href="{{ route('admin.core.health') }}" class="mm-act">System Health</a>
                                    <span class="mm-act-sep">|</span>
                                @endif
                                @if(!empty($mod['changelog']))
                                    <button type="button" class="mm-act"
                                        onclick="this.closest('tr').querySelector('.mm-cl').classList.toggle('mm-hidden')">
                                        Changelog
                                    </button>
                                    <span class="mm-act-sep">|</span>
                                @endif
                                <span class="mm-act mm-act--locked" title="Core cannot be disabled or deleted">🔒 Protected — cannot be modified</span>
                            </div>

                            {{-- Components list --}}
                            @if(!empty($mod['components']))
                            <div class="mm-core-components">
                                @foreach($mod['components'] as $component)
                                    <span class="mm-core-component">✓ {{ $component }}</span>
                                @endforeach
                            </div>
                            @endif

                            {{-- Changelog --}}
                            @if(!empty($mod['changelog']))
                                <div class="mm-cl mm-hidden">
                                    @foreach($mod['changelog'] as $ver => $note)
                                        <div class="mm-cl__row">
                                            <code class="mm-cl__ver">{{ $ver }}</code>
                                            <span class="mm-cl__note">{{ $note }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </td>

                        <td class="mm-td-desc">{{ $mod['description'] }}</td>

                        <td class="mm-td-ver">
                            <code class="mm-ver">v{{ $mod['version'] }}</code>
                        </td>

                        <td class="mm-td-author">
                            @if($mod['author_uri'])
                                <a href="{{ $mod['author_uri'] }}" target="_blank" class="mm-alink">{{ $mod['author'] }}</a>
                            @else
                                {{ $mod['author'] ?: '—' }}
                            @endif
                            @if($mod['license'])
                                <div class="mm-lic">
                                    @if($mod['license_uri'])
                                        <a href="{{ $mod['license_uri'] }}" target="_blank" class="mm-alink">{{ $mod['license'] }}</a>
                                    @else
                                        {{ $mod['license'] }}
                                    @endif
                                </div>
                            @endif
                        </td>

                        <td class="mm-td-tags">
                            @foreach($mod['tags'] as $tag)
                                <span class="mm-tag">{{ $tag }}</span>
                            @endforeach
                        </td>
                    </tr>

                    {{-- ═══════════════════════════════════════════
                         REGULAR MODULE ROW
                    ═══════════════════════════════════════════ --}}
                    @else
                    <tr class="mm-row {{ $mod['enabled'] ? 'mm-row--on' : '' }} {{ $mod['update'] ? 'mm-row--upd' : '' }}"
                        data-state="{{ $mod['enabled'] ? 'active' : 'inactive' }}"
                        data-update="{{ $mod['update'] ? 'true' : 'false' }}">

                        <td class="mm-td-module">
                            <div class="mm-mod-name">
                                {{ $mod['name'] }}
                                @if($mod['enabled'])
                                    <span class="mm-pill mm-pill--on">Active</span>
                                @else
                                    <span class="mm-pill mm-pill--off">Inactive</span>
                                @endif
                                @if($mod['update'])
                                    <span class="mm-pill mm-pill--upd">Update available</span>
                                @endif
                            </div>
                            <div class="mm-mod-alias">{{ $alias }}</div>

                            <div class="mm-actions">
                                @if($mod['enabled'])
                                    <form action="{{ route('admin.modules.disable', $alias) }}" method="POST" class="mm-act-form">
                                        @csrf
                                        <button class="mm-act mm-act--deactivate"
                                            onclick="return confirm('Deactivate {{ addslashes($mod['name']) }}?')">Deactivate</button>
                                    </form>
                                    <span class="mm-act-sep">|</span>
                                @else
                                    <form action="{{ route('admin.modules.enable', $alias) }}" method="POST" class="mm-act-form">
                                        @csrf
                                        <button class="mm-act mm-act--activate">Activate</button>
                                    </form>
                                    <span class="mm-act-sep">|</span>
                                @endif

                                @if($mod['update'])
                                    <form action="{{ route('admin.modules.update', $alias) }}" method="POST" class="mm-act-form">
                                        @csrf
                                        <button class="mm-act mm-act--update">Update to v{{ $mod['update']['version'] }}</button>
                                    </form>
                                    <span class="mm-act-sep">|</span>
                                @endif

                                @if($mod['module_uri'])
                                    <a href="{{ $mod['module_uri'] }}" target="_blank" class="mm-act">Module page</a>
                                    <span class="mm-act-sep">|</span>
                                @endif

                                @if($mod['docs_uri'])
                                    <a href="{{ $mod['docs_uri'] }}" target="_blank" class="mm-act">Docs</a>
                                    <span class="mm-act-sep">|</span>
                                @endif

                                @if(!empty($mod['changelog']))
                                    <button type="button" class="mm-act"
                                        onclick="this.closest('tr').querySelector('.mm-cl').classList.toggle('mm-hidden')">
                                        Changelog
                                    </button>
                                    <span class="mm-act-sep">|</span>
                                @endif

                                <form action="{{ route('admin.modules.delete', $alias) }}" method="POST" class="mm-act-form">
                                    @csrf
                                    <button class="mm-act mm-act--delete"
                                        onclick="return confirm('Permanently delete {{ addslashes($mod['name']) }} and all its data?')">
                                        Delete
                                    </button>
                                </form>
                            </div>

                            @if(!empty($mod['changelog']))
                                <div class="mm-cl mm-hidden">
                                    @foreach($mod['changelog'] as $ver => $note)
                                        <div class="mm-cl__row">
                                            <code class="mm-cl__ver">{{ $ver }}</code>
                                            <span class="mm-cl__note">{{ $note }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </td>

                        <td class="mm-td-desc">
                            {{ $mod['description'] }}
                            @if($mod['update'])
                                <div class="mm-upd-line">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                                    v{{ $mod['update']['version'] }} available
                                    @if(!empty($mod['update']['changelog'])) — {{ $mod['update']['changelog'] }}@endif
                                </div>
                            @endif
                        </td>

                        <td class="mm-td-ver">
                            <code class="mm-ver">{{ $mod['version'] }}</code>
                            @if($mod['requires_core'] !== '*')
                                <div class="mm-req">core {{ $mod['requires_core'] }}</div>
                            @endif
                        </td>

                        <td class="mm-td-author">
                            @if($mod['author_uri'])
                                <a href="{{ $mod['author_uri'] }}" target="_blank" class="mm-alink">{{ $mod['author'] }}</a>
                            @else
                                {{ $mod['author'] ?: '—' }}
                            @endif
                            @if($mod['license'])
                                <div class="mm-lic">
                                    @if($mod['license_uri'])
                                        <a href="{{ $mod['license_uri'] }}" target="_blank" class="mm-alink">{{ $mod['license'] }}</a>
                                    @else
                                        {{ $mod['license'] }}
                                    @endif
                                </div>
                            @endif
                        </td>

                        <td class="mm-td-tags">
                            @foreach($mod['tags'] as $tag)
                                <span class="mm-tag">{{ $tag }}</span>
                            @endforeach
                        </td>

                    </tr>
                    @endif

                @endforeach
                </tbody>
            </table>
        </div>

        <p class="mm-footnote">Core <code>v{{ $coreVersion }}</code> &nbsp;·&nbsp; {{ $activeCount }} of {{ $total }} modules active</p>
    @endif

</div>

<style>
.mm { max-width:1140px; margin:0 auto; padding:2rem 1rem 4rem; font-family:inherit; color:#111827; }
.mm-titlerow { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:.75rem; margin-bottom:1.25rem; }
.mm-title { display:flex; align-items:center; gap:.55rem; font-size:1.3rem; font-weight:700; margin:0; letter-spacing:-.02em; }
.mm-title svg { width:20px; height:20px; stroke:#2563eb; flex-shrink:0; }
.mm-titlerow__right { display:flex; gap:.5rem; align-items:center; }
.mm-btn-primary { display:inline-flex; align-items:center; gap:.4rem; background:#2563eb; color:#fff; border:1px solid #2563eb; font-size:.82rem; font-family:inherit; font-weight:500; padding:.45rem .9rem; cursor:pointer; transition:background .15s; }
.mm-btn-primary:hover { background:#1d4ed8; }
.mm-btn-ghost { display:inline-flex; align-items:center; gap:.4rem; background:#fff; border:1px solid #d1d5db; color:#374151; font-size:.82rem; font-family:inherit; font-weight:500; padding:.45rem .9rem; cursor:pointer; transition:all .15s; }
.mm-btn-ghost:hover { background:#f9fafb; border-color:#9ca3af; }
.mm-upload-panel { background:#fff; border:1px solid #d1d5db; margin-bottom:1.25rem; box-shadow:0 2px 8px rgba(0,0,0,.07); overflow:hidden; }
.mm-upload-panel__head { display:flex; align-items:center; gap:.5rem; background:#f8fafc; border-bottom:1px solid #e5e7eb; padding:.65rem 1.1rem; font-size:.85rem; font-weight:600; color:#374151; }
.mm-upload-form { padding:1.1rem; }
.mm-upload-drop { border:2px dashed #d1d5db; padding:2rem 1rem; text-align:center; cursor:pointer; transition:border-color .15s,background .15s; background:#fafafa; }
.mm-upload-drop:hover { border-color:#2563eb; background:#eff6ff; }
.mm-upload-drop svg { display:block; margin:0 auto .6rem; }
.mm-upload-drop__text { font-size:.875rem; color:#374151; margin:0 0 .3rem; }
.mm-upload-drop__hint { font-size:.78rem; color:#9ca3af; margin:0; }
.mm-upload-drop__hint code { font-family:ui-monospace,monospace; }
.mm-upload-browse { color:#2563eb; cursor:pointer; text-decoration:underline; }
.mm-upload-err { color:#dc2626; font-size:.82rem; margin:.5rem 0 0; }
.mm-upload-actions { display:flex; gap:.5rem; margin-top:.85rem; }
.mm-notice { display:flex; align-items:flex-start; gap:.55rem; padding:.7rem 1rem; font-size:.875rem; margin-bottom:1rem; line-height:1.45; }
.mm-notice svg { flex-shrink:0; margin-top:.15rem; }
.mm-notice--ok   { background:#f0fdf4; border:1px solid #bbf7d0; color:#15803d; }
.mm-notice--err  { background:#fef2f2; border:1px solid #fecaca; color:#dc2626; }
.mm-notice--warn { background:#fffbeb; border:1px solid #fde68a; color:#92400e; }
.mm-tabs { display:flex; margin-bottom:0; border-bottom:2px solid #e5e7eb; }
.mm-tab { font-size:.82rem; font-weight:500; color:#6b7280; padding:.55rem .9rem; text-decoration:none; border-bottom:2px solid transparent; margin-bottom:-2px; transition:color .15s,border-color .15s; white-space:nowrap; cursor:pointer; }
.mm-tab:hover { color:#374151; }
.mm-tab--active { color:#2563eb; border-bottom-color:#2563eb; font-weight:600; }
.mm-tab__count { display:inline-flex; align-items:center; justify-content:center; background:#f3f4f6; color:#374151; font-size:.68rem; font-weight:700; min-width:18px; height:18px; border-radius:9999px; padding:0 .3rem; margin-left:.35rem; }
.mm-tab__count--warn { background:#fef3c7; color:#92400e; }
.mm-table-wrap { border:1px solid #e5e7eb; border-radius:0 0 10px 10px; overflow:hidden; border-top:none; }
.mm-table { width:100%; border-collapse:collapse; font-size:.858rem; }
.mm-table thead tr { background:#f8fafc; }
.mm-table th { padding:.6rem .9rem; text-align:left; font-weight:600; color:#4b5563; font-size:.78rem; text-transform:uppercase; letter-spacing:.04em; border-bottom:1px solid #e5e7eb; white-space:nowrap; }
.mm-table td { padding:.85rem .9rem; vertical-align:top; border-bottom:1px solid #f3f4f6; }
.mm-table tbody tr:last-child td { border-bottom:none; }
.mm-th-module { width:210px; }

/* Regular row states */
.mm-row--on td  { background:#f0fdf4; }
.mm-row--on:hover td { background:#ecfdf5; }
.mm-row--upd td { background:#fffbeb; }
.mm-row--upd:hover td { background:#fef9e7; }
.mm-row:not(.mm-row--on):not(.mm-row--upd):not(.mm-row--core):hover td { background:#fafafa; }
.mm-row--on  td:first-child { box-shadow:inset 4px 0 0 #22c55e; }
.mm-row--upd td:first-child { box-shadow:inset 4px 0 0 #f59e0b; }

/* Core row — special treatment */
.mm-row--core td { background:#f0f4ff; }
.mm-row--core:hover td { background:#e8eeff; }
.mm-row--core td:first-child { box-shadow:inset 4px 0 0 #003366; }
.mm-core-lock { font-size:14px; flex-shrink:0; }
.mm-pill--core { background:#dbeafe; color:#1e40af; font-size:.68rem; font-weight:700; padding:.15rem .55rem; border-radius:999px; border:1px solid #bfdbfe; }
.mm-act--locked { color:#9ca3af !important; cursor:default; font-size:.75rem; }
.mm-act--locked:hover { text-decoration:none !important; }

/* Core components */
.mm-core-components { display:flex; flex-wrap:wrap; gap:.3rem; margin-top:.55rem; }
.mm-core-component { font-size:.7rem; padding:.15rem .45rem; background:#e0e7ff; border:1px solid #c7d2fe; color:#3730a3; border-radius:4px; font-family:ui-monospace,monospace; }

/* Module name cell */
.mm-mod-name { font-weight:600; font-size:.9rem; color:#111827; display:flex; align-items:center; gap:.4rem; flex-wrap:wrap; }
.mm-mod-alias { font-family:ui-monospace,monospace; font-size:.7rem; color:#9ca3af; margin:.1rem 0 .45rem; }
.mm-actions { display:flex; align-items:center; gap:.25rem; flex-wrap:wrap; }
.mm-act-form { display:inline; }
.mm-act-sep { color:#e5e7eb; font-size:.75rem; user-select:none; }
.mm-act { background:none; border:none; padding:0; cursor:pointer; font-size:.78rem; font-family:inherit; color:#2563eb; text-decoration:none; transition:color .12s; }
.mm-act:hover { color:#1d4ed8; text-decoration:underline; }
.mm-act--activate    { color:#16a34a; font-weight:600; }
.mm-act--activate:hover { color:#15803d; }
.mm-act--deactivate  { color:#6b7280; }
.mm-act--deactivate:hover { color:#374151; }
.mm-act--update      { color:#d97706; font-weight:600; }
.mm-act--update:hover { color:#b45309; }
.mm-act--delete      { color:#dc2626; }
.mm-act--delete:hover { color:#b91c1c; }
.mm-cl { margin-top:.55rem; border-top:1px solid #e5e7eb; padding-top:.5rem; }
.mm-cl__row { display:flex; gap:.65rem; padding:.2rem 0; font-size:.78rem; }
.mm-cl__ver { font-family:ui-monospace,monospace; color:#2563eb; flex-shrink:0; min-width:44px; }
.mm-cl__note { color:#6b7280; }
.mm-td-desc { color:#4b5563; line-height:1.5; }
.mm-upd-line { display:flex; align-items:center; gap:.35rem; margin-top:.4rem; font-size:.78rem; color:#92400e; background:#fef3c7; border:1px solid #fde68a; padding:.25rem .55rem; }
.mm-td-ver { white-space:nowrap; }
.mm-ver { font-family:ui-monospace,monospace; font-size:.78rem; background:#eff6ff; border:1px solid #bfdbfe; color:#2563eb; padding:.15rem .45rem; }
.mm-req { font-size:.72rem; color:#9ca3af; margin-top:.3rem; font-family:ui-monospace,monospace; }
.mm-td-author { color:#374151; font-size:.83rem; }
.mm-alink { color:#2563eb; text-decoration:none; }
.mm-alink:hover { text-decoration:underline; }
.mm-lic { font-size:.72rem; color:#9ca3af; margin-top:.2rem; }
.mm-tag { display:inline-block; font-size:.7rem; font-family:ui-monospace,monospace; background:#f3f4f6; border:1px solid #e5e7eb; color:#374151; padding:.15rem .45rem; margin:.1rem .15rem 0 0; }
.mm-pill { font-size:.68rem; font-weight:600; padding:.15rem .5rem; border-radius:999px; }
.mm-pill--on  { background:#dcfce7; color:#15803d; }
.mm-pill--off { background:#f3f4f6; color:#6b7280; }
.mm-pill--upd { background:#fef3c7; color:#92400e; }
.mm-hidden { display:none !important; }
.mm-footnote { font-size:.78rem; color:#9ca3af; text-align:right; margin:.6rem 0 0; }
.mm-footnote code { font-family:ui-monospace,monospace; }
.mm-empty { display:flex; flex-direction:column; align-items:center; gap:.4rem; padding:3.5rem 2rem; color:#9ca3af; border:2px dashed #e5e7eb; text-align:center; margin-top:-2px; }
</style>

<script>
document.querySelectorAll('.mm-tab').forEach(tab => {
    tab.addEventListener('click', e => {
        e.preventDefault();
        document.querySelectorAll('.mm-tab').forEach(t => t.classList.remove('mm-tab--active'));
        tab.classList.add('mm-tab--active');
        const filter = tab.dataset.filter;
        document.querySelectorAll('.mm-row').forEach(row => {
            if (filter === 'all')      { row.style.display = ''; }
            else if (filter === 'active')   { row.style.display = row.dataset.state === 'active' ? '' : 'none'; }
            else if (filter === 'inactive') { row.style.display = row.dataset.state === 'inactive' ? '' : 'none'; }
            else if (filter === 'update')   { row.style.display = row.dataset.update === 'true' ? '' : 'none'; }
        });
    });
});

const dropZone = document.getElementById('mm-drop-zone');
const fileInput = document.getElementById('mm-file-input');
if (dropZone) {
    ['dragenter','dragover'].forEach(ev => {
        dropZone.addEventListener(ev, e => { e.preventDefault(); dropZone.style.borderColor='#2563eb'; dropZone.style.background='#eff6ff'; });
    });
    ['dragleave','drop'].forEach(ev => {
        dropZone.addEventListener(ev, e => { dropZone.style.borderColor=''; dropZone.style.background=''; });
    });
    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        const file = e.dataTransfer.files[0];
        if (file) {
            const dt = new DataTransfer(); dt.items.add(file); fileInput.files = dt.files;
            document.getElementById('mm-file-name').textContent = file.name;
        }
    });
    dropZone.addEventListener('click', () => fileInput.click());
}
</script>

{{-- ── Module Registry Modal ── --}}
<div id="mm-registry-modal" style="display:none;position:fixed;inset:0;background:rgba(0,15,30,.75);z-index:9999;overflow-y:auto;padding:2rem 1rem;">
    <div style="max-width:1000px;margin:0 auto;background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.3);">
        <div style="background:linear-gradient(135deg,#001f40,#003366);padding:1.5rem 1.75rem;display:flex;align-items:center;justify-content:space-between;">
            <div>
                <div style="font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.2em;color:rgba(255,255,255,.4);margin-bottom:.25rem;">ROCK Module Registry</div>
                <div style="font-size:1.25rem;font-weight:bold;color:#fff;">Browse & Install Modules</div>
            </div>
            <button onclick="closeRegistry()" style="background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);color:#fff;width:32px;height:32px;border-radius:50%;cursor:pointer;font-size:1.1rem;display:flex;align-items:center;justify-content:center;">✕</button>
        </div>

        <div style="padding:1.5rem;background:#f8f9fb;border-bottom:1px solid #dde2e8;display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
            <input type="text" id="mm-reg-search" placeholder="Search modules..." oninput="filterRegistry()"
                   style="padding:.5rem .85rem;border:1px solid #dde2e8;border-radius:5px;font-size:13px;width:250px;outline:none;">
            <div id="mm-reg-count" style="font-size:12px;color:#6b7f96;"></div>
            <div style="margin-left:auto;font-size:11px;color:#6b7f96;">
                Source: <a href="https://github.com/raynet-uk/raynet-cms-modules" target="_blank" style="color:#003366;">raynet-uk/raynet-cms-modules</a>
            </div>
        </div>

        <div id="mm-reg-loading" style="padding:3rem;text-align:center;color:#6b7f96;">
            <div style="font-size:2rem;margin-bottom:.75rem;animation:spin 1s linear infinite;display:inline-block;">⟳</div>
            <div>Loading module registry...</div>
        </div>

        <div id="mm-reg-error" style="display:none;padding:2rem;text-align:center;color:#C8102E;"></div>

        <div id="mm-reg-grid" style="display:none;padding:1.5rem;display:none;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1rem;"></div>

        <div style="padding:1rem 1.5rem;background:#f8f9fb;border-top:1px solid #dde2e8;font-size:11px;color:#9aa3ae;text-align:center;">
            Modules are provided by the ROCK community. Always review module code before installing on production sites.
        </div>
    </div>
</div>

<style>
.mm-btn-secondary{display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1rem;font-size:12px;font-weight:bold;background:#e8eef5;border:1px solid #c5d5e8;color:#003366;border-radius:4px;cursor:pointer;transition:all .15s;font-family:inherit;}
.mm-btn-secondary:hover{background:#d5e3f0;}
.reg-card{background:#fff;border:1px solid #dde2e8;border-radius:8px;overflow:hidden;transition:box-shadow .15s;}
.reg-card:hover{box-shadow:0 4px 16px rgba(0,51,102,.12);}
.reg-card-head{padding:1rem;border-bottom:1px solid #f0f1f3;display:flex;align-items:center;gap:.75rem;}
.reg-card-code{background:#003366;color:#fff;font-size:10px;font-weight:bold;padding:.2rem .5rem;border-radius:3px;letter-spacing:.08em;font-family:monospace;}
.reg-card-name{font-size:14px;font-weight:bold;color:#001f40;}
.reg-card-author{font-size:11px;color:#6b7f96;}
.reg-card-body{padding:1rem;}
.reg-card-desc{font-size:12px;color:#4b5563;line-height:1.5;margin-bottom:.75rem;}
.reg-card-tags{display:flex;gap:.3rem;flex-wrap:wrap;margin-bottom:.75rem;}
.reg-card-tag{background:#f0f4f8;color:#6b7f96;font-size:10px;padding:.15rem .45rem;border-radius:3px;}
.reg-card-foot{padding:.75rem 1rem;background:#f8f9fb;border-top:1px solid #f0f1f3;display:flex;align-items:center;justify-content:space-between;}
.reg-card-version{font-size:11px;color:#9aa3ae;font-family:monospace;}
.reg-install-btn{padding:.4rem .9rem;font-size:12px;font-weight:bold;border:none;border-radius:4px;cursor:pointer;font-family:inherit;transition:all .15s;}
.reg-install-btn.install{background:#003366;color:#fff;}
.reg-install-btn.install:hover{background:#002244;}
.reg-install-btn.installed{background:#eef7f2;color:#1a6b3c;border:1px solid #b8ddc9;cursor:default;}
.reg-install-btn.installing{background:#f0f4f8;color:#6b7f96;cursor:wait;}
@keyframes spin{from{transform:rotate(0)}to{transform:rotate(360deg)}}
</style>

<script>
let registryData = [];

function openRegistry() {
    document.getElementById('mm-registry-modal').style.display = 'block';
    document.body.style.overflow = 'hidden';
    if (registryData.length === 0) loadRegistry();
}

function closeRegistry() {
    document.getElementById('mm-registry-modal').style.display = 'none';
    document.body.style.overflow = '';
}

function loadRegistry() {
    document.getElementById('mm-reg-loading').style.display = 'block';
    document.getElementById('mm-reg-error').style.display = 'none';
    document.getElementById('mm-reg-grid').style.display = 'none';

    fetch('{{ route("admin.modules.browse") }}', {
        headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'}
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) throw new Error(data.error);
        registryData = data.modules || [];
        document.getElementById('mm-reg-loading').style.display = 'none';
        renderRegistry(registryData);
    })
    .catch(e => {
        document.getElementById('mm-reg-loading').style.display = 'none';
        document.getElementById('mm-reg-error').style.display = 'block';
        document.getElementById('mm-reg-error').textContent = '⚠ ' + e.message;
    });
}

function renderRegistry(modules) {
    const grid = document.getElementById('mm-reg-grid');
    document.getElementById('mm-reg-count').textContent = modules.length + ' module' + (modules.length !== 1 ? 's' : '') + ' available';
    grid.style.display = 'grid';
    grid.innerHTML = modules.map(m => `
        <div class="reg-card" data-alias="${m.alias}">
            <div class="reg-card-head">
                <div>
                    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.2rem;">
                        <span class="reg-card-code">${m.system_code || m.alias.toUpperCase()}</span>
                        ${m.installed ? '<span style="background:#eef7f2;color:#1a6b3c;font-size:10px;padding:.15rem .45rem;border-radius:3px;font-weight:bold;">✓ Installed</span>' : ''}
                    </div>
                    <div class="reg-card-name">${m.name}</div>
                    <div class="reg-card-author">by ${m.author || 'RAYNET Liverpool'}</div>
                </div>
            </div>
            <div class="reg-card-body">
                <div class="reg-card-desc">${m.description}</div>
                <div class="reg-card-tags">${(m.tags || []).map(t => `<span class="reg-card-tag">${t}</span>`).join('')}</div>
            </div>
            <div class="reg-card-foot">
                <span class="reg-card-version">v${m.version}</span>
                <div style="display:flex;gap:.4rem;">
                    ${m.docs_url ? `<a href="${m.docs_url}" target="_blank" style="padding:.4rem .7rem;font-size:11px;font-weight:bold;border:1px solid #dde2e8;border-radius:4px;color:#6b7f96;text-decoration:none;">Docs</a>` : ''}
                    <button class="reg-install-btn ${m.installed ? 'installed' : 'install'}"
                            id="reg-btn-${m.alias}"
                            ${m.installed ? 'disabled' : `onclick="installModule('${m.alias}','${m.download_url}',this)"`}>
                        ${m.installed ? '✓ Installed' : '⬇ Install'}
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

function filterRegistry() {
    const q = document.getElementById('mm-reg-search').value.toLowerCase();
    const filtered = registryData.filter(m =>
        m.name.toLowerCase().includes(q) ||
        m.alias.toLowerCase().includes(q) ||
        m.description.toLowerCase().includes(q) ||
        (m.tags || []).some(t => t.includes(q))
    );
    renderRegistry(filtered);
}

function installModule(alias, url, btn) {
    if (!confirm('Install ' + alias + '? It will be downloaded from the ROCK module registry.')) return;
    btn.textContent = '⟳ Installing...';
    btn.className = 'reg-install-btn installing';
    btn.disabled = true;

    const token = document.querySelector('meta[name=csrf-token]')?.content || '';

    fetch('{{ route("admin.modules.install-from-registry") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json',
        },
        body: JSON.stringify({download_url: url, alias: alias})
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) throw new Error(data.error);
        btn.textContent = '✓ Installed';
        btn.className = 'reg-install-btn installed';
        btn.disabled = true;
        // Refresh page after short delay so the module appears in list
        setTimeout(() => location.reload(), 1200);
    })
    .catch(e => {
        btn.textContent = '⬇ Install';
        btn.className = 'reg-install-btn install';
        btn.disabled = false;
        alert('Install failed: ' + e.message);
    });
}

// Close on backdrop click
document.getElementById('mm-registry-modal').addEventListener('click', function(e) {
    if (e.target === this) closeRegistry();
});
</script>
@endsection