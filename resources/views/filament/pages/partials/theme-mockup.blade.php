{{--
    Props:
      $p    - preview array: ['header','hero','card','cta']
      $name - theme label
      $tall - bool
--}}
@php
    $storeName  = strtoupper(mb_substr(store_name(), 0, 12));
    $heroText   = '#ffffff';
    $heroHeight = $tall ? '80px' : '60px';
    $prodHeight = $tall ? '52px' : '40px';
@endphp

<div style="border-radius: .75rem .75rem 0 0; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.15);">

    {{-- Browser chrome --}}
    <div style="display:flex; align-items:center; gap:8px; padding:6px 10px; background: #d1d5db;">
        <div style="display:flex; gap:4px; flex-shrink:0;">
            <span style="display:block; width:10px; height:10px; border-radius:50%; background:#f87171;"></span>
            <span style="display:block; width:10px; height:10px; border-radius:50%; background:#fbbf24;"></span>
            <span style="display:block; width:10px; height:10px; border-radius:50%; background:#34d399;"></span>
        </div>
        <div style="flex:1; background:#fff; border-radius:4px; padding:2px 8px; overflow:hidden;">
            <span style="font-size:8px; color:#9ca3af; white-space:nowrap; display:block; line-height:1.6;">
                {{ strtolower(str_replace(' ', '-', store_name())) }}.localhost
            </span>
        </div>
    </div>

    {{-- Page: header --}}
    <div style="background:{{ $p['header'] }};">
        <div style="display:flex; align-items:center; justify-content:space-between; padding:6px 12px; border-bottom:1px solid rgba(127,127,127,.12);">
            <div style="display:flex; gap:5px;">
                <div style="width:18px; height:4px; border-radius:2px; background:{{ $p['hero'] }}; opacity:.25;"></div>
                <div style="width:22px; height:4px; border-radius:2px; background:{{ $p['hero'] }}; opacity:.15;"></div>
                <div style="width:14px; height:4px; border-radius:2px; background:{{ $p['hero'] }}; opacity:.15;"></div>
            </div>
            <span style="font-size:6.5px; font-weight:800; letter-spacing:.15em; color:{{ $p['hero'] }}; opacity:.85;">
                {{ $storeName }}
            </span>
            <div style="display:flex; gap:4px;">
                <div style="width:8px; height:8px; border-radius:50%; background:{{ $p['hero'] }}; opacity:.2;"></div>
                <div style="width:8px; height:8px; border-radius:50%; background:{{ $p['hero'] }}; opacity:.2;"></div>
                <div style="width:8px; height:8px; border-radius:50%; background:{{ $p['hero'] }}; opacity:.2;"></div>
            </div>
        </div>

        {{-- Page: hero --}}
        <div style="background:{{ $p['hero'] }}; padding:{{ $tall ? '18px 14px' : '12px 10px' }};">
            <div style="max-width:55%;">
                <div style="width:32px; height:3px; border-radius:2px; background:{{ $heroText }}; opacity:.3; margin-bottom:5px;"></div>
                <div style="width:68px; height:{{ $tall ? '9px' : '7px' }}; border-radius:3px; background:{{ $heroText }}; opacity:.8; margin-bottom:4px;"></div>
                <div style="width:50px; height:{{ $tall ? '9px' : '7px' }}; border-radius:3px; background:{{ $heroText }}; opacity:.75; margin-bottom:4px;"></div>
                <div style="width:90px; height:3px; border-radius:2px; background:{{ $heroText }}; opacity:.25; margin-bottom:3px;"></div>
                <div style="width:70px; height:3px; border-radius:2px; background:{{ $heroText }}; opacity:.2; margin-bottom:{{ $tall ? '12px' : '8px' }};"></div>
                <div style="display:inline-block; background:{{ $p['cta'] }}; color:{{ $p['hero'] }}; font-size:6.5px; font-weight:800; letter-spacing:.06em; padding:3px 10px; border-radius:{{ $p['cta'] === '#ffffff' ? '20px' : '6px' }};">
                    Belanja →
                </div>
            </div>
        </div>

        {{-- Page: product grid --}}
        <div style="background:{{ $p['header'] }}; padding:{{ $tall ? '10px 10px 0' : '8px 8px 0' }}; display:grid; grid-template-columns:repeat(4,1fr); gap:5px;">
            @for ($i = 0; $i < 4; $i++)
                <div style="background:{{ $p['card'] }}; border-radius:4px; aspect-ratio:3/4;"></div>
            @endfor
        </div>

        {{-- Spacer --}}
        <div style="background:{{ $p['header'] }}; height:{{ $tall ? '16px' : '10px' }};"></div>
    </div>

</div>
