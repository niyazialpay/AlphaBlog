@php
    $trend = $widgetData['ga4']['trend'] ?? collect();
    $chartId = 'wc_' . uniqid();
@endphp
<div class="card h-100 mb-0 border-0">
    <div class="card-header py-2 px-3 border-0 bg-transparent">
        <small class="text-muted font-weight-bold">Ziyaretçi Trendi</small>
    </div>
    <div class="card-body p-2" style="min-height:120px">
        @if($trend->isNotEmpty())
        <div id="{{ $chartId }}"></div>
        <script>
        (function(){
            var dates=[], users=[], views=[];
            @foreach($trend as $t)
            dates.push('{{ $t['date'] }}');
            users.push({{ $t['activeUsers'] }});
            views.push({{ $t['screenPageViews'] }});
            @endforeach
            var opts={series:[{name:'Kullanıcı',data:users},{name:'Görüntüleme',data:views}],chart:{type:'line',height:120,sparkline:{enabled:false},toolbar:{show:false}},stroke:{curve:'smooth',width:2},xaxis:{categories:dates,type:'datetime'},legend:{show:false},theme:{mode:localStorage.getItem('dark-mode')==='true'?'dark':'light'}};
            new ApexCharts(document.getElementById('{{ $chartId }}'),opts).render();
        })();
        </script>
        @else
        <div class="text-muted small p-2">Veri yüklenemedi</div>
        @endif
    </div>
</div>
