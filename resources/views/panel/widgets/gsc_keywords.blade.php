@php $keywords = $widgetData['gsc']['keywords'] ?? []; @endphp
<div class="card h-100 mb-0 border-0 d-flex flex-column">
    <div class="card-header py-2 px-3 border-0 bg-transparent d-flex justify-content-between align-items-center">
        <small class="text-muted font-weight-bold">Top Keywords</small>
        <a href="{{ route('admin.search-console') }}" class="btn btn-xs btn-outline-secondary" style="font-size:10px">Tümü →</a>
    </div>
    <div class="card-body p-0 flex-grow-1 overflow-auto">
        @if(!empty($keywords))
        <table class="table table-sm table-borderless mb-0">
            <thead class="thead-light">
                <tr>
                    <th style="font-size:11px">Kelime</th>
                    <th class="text-right" style="font-size:11px">Tıklama</th>
                    <th class="text-right" style="font-size:11px">Konum</th>
                </tr>
            </thead>
            <tbody>
                @foreach(array_slice($keywords, 0, 10) as $kw)
                <tr>
                    <td style="font-size:11px;max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $kw['query'] }}</td>
                    <td class="text-right" style="font-size:11px">{{ number_format($kw['clicks']) }}</td>
                    <td class="text-right" style="font-size:11px">
                        @php $pos = $kw['position']; @endphp
                        <span class="badge badge-{{ $pos <= 3 ? 'success' : ($pos <= 10 ? 'warning' : 'danger') }}" style="font-size:9px">{{ $pos }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="text-muted small p-3">Veri yüklenemedi</div>
        @endif
    </div>
</div>
