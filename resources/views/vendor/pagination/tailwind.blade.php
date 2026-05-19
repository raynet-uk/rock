@if ($paginator->hasPages())
<nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" style="display:flex;align-items:center;justify-content:space-between;gap:.5rem;flex-wrap:wrap;">

    {{-- Record count --}}
    <span style="font-size:11px;font-weight:700;color:#6b7f96;text-transform:uppercase;letter-spacing:.08em;">
        @if ($paginator->firstItem())
            Records {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}
        @else
            {{ $paginator->count() }} records
        @endif
    </span>

    {{-- Page buttons --}}
    <div style="display:flex;align-items:center;gap:2px;">

        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border:1px solid #dde2e8;background:#f2f5f9;color:#9aa3ae;cursor:default;font-size:11px;" aria-disabled="true">
                ‹
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border:1px solid #dde2e8;background:#fff;color:#6b7f96;font-size:11px;font-weight:700;text-decoration:none;transition:all .12s;" aria-label="{{ __('pagination.previous') }}"
               onmouseover="this.style.background='#1e0040';this.style.color='#fff';this.style.borderColor='#1e0040';"
               onmouseout="this.style.background='#fff';this.style.color='#6b7f96';this.style.borderColor='#dde2e8';">
                ‹
            </a>
        @endif

        {{-- Page numbers --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border:1px solid #dde2e8;background:#fff;color:#9aa3ae;font-size:11px;font-weight:700;">…</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page" style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border:1px solid #7c3aed;background:#1e0040;color:#c4b5fd;font-size:11px;font-weight:700;cursor:default;">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border:1px solid #dde2e8;background:#fff;color:#2d4a6b;font-size:11px;font-weight:700;text-decoration:none;transition:all .12s;" aria-label="{{ __('Go to page :page', ['page' => $page]) }}"
                           onmouseover="this.style.background='#f5f3ff';this.style.borderColor='#7c3aed';this.style.color='#7c3aed';"
                           onmouseout="this.style.background='#fff';this.style.borderColor='#dde2e8';this.style.color='#2d4a6b';">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border:1px solid #dde2e8;background:#fff;color:#6b7f96;font-size:11px;font-weight:700;text-decoration:none;transition:all .12s;" aria-label="{{ __('pagination.next') }}"
               onmouseover="this.style.background='#1e0040';this.style.color='#fff';this.style.borderColor='#1e0040';"
               onmouseout="this.style.background='#fff';this.style.color='#6b7f96';this.style.borderColor='#dde2e8';">
                ›
            </a>
        @else
            <span style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border:1px solid #dde2e8;background:#f2f5f9;color:#9aa3ae;cursor:default;font-size:11px;" aria-disabled="true">
                ›
            </span>
        @endif

    </div>
</nav>
@endif