@extends('panel.base')
@section('title', __('dashboard.dashboard'))
@section('breadcrumb_link')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item active">@lang('dashboard.dashboard')</li>
    </ol>
@endsection
@section('content')
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
@endsection
@section('script')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>

        $(document).ready(function(){
            let operating_systems = [];
            let operating_systems_view = [];

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
            new ApexCharts(document.querySelector("#operating_system_chart"), operating_system_options).render();

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
            new ApexCharts(document.querySelector("#user_types_chart"), user_types_options).render();

            let browsers = [];
            let browsers_view = [];

            @foreach($topBrowsers as $browser)
            browsers.push('{{$browser['browser']}}');
            browsers_view.push({{$browser['screenPageViews']}});
            @endforeach

            let browsers_options = {
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
            new ApexCharts(document.querySelector("#browsers_chart"), browsers_options).render();

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
            new ApexCharts(document.querySelector("#countries_chart"), topCountries_options).render();

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
                                    fontWeight: 900
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
            new ApexCharts(document.querySelector("#total_visitors_chart"), totalVisitors_options).render();

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
            new ApexCharts(document.querySelector("#top_viewed_chart"), topViewed_options).render();
        });
    </script>
@endsection
