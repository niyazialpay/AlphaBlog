@extends('panel.base')
@section('title', __('dashboard.dashboard'))
@section('breadcrumb_link')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item active">@lang('dashboard.dashboard')</li>
    </ol>
@endsection
@section('content')
    @can('admin', 'App\Models\User')
    <div class="row">
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
    </div>

    <div class="row">
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
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>

        $(document).ready(function(){
            @can('admin', 'App\Models\User')
            let operating_systems = [];
            let operating_systems_view = [];

            let dashboard_theme_mode;
            let dashboard_text_color;

            if(localStorage.getItem("dark-mode") === "true"){
                dashboard_theme_mode = 'dark';
                dashboard_text_color = '#ffffff';
            }
            else{
                dashboard_theme_mode = 'light';
                dashboard_text_color = '#000000';
            }


            @foreach($operatingSystem as $system)
            operating_systems.push('{{$system['operatingSystem']}}');
            operating_systems_view.push({{$system['screenPageViews']}});
            @endforeach
            let operating_system_options = {
                series: operating_systems_view,
                labels: operating_systems,
                chart: {
                    type: 'pie'
                },
                plotOptions: {
                    pie: {
                        customScale: 1
                    }
                }
            };
           let operation_system_chart = new ApexCharts(document.querySelector("#operating_system_chart"), operating_system_options);

            let user_types = [];
            let user_types_view = [];

            @foreach($user_types as $user_type)
                @if($user_type['newVsReturning']== 'new')
                    user_types.push('@lang('dashboard.user_type.'.$user_type['newVsReturning'])');
                @elseif($user_type['newVsReturning']== 'returning')
                    user_types.push('@lang('dashboard.user_type.'.$user_type['newVsReturning'])');
                @else
                    user_types.push('@lang('dashboard.user_type.others')');
               @endif
            user_types_view.push({{$user_type['activeUsers']}});
            @endforeach
            let user_types_options = {
                series: user_types_view,
                labels: user_types,
                chart: {
                    type: 'pie'
                },
                plotOptions: {
                    pie: {
                        customScale: 1
                    }
                }
            };
            let user_type_chart = new ApexCharts(document.querySelector("#user_types_chart"), user_types_options);

            let browsers = [];
            let browsers_view = [];

            @foreach($topBrowsers as $browser)
            browsers.push('{{$browser['browser']}}');
            browsers_view.push({{$browser['screenPageViews']}});
            @endforeach

            let browsers_options = {
                series: browsers_view,
                labels: browsers,
                chart: {
                    type: 'pie'
                },
                plotOptions: {
                    pie: {
                        customScale: 1
                    }
                }
            };
            let browser_chart = new ApexCharts(document.querySelector("#browsers_chart"), browsers_options);

            let topCountries = [];
            let topCountries_view = [];

            @foreach($topCountries as $country)
            topCountries.push('{{$country['country']}}');
            topCountries_view.push({{$country['screenPageViews']}});
            @endforeach

            let topCountries_options = {
                series: topCountries_view,
                labels: topCountries,
                chart: {
                    type: 'pie'
                },
                plotOptions: {
                    pie: {
                        customScale: 1
                    }
                }
            };
            let countries_chart = new ApexCharts(document.querySelector("#countries_chart"), topCountries_options);

            let TotalVisitorsAndPageViews = [];
            let TotalVisitorsAndPageViews_view = [];
            let TotalVisitorsAndPageViews_date = [];

            @foreach($TotalVisitorsAndPageViews as $TotalVisitorsAndPageView)
            TotalVisitorsAndPageViews.push({{$TotalVisitorsAndPageView['activeUsers']}})
            TotalVisitorsAndPageViews_view.push({{$TotalVisitorsAndPageView['screenPageViews']}})
            TotalVisitorsAndPageViews_date.push('{{$TotalVisitorsAndPageView['date']}}')
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
                    stacked: true,
                    toolbar: {
                        show: true
                    },
                    zoom: {
                        enabled: true
                    }
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        legend: {
                            position: 'bottom',
                            offsetX: -10,
                            offsetY: 0
                        }
                    }
                }],
                plotOptions: {
                    bar: {
                        horizontal: false,
                        borderRadius: 10,
                        dataLabels: {
                            total: {
                                enabled: true,
                                style: {
                                    fontSize: '13px',
                                    fontWeight: 900,
                                    color: '#009500'
                                }
                            }
                        }
                    },
                },
                xaxis: {
                    type: 'datetime',
                    categories: TotalVisitorsAndPageViews_date,
                },
                legend: {
                    position: 'right',
                    offsetY: 40
                },
                fill: {
                    opacity: 1
                }
            };
            let visitors_chart = new ApexCharts(document.querySelector("#total_visitors_chart"), totalVisitors_options);

            let topViewed = [];
            let topViewed_view = [];

            @foreach($viewData as $views)
            topViewed.push('{{$views['pageTitle']}}');
            topViewed_view.push({{$views['screenPageViews']}});
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
                        show: false,
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
            let viewed_chart = new ApexCharts(document.querySelector("#top_viewed_chart"), topViewed_options);

            user_type_chart.render();
            browser_chart.render();
            countries_chart.render();
            visitors_chart.render();
            viewed_chart.render();
            operation_system_chart.render();

            @endcan

            $('#dark-mode-switcher-button').on('click', function(){
                if(localStorage.getItem("dark-mode") === "true"){
                    dashboard_theme_mode = 'dark';
                    dashboard_text_color = '#ffffff';
                }
                else{
                    dashboard_theme_mode = 'light';
                    dashboard_text_color = '#000000';
                }
                @can('admin', 'App\Models\User')
                updateChartThemeMode(user_type_chart, dashboard_theme_mode, dashboard_text_color);
                updateChartThemeMode(browser_chart, dashboard_theme_mode, dashboard_text_color);
                updateChartThemeMode(countries_chart, dashboard_theme_mode, dashboard_text_color);
                updateChartThemeMode(visitors_chart, dashboard_theme_mode, dashboard_text_color);
                updateChartThemeMode(viewed_chart, dashboard_theme_mode, dashboard_text_color);
                updateChartThemeMode(operation_system_chart, dashboard_theme_mode, dashboard_text_color);
                @endcan
            });
            @can('admin', 'App\Models\User')
            updateChartThemeMode(user_type_chart, dashboard_theme_mode, dashboard_text_color);
            updateChartThemeMode(browser_chart, dashboard_theme_mode, dashboard_text_color);
            updateChartThemeMode(countries_chart, dashboard_theme_mode, dashboard_text_color);
            updateChartThemeMode(visitors_chart, dashboard_theme_mode, dashboard_text_color);
            updateChartThemeMode(viewed_chart, dashboard_theme_mode, dashboard_text_color);
            updateChartThemeMode(operation_system_chart, dashboard_theme_mode, dashboard_text_color);
            @endcan
        });

        @can('admin', 'App\Models\User')
        function updateChartThemeMode(chart, theme_mode, color){
            chart.updateOptions({
                theme: {
                    mode: theme_mode,
                    palette: 'palette1',
                    monochrome: {
                        color: color,
                        shadeTo: theme_mode,
                    },
                }
            });
        }
        @endcan
    </script>
@endsection
