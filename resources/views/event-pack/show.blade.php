@extends('layouts.app')
@section('title', $eventSupportPack->event_name)
@section('content')
<style>
:root{--navy:#003366;--red:#C8102E;--grey:#f2f5f9;--grey-mid:#dde2e8;--text:#001f40;--muted:#6b7f96;}
*{box-sizing:border-box;}
.wrap{max-width:1100px;margin:0 auto;padding:1.5rem 1rem 4rem;}
.btn{padding:.5rem 1.1rem;border-radius:999px;font-size:.85rem;font-weight:bold;border:none;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:.35rem;transition:all .15s;}
.btn-primary{background:var(--red);color:#fff;}
.btn-navy{background:#e8eef5;color:var(--navy);}
.btn-green{background:#d1fae5;color:#065f46;}
.btn-amber{background:#fef3c7;color:#92400e;}
.btn-sm{padding:.3rem .75rem;font-size:.78rem;}
.card{background:#fff;border:1px solid var(--grey-mid);border-radius:10px;padding:1.25rem;margin-bottom:1rem;}
.card-title{font-size:.82rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--navy);margin-bottom:.85rem;padding-bottom:.5rem;border-bottom:1px solid var(--grey-mid);}
.info-grid{display:grid;grid-template-columns:1fr 1fr;gap:.4rem .75rem;font-size:.88rem;}
.info-grid dt{color:var(--muted);font-weight:600;}
.info-grid dd{color:var(--text);}
.doc-btns{display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:1.5rem;}
.risk-table{width:100%;border-collapse:collapse;font-size:.83rem;}
.risk-table th{background:var(--navy);color:#fff;padding:.5rem .75rem;font-size:.78rem;}
.risk-table td{padding:.5rem .75rem;border-bottom:1px solid var(--grey-mid);}
.res-Low{background:#d1fae5;color:#065f46;padding:.15rem .45rem;border-radius:3px;font-weight:bold;font-size:.75rem;white-space:nowrap;}
.res-Medium{background:#fef3c7;color:#92400e;padding:.15rem .45rem;border-radius:3px;font-weight:bold;font-size:.75rem;white-space:nowrap;}
.res-High,.res-Red{background:#fee2e2;color:#991b1b;padding:.15rem .45rem;border-radius:3px;font-weight:bold;font-size:.75rem;white-space:nowrap;}
.status-pill{padding:.25rem .7rem;border-radius:4px;font-size:.78rem;font-weight:bold;display:inline-block;}
.timeline{display:flex;flex-direction:column;gap:.5rem;}
.tl-item{display:flex;gap:.75rem;align-items:flex-start;font-size:.82rem;}
.tl-dot{width:10px;height:10px;border-radius:50%;background:var(--navy);flex-shrink:0;margin-top:3px;}
.post-card{background:var(--grey);border:1px solid var(--grey-mid);border-radius:8px;padding:1rem;margin-bottom:.65rem;}
</style>

<div class="wrap">
    {{-- Header --}}
    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem;">
        <div>
            <div style="font-size:.75rem;font-weight:bold;text-transform:uppercase;letter-spacing:.15em;color:var(--red);margin-bottom:.3rem;">Event Support Pack</div>
            <h1 style="font-size:1.75rem;font-weight:800;color:var(--navy);margin-bottom:.25rem;">{{ $eventSupportPack->event_name }}</h1>
            <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;">
                <span style="font-size:.88rem;color:var(--muted);">{{ $eventSupportPack->event_date->format('d M Y') }} · {{ $eventSupportPack->location }}</span>
                @if($eventSupportPack->rag_status)
                <span style="background:{{ $eventSupportPack->ragColour() }};color:#fff;padding:.2rem .65rem;border-radius:999px;font-size:.75rem;font-weight:bold;">{{ strtoupper($eventSupportPack->rag_status) }}</span>
                @endif
                <span class="status-pill" style="background:{{ $eventSupportPack->status === 'approved' ? '#d1fae5' : ($eventSupportPack->status === 'escalated' ? '#fee2e2' : '#fef3c7') }};color:{{ $eventSupportPack->status === 'approved' ? '#065f46' : ($eventSupportPack->status === 'escalated' ? '#991b1b' : '#92400e') }};">{{ $eventSupportPack->statusLabel() }}</span>
            </div>
        </div>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
            <a href="{{ route('event-pack.index') }}" class="btn btn-navy btn-sm">← Back</a>
            <form method="POST" action="{{ route('event-pack.clone', $eventSupportPack) }}" style="display:inline;">
                @csrf <button type="submit" class="btn btn-navy btn-sm">📋 Clone</button>
            </form>
        </div>
    </div>

    @if(session('success'))
    <div style="background:#d1fae5;border-left:3px solid #059669;padding:.75rem 1rem;border-radius:4px;margin-bottom:1rem;font-size:.88rem;color:#065f46;font-weight:bold;">✓ {{ session('success') }}</div>
    @endif

    {{-- RAG Banner --}}
    @if($eventSupportPack->rag_status)
    <div style="background:{{ $eventSupportPack->ragColour() }};color:#fff;padding:1rem 1.5rem;border-radius:8px;margin-bottom:1.5rem;text-align:center;">
        <div style="font-size:.85rem;text-transform:uppercase;letter-spacing:.1em;opacity:.8;font-weight:700;">Overall Event Risk Status</div>
        <div style="font-size:1.4rem;font-weight:900;margin-top:.2rem;">{{ $eventSupportPack->ragLabel() }}</div>
    </div>
    @endif

    @if($eventSupportPack->rag_status === 'red')
    <div style="background:#fee2e2;border-left:4px solid #dc2626;padding:1rem 1.25rem;border-radius:0 6px 6px 0;margin-bottom:1.25rem;font-size:.88rem;color:#991b1b;">
        <strong>⚠ This assessment contains High residual risks.</strong> It requires Group Controller review before approval. PDF will be marked Draft — Not Approved.
    </div>
    @endif

    {{-- Generate documents --}}
    <div class="doc-btns">
        <a href="{{ route('event-pack.pdf', [$eventSupportPack, 'risk']) }}" target="_blank" class="btn btn-primary">📋 Risk Assessment PDF</a>
        <a href="{{ route('event-pack.pdf', [$eventSupportPack, 'operator']) }}" target="_blank" class="btn btn-navy">👥 Operator Brief PDF</a>
        <a href="{{ route('event-pack.pdf', [$eventSupportPack, 'assist']) }}" target="_blank" class="btn btn-navy">🤝 Assistance Request PDF</a>
        <a href="{{ route('event-pack.pdf', [$eventSupportPack, 'joining']) }}" target="_blank" class="btn btn-navy">📍 Joining Instructions PDF</a>
    </div>

    {{-- Approval actions --}}
    @if($eventSupportPack->status === 'draft')
    <div class="card" style="border-left:4px solid #f59e0b;">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;">
            <div style="font-size:.9rem;color:#92400e;font-weight:600;">📤 Ready to submit for review?</div>
            <form method="POST" action="{{ route('event-pack.submit', $eventSupportPack) }}" onsubmit="return confirm('Submit this event pack for review?')">
                @csrf <button type="submit" class="btn btn-primary btn-sm">Submit for Review →</button>
            </form>
        </div>
    </div>
    @elseif($eventSupportPack->status === 'awaiting_review' && auth()->user()->isAdmin())
    <div class="card" style="border-left:4px solid #003366;">
        <div class="card-title">Approval Actions</div>
        @if($eventSupportPack->rag_status === 'red')
        <div style="background:#fee2e2;border-left:4px solid #dc2626;padding:.75rem 1rem;border-radius:0 6px 6px 0;font-size:.85rem;color:#991b1b;margin-bottom:.75rem;">
            <strong>⚠ Red RAG — Group Controller approval required.</strong> This event contains High residual risks.
        </div>
        @endif
        @if($eventSupportPack->rag_status !== 'red')
        <form method="POST" action="{{ route('event-pack.approve', $eventSupportPack) }}" style="display:inline-block;margin-right:.5rem;">
            @csrf
            @if($eventSupportPack->rag_status === 'amber')
            <div style="background:#fffbeb;border:1px solid #fcd34d;padding:.75rem;border-radius:6px;margin-bottom:.75rem;">
                <label style="display:flex;align-items:flex-start;gap:.5rem;cursor:pointer;font-size:.85rem;color:#92400e;">
                    <input type="checkbox" name="confirm_amber" value="1" required style="margin-top:2px;">
                    I confirm that the amber controls have been reviewed and will be included in the event briefing.
                </label>
            </div>
            @endif
            <textarea name="statement" class="input" style="width:100%;padding:.5rem;border:1px solid var(--grey-mid);border-radius:4px;font-size:.85rem;margin-bottom:.5rem;min-height:60px;" placeholder="Approval statement (optional)..."></textarea>
            <button type="submit" class="btn btn-green btn-sm">✓ Approve</button>
        </form>
        @endif
        @if($eventSupportPack->rag_status === 'red' && auth()->user()->hasRole('super-admin'))
        <form method="POST" action="{{ route('event-pack.approve', $eventSupportPack) }}" style="display:inline-block;margin-right:.5rem;">
            @csrf
            <input type="hidden" name="statement" value="Approved by Group Controller following review of High residual risks.">
            <input type="hidden" name="force_red" value="1">
            <button type="submit" class="btn btn-green btn-sm" onclick="return confirm('Approve this RED event as Group Controller?')">✓ Group Controller Approve</button>
        </form>
        @endif
        <form method="POST" action="{{ route('event-pack.escalate', $eventSupportPack) }}" style="display:inline-block;margin-right:.5rem;">
            @csrf
            <input type="hidden" name="comments" value="Escalated to Group Controller for review.">
            <button type="submit" class="btn btn-amber btn-sm" onclick="return confirm('Escalate to Group Controller?')">↑ Escalate</button>
        </form>
        <form method="POST" action="{{ route('event-pack.return', $eventSupportPack) }}" style="display:inline-block;">
            @csrf
            <button type="submit" class="btn btn-sm" style="background:#fee2e2;color:#991b1b;" onclick="return confirm('Return for correction?')">↩ Return</button>
        </form>
    </div>
    @endif

    {{-- Two column layout --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
        {{-- Event Details --}}
        <div class="card">
            <div class="card-title">Event Details</div>
            <dl class="info-grid">
                <dt>Location</dt><dd>{{ $eventSupportPack->location }}</dd>
                <dt>Date</dt><dd>{{ $eventSupportPack->event_date->format('d M Y') }}</dd>
                <dt>Duration</dt><dd>{{ $eventSupportPack->duration_days }} day(s)</dd>
                <dt>Type</dt><dd>{{ $eventSupportPack->event_type ?? '—' }}</dd>
                <dt>Organiser</dt><dd>{{ $eventSupportPack->organiser_name ?? '—' }}</dd>
                <dt>Controller</dt><dd>{{ $eventSupportPack->controller_callsign ? strtoupper($eventSupportPack->controller_callsign) : '—' }}</dd>
                <dt>Start</dt><dd>{{ $eventSupportPack->start_time ?? '—' }}</dd>
                <dt>Finish</dt><dd>{{ $eventSupportPack->finish_time ?? '—' }}</dd>
            </dl>
        </div>

        {{-- Comms Plan --}}
        <div class="card">
            <div class="card-title">Communications Plan</div>
            <dl class="info-grid">
                <dt>Primary Frequency</dt><dd>{{ $eventSupportPack->primary_frequency ?? '—' }}</dd>
                <dt>Secondary Frequency</dt><dd>{{ $eventSupportPack->secondary_frequency ?? '—' }}</dd>
                <dt>Talk-Through</dt><dd>{{ $eventSupportPack->talkthrough_used }}</dd>
                <dt>Control Callsign</dt><dd>{{ $eventSupportPack->control_callsign ?? '—' }}</dd>
                <dt>Event Controller</dt><dd>{{ $eventSupportPack->event_controller ?? '—' }}</dd>
                <dt>Deputy</dt><dd>{{ $eventSupportPack->deputy_controller ?? '—' }}</dd>
                <dt>Call-Round</dt><dd>{{ $eventSupportPack->call_round_interval ?? '—' }}</dd>
                <dt>Fallback</dt><dd>{{ $eventSupportPack->fallback_methods ?? '—' }}</dd>
            </dl>
        </div>
    </div>

    {{-- Operator Posts --}}
    <div class="card">
        <div class="card-title" style="display:flex;align-items:center;justify-content:space-between;">
            <span>Operator Posts ({{ $eventSupportPack->posts->count() }})</span>
            <button type="button" onclick="document.getElementById('addPostForm').classList.toggle('open')" class="btn btn-navy btn-sm">+ Add Post</button>
        </div>
        <div id="addPostForm" style="display:none;background:var(--grey);padding:1rem;border-radius:6px;margin-bottom:1rem;">
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.75rem;">
                <div><label style="font-size:.75rem;font-weight:700;color:var(--navy);text-transform:uppercase;display:block;margin-bottom:.3rem;">Post Name *</label>
                <input type="text" id="newPostName" class="input" style="padding:.45rem .65rem;font-size:.85rem;"></div>
                <div><label style="font-size:.75rem;font-weight:700;color:var(--navy);text-transform:uppercase;display:block;margin-bottom:.3rem;">Tactical Callsign</label>
                <input type="text" id="newPostCallsign" class="input" style="padding:.45rem .65rem;font-size:.85rem;"></div>
                <div><label style="font-size:.75rem;font-weight:700;color:var(--navy);text-transform:uppercase;display:block;margin-bottom:.3rem;">Location</label>
                <input type="text" id="newPostLocation" class="input" style="padding:.45rem .65rem;font-size:.85rem;"></div>
            </div>
            <div style="margin-top:.75rem;display:flex;gap:.5rem;">
                <button onclick="savePost()" class="btn btn-primary btn-sm">Save Post</button>
                <button onclick="document.getElementById('addPostForm').classList.remove('open')" class="btn btn-navy btn-sm">Cancel</button>
            </div>
        </div>
        @forelse($eventSupportPack->posts as $post)
        <div class="post-card">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.35rem;">
                <div style="font-size:.9rem;font-weight:700;color:var(--navy);">
                    @if($post->tactical_callsign)<span style="font-family:monospace;font-size:.85rem;background:#003366;color:#fff;padding:.1rem .4rem;border-radius:3px;margin-right:.5rem;">{{ strtoupper($post->tactical_callsign) }}</span>@endif
                    {{ $post->post_name }}
                </div>
                <form method="POST" action="{{ route('event-pack.posts.destroy', $post) }}" style="display:inline;">
                    @csrf @method('DELETE')
                    <button type="submit" onclick="return confirm('Remove this post?')" style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:.8rem;">✕</button>
                </form>
            </div>
            <div style="font-size:.8rem;color:var(--muted);display:flex;gap:1rem;flex-wrap:wrap;">
                @if($post->location)<span>📍 {{ $post->location }}</span>@endif
                @if($post->grid_ref)<span>Grid: {{ $post->grid_ref }}</span>@endif
                @if($post->what3words)<span>w3w: {{ $post->what3words }}</span>@endif
                <span>Min operators: {{ $post->minimum_operators }}</span>
                @if($post->lone_working_possible)<span style="color:#92400e;">⚠ Lone working possible</span>@endif
                @if($post->remote_post)<span style="color:#dc2626;">⚠ Remote post</span>@endif
            </div>
        </div>
        @empty
        <div style="text-align:center;padding:1.5rem;color:var(--muted);font-size:.88rem;">No posts added yet.</div>
        @endforelse
    </div>

    {{-- Risks --}}
    <div class="card">
        <div class="card-title">Generated Risk Register ({{ $eventSupportPack->risks->count() }} risks)</div>
        @if($eventSupportPack->risks->isEmpty())
        <div style="text-align:center;padding:1.5rem;color:var(--muted);font-size:.88rem;">No risks generated. <a href="{{ route('event-pack.create', ['clone'=>$eventSupportPack->id]) }}" style="color:var(--red);">Regenerate from edit.</a></div>
        @else
        <table class="risk-table">
            <thead><tr><th>Hazard</th><th>Controls</th><th>Likelihood</th><th>Severity</th><th>Residual</th></tr></thead>
            <tbody>
                @foreach($eventSupportPack->risks as $risk)
                <tr>
                    <td><strong>{{ $risk->hazard }}</strong><br><small style="color:var(--muted);">{{ $risk->cause }}</small></td>
                    <td>{{ $risk->controls }}</td>
                    <td>{{ $risk->likelihood }}</td>
                    <td>{{ $risk->severity }}</td>
                    <td><span class="res-{{ $risk->residual }}">{{ $risk->residual }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    {{-- Approval history --}}
    @if($eventSupportPack->approvals->isNotEmpty())
    <div class="card">
        <div class="card-title">Approval History</div>
        <div class="timeline">
            @foreach($eventSupportPack->approvals->sortByDesc('created_at') as $approval)
            <div class="tl-item">
                <div class="tl-dot"></div>
                <div>
                    <div style="font-weight:600;color:var(--navy);">{{ ucfirst(str_replace('_',' ',$approval->status)) }}</div>
                    @if($approval->approver)<div style="color:var(--muted);">{{ $approval->approver->name }} · {{ $approval->created_at->format('d M Y H:i') }}</div>@endif
                    @if($approval->statement)<div style="font-style:italic;color:var(--muted);margin-top:.2rem;">{{ $approval->statement }}</div>@endif
                    @if($approval->comments)<div style="color:var(--muted);margin-top:.2rem;">{{ $approval->comments }}</div>@endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Generated documents --}}
    @if($eventSupportPack->documents->isNotEmpty())
    <div class="card">
        <div class="card-title">Generated Documents</div>
        @foreach($eventSupportPack->documents->sortByDesc('generated_at') as $doc)
        <div style="display:flex;align-items:center;justify-content:space-between;padding:.4rem 0;border-bottom:1px solid var(--grey-mid);font-size:.85rem;">
            <span>{{ $doc->filename }}</span>
            <span style="color:var(--muted);font-size:.78rem;">{{ $doc->generated_at?->format('d M Y H:i') ?? '—' }}</span>
        </div>
        @endforeach
    </div>
    @endif
</div>

<script>
var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
var packId    = {{ $eventSupportPack->id }};

function savePost() {
    var name     = document.getElementById('newPostName').value.trim();
    var callsign = document.getElementById('newPostCallsign').value.trim();
    var location = document.getElementById('newPostLocation').value.trim();
    if (!name) { alert('Post name is required.'); return; }
    fetch('/event-pack/'+packId+'/posts', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'},
        body: JSON.stringify({post_name:name, tactical_callsign:callsign, location:location})
    }).then(r=>r.json()).then(function(d) {
        if (d.success) window.location.reload();
    });
}

document.getElementById('addPostForm').addEventListener('click', function(e){ e.stopPropagation(); });
</script>
@endsection
