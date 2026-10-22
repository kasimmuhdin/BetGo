@props(['points' => [], 'color' => '#6366f1'])

@php
    $points = array_values($points);
    $n = count($points);
    $w = 120;
    $h = 36;
    $max = $n ? max($points) : 0;
    $min = $n ? min($points) : 0;
    $range = max($max - $min, 1);
    $coords = [];

    foreach ($points as $i => $v) {
        $x = $n > 1 ? ($i / ($n - 1)) * $w : 0;
        $y = $h - 3 - (($v - $min) / $range) * ($h - 6);
        $coords[] = round($x, 1) . ',' . round($y, 1);
    }

    $line = implode(' ', $coords);
@endphp

@if ($n > 1 && $max > 0)
    <svg viewBox="0 0 {{ $w }} {{ $h }}" preserveAspectRatio="none" class="h-9 w-full"
        aria-hidden="true">
        <polygon points="0,{{ $h }} {{ $line }} {{ $w }},{{ $h }}"
            fill="{{ $color }}" opacity=".12" />
        <polyline points="{{ $line }}" fill="none" stroke="{{ $color }}" stroke-width="2"
            stroke-linejoin="round" stroke-linecap="round" vector-effect="non-scaling-stroke" />
    </svg>
@endif
