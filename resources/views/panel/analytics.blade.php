@extends('panel.base')
@section('title', __('dashboard.analytics'))
@section('breadcrumb_link')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item active">@lang('dashboard.analytics')</li>
    </ol>
@endsection
@section('content')
    @can('admin', 'App\Models\User')
        <div class="row">
            <form class="row d-flex justify-content-end mb-3" method="post" action="javascript:fetchDataAndUpdateCharts()">
                <div class="col-sm-6 col-md-3 col-lg-2">
                    <div class="input-group">
                        <input type="text" class="form-control" name="daterange" id="daterange" value="{{ $date_range }}" />
                        <button class="btn btn-primary" type="button" id="refresh-button">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
            </form>
            <!-- Browsers Chart -->
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-xs-6">
                <div class="card radius-10">
                    <div class="card-header">
                        @lang('dashboard.top_browsers')
                    </div>
                    <div class="card-body">
                        <div id="browsers_chart"></div>
                    </div>
                </div>
            </div>
            <!-- Countries Chart -->
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-xs-6">
                <div class="card radius-10">
                    <div class="card-header">
                        @lang('dashboard.top_countries')
                    </div>
                    <div class="card-body">
                        <div id="countries_chart"></div>
                    </div>
                </div>
            </div>
            <!-- Operating Systems Chart -->
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-xs-6">
                <div class="card radius-10">
                    <div class="card-header">
                        @lang('dashboard.top_operating_systems')
                    </div>
                    <div class="card-body">
                        <div id="operating_system_chart"></div>
                    </div>
                </div>
            </div>
            <!-- User Types Chart -->
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-xs-6">
                <div class="card radius-10">
                    <div class="card-header">
                        @lang('dashboard.user_types')
                    </div>
                    <div class="card-body">
                        <div id="user_types_chart"></div>
                    </div>
                </div>
            </div>
            <!-- Total Visitors and Page Views Chart -->
            <div class="col-sm-12 col-md-6">
                <div class="card radius-10">
                    <div class="card-header">
                        @lang('dashboard.total_visitors_and_page_views')
                        <div class="ms-auto widget-icon-small text-white bg-gradient-info">
                            <ion-icon name="people-outline"></ion-icon>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="total_visitors_chart"></div>
                    </div>
                </div>
            </div>
            <!-- Page Views Chart -->
            <div class="col-sm-12 col-md-6">
                <div class="card radius-10">
                    <div class="card-header">
                        @lang('dashboard.page_views')
                    </div>
                    <div class="card-body">
                        <div id="top_viewed_chart"></div>
                    </div>
                </div>
            </div>
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

            // Declare chart variables globally
            let operation_system_chart;
            let user_type_chart;
            let browser_chart;
            let countries_chart;
            let visitors_chart;
            let viewed_chart;

            // Theme settings
            let dashboard_theme_mode = localStorage.getItem("dark-mode") === "true" ? 'dark' : 'light';
            let dashboard_text_color = dashboard_theme_mode === 'dark' ? '#ffffff' : '#000000';

            // Initialize charts with initial data
            initializeCharts();

            // Render charts
            operation_system_chart.render();
            user_type_chart.render();
            browser_chart.render();
            countries_chart.render();
            visitors_chart.render();
            viewed_chart.render();

            // Date range picker and AJAX request
            $('input[name="daterange"]').daterangepicker({
                opens: 'left',
                locale: {
                    cancelLabel: '@lang('general.cancel')',
                    applyLabel: '@lang('general.apply')',
                },
                maxDate: new Date(),
            }, function(start, end, label) {
                fetchDataAndUpdateCharts();
            });

            // Dark mode switcher
            $('#dark-mode-switcher-button').on('click', function(){
                dashboard_theme_mode = localStorage.getItem("dark-mode") === "true" ? 'dark' : 'light';
                dashboard_text_color = dashboard_theme_mode === 'dark' ? '#ffffff' : '#000000';

                updateChartThemeMode(operation_system_chart, dashboard_theme_mode, dashboard_text_color);
                updateChartThemeMode(user_type_chart, dashboard_theme_mode, dashboard_text_color);
                updateChartThemeMode(browser_chart, dashboard_theme_mode, dashboard_text_color);
                updateChartThemeMode(countries_chart, dashboard_theme_mode, dashboard_text_color);
                updateChartThemeMode(visitors_chart, dashboard_theme_mode, dashboard_text_color);
                updateChartThemeMode(viewed_chart, dashboard_theme_mode, dashboard_text_color);
            });

            // Function to initialize charts
            function initializeCharts() {
                // Operating Systems Chart
                let operating_systems = [];
                let operating_systems_view = [];

                @foreach($operatingSystem as $system)
                operating_systems.push('{{ $system['operatingSystem'] }}');
                operating_systems_view.push({{ $system['screenPageViews'] }});
                @endforeach

                let operating_system_options = {
                    series: operating_systems_view,
                    labels: operating_systems,
                    chart: {
                        type: 'pie'
                    },
                    theme: {
                        mode: dashboard_theme_mode
                    }
                };

                operation_system_chart = new ApexCharts(document.querySelector("#operating_system_chart"), operating_system_options);

                // User Types Chart
                let user_types = [];
                let user_types_view = [];

                @foreach($user_types as $user_type)
                @if($user_type['newVsReturning']== 'new')
                user_types.push('@lang('dashboard.user_type.new')');
                @elseif($user_type['newVsReturning']== 'returning')
                user_types.push('@lang('dashboard.user_type.returning')');
                @else
                user_types.push('@lang('dashboard.user_type.others')');
                @endif
                user_types_view.push({{ $user_type['activeUsers'] }});
                @endforeach

                let user_types_options = {
                    series: user_types_view,
                    labels: user_types,
                    chart: {
                        type: 'pie'
                    },
                    theme: {
                        mode: dashboard_theme_mode
                    }
                };

                user_type_chart = new ApexCharts(document.querySelector("#user_types_chart"), user_types_options);

                // Browsers Chart
                let browsers = [];
                let browsers_view = [];

                @foreach($topBrowsers as $browser)
                browsers.push('{{ $browser['browser'] }}');
                browsers_view.push({{ $browser['screenPageViews'] }});
                @endforeach

                let browsers_options = {
                    series: browsers_view,
                    labels: browsers,
                    chart: {
                        type: 'pie'
                    },
                    theme: {
                        mode: dashboard_theme_mode
                    }
                };

                browser_chart = new ApexCharts(document.querySelector("#browsers_chart"), browsers_options);

                // Countries Chart
                let topCountries = [];
                let topCountries_view = [];

                @foreach($topCountries as $country)
                topCountries.push('{{ $country['country'] }}');
                topCountries_view.push({{ $country['screenPageViews'] }});
                @endforeach

                let countries_options = {
                    series: topCountries_view,
                    labels: topCountries,
                    chart: {
                        type: 'pie'
                    },
                    theme: {
                        mode: dashboard_theme_mode
                    }
                };

                countries_chart = new ApexCharts(document.querySelector("#countries_chart"), countries_options);

                // Total Visitors and Page Views Chart
                let TotalVisitorsAndPageViews = [];
                let TotalVisitorsAndPageViews_view = [];
                let TotalVisitorsAndPageViews_date = [];

                @foreach($TotalVisitorsAndPageViews as $dataPoint)
                TotalVisitorsAndPageViews.push({{ $dataPoint['activeUsers'] }});
                TotalVisitorsAndPageViews_view.push({{ $dataPoint['screenPageViews'] }});
                TotalVisitorsAndPageViews_date.push('{{ $dataPoint['date'] }}');
                @endforeach

                let totalVisitors_options = {
                    series: [
                        {
                            name: "@lang('dashboard.total_visitors')",
                            data: TotalVisitorsAndPageViews
                        },
                        {
                            name: "@lang('dashboard.total_page_views')",
                            data: TotalVisitorsAndPageViews_view
                        }
                    ],
                    chart: {
                        type: 'bar',
                        height: 350,
                        stacked: true
                    },
                    xaxis: {
                        type: 'datetime',
                        categories: TotalVisitorsAndPageViews_date
                    },
                    theme: {
                        mode: dashboard_theme_mode
                    }
                };

                visitors_chart = new ApexCharts(document.querySelector("#total_visitors_chart"), totalVisitors_options);

                // Top Viewed Pages Chart
                let topViewed = [];
                let topViewed_view = [];

                @foreach($viewData as $views)
                topViewed.push('{{ $views['pageTitle'] }}');
                topViewed_view.push({{ $views['screenPageViews'] }});
                @endforeach

                let topViewed_options = {
                    series: [{
                        name: '@lang('dashboard.page_views')',
                        data: topViewed_view
                    }],
                    chart: {
                        type: 'bar',
                        height: 350
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 10,
                            dataLabels: {
                                position: 'top',
                            },
                            horizontal: true
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function (val) {
                            return val;
                        },
                        offsetY: 0,
                        offsetX: -20,
                        style: {
                            fontSize: '12px',
                            colors: ["#304758"]
                        }
                    },

                    xaxis: {
                        categories: topViewed,
                        position: 'bottom',
                        labels: {
                            offsetY: 0,
                        },
                        axisBorder: {
                            show: false
                        },
                        axisTicks: {
                            show: false
                        },
                        crosshairs: {
                            fill: {
                                type: 'gradient',
                                gradient: {
                                    colorFrom: '#D8E3F0',
                                    colorTo: '#BED1E6',
                                    stops: [0, 100],
                                    opacityFrom: 0.4,
                                    opacityTo: 0.5,
                                }
                            }
                        },
                        tooltip: {
                            enabled: true,
                        }
                    },
                    yaxis: {
                        axisBorder: {
                            show: false
                        },
                        axisTicks: {
                            show: false,
                        },
                        labels: {
                            show: true,
                            formatter: function (val) {
                                return val;
                            }
                        }

                    },
                    title: {
                        floating: true,
                        offsetY: 330,
                        align: 'center',
                        style: {
                            color: '#444'
                        }
                    }
                };
                viewed_chart = new ApexCharts(document.querySelector("#top_viewed_chart"), topViewed_options);
            }

            $('#refresh-button').on('click', function(){
                fetchDataAndUpdateCharts();
            });

            // Function to fetch data and update charts
            function fetchDataAndUpdateCharts() {
                $.ajax({
                    url: '{{ route('admin.analytics.fetch') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        date_range: $('#daterange').val()
                    },
                    success: function(data){
                        // Update Operating Systems Chart
                        let operating_systems = [];
                        let operating_systems_view = [];

                        data.operatingSystem.forEach(function(system){
                            operating_systems.push(system.operatingSystem);
                            operating_systems_view.push(system.screenPageViews);
                        });

                        operation_system_chart.updateOptions({
                            series: operating_systems_view,
                            labels: operating_systems
                        });

                        // Update User Types Chart
                        let user_types = [];
                        let user_types_view = [];

                        data.user_types.forEach(function(user_type){
                            if(user_type.newVsReturning === 'new'){
                                user_types.push('@lang('dashboard.user_type.new')');
                            } else if(user_type.newVsReturning === 'returning'){
                                user_types.push('@lang('dashboard.user_type.returning')');
                            } else {
                                user_types.push('@lang('dashboard.user_type.others')');
                            }
                            user_types_view.push(user_type.activeUsers);
                        });

                        user_type_chart.updateOptions({
                            series: user_types_view,
                            labels: user_types
                        });

                        // Update Browsers Chart
                        let browsers = [];
                        let browsers_view = [];

                        data.topBrowsers.forEach(function(browser){
                            browsers.push(browser.browser);
                            browsers_view.push(browser.screenPageViews);
                        });

                        browser_chart.updateOptions({
                            series: browsers_view,
                            labels: browsers
                        });

                        // Update Countries Chart
                        let topCountries = [];
                        let topCountries_view = [];

                        data.topCountries.forEach(function(country){
                            topCountries.push(country.country);
                            topCountries_view.push(country.screenPageViews);
                        });

                        countries_chart.updateOptions({
                            series: topCountries_view,
                            labels: topCountries
                        });

                        // Update Total Visitors and Page Views Chart
                        let TotalVisitorsAndPageViews = [];
                        let TotalVisitorsAndPageViews_view = [];
                        let TotalVisitorsAndPageViews_date = [];

                        data.TotalVisitorsAndPageViews.forEach(function(item){
                            TotalVisitorsAndPageViews.push(item.activeUsers);
                            TotalVisitorsAndPageViews_view.push(item.screenPageViews);
                            TotalVisitorsAndPageViews_date.push(item.date);
                        });

                        visitors_chart.updateOptions({
                            series: [
                                {
                                    name: "@lang('dashboard.total_visitors')",
                                    data: TotalVisitorsAndPageViews
                                },
                                {
                                    name: "@lang('dashboard.total_page_views')",
                                    data: TotalVisitorsAndPageViews_view
                                }
                            ],
                            xaxis: {
                                categories: TotalVisitorsAndPageViews_date
                            }
                        });

                        // Update Top Viewed Pages Chart
                        let topViewed = [];
                        let topViewed_view = [];

                        data.viewData.forEach(function(view){
                            topViewed.push(view.pageTitle);
                            topViewed_view.push(view.screenPageViews);
                        });

                        viewed_chart.updateOptions({
                            series: [{
                                name: '@lang('dashboard.page_views')',
                                data: topViewed_view
                            }],
                            xaxis: {
                                categories: topViewed
                            }
                        });

                        // Update theme mode if necessary
                        updateChartThemeMode(operation_system_chart, dashboard_theme_mode, dashboard_text_color);
                        updateChartThemeMode(user_type_chart, dashboard_theme_mode, dashboard_text_color);
                        updateChartThemeMode(browser_chart, dashboard_theme_mode, dashboard_text_color);
                        updateChartThemeMode(countries_chart, dashboard_theme_mode, dashboard_text_color);
                        updateChartThemeMode(visitors_chart, dashboard_theme_mode, dashboard_text_color);
                        updateChartThemeMode(viewed_chart, dashboard_theme_mode, dashboard_text_color);
                    }
                });
            }

            // Function to update chart theme mode
            function updateChartThemeMode(chart, theme_mode, color){
                chart.updateOptions({
                    theme: {
                        mode: theme_mode,
                        monochrome: {
                            enabled: false,
                            color: color
                        }
                    }
                });
            }

            updateChartThemeMode(viewed_chart, dashboard_theme_mode, dashboard_text_color);
        });
        @endcan
    </script>
@endsection
