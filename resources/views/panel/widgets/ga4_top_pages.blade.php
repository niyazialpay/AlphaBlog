@php
    $data = $widgetData['ga4']['top_pages'] ?? collect();
    $chartId = 'wc_' . uniqid();
@endphp
<div class="card h-100 mb-0 border-0">
    <div class="card-header py-2 px-3 border-0 bg-transparent">
        <small class="text-muted font-weight-bold">En Çok Görüntülenen</small>
    </div>
    <div class="card-body p-2" style="min-height:120px">
        @if($data->isNotEmpty())
        <div id="{{ $chartId }}"></div>
        <script>
        window.addEventListener('load', function(){
            var cats=[], vals=[];
            @foreach($data as $row)
            cats.push('{{ addslashes($row['pageTitle']) }}');
            vals.push({{ $row['screenPageViews'] }});
            @endforeach
            var opts={series:[{name:'Görüntüleme',data:vals}],chart:{type:'bar',height:160,toolbar:{show:false}},plotOptions:{bar:{horizontal:true,borderRadius:4}},xaxis:{categories:cats},theme:{mode:localStorage.getItem('dark-mode')==='true'?'dark':'light'}};
            new ApexCharts(document.getElementById('{{ $chartId }}'),opts).render();
        });
        </script>
        @else
        <div class="text-muted small p-2">Veri yüklenemedi</div>
        @endif
    </div>
</div>
