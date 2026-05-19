@extends('layouts.app')

@section('title', 'My profile')

@section('content')
    <section style="max-width: 640px; margin: 0 auto;">
        <h1 style="margin:0 0 0.75rem; font-size:1.3rem; color:#e5e7eb;">
            My profile
        </h1>
        <p style="margin:0 0 1rem; font-size:0.9rem; color:#9ca3af;">
            Update your name and callsign. Callsigns are unique and not case sensitive.
        </p>

        @if (session('status') === 'profile-updated')
            <div style="
                margin-bottom:0.8rem;
                padding:0.6rem 0.8rem;
                border-radius:0.7rem;
                background:rgba(22,163,74,0.15);
                border:1px solid rgba(22,163,74,0.6);
                font-size:0.85rem;
                color:#bbf7d0;
            ">
                Profile updated successfully.
            </div>
        @endif

        @if ($errors->any())
            <div style="
                margin-bottom:0.8rem;
                padding:0.6rem 0.8rem;
                border-radius:0.7rem;
                background:rgba(185,28,28,0.15);
                border:1px solid rgba(239,68,68,0.7);
                font-size:0.85rem;
                color:#fecaca;
            ">
                <strong>There were some problems with your input:</strong>
                <ul style="margin:0.4rem 0 0 1rem; padding:0;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}" style="display:flex; flex-direction:column; gap:0.75rem;">
            @csrf

            <div>
                <label for="name" style="display:block; font-size:0.85rem; color:#e5e7eb; margin-bottom:0.25rem;">
                    Name
                </label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    value="{{ old('name', $user->name) }}"
                    required
                    style="
                        width:100%;
                        padding:0.45rem 0.6rem;
                        border-radius:0.5rem;
                        border:1px solid rgba(148,163,184,0.7);
                        background:#020617;
                        color:#e5e7eb;
                    "
                >
            </div>

            <div>
                <label for="callsign" style="display:block; font-size:0.85rem; color:#e5e7eb; margin-bottom:0.25rem;">
                    Callsign
                </label>
                <input
                    id="callsign"
                    name="callsign"
                    type="text"
                    value="{{ old('callsign', $user->callsign) }}"
                    placeholder="e.g. G4BDS"
                    style="
                        width:100%;
                        padding:0.45rem 0.6rem;
                        border-radius:0.5rem;
                        border:1px solid rgba(148,163,184,0.7);
                        background:#020617;
                        color:#e5e7eb;
                    "
                >
                <p style="margin:0.3rem 0 0; font-size:0.75rem; color:#9ca3af;">
                    Format: letters/numbers/slash, 3–10 characters (for example G4BDS, M0ABC/P). Stored in upper case.
                </p>
            </div>

            <div>
                <label for="telegram_chat_id" style="display:block; font-size:0.85rem; color:#e5e7eb; margin-bottom:0.25rem;">
                    Telegram Chat ID
                </label>
                <input
                    id="telegram_chat_id"
                    name="telegram_chat_id"
                    type="text"
                    value="{{ old('telegram_chat_id', $user->telegram_chat_id) }}"
                    placeholder="e.g. 5257679106"
                    style="
                        width:100%;
                        padding:0.45rem 0.6rem;
                        border-radius:0.5rem;
                        border:1px solid rgba(148,163,184,0.7);
                        background:#020617;
                        color:#e5e7eb;
                    "
                >
                <p style="margin:0.3rem 0 0; font-size:0.75rem; color:#9ca3af;">
                    DM <strong>@raynet_liverpool_bot</strong> on Telegram and send <code>/id</code> to get your Chat ID. This enables direct alert notifications and bot commands like /available.
                </p>
            </div>

            <div style="margin-top:0.5rem;">
                <button type="submit" style="
                    padding:0.5rem 1.0rem;
                    border-radius:0.7rem;
                    border:none;
                    background:linear-gradient(to right,#38bdf8,#2563eb);
                    color:#f9fafb;
                    font-size:0.9rem;
                    font-weight:600;
                    cursor:pointer;
                ">
                    Save changes
                </button>
            </div>
        </form>
    </section>
@endsection