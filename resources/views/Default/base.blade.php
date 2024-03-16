<!DOCTYPE html>
<html lang="{{session('language')}}">
<head>
    <meta charset="utf-8" />
    <title>@yield('site_title')</title>
    <meta name="description" content="@yield('site_description')">
    <meta name="keywords" content="@yield('site_keywords')">
    <meta name="author" content="@yield('site_author')">
    <meta name="robots" content="@yield('robots')" />

    @yield('tags')

    <link rel="dns-prefetch" href="{{config('app.url')}}" />
    <link rel="dns-prefetch" href="https://mc.yandex.ru" />
    <link rel="dns-prefetch" href="https://www.google.com" />
    <link rel="dns-prefetch" href="https://fonts.googleapis.com" />
    <link rel="dns-prefetch" href="https://www.googletagmanager.com" />
    <link rel="dns-prefetch" href="https://s7.addthis.com" />
    <link rel="dns-prefetch" href="https://v1.addthisedge.com" />
    <link rel="dns-prefetch" href="https://m.addthis.com" />
    <link rel="dns-prefetch" href="https://z.moatads.com" />
    <link rel="dns-prefetch" href="https://www.google-analytics.com" />

    <meta property="og:url"                content="@yield('canonical_url')" />
    <meta property="og:type"               content="article" />
    <meta property="og:title"              content="@yield('site_title')" />
    <meta property="og:description"        content="@yield('site_description')" />
    <meta property="og:image"              content="@yield('og_image')">
    <meta property="og:image:url"          content="@yield('og_image')" />
    <meta property="og:image:secure_url"   content="@yield('og_image')" />
    <meta property="og:image:alt"          content="@yield('site_title')">
    @if($social_networks->facebook)
    <meta property="article:author"        content="https://www.facebook.com/{{ $social_networks->facebook }}">
    @endif

    <link href="{{route('rss', ['language' => session()->get('language')])}}" rel="alternate" type="application/rss+xml" title="RSS" />

    <meta name="twitter:card" content="summary" />
    <meta name="twitter:site" content="{{ "@".app('social_networks')->x }}" />
    <meta name="twitter:creator" content="{{ "@".app('social_networks')->x }}" />
    <meta name="twitter:description" content="@yield('site_description')" />
    <meta name="twitter:title" content="@yield('site_title')" />
    <meta name="twitter:image" content="@yield('og_image')" />
    <meta name="twitter:image:src" content="@yield('og_image')" />

    <link rel="canonical" href="@yield('canonical_url')" />
    @yield('href_lang')

    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- Bootstrap -->
    <link rel="stylesheet" href="{{config('app.url')}}/themes/Default/css/bootstrap.min.css" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{config('app.url')}}/themes/fontawesome/css/all.css" />

    <!-- Carousel -->
    <link rel="stylesheet" href="{{config('app.url')}}/themes/Default/css/owl.carousel.min.css" />
    <link rel="stylesheet" href="{{config('app.url')}}/themes/Default/css/owl.theme.default.css" />

    <!-- Main Style -->
    <link rel="stylesheet" href="{{config('app.url')}}/themes/Default/css/style.min.css" />

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <!-- Google Fonts -->
    <link href='https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,700' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Raleway:400,800,900' rel='stylesheet' type='text/css'>

    <!--[if lte IE 8]>
    <script src="{{config('app.url')}}/themes/Default/js/ie/html5shiv.js"></script><![endif]-->
    <!--[if lte IE 9]>
    <link rel="stylesheet" href="{{config('app.url')}}/themes/Default/css/ie9.css"/><![endif]-->
    <!--[if lte IE 8]>
    <link rel="stylesheet" href="{{config('app.url')}}/themes/Default/css/ie8.css"/><![endif]-->

    <link rel="shortcut icon" href="{{$general_settings->getFirstMediaUrl('site_favicon')}}" type="image/x-icon">
    <link rel="icon" href="{{$general_settings->getFirstMediaUrl('site_favicon')}}" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="57x57" href="{{$general_settings->getFirstMediaUrl('site_favicon', 'r_57x57')}}" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="60x60" href="{{$general_settings->getFirstMediaUrl('site_favicon', 'r_60x60')}}" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="72x72" href="{{$general_settings->getFirstMediaUrl('site_favicon', 'r_72x72')}}" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="76x76" href="{{$general_settings->getFirstMediaUrl('site_favicon', 'r_76x76')}}" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="114x114" href="{{$general_settings->getFirstMediaUrl('site_favicon', 'r_114x114')}}" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="120x120" href="{{$general_settings->getFirstMediaUrl('site_favicon', 'r_120x120')}}" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="144x144" href="{{$general_settings->getFirstMediaUrl('site_favicon', 'r_144x144')}}" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="152x152" href="{{$general_settings->getFirstMediaUrl('site_favicon', 'r_152x152')}}" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="180x180" href="{{$general_settings->getFirstMediaUrl('site_favicon', 'r_180x180')}}" type="image/x-icon">
    <link rel="icon" type="image/png" sizes="192x192"  href="{{$general_settings->getFirstMediaUrl('site_favicon', 'r_192x192')}}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{$general_settings->getFirstMediaUrl('site_favicon', 'r_32x32')}}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{$general_settings->getFirstMediaUrl('site_favicon', 'r_96x96')}}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{$general_settings->getFirstMediaUrl('site_favicon', 'r_16x16')}}">
    <link rel="manifest" href="{{config('app.url')}}/themes/Default/images/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="{{$general_settings->getFirstMediaUrl('site_favicon', 'r_144x144')}}">
    <meta name="theme-color" content="#ffffff">

    {!! $general_settings->sharethis !!}

    <!-- google developer schema coding -->
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "Organization",
            "image": "{{$general_settings->getFirstMediaUrl('site_logo_light')}}",
            "url": "{{config('app.url')}}",
            "sameAs": [
        @foreach($languages as $n => $language)
            "{{route('home', ['language' => $language->code])}}"@if($n < $languages->count() - 1),@endif
        @endforeach
        ],
        "logo": "{{$general_settings->getFirstMediaUrl('site_logo_light')}}",
            "name": "{{$seo_settings->site_name}}",
            "description": "{{ $seo_settings->description }}",
            "email": "{{ $general_settings->contact_email }}"
        }
    </script>

    <script type="application/ld+json">
        {
            "@context":"https://schema.org",
            "@type":"WebSite",
            "name":"{{$seo_settings->site_name}}",
            "url":"{{route('home', ['language' => session('language')])}}",
            "potentialAction":[{
                "@type":"SearchAction",
                "target":"{{route('home', ['language' => session('language')])}}?search={search_term}",
                "query-input": {
                    "@type": "PropertyValueSpecification",
                    "valueRequired": true,
                    "valueMaxlength": 150,
                    "valueName": "search_term"
                }
            }]
        }
    </script>

    @yield('rich_snipped')

    {!! $ad_settings?->google_ad_manager !!}
