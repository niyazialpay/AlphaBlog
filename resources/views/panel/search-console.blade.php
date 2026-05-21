@extends('panel.base')
@section('title', 'Search Console')
@section('breadcrumb_link')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{route('admin.index')}}">@lang('dashboard.dashboard')</a></li>
        <li class="breadcrumb-item active">Search Console</li>
    </ol>
@endsection
@section('content')
    @can('admin', 'App\Models\User')
        <div class="row">
            <!-- Date range picker -->
            <form class="row d-flex justify-content-end mb-3" method="post" action="javascript:fetchGSCData()">
                <div class="col-sm-6 col-md-3 col-lg-2">
                    <div class="input-group">
                        <input type="text" class="form-control" name="daterange" id="daterange" value="{{ $date_range }}" />
                        <button class="btn btn-primary" type="button" id="refresh-button">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
            </form>

            @if(!$configured)
                <!-- Not configured alert -->
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-1"></i>
                        Search Console entegrasyonu yapılandırılmamış.
                        <code>storage/app/analytics/service-account-credentials.json</code> dosyasının mevcut olduğundan
                        ve <a href="{{ route('admin.settings') }}?tab=seo">Google Indexing ayarlarından</a> site URL'sinin
                        girildiğinden emin olun.
                    </div>
                </div>
            @else
                <!-- 4 Summary Cards -->
                @php
                    $perf = $performance['current'] ?? [];
                    $prevPerf = $performance['previous'] ?? [];
                    $cards = [
                        ['key' => 'clicks',      'label' => 'Toplam Tıklama',  'value' => number_format($perf['clicks'] ?? 0),      'prev' => $prevPerf['clicks'] ?? 0,      'curr' => $perf['clicks'] ?? 0],
                        ['key' => 'impressions', 'label' => 'Toplam Gösterim', 'value' => number_format($perf['impressions'] ?? 0), 'prev' => $prevPerf['impressions'] ?? 0, 'curr' => $perf['impressions'] ?? 0],
                        ['key' => 'ctr',         'label' => 'Ort. TO (CTR)',   'value' => ($perf['ctr'] ?? 0) . '%',               'prev' => $prevPerf['ctr'] ?? 0,         'curr' => $perf['ctr'] ?? 0],
                        ['key' => 'position',    'label' => 'Ort. Konum',      'value' => $perf['position'] ?? 0,                  'prev' => $prevPerf['position'] ?? 0,    'curr' => $perf['position'] ?? 0],
                    ];
                @endphp
                <div class="row mb-3">
                    @foreach($cards as $card)
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card radius-10">
                            <div class="card-body p-3">
                                <div class="text-muted small mb-1">{{ $card['label'] }}</div>
                                <div class="h4 mb-1 gsc-metric-value" id="gsc-{{ $card['key'] }}-value">{{ $card['value'] }}</div>
                                @php
                                    $prev = $card['prev'];
                                    $curr = $card['curr'];
                                    $change = $prev > 0 ? round(($curr - $prev) / $prev * 100, 1) : null;
                                    // For position: lower is better, so invert the change direction
                                    $isPositive = $card['key'] === 'position' ? ($change !== null && $change < 0) : ($change !== null && $change >= 0);
                                @endphp
                                @if($change !== null)
                                <span class="badge badge-{{ $isPositive ? 'success' : 'danger' }} gsc-metric-badge" id="gsc-{{ $card['key'] }}-badge">
                                    {{ $isPositive ? '▲' : '▼' }} {{ abs($change) }}%
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Trend Chart (dual Y-axis: clicks green left, impressions blue right) -->
                <div class="col-12 mb-3">
                    <div class="card radius-10">
                        <div class="card-header">Tıklama & Gösterim Trendi</div>
                        <div class="card-body">
                            <div id="gsc_trend_chart"></div>
                        </div>
                    </div>
                </div>

                <!-- Keywords Table -->
                <div class="col-12">
                    <div class="card radius-10">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>Anahtar Kelime Performansı (Top 50)</span>
                            <input type="text" id="keyword-filter" class="form-control form-control-sm w-auto"
                                   placeholder="Filtrele..." style="max-width:200px">
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-bordered table-hover mb-0" id="keywords-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="sortable" data-col="0" style="cursor:pointer">Anahtar Kelime ↕</th>
                                        <th class="sortable text-right" data-col="1" style="cursor:pointer">Tıklama ↕</th>
                                        <th class="sortable text-right" data-col="2" style="cursor:pointer">Gösterim ↕</th>
                                        <th class="sortable text-right" data-col="3" style="cursor:pointer">TO ↕</th>
                                        <th class="sortable text-right" data-col="4" style="cursor:pointer">Konum ↕</th>
                                    </tr>
                                </thead>
                                <tbody id="keywords-tbody">
                                    @foreach($keywords as $kw)
                                    <tr>
                                        <td>{{ $kw['query'] }}</td>
                                        <td class="text-right font-weight-bold">{{ number_format($kw['clicks']) }}</td>
                                        <td class="text-right">{{ number_format($kw['impressions']) }}</td>
                                        <td class="text-right">{{ $kw['ctr'] }}%</td>
                                        <td class="text-right">
                                            @php
                                                $pos = $kw['position'];
                                                $badgeClass = $pos <= 3 ? 'badge-success' : ($pos <= 10 ? 'badge-warning' : 'badge-danger');
                                            @endphp
                                            <span class="badge {{ $badgeClass }}">{{ $pos }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endcan
@endsection
@section('script')
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        @can('admin', 'App\Models\User')
        $(document).ready(function(){
            let dashboard_theme_mode = localStorage.getItem("dark-mode") === "true" ? 'dark' : 'light';
            let gsc_trend_chart;

            @if($configured && !empty($trend))
            // Initialize trend chart
            let trendDates = [];
            let trendClicks = [];
            let trendImpressions = [];

            @foreach($trend as $t)
            trendDates.push('{{ $t['date'] }}');
            trendClicks.push({{ $t['clicks'] }});
            trendImpressions.push({{ $t['impressions'] }});
            @endforeach

            let trend_options = {
                series: [
                    { name: 'Tıklamalar', type: 'line', data: trendClicks },
                    { name: 'Gösterimler', type: 'line', data: trendImpressions }
                ],
                chart: { height: 300, type: 'line', toolbar: { show: false } },
                stroke: { curve: 'smooth', width: [2, 2] },
                colors: ['#198754', '#0d6efd'],
                xaxis: { categories: trendDates, type: 'datetime' },
                yaxis: [
                    { title: { text: 'Tıklamalar' }, labels: { style: { colors: '#198754' } } },
                    { opposite: true, title: { text: 'Gösterimler' }, labels: { style: { colors: '#0d6efd' } } }
                ],
                legend: { position: 'top' },
                theme: { mode: dashboard_theme_mode }
            };
            gsc_trend_chart = new ApexCharts(document.querySelector("#gsc_trend_chart"), trend_options);
            gsc_trend_chart.render();
            @endif

            // Date range picker
            $('input[name="daterange"]').daterangepicker({
                opens: 'left',
                autoApply: true,
                maxDate: new Date(),
            }, function(start, end) {
                fetchGSCData();
            });

            $('#refresh-button').on('click', function(){ fetchGSCData(); });

            // Dark mode
            $('#dark-mode-switcher-button').on('click', function(){
                dashboard_theme_mode = localStorage.getItem("dark-mode") === "true" ? 'dark' : 'light';
                if (gsc_trend_chart) {
                    gsc_trend_chart.updateOptions({ theme: { mode: dashboard_theme_mode } });
                }
            });

            // Client-side keyword filter
            $('#keyword-filter').on('input', function(){
                let val = $(this).val().toLowerCase();
                $('#keywords-tbody tr').each(function(){
                    $(this).toggle($(this).text().toLowerCase().includes(val));
                });
            });

            // Client-side sortable columns
            let sortDir = {};
            $('.sortable').on('click', function(){
                let col = parseInt($(this).data('col'));
                sortDir[col] = !sortDir[col];
                let rows = $('#keywords-tbody tr').get();
                rows.sort(function(a, b){
                    let aVal = $(a).find('td').eq(col).text().replace(/[,%]/g,'').trim();
                    let bVal = $(b).find('td').eq(col).text().replace(/[,%]/g,'').trim();
                    let aNum = parseFloat(aVal);
                    let bNum = parseFloat(bVal);
                    if (!isNaN(aNum) && !isNaN(bNum)) {
                        return sortDir[col] ? aNum - bNum : bNum - aNum;
                    }
                    return sortDir[col] ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
                });
                $.each(rows, function(i, row){ $('#keywords-tbody').append(row); });
            });
        });

        function fetchGSCData() {
            $.ajax({
                url: '{{ route('admin.search-console.fetch') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    date_range: $('#daterange').val()
                },
                success: function(data) {
                    if (!data.configured) return;
                    // Update cards
                    if (data.performance && data.performance.current) {
                        let p = data.performance.current;
                        $('#gsc-clicks-value').text(p.clicks ? p.clicks.toLocaleString() : '0');
                        $('#gsc-impressions-value').text(p.impressions ? p.impressions.toLocaleString() : '0');
                        $('#gsc-ctr-value').text((p.ctr || 0) + '%');
                        $('#gsc-position-value').text(p.position || 0);
                    }
                    // Update trend chart
                    if (data.trend && gsc_trend_chart) {
                        let dates = data.trend.map(t => t.date);
                        let clicks = data.trend.map(t => t.clicks);
                        let imps = data.trend.map(t => t.impressions);
                        gsc_trend_chart.updateOptions({
                            series: [
                                { name: 'Tıklamalar', data: clicks },
                                { name: 'Gösterimler', data: imps }
                            ],
                            xaxis: { categories: dates, type: 'datetime' }
                        });
                    }
                    // Update keywords table
                    if (data.keywords) {
                        let tbody = '';
                        data.keywords.forEach(function(kw){
                            let pos = kw.position;
                            let badgeClass = pos <= 3 ? 'badge-success' : (pos <= 10 ? 'badge-warning' : 'badge-danger');
                            tbody += '<tr><td>' + kw.query + '</td><td class="text-right font-weight-bold">' + kw.clicks.toLocaleString() + '</td><td class="text-right">' + kw.impressions.toLocaleString() + '</td><td class="text-right">' + kw.ctr + '%</td><td class="text-right"><span class="badge ' + badgeClass + '">' + pos + '</span></td></tr>';
                        });
                        $('#keywords-tbody').html(tbody);
                    }
                }
            });
        }
        @endcan
    </script>
@endsection
