@php
    $curr = $widgetData['gsc']['performance']['current'] ?? [];
    $prev = $widgetData['gsc']['performance']['previous'] ?? [];
    $val = $curr['clicks'] ?? null;
    $prevVal = $prev['clicks'] ?? 0;
    $change = ($prevVal > 0 && $val !== null) ? round(($val - $prevVal) / $prevVal * 100, 1) : null;
@endphp
<div class="card h-100 mb-0 border-0">
    <div class="card-body p-3 d-flex flex-column justify-content-between">
        @if($val !== null)
        <div class="text-muted small">GSC Tıklama</div>
        <div class="h3 mb-1">{{ number_format($val) }}</div>
        @if($change !== null)
        <span class="badge badge-{{ $change >= 0 ? 'success' : 'danger' }}">{{ $change >= 0 ? '▲' : '▼' }} {{ abs($change) }}%</span>
        @endif
        @else
        <div class="text-muted small">GSC Tıklama</div>
        <div class="text-muted">Veri yüklenemedi</div>
        @endif
    </div>
</div>