</head>
<body >

<!-- The Modal -->
<div id="search_modal" class="custom-modal">
    <div class="custom-modal-header">
        <div id="search_header">@lang('post.search_title')</div><span class="close">&times;</span>
        <div class="clearfix"></div>
    </div>
    <!-- Modal content -->
    <div class="custom-modal-content">
        <form action="javascript:void(0);" id="search_form" class="form-horizontal">
            <label for="search_input"></label>
            <input type="search" id="search_input" name="search_input" placeholder="@lang('post.search_placeholder')" class="form-control" autofocus required>
        </form>
    </div>
    <div class="custom-modal-footer">
        <div class="col-lg-10 col-md-10 col-sm-12" id="search_message"></div>
        <div class="col-lg-2 col-md-2 col-sm-12 align-right">
            <button type="button" class="btn btn-primary mr-auto" id="search_button">@lang('post.search')</button>
        </div>
        <div class="clearfix"></div>
    </div>
</div>


<!--Navigation-->
<nav class="navbar navbar-default navbar-static-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#main_nav" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a href="/" class="navbar-brand"><img src="{{$general_settings->getFirstMediaUrl('site_logo_light')}}" height="32" alt="{{$seo_settings->site_name}}" id="logo"></a>
        </div>
        <div class="collapse navbar-collapse" id="main_nav">
            <div class=" pull-right hidden-xs hidden-sm">
                <ul class="nav social-links">
                    <li><a href="javascript:void(0);" class="searchbutton" title="Ara"><i class="fa fa-search searchbutton" title="@lang('post.search')"></i></a></li>
                    <x-menu.social-menu :show="$social_settings->social_networks_header"/>
                </ul>

            </div>
            <x-menu.header-menu/>
        </div>
    </div>
</nav>

@yield('slider_breadcrumb')
<!-- End navigation -->
<div id="main"@yield("marginclass")>
    <div class="container">
        <div class="row">
            @yield('content')
        </div>
    </div> <!-- End Container -->
    <div id="instagram-footer">
    </div>

    <!--back-to-top-->

    <div id="back-to-top">
        <a href="#top"><i class="fa fa-arrow-up"></i></a>
    </div>

    <footer class="text-center footer">
        <div class="container">
            <div class="row">
                <div class="full">
                    <ul class="quick-link">
                        <x-menu.social-menu :show="$social_settings->social_networks_footer"/>
                    </ul>

                    <div class="copy-right">
                        <p>&copy; {{date('Y')}} - All Rights Reserved.</p>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</div><!-- End Main -->

<script src="{{config('app.url')}}/themes/Default/js/jquery-3.2.1.min.js"></script>
<script src="{{config('app.url')}}/themes/Default/js/bootstrap.min.js"></script>
<script src="{{config('app.url')}}/themes/Default/js/main.js"></script>
<link href="{{config('app.url')}}/themes/Default/css/custombox.min.css" rel="stylesheet">
<script src="{{config('app.url')}}/themes/Default/js/custombox.min.js"></script>
<script src="{{config('app.url')}}/themes/Default/js/custombox.legacy.min.js"></script>

@yield('scripts')
<!-- Global site tag (gtag.js) - Google Analytics -->
{!! $analytic_settings?->google_analytics !!}

<script>
    $(document).ready(function(){
        $(".searchbutton").click(function(){
            $("#search_modal").css("display", "block");
        });
        $(".close").click(function(){
            $("#search_modal").css("display", "none");
        });
        $("#search_button").click(function(){
            let search_input = $("#search_input").val();
            if(search_input.length > 0){
                window.location.href = "{{config('app.url')}}/{{session('language')}}?search="+search_input;
            }else{
                $("#search_message").html("{{__('post.search_input_empty')}}");
            }
        });
    });
</script>
</body>
</html>
