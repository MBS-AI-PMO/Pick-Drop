@props([
    'percent' => 0,
    'tone' => 'blue',
])
@php
    $pct = max(0, min(100, (int) round((float) $percent)));
@endphp
<div {{ $attributes->class(['pd-stat-ring', 'is-' . $tone]) }}>
    <svg viewBox="0 0 36 36" aria-hidden="true">
        <circle class="pd-stat-ring-track" cx="18" cy="18" r="15"></circle>
        <circle class="pd-stat-ring-bar" cx="18" cy="18" r="15" pathLength="100" stroke-dasharray="{{ $pct }} 100"></circle>
    </svg>
    <span>{{ $pct }}%</span>
</div>
