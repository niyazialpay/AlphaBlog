@php
    $data = $widgetData['ga4']['user_types'] ?? collect();
    $chartId = 'wc_' . uniqid();
@endphp
<div class="card h-100 mb-0 border-0">
    <div class="card-header py-2 px-3 border-0 bg-transparent">
        <small class="text-muted font-weight-bold">Kullanıcı Tipi</small>
    </div>
    <div class="card-body p-2" style="min-height:120px">
        @if($data->isNotEmpty())
        <div id="{{ $chartId }}"></div>
        <script>
        (function(){
            var labels=[], series=[];
            @foreach($data as $row)
            labels.push('{{ $row['newVsReturning'] }}');
            series.push({{ $row['activeUsers'] }});
            @endforeach
            var opts={series:series,labels:labels,chart:{type:'pie',height:150},legend:{position:'bottom'},theme:{mode:localStorage.getItem('dark-mode')==='true'?'dark':'light'}};
            new ApexCharts(document.getElementById('{{ $chartId }}'),opts).render();
        })();
        </script>
        @else
        <div class="text-muted small p-2">Veri yüklenemedi</div>
        @endif
    </div>
</div>
