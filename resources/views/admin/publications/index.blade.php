@extends('layouts.admin')
@section('title', 'Publications — Admin')
@section('content')
<style>
.wrap{max-width:1000px;margin:2rem auto;padding:0 1.5rem 4rem;}
.card{background:#fff;border:1px solid #dde2e8;border-top:3px solid #003366;padding:1.5rem;margin-bottom:1.5rem;}
.card-title{font-size:12px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:#003366;margin-bottom:1rem;display:flex;align-items:center;justify-content:space-between;}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;}
.form-row.triple{grid-template-columns:1fr 1fr 1fr;}
.field{display:flex;flex-direction:column;gap:.3rem;}
.field label{font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.1em;color:#6b7f96;}
.field input,.field select,.field textarea{padding:.5rem .75rem;border:1px solid #dde2e8;font-size:13px;outline:none;width:100%;}
.btn{padding:.5rem 1.1rem;font-size:12px;font-weight:bold;text-transform:uppercase;letter-spacing:.07em;cursor:pointer;border:1px solid;display:inline-block;text-decoration:none;}
.btn-navy{background:#003366;border-color:#003366;color:#fff;}
.btn-red{background:#C8102E;border-color:#C8102E;color:#fff;}
.btn-outline{background:transparent;border-color:#003366;color:#003366;}
.btn-sm{padding:.3rem .7rem;font-size:11px;}
.alert{padding:.65rem 1rem;margin-bottom:1rem;font-size:13px;font-weight:bold;}
.alert-green{background:#eef7f2;border:1px solid #b8ddc9;border-left:3px solid #1a6b3c;color:#1a6b3c;}
.pub-table{width:100%;border-collapse:collapse;font-size:13px;}
.pub-table th{background:#f4f5f7;padding:.5rem .75rem;text-align:left;font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;color:#6b7f96;border-bottom:2px solid #dde2e8;}
.pub-table td{padding:.6rem .75rem;border-bottom:1px solid #f0f1f3;vertical-align:middle;}
.pub-table tr:hover td{background:#fafbfc;}
.badge-current{background:#eef7f2;border:1px solid #b8ddc9;color:#1a6b3c;font-size:10px;font-weight:bold;padding:2px 7px;text-transform:uppercase;border-radius:2px;}
.badge-archive{background:#f4f5f7;border:1px solid #dde2e8;color:#6b7f96;font-size:10px;font-weight:bold;padding:2px 7px;text-transform:uppercase;border-radius:2px;}
.tab-nav{display:flex;gap:0;margin-bottom:0;border-bottom:2px solid #dde2e8;}
.tab-btn{padding:.6rem 1.25rem;font-size:12px;font-weight:bold;text-transform:uppercase;letter-spacing:.08em;cursor:pointer;background:none;border:none;color:#6b7f96;border-bottom:2px solid transparent;margin-bottom:-2px;}
.tab-btn.active{color:#003366;border-bottom-color:#003366;}
.tab-content{display:none;padding-top:1rem;}
.tab-content.active{display:block;}
</style>

<div class="wrap">
    <h1 style="font-size:22px;font-weight:bold;color:#003366;margin-bottom:.25rem;">📚 Publications</h1>
    <p style="font-size:13px;color:#6b7f96;margin-bottom:1.5rem;">Manage RAYNET News and Checkpoint editions.</p>

    @if(session('status'))
    <div class="alert alert-green">{{ session('status') }}</div>
    @endif

    {{-- Add Publication Form --}}
    <div class="card">
        <div class="card-title">Add New Publication</div>
        <form method="POST" action="{{ route('admin.publications.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-row">
                <div class="field">
                    <label>Type <span style="color:#C8102E">*</span></label>
                    <select name="type" required>
                        <option value="news">📰 RAYNET News</option>
                        <option value="checkpoint">📋 Checkpoint</option>
                    </select>
                </div>
                <div class="field">
                    <label>Edition <span style="color:#6b7f96;font-weight:normal;text-transform:none">(e.g. Spring 2026, Issue 47)</span></label>
                    <input type="text" name="edition" placeholder="e.g. Spring 2026">
                </div>
            </div>
            <div class="form-row">
                <div class="field">
                    <label>Title <span style="color:#C8102E">*</span></label>
                    <input type="text" name="title" required placeholder="e.g. RAYNET News Spring 2026">
                </div>
                <div class="field">
                    <label>Published Date <span style="color:#C8102E">*</span></label>
                    <input type="date" name="published_date" required value="{{ date('Y-m-d') }}">
                </div>
            </div>
            <div class="field" style="margin-bottom:1rem;">
                <label>Description <span style="color:#6b7f96;font-weight:normal;text-transform:none">(optional — shown to members)</span></label>
                <textarea name="description" rows="2" placeholder="Brief summary of this edition..."></textarea>
            </div>
            <div class="form-row">
                <div class="field">
                    <label>Upload PDF <span style="color:#6b7f96;font-weight:normal;text-transform:none">(or enter URL below)</span></label>
                    <input type="file" name="file" accept="application/pdf">
                </div>
                <div class="field">
                    <label>External PDF URL <span style="color:#6b7f96;font-weight:normal;text-transform:none">(if not uploading)</span></label>
                    <input type="url" name="external_url" placeholder="https://raynet-uk.net/...">
                </div>
            </div>
            <div class="form-row">
                <div class="field">
                    <label>Cover Image <span style="color:#6b7f96;font-weight:normal;text-transform:none">(optional thumbnail)</span></label>
                    <input type="file" name="cover_image" accept="image/*">
                </div>
                <div class="field" style="justify-content:flex-end;padding-bottom:.25rem;">
                    <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:13px;text-transform:none;letter-spacing:0;font-weight:normal;margin-top:auto;">
                        <input type="checkbox" name="is_current" value="1"> Mark as current edition
                    </label>
                </div>
            </div>
            <button type="submit" class="btn btn-navy">+ Add Publication</button>
        </form>
    </div>

    {{-- Publication Lists --}}
    <div class="card">
        <div class="tab-nav">
            <button class="tab-btn active" onclick="showTab('news',this)">📰 RAYNET News ({{ $news->count() }})</button>
            <button class="tab-btn" onclick="showTab('checkpoint',this)">📋 Checkpoint ({{ $checkpoint->count() }})</button>
        </div>

        <div id="tab-news" class="tab-content active">
            @if($news->count())
            <table class="pub-table">
                <thead><tr><th>Edition</th><th>Title</th><th>Date</th><th>Status</th><th>File</th><th></th></tr></thead>
                <tbody>
                @foreach($news as $pub)
                <tr>
                    <td>{{ $pub->edition ?? '—' }}</td>
                    <td style="font-weight:bold;">{{ $pub->title }}</td>
                    <td>{{ $pub->published_date->format('j M Y') }}</td>
                    <td>
                        @if($pub->is_current)
                            <span class="badge-current">✓ Current</span>
                        @else
                            <span class="badge-archive">Archive</span>
                        @endif
                    </td>
                    <td>
                        @if($pub->file_url)
                            <a href="{{ $pub->file_url }}" target="_blank" style="color:#C8102E;font-size:12px;">⬇ PDF</a>
                        @else —
                        @endif
                    </td>
                    <td style="display:flex;gap:.35rem;justify-content:flex-end;">
                        @if(!$pub->is_current)
                        <form method="POST" action="{{ route('admin.publications.set-current', $pub) }}">@csrf
                            <button type="submit" class="btn btn-outline btn-sm">Set Current</button>
                        </form>
                        @endif
                        <form method="POST" action="{{ route('admin.publications.destroy', $pub) }}"
                              onsubmit="return confirm('Delete this publication?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-red btn-sm">✕</button>
                        </form>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
            @else
            <p style="color:#6b7f96;font-size:13px;padding:.5rem 0;">No RAYNET News editions added yet.</p>
            @endif
        </div>

        <div id="tab-checkpoint" class="tab-content">
            @if($checkpoint->count())
            <table class="pub-table">
                <thead><tr><th>Edition</th><th>Title</th><th>Date</th><th>Status</th><th>File</th><th></th></tr></thead>
                <tbody>
                @foreach($checkpoint as $pub)
                <tr>
                    <td>{{ $pub->edition ?? '—' }}</td>
                    <td style="font-weight:bold;">{{ $pub->title }}</td>
                    <td>{{ $pub->published_date->format('j M Y') }}</td>
                    <td>
                        @if($pub->is_current)
                            <span class="badge-current">✓ Current</span>
                        @else
                            <span class="badge-archive">Archive</span>
                        @endif
                    </td>
                    <td>
                        @if($pub->file_url)
                            <a href="{{ $pub->file_url }}" target="_blank" style="color:#C8102E;font-size:12px;">⬇ PDF</a>
                        @else —
                        @endif
                    </td>
                    <td style="display:flex;gap:.35rem;justify-content:flex-end;">
                        @if(!$pub->is_current)
                        <form method="POST" action="{{ route('admin.publications.set-current', $pub) }}">@csrf
                            <button type="submit" class="btn btn-outline btn-sm">Set Current</button>
                        </form>
                        @endif
                        <form method="POST" action="{{ route('admin.publications.destroy', $pub) }}"
                              onsubmit="return confirm('Delete this publication?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-red btn-sm">✕</button>
                        </form>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
            @else
            <p style="color:#6b7f96;font-size:13px;padding:.5rem 0;">No Checkpoint editions added yet.</p>
            @endif
        </div>
    </div>
</div>

<script>
function showTab(name, btn) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + name).classList.add('active');
    btn.classList.add('active');
}
</script>
@endsection
