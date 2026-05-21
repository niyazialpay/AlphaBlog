@php $metric = $widgetData['ga4']['overview']['events'] ?? null; @endphp
<div class="card h-100 mb-0 border-0">
    <div class="card-body p-3 d-flex flex-column justify-content-between">
        @if($metric)
        <div class="text-muted small">Etkinlik Sayısı</div>
        <div class="h3 mb-1">{{ number_format($metric['value']) }}</div>
        @if($metric['change'] !== null)
        <span class="badge badge-{{ $metric['change'] >= 0 ? 'success' : 'danger' }}">
            {{ $metric['change'] >= 0 ? '▲' : '▼' }} {{ abs($metric['change']) }}%
        </span>
        @endif
        @else
        <div class="text-muted small">Etkinlik Sayısı</div>
        <div class="text-muted">Veri yüklenemedi</div>
        @endif
    </div>
</div>
