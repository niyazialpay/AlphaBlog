@php $logs = $widgetData['firewall'] ?? collect(); @endphp
<div class="card h-100 mb-0 border-0 d-flex flex-column">
    <div class="card-header py-2 px-3 border-0 bg-transparent d-flex justify-content-between align-items-center">
        <small class="text-muted font-weight-bold">Firewall Logları</small>
        <a href="{{ route('admin.firewall.logs') }}" class="btn btn-xs btn-outline-secondary" style="font-size:10px">Tümü →</a>
    </div>
    <div class="card-body p-0 flex-grow-1 overflow-auto">
        @if($logs->isNotEmpty())
        <table class="table table-sm table-borderless mb-0">
            <tbody>
                @foreach($logs as $log)
                <tr>
                    <td style="font-size:11px">
                        <span class="font-weight-bold text-danger">{{ $log->ip }}</span>
                        <div class="text-muted small">{{ $log->reason }}</div>
                    </td>
                    <td class="text-muted text-right" style="font-size:10px;white-space:nowrap">{{ $log->created_at?->diffForHumans() }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="text-muted small p-3">Log bulunamadı</div>
        @endif
    </div>
</div>
