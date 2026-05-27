@php
    /** @var \App\Models\AlertStatus|null $alertStatus */
    $alertStatus = $alertStatus ?? null;
    $meta        = $alertStatus?->meta();
    $level       = $alertStatus->level ?? 5;
    $colour      = $meta['colour'] ?? '#22c55e';
    $textColour  = in_array($level, [1, 2, 4]) ? '#0b1120' : '#ffffff';
    $subtleText  = in_array($level, [1, 2, 4]) ? '#374151' : 'rgba(255,255,255,0.75)';
@endphp

<a href="{{ route('alert-levels') }}"
   style="text-decoration:none;display:block;"
   title="View all response levels">
    <div style="
        border-radius: 12px;
        overflow: hidden;
        font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif;
        box-shadow: 0 2px 12px rgba(0,0,0,0.15);
        border: 1px solid rgba(0,0,0,0.12);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    "
    onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 6px 20px rgba(0,0,0,0.2)'"
    onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 2px 12px rgba(0,0,0,0.15)'"
    >
        {{-- Coloured body --}}
        <div style="
            background: {{ $colour }};
            padding: 1rem 1.1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        ">
            {{-- Level number box --}}
            <div style="
                width: 52px;
                height: 52px;
                border-radius: 10px;
                background: rgba(0,0,0,0.18);
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                line-height: 1;
            ">
                <div style="font-size: 0.55rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: {{ $textColour }}; opacity: 0.75; margin-bottom: 1px;">LVL</div>
                <div style="font-size: 1.6rem; font-weight: 800; color: {{ $textColour }}; letter-spacing: -1px;">{{ $level }}</div>
            </div>

            {{-- Text --}}
            <div style="flex: 1; min-width: 0;">
                <div style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: {{ $subtleText }}; margin-bottom: 2px;">
                    Current Response Level
                </div>
                <div style="font-size: 1rem; font-weight: 700; color: {{ $textColour }}; line-height: 1.2; margin-bottom: 3px;">
                    {{ $meta['title'] ?? 'Level '.$level }}
                </div>
                <div style="font-size: 0.8rem; color: {{ $subtleText }}; line-height: 1.35; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; ">
                    {{ $meta['description'] ?? '' }}
                </div>
            </div>

            {{-- Arrow --}}
            <div style="font-size: 1.2rem; color: {{ $textColour }}; opacity: 0.4; flex-shrink: 0;">›</div>
        </div>

        {{-- Custom message strip --}}
        @if (!empty($alertStatus?->message))
            <div style="
                background: rgba(0,0,0,0.65);
                padding: 0.5rem 1.1rem;
                font-size: 0.8rem;
                color: rgba(255,255,255,0.9);
                line-height: 1.4;
                display: flex;
                align-items: flex-start;
                gap: 0.5rem;
            ">
                <span style="flex-shrink:0;">📢</span>
                <span>{{ $alertStatus->message }}</span>
            </div>
        @endif

        {{-- Footer — RAYNET branding --}}
        <div style="
            background: #0b1b3b;
            padding: 0.45rem 1.1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
        ">
            <div style="display:flex;align-items:center;gap:0.5rem;">
                <img src="https://www.raynet-uk.net/technical/graphics/raynet-uk.gif"
                     alt="RAYNET-UK"
                     style="height:18px;width:auto;opacity:0.85;flex-shrink:0;"
                     onerror="this.style.display='none'">
                <span style="font-size: 0.7rem; color: #64748b; font-weight: 600; letter-spacing: 0.05em;">
                    {{ \App\Helpers\RaynetSetting::groupNumber() }}
                </span>
            </div>
            <div style="font-size: 0.7rem; color: #475569; font-weight: 600;">
                Tap for details →
            </div>
        </div>
    </div>
</a>
