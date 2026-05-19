{{--
    admin/partials/telegram_perms_card.blade.php
    ─────────────────────────────────────────────
    Mobile-friendly card layout.
    Shows ALL users with a telegram_chat_id linked.
    Admins / super-admins shown as locked full-access (no checkboxes).
    Regular members get per-command toggles (deny-list saved to telegram_permissions).
--}}

@php
    use App\Http\Controllers\TelegramWebhookController;

    $allCommands  = TelegramWebhookController::availableCommands();
    $memberCmds   = array_filter($allCommands, fn($c) => $c['group'] !== 'Admin');
    $telegramUsers = \Illuminate\Support\Facades\DB::table('users')
                        ->whereNotNull('telegram_chat_id')
                        ->where('telegram_chat_id', '!=', '')
                        ->orderByRaw('is_super_admin DESC, is_admin DESC, name ASC')
                        ->get();
@endphp

<style>
.tp-wrap { display:flex; flex-direction:column; gap:.75rem; padding:1rem; }

.tp-card { border:1px solid #dde2e8; background:#fff; overflow:hidden; }

.tp-card-head {
    display:flex; align-items:center; justify-content:space-between;
    padding:.6rem .9rem; background:#f4f5f7; border-bottom:1px solid #dde2e8;
    gap:.5rem; flex-wrap:wrap;
}
.tp-card-name  { font-size:13px; font-weight:bold; color:#111827; line-height:1.2; }
.tp-card-meta  { font-size:10.5px; color:#9aa3ae; font-family:monospace; margin-top:.1rem; }

.tp-card-badge { font-size:10px; font-weight:bold; text-transform:uppercase;
                 letter-spacing:.08em; padding:.2rem .55rem; white-space:nowrap; }
.tp-badge-admin   { background:#e8eef6; color:#003366; border:1px solid #b8cce0; }
.tp-badge-full    { background:#eef7f2; color:#1a6b3c; border:1px solid #b8ddc9; }
.tp-badge-limited { background:#fdf6ec; color:#92400e; border:1px solid #f6d8a8; }

.tp-grid {
    display:grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap:.5rem; padding:.8rem .9rem;
}
.tp-toggle {
    display:flex; align-items:center; gap:.45rem;
    padding:.4rem .6rem; border:1px solid #dde2e8; background:#fff;
    cursor:pointer; user-select:none;
    transition:background .1s, border-color .1s;
    -webkit-tap-highlight-color:transparent;
}
.tp-toggle.is-denied { background:#fdf0f2; border-color:rgba(200,16,46,.25); }
.tp-toggle input[type=checkbox] {
    width:14px; height:14px; flex-shrink:0;
    accent-color:#003366; cursor:pointer; margin:0;
}
.tp-toggle-label {
    font-size:11px; font-weight:bold; color:#374151;
    line-height:1.2; pointer-events:none;
}
.tp-toggle.is-denied .tp-toggle-label { color:#C8102E; }

.tp-card-foot {
    padding:.55rem .9rem; border-top:1px solid #dde2e8; background:#f4f5f7;
    display:flex; align-items:center; justify-content:flex-end; gap:.6rem;
}
.tp-status-text { font-size:11px; color:#9aa3ae; flex:1; }

.tp-admin-cmds { padding:.7rem .9rem; display:flex; flex-wrap:wrap; gap:.35rem; }
.tp-admin-pill {
    font-size:10px; font-weight:bold; padding:.2rem .55rem;
    background:#f4f5f7; border:1px solid #dde2e8; color:#9aa3ae; letter-spacing:.04em;
}
.tp-empty { padding:1.5rem; text-align:center; font-size:13px; color:#9aa3ae; }
</style>

<div class="as-card" style="margin-top:0">
    <div class="as-card-head">
        <h2>🔐 Member Telegram Permissions</h2>
    </div>

    @if($telegramUsers->isEmpty())
        <div class="tp-empty">No members have linked a Telegram Chat ID yet.</div>
    @else
        <div class="tp-wrap">
            @foreach($telegramUsers as $tUser)
                @php
                    $isPrivileged = $tUser->is_admin || $tUser->is_super_admin;
                    $denied       = json_decode($tUser->telegram_permissions ?? '[]', true) ?? [];
                    $deniedCount  = count($denied);
                    $role         = $tUser->is_super_admin ? 'Super Admin' : ($tUser->is_admin ? 'Admin' : null);
                @endphp

                <div class="tp-card" id="tpcard-{{ $tUser->id }}">

                    <div class="tp-card-head">
                        <div>
                            <div class="tp-card-name">{{ $tUser->name }}</div>
                            <div class="tp-card-meta">
                                {{ $tUser->callsign ?? '—' }}
                                &nbsp;·&nbsp;
                                <span style="opacity:.7">{{ $tUser->telegram_chat_id }}</span>
                            </div>
                        </div>
                        @if($isPrivileged)
                            <span class="tp-card-badge tp-badge-admin">{{ $role }}</span>
                        @elseif($deniedCount > 0)
                            <span class="tp-card-badge tp-badge-limited" id="tpbadge-{{ $tUser->id }}">
                                {{ $deniedCount }} restricted
                            </span>
                        @else
                            <span class="tp-card-badge tp-badge-full" id="tpbadge-{{ $tUser->id }}">
                                Full access
                            </span>
                        @endif
                    </div>

                    @if($isPrivileged)
                        <div class="tp-admin-cmds">
                            @foreach($memberCmds as $cmdKey => $cmdMeta)
                                <span class="tp-admin-pill">{{ $cmdMeta['label'] }}</span>
                            @endforeach
                        </div>
                        <div class="tp-card-foot">
                            <span class="tp-status-text">Admins always have full access — permissions cannot be restricted.</span>
                        </div>
                    @else
                        <div class="tp-grid">
                            @foreach($memberCmds as $cmdKey => $cmdMeta)
                                @php $isDenied = in_array($cmdKey, $denied); @endphp
                                <label class="tp-toggle {{ $isDenied ? 'is-denied' : '' }}"
                                       id="tptoggle-{{ $tUser->id }}-{{ $cmdKey }}">
                                    <input type="checkbox"
                                           class="tp-check"
                                           data-user="{{ $tUser->id }}"
                                           data-cmd="{{ $cmdKey }}"
                                           {{ !$isDenied ? 'checked' : '' }}
                                           onchange="onToggleChange(this)">
                                    <span class="tp-toggle-label">{{ $cmdMeta['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                        <div class="tp-card-foot">
                            <span class="tp-status-text" id="tpstatus-{{ $tUser->id }}">
                                {{ $deniedCount > 0 ? $deniedCount . ' command(s) restricted' : 'No restrictions' }}
                            </span>
                            <button type="button"
                                    class="as-btn as-btn-primary tp-save-btn"
                                    data-user="{{ $tUser->id }}"
                                    style="font-size:11px;padding:.35rem .9rem"
                                    onclick="savePermissions({{ $tUser->id }})">
                                Save
                            </button>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div style="padding:.6rem 1rem;border-top:1px solid #dde2e8;background:#f4f5f7">
            <span class="as-hint">✅ ticked = allowed &nbsp;·&nbsp; ☐ unticked = blocked for that member</span>
        </div>
    @endif
</div>

<script>
function onToggleChange(cb) {
    var label = cb.closest('.tp-toggle');
    if (!cb.checked) {
        label.classList.add('is-denied');
    } else {
        label.classList.remove('is-denied');
    }
}

function savePermissions(userId) {
    var denied = [];
    document.querySelectorAll('.tp-check[data-user="' + userId + '"]').forEach(function(cb) {
        if (!cb.checked) denied.push(cb.dataset.cmd);
    });

    var btn    = document.querySelector('.tp-save-btn[data-user="' + userId + '"]');
    var status = document.getElementById('tpstatus-' + userId);
    var badge  = document.getElementById('tpbadge-' + userId);

    var orig = btn.textContent;
    btn.textContent         = 'Saving…';
    btn.style.opacity       = '.6';
    btn.style.pointerEvents = 'none';

    fetch('{{ route("admin.settings.telegram.permissions") }}', {
        method : 'POST',
        headers: {
            'Content-Type' : 'application/json',
            'X-CSRF-TOKEN' : '{{ csrf_token() }}',
            'Accept'       : 'application/json',
        },
        body: JSON.stringify({ user_id: userId, denied: denied }),
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            btn.textContent = '✓ Saved';
            setTimeout(function() {
                btn.textContent         = orig;
                btn.style.opacity       = '1';
                btn.style.pointerEvents = 'auto';
            }, 2000);
            var count = denied.length;
            if (status) status.textContent = count > 0 ? count + ' command(s) restricted' : 'No restrictions';
            if (badge) {
                if (count > 0) {
                    badge.className   = 'tp-card-badge tp-badge-limited';
                    badge.textContent = count + ' restricted';
                } else {
                    badge.className   = 'tp-card-badge tp-badge-full';
                    badge.textContent = 'Full access';
                }
            }
        } else {
            btn.textContent         = '⚠ Error';
            btn.style.opacity       = '1';
            btn.style.pointerEvents = 'auto';
        }
    })
    .catch(function() {
        btn.textContent         = '⚠ Error';
        btn.style.opacity       = '1';
        btn.style.pointerEvents = 'auto';
    });
}
</script>