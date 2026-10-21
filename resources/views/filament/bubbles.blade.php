@php
    // A quiet version of the game's background: 10 small, slow bubbles with varied size, speed and phase.
    $fills = ['rgba(139,92,246,0.22)', 'rgba(168,85,247,0.18)', 'rgba(88,28,135,0.45)', 'rgba(192,132,252,0.14)', 'rgba(76,29,149,0.40)'];
    $rims = ['rgba(192,132,252,0.30)', 'rgba(221,214,254,0.22)', 'rgba(168,85,247,0.40)', 'rgba(221,214,254,0.18)', 'rgba(139,92,246,0.34)'];
@endphp
<div class="pq-bubbles" aria-hidden="true">
    @for ($i = 0; $i < 10; $i++)
        @php($size = 10 + (($i * 37) % 9) * 7)
        <span class="pq-bubble" style="left: {{ ($i * 41 + 7) % 100 }}%; width: {{ $size }}px; height: {{ $size }}px; background: {{ $fills[$i % 5] }}; border-color: {{ $rims[($i + 2) % 5] }}; animation-duration: {{ 22 + (($i * 7) % 10) * 2.4 }}s; animation-delay: -{{ (($i * 13) % 20) * 1.3 }}s"></span>
    @endfor
</div>
