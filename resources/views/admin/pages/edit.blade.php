<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit: {{ ucwords(str_replace('-', ' ', $slug)) }} — RAYNET Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/theme/dracula.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/fold/foldgutter.min.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        html,body{height:100%;overflow:hidden;font-family:Arial,'Helvetica Neue',Helvetica,sans-serif;font-size:13px;background:#f4f5f7;color:#111827}
        :root{
            --navy:#003366;--red:#C8102E;--grey:#f4f5f7;--grey-mid:#dde2e8;
            --muted:#6b7f96;--green:#1a6b3c;--green-bg:#eef7f2;
            --shadow:0 2px 8px rgba(0,51,102,.09);
        }

        /* Layout */
        .pe{display:flex;flex-direction:column;height:100vh;overflow:hidden}

        /* Top bar */
        .pe-topbar{background:#fff;border-bottom:1px solid var(--grey-mid);padding:.5rem 1rem;display:flex;align-items:center;gap:.65rem;flex-wrap:wrap;flex-shrink:0;box-shadow:var(--shadow);z-index:10;position:relative}
        .pe-topbar__title{font-weight:bold;font-size:.9rem;color:var(--navy);display:flex;align-items:center;gap:.4rem}
        .pe-topbar__title small{font-weight:normal;font-size:.72rem;color:var(--muted);font-family:ui-monospace,monospace}
        .pe-topbar__right{display:flex;align-items:center;gap:.45rem;margin-left:auto;flex-wrap:wrap}

        /* Buttons */
        .pe-btn{display:inline-flex;align-items:center;gap:.3rem;padding:.38rem .85rem;border:1px solid;font-family:inherit;font-size:11px;font-weight:bold;cursor:pointer;transition:all .12s;text-transform:uppercase;letter-spacing:.05em;text-decoration:none;white-space:nowrap}
        .pe-btn-primary{background:var(--navy);border-color:var(--navy);color:#fff}
        .pe-btn-primary:hover{background:#002244}
        .pe-btn-ghost{background:#fff;border-color:var(--grey-mid);color:var(--muted)}
        .pe-btn-ghost:hover{border-color:var(--navy);color:var(--navy)}
        .pe-btn-green{background:var(--green-bg);border-color:#b8ddc9;color:var(--green)}
        .pe-btn-green:hover{background:#d6ede3}

        /* Mode tabs */
        .pe-tabs{display:flex;border:1px solid var(--grey-mid);overflow:hidden}
        .pe-tab{padding:.35rem .8rem;font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.05em;cursor:pointer;transition:all .12s;background:#fff;border:none;font-family:inherit;color:var(--muted)}
        .pe-tab.active{background:var(--navy);color:#fff}
        .pe-tab:not(.active):hover{background:var(--grey)}

        /* Body */
        .pe-body{flex:1;display:flex;overflow:hidden}
        .pe-pane{flex:1;display:flex;flex-direction:column;overflow:hidden}
        .pe-pane--hidden{display:none!important}

        /* Source editor */
        .pe-source-wrap{flex:1;overflow:hidden;display:flex;flex-direction:column}
        .CodeMirror{height:100%!important;font-size:13px;font-family:ui-monospace,monospace;line-height:1.6}
        .CodeMirror-scroll{height:100%}

        /* Visual editor */
        .pe-visual-wrap{flex:1;overflow-y:auto;padding:1rem;display:flex;flex-direction:column;gap:.85rem}
        .pe-visual-info{background:#fffbeb;border:1px solid #fde68a;border-left:3px solid #f59e0b;padding:.6rem .85rem;font-size:11px;color:#92400e}
        .pe-visual-panel{background:#fff;border:1px solid var(--grey-mid);overflow:hidden;flex:1;display:flex;flex-direction:column;min-height:500px}
        .pe-visual-panel-head{padding:.5rem 1rem;background:var(--grey);border-bottom:1px solid var(--grey-mid);font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;color:var(--navy)}
        .pe-visual-panel-body{flex:1;overflow:hidden}
        .ql-container{border:none!important;height:100%;font-size:14px}
        .ql-toolbar{border:none!important;border-bottom:1px solid var(--grey-mid)!important;background:var(--grey)}
        .ql-editor{min-height:400px;overflow-y:auto;font-family:inherit;line-height:1.6}

        /* Preview */
        .pe-preview-pane{width:420px;border-left:1px solid var(--grey-mid);display:flex;flex-direction:column;flex-shrink:0}
        .pe-preview-pane--hidden{display:none}
        .pe-preview-head{padding:.45rem .85rem;background:var(--grey);border-bottom:1px solid var(--grey-mid);font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;color:var(--navy);display:flex;align-items:center;justify-content:space-between;flex-shrink:0}
        .pe-preview-iframe{flex:1;border:none}

        /* Status bar */
        .pe-statusbar{padding:.3rem 1rem;background:#fff;border-top:1px solid var(--grey-mid);display:flex;align-items:center;gap:1rem;font-size:.72rem;color:var(--muted);flex-shrink:0}

        /* Notices */
        .pe-notice{padding:.55rem 1rem;font-size:12px;font-weight:bold;display:flex;align-items:center;gap:.5rem;flex-shrink:0}
        .pe-notice--ok {background:var(--green-bg);border-bottom:1px solid #b8ddc9;color:var(--green)}
        .pe-notice--err{background:#fdf0f2;border-bottom:1px solid rgba(200,16,46,.2);color:var(--red)}

        /* Complex banner */
        .pe-complex-banner{background:#fffbeb;border-bottom:1px solid #fde68a;padding:.5rem 1rem;font-size:12px;color:#92400e;display:flex;align-items:center;gap:.5rem;flex-shrink:0}

        /* Backup panel */
        .pe-backup-panel{display:none;position:absolute;top:50px;right:0;width:360px;background:#fff;border:1px solid var(--grey-mid);border-top:3px solid var(--navy);box-shadow:0 8px 24px rgba(0,51,102,.15);z-index:200}
        .pe-backup-panel.open{display:block}
        .pe-backup-head{padding:.55rem .9rem;background:var(--grey);border-bottom:1px solid var(--grey-mid);font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;color:var(--navy);display:flex;justify-content:space-between;align-items:center}
        .pe-backup-list{max-height:280px;overflow-y:auto}
        .pe-backup-item{display:flex;align-items:center;justify-content:space-between;padding:.55rem .9rem;border-bottom:1px solid #f3f4f6;gap:.5rem}
        .pe-backup-item:last-child{border-bottom:none}
        .pe-backup-item__date{font-weight:bold;color:var(--text);font-size:.78rem}
        .pe-backup-item__ago{color:var(--muted);font-size:.72rem}
        .pe-backup-empty{padding:1.5rem;text-align:center;font-size:.82rem;color:var(--muted)}

        @media(max-width:900px){.pe-preview-pane{display:none!important}}
    </style>
</head>
<body>
<div class="pe">

    {{-- Top bar --}}
    <div class="pe-topbar">
        <div class="pe-topbar__title">
            <a href="{{ route('admin.pages.index') }}" class="pe-btn pe-btn-ghost" style="padding:.28rem .55rem">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
            </a>
            {{ ucwords(str_replace('-', ' ', $slug)) }}
            <small>{{ $slug }}.blade.php &nbsp;·&nbsp; {{ $size }} &nbsp;·&nbsp; {{ $modified->format('d M Y H:i') }}</small>
        </div>

        <div class="pe-topbar__right">
            @if(!$isComplex && $visualContentB64 !== null)
            <div class="pe-tabs">
                <button class="pe-tab active" id="tab-visual" onclick="switchMode('visual')">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    Visual
                </button>
                <button class="pe-tab" id="tab-source" onclick="switchMode('source')">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                    Source
                </button>
            </div>
            @endif

            @if($url)
            <button class="pe-btn pe-btn-ghost" onclick="togglePreview()" id="previewToggleBtn">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                Preview
            </button>
            @endif

            <div style="position:relative">
                <button class="pe-btn pe-btn-ghost" id="backupToggleBtn" onclick="toggleBackupPanel()">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3"/></svg>
                    Backups @if(count($backups) > 0)<span style="background:var(--navy);color:#fff;font-size:9px;font-weight:bold;min-width:16px;height:14px;border-radius:8px;padding:0 3px;display:inline-flex;align-items:center;justify-content:center;margin-left:.25rem">{{ count($backups) }}</span>@endif
                </button>

                <div class="pe-backup-panel" id="backupPanel">
                    <div class="pe-backup-head">
                        <span>Backups — {{ $slug }}.blade.php</span>
                        <button onclick="toggleBackupPanel()" style="background:none;border:none;cursor:pointer;color:var(--muted);font-size:16px;line-height:1;padding:0">×</button>
                    </div>
                    <div class="pe-backup-list">
                        @forelse($backups as $bk)
                        <div class="pe-backup-item">
                            <div>
                                <div class="pe-backup-item__date">{{ $bk['date'] }}</div>
                                <div class="pe-backup-item__ago">{{ $bk['ago'] }} · {{ $bk['size'] }}</div>
                            </div>
                            <form method="POST" action="{{ route('admin.pages.restore', $slug) }}" onsubmit="return confirm('Restore this backup? Current file will be backed up first.')">
                                @csrf
                                <input type="hidden" name="backup" value="{{ $bk['filename'] }}">
                                <button class="pe-btn pe-btn-green" style="font-size:10px;padding:.25rem .6rem">↩ Restore</button>
                            </form>
                        </div>
                        @empty
                        <div class="pe-backup-empty">No backups yet — one is created every save.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <button class="pe-btn pe-btn-ghost" onclick="document.getElementById('urlPanel').style.display='flex'" title="Change page URL">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                URL Settings
            </button>
            <button class="pe-btn pe-btn-primary" onclick="savePage()" id="saveBtn">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save  <small style="font-weight:normal;text-transform:none;letter-spacing:0;opacity:.7">Ctrl+S</small>
            </button>
        </div>
    </div>

    {{-- Notices --}}
    @if(session('success'))
    <div class="pe-notice pe-notice--ok">✓ {{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div class="pe-notice pe-notice--err">⚠ {{ $errors->first() }}</div>
    @endif

    @if($isComplex)
    <div class="pe-complex-banner">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        This page contains complex PHP/JS logic — source editing only. Take care when editing.
    </div>
    @endif

    {{-- Editor body --}}
    <div class="pe-body">

        {{-- Visual pane --}}
        @if(!$isComplex && $visualContentB64 !== null)
        <div class="pe-pane" id="pane-visual">
            <div class="pe-visual-wrap">
                <div class="pe-visual-info">
                    <strong>Visual editor</strong> — editing the main content section of this page.
                    Blade directives are preserved but not shown. Switch to <strong>Source</strong> to edit the full file including CSS, PHP and Blade directives.
                </div>
                <div class="pe-visual-panel">
                    <div class="pe-visual-panel-head">Content — {{ ucwords(str_replace('-',' ',$slug)) }}</div>
                    <div class="pe-visual-panel-body">
                        <div id="quillEditor"></div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Source pane --}}
        <div class="pe-pane {{ (!$isComplex && $visualContentB64 !== null) ? 'pe-pane--hidden' : '' }}" id="pane-source">
            <div class="pe-source-wrap">
                <textarea id="cmEditor">{{ $raw }}</textarea>
            </div>
        </div>

        {{-- Preview pane --}}
        @if($url)
        <div class="pe-preview-pane pe-preview-pane--hidden" id="previewPane">
            <div class="pe-preview-head">
                Live Preview
                <a href="{{ $url }}" target="_blank" style="color:var(--navy);font-size:10px;text-decoration:none">Open ↗</a>
            </div>
            <iframe class="pe-preview-iframe" src="{{ $url }}" id="previewFrame"></iframe>
        </div>
        @endif

    </div>

    {{-- Status bar --}}
    <div class="pe-statusbar">
        <span id="editorModeLabel">{{ ($isComplex || $visualContentB64 === null) ? 'Source' : 'Visual' }} mode</span>
        <span id="cursorPos">Line 1, Col 1</span>
        <span>{{ $slug }}.blade.php</span>
        <span style="margin-left:auto">Last saved: {{ $modified->format('d M Y H:i') }}</span>
    </div>

</div>

{{-- Hidden save form --}}
<form id="saveForm" method="POST" action="{{ route('admin.pages.update', $slug) }}" style="display:none">
    @csrf
    @method('PUT')
    <input type="hidden" name="content" id="saveContent">
    <input type="hidden" name="mode" id="saveMode" value="{{ (!$isComplex && $visualContentB64 !== null) ? 'visual' : 'source' }}">
</form>

{{-- Scripts --}}
{{-- ── URL Settings Modal ── --}}
<div id="urlPanel" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center">
    <div style="background:#fff;width:100%;max-width:480px;box-shadow:0 8px 32px rgba(0,0,0,.2);overflow:hidden">
        <div style="background:#003366;padding:.85rem 1.1rem;display:flex;align-items:center;justify-content:space-between">
            <span style="font-size:13px;font-weight:bold;color:#fff">🌐 URL Settings — {{ $slug }}.blade.php</span>
            <button onclick="document.getElementById('urlPanel').style.display='none'" style="background:none;border:none;color:rgba(255,255,255,.6);cursor:pointer;font-size:18px;line-height:1">×</button>
        </div>

        @php
            $currentUrl = null;
            $routesContent = file_get_contents(base_path('routes/web.php'));
            $lines2 = explode(chr(10), $routesContent);
            foreach ($lines2 as $line2) {
                if (strpos($line2, "pages.{$slug}") !== false) {
                    if (preg_match(chr(47).chr(39)."([^".chr(39)."]+)".chr(39).chr(47), $line2, $rm2)) {
                        $currentUrl = $rm2[1];
                        break;
                    }
                }
            }
        @endphp

        @if($currentUrl)
        <div style="padding:.65rem 1.1rem;background:#eef7f2;border-bottom:1px solid #b8ddc9;font-size:12px;color:#1a6b3c;font-weight:bold">
            ✓ Currently live at <code>{{ $currentUrl }}</code>
        </div>
        @else
        <div style="padding:.65rem 1.1rem;background:#fffbeb;border-bottom:1px solid #fde68a;font-size:12px;color:#92400e;font-weight:bold">
            ⚠ This page has no public route yet — it won't be accessible until you set one below.
        </div>
        @endif

        @if(session('url_error'))
        <div style="padding:.65rem 1.1rem;background:#fdf0f2;border-bottom:1px solid rgba(200,16,46,.2);font-size:12px;color:#C8102E;font-weight:bold">
            ⚠ {{ session('url_error') }}
        </div>
        @endif

        <form method="POST" action="{{ route('admin.pages.rename', $slug) }}">
            @csrf
            <div style="padding:1.25rem 1.1rem;display:flex;flex-direction:column;gap:.9rem">

                <div>
                    <label style="display:block;font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.09em;color:#6b7f96;margin-bottom:.3rem">
                        Public URL <span style="font-weight:normal;text-transform:none;letter-spacing:0">(what visitors type in the browser)</span>
                    </label>
                    <div style="display:flex;align-items:center;border:1px solid #dde2e8;background:#fff;overflow:hidden">
                        <span style="padding:.45rem .65rem;background:#f4f5f7;border-right:1px solid #dde2e8;font-size:12px;color:#6b7f96;white-space:nowrap">{{ request()->getSchemeAndHttpHost() }}</span>
                        <input type="text" name="new_url" id="urlInput"
                               value="{{ $currentUrl ?? '/' . $slug }}"
                               style="flex:1;border:none;padding:.45rem .65rem;font-size:13px;font-family:ui-monospace,monospace;outline:none"
                               placeholder="/about"
                               oninput="syncSlug(this.value)">
                    </div>
                    <div style="font-size:11px;color:#9aa3ae;margin-top:.25rem">Use lowercase letters, numbers and hyphens. Start with /</div>
                </div>

                <div>
                    <label style="display:block;font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.09em;color:#6b7f96;margin-bottom:.3rem">
                        File Slug <span style="font-weight:normal;text-transform:none;letter-spacing:0">(renames the .blade.php file)</span>
                    </label>
                    <div style="display:flex;align-items:center;border:1px solid #dde2e8;background:#fff;overflow:hidden">
                        <span style="padding:.45rem .65rem;background:#f4f5f7;border-right:1px solid #dde2e8;font-size:12px;color:#6b7f96;white-space:nowrap">resources/views/pages/</span>
                        <input type="text" name="new_slug" id="slugInput"
                               value="{{ $slug }}"
                               style="flex:1;border:none;padding:.45rem .65rem;font-size:13px;font-family:ui-monospace,monospace;outline:none">
                        <span style="padding:.45rem .65rem;background:#f4f5f7;border-left:1px solid #dde2e8;font-size:12px;color:#6b7f96">.blade.php</span>
                    </div>
                    <div style="font-size:11px;color:#9aa3ae;margin-top:.25rem">Changing the slug renames the file. The URL and slug can differ.</div>
                </div>

                <div style="background:#f4f5f7;border:1px solid #dde2e8;padding:.65rem .9rem;font-size:11px;color:#6b7f96;line-height:1.6">
                    <strong style="color:#003366">What happens when you save:</strong><br>
                    • <code>routes/web.php</code> is updated with the new URL (backed up first)<br>
                    • If the slug changes, the blade file is renamed<br>
                    • The old URL will stop working immediately
                </div>
            </div>

            <div style="padding:.75rem 1.1rem;background:#f4f5f7;border-top:1px solid #dde2e8;display:flex;align-items:center;justify-content:space-between;gap:.65rem">
                <button type="button" onclick="document.getElementById('urlPanel').style.display='none'" class="pe-btn pe-btn-ghost">Cancel</button>
                <button type="submit" class="pe-btn pe-btn-primary">✓ Update URL &amp; Route</button>
            </div>
        </form>
    </div>
</div>

<script>
function syncSlug(url) {
    // Auto-suggest slug from URL (strip leading slash, convert / to -)
    const slug = url.replace(/^\//, '').replace(/\//g, '-').replace(/[^a-z0-9\-]/gi, '').toLowerCase();
    if (slug) document.getElementById('slugInput').value = slug;
}
// Close on backdrop click
document.getElementById('urlPanel')?.addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
});
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/xml/xml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/javascript/javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/css/css.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/htmlmixed/htmlmixed.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/php/php.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/edit/matchbrackets.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/edit/closebrackets.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/fold/foldcode.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/fold/foldgutter.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/fold/brace-fold.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/fold/xml-fold.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/comment/comment.min.js"></script>
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

<script>
// ── CodeMirror ───────────────────────────────────────────────────────
const cm = CodeMirror.fromTextArea(document.getElementById('cmEditor'), {
    mode:           'htmlmixed',
    theme:          'dracula',
    lineNumbers:    true,
    lineWrapping:   false,
    matchBrackets:  true,
    autoCloseBrackets: true,
    foldGutter:     true,
    gutters:        ['CodeMirror-linenumbers', 'CodeMirror-foldgutter'],
    tabSize:        4,
    indentWithTabs: false,
    extraKeys: {
        'Ctrl-S': savePage,
        'Cmd-S':  savePage,
        'Ctrl-/': cm => cm.execCommand('toggleComment'),
        'Cmd-/':  cm => cm.execCommand('toggleComment'),
    },
});
cm.on('cursorActivity', () => {
    const c = cm.getCursor();
    document.getElementById('cursorPos').textContent = `Line ${c.line+1}, Col ${c.ch+1}`;
});
function refreshCM() {
    const pane = document.getElementById('pane-source');
    if (pane && !pane.classList.contains('pe-pane--hidden')) {
        const wrap = pane.querySelector('.pe-source-wrap');
        if (wrap) { cm.getWrapperElement().style.height = wrap.clientHeight + 'px'; cm.refresh(); }
    }
}
window.addEventListener('resize', refreshCM);
setTimeout(refreshCM, 100);

// ── Quill ────────────────────────────────────────────────────────────
let quill = null;
@if(!$isComplex && $visualContentB64 !== null)
quill = new Quill('#quillEditor', {
    theme: 'snow',
    modules: { toolbar: [
        [{ header: [1,2,3,4,false] }],
        ['bold','italic','underline','strike'],
        [{ color: [] }, { background: [] }],
        [{ list: 'ordered' }, { list: 'bullet' }],
        [{ indent: '-1' }, { indent: '+1' }],
        [{ align: [] }],
        ['link','image'],
        ['blockquote','code-block'],
        ['clean'],
    ]},
    placeholder: 'Edit page content here…',
});
// Decode base64 content safely — no Blade directives in this value, it's pure base64
function b64ToUtf8(b) {
    return decodeURIComponent(
        Array.prototype.map.call(atob(b), c => '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2)).join('')
    );
}
const b64 = '{{ $visualContentB64 }}';
if (b64) quill.clipboard.dangerouslyPasteHTML(0, b64ToUtf8(b64));
@endif

// ── Mode switching ────────────────────────────────────────────────────
let currentMode = '{{ (!$isComplex && $visualContentB64 !== null) ? "visual" : "source" }}';

function switchMode(mode) {
    currentMode = mode;
    document.getElementById('tab-visual')?.classList.toggle('active', mode === 'visual');
    document.getElementById('tab-source')?.classList.toggle('active', mode === 'source');
    document.getElementById('pane-visual')?.classList.toggle('pe-pane--hidden', mode !== 'visual');
    document.getElementById('pane-source').classList.toggle('pe-pane--hidden', mode !== 'source');
    document.getElementById('saveMode').value = mode;
    document.getElementById('editorModeLabel').textContent = (mode === 'visual' ? 'Visual' : 'Source') + ' mode';
    if (mode === 'source') setTimeout(refreshCM, 50);
}

// ── Preview ────────────────────────────────────────────────────────────
let previewOpen = false;
function togglePreview() {
    previewOpen = !previewOpen;
    document.getElementById('previewPane')?.classList.toggle('pe-preview-pane--hidden', !previewOpen);
    document.getElementById('previewToggleBtn').style.background = previewOpen ? '#e8eef5' : '';
    setTimeout(refreshCM, 50);
}

// ── Backup panel ──────────────────────────────────────────────────────
function toggleBackupPanel() {
    document.getElementById('backupPanel').classList.toggle('open');
}
document.addEventListener('click', e => {
    const p = document.getElementById('backupPanel');
    const b = document.getElementById('backupToggleBtn');
    if (p?.classList.contains('open') && !p.contains(e.target) && !b.contains(e.target)) {
        p.classList.remove('open');
    }
});

// ── Save ──────────────────────────────────────────────────────────────
function savePage() {
    const btn = document.getElementById('saveBtn');
    btn.textContent = 'Saving…';
    btn.disabled = true;

    let content;
    if (currentMode === 'visual' && quill) {
        content = quill.root.innerHTML;
    } else {
        cm.save();
        content = cm.getValue();
    }

    document.getElementById('saveContent').value = content;
    document.getElementById('saveMode').value = currentMode;
    document.getElementById('saveForm').submit();
}

document.addEventListener('keydown', e => {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); savePage(); }
});

let dirty = false;
cm.on('change', () => dirty = true);
if (quill) quill.on('text-change', () => dirty = true);
window.addEventListener('beforeunload', e => { if (dirty) { e.preventDefault(); e.returnValue = ''; } });
document.getElementById('saveForm').addEventListener('submit', () => dirty = false);
</script>
</body>
</html>