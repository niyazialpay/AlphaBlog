<!DOCTYPE html>
<html lang="{{session()->get('language')}}">
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

    <link rel="alternate" hreflang="x-default" href="{{config('app.name')}}">

    <meta property="og:url"                content="@yield('canonical_url')" />
    <meta property="og:type"               content="article" />
    <meta property="og:title"              content="@yield('site_title')" />
    <meta property="og:description"        content="@yield('site_description')" />
    <meta property="og:image"              content="@yield('og_image')">
    <meta property="og:image:url"          content="@yield('og_image')" />
    <meta property="og:image:secure_url"   content="@yield('og_image')" />
    <meta property="og:image:alt"          content="@yield('site_title')">
    @if($social_settings->facebook)
    <meta property="article:author"        content="https://www.facebook.com/{{ $social_settings->facebook }}">
    @endif

    <link href="https://niyazi.net/rss" rel="alternate" type="application/rss+xml" title=" RSS" />



    <meta name="twitter:card" content="summary" />
    <meta name="twitter:site" content="{{ "@".$social_settings->x }}" />
    <meta name="twitter:creator" content="{{ "@".$social_settings->x }}" />
    <meta name="twitter:description" content="@yield('site_description')" />
    <meta name="twitter:title" content="@yield('site_title')" />
    <meta name="twitter:image" content="@yield('og_image')" />
    <meta name="twitter:image:src" content="@yield('og_image')" />

    <link rel="canonical" href="@yield('canonical_url')" />

    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- Bootstrap -->
    <link rel="stylesheet" href="{{config('app.url')}}/themes/Default/css/bootstrap.min.css" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{config('app.url')}}/themes/Default/css/font-awesome.min.css" />

    <!-- Carousel -->
    <link rel="stylesheet" href="{{config('app.url')}}/themes/Default/css/owl.carousel.min.css" />
    <link rel="stylesheet" href="{{config('app.url')}}/themes/Default/css/owl.theme.default.css" />

    <!-- Main Style -->
    <link rel="stylesheet" href="{{config('app.url')}}/themes/Default/css/style.min.css" />


    <!-- Google Fonts -->
    <link href='https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,700' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Raleway:400,800,900' rel='stylesheet' type='text/css'>

    <!--[if lte IE 8]>
    <script src="{{config('app.url')}}/themes/Default/js/ie/html5shiv.js"></script><![endif]-->
    <!--[if lte IE 9]>
    <link rel="stylesheet" href="{{config('app.url')}}/themes/Default/css/ie9.css"/><![endif]-->
    <!--[if lte IE 8]>
    <link rel="stylesheet" href="{{config('app.url')}}/themes/Default/css/ie8.css"/><![endif]-->

    <script src="https://www.google.com/recaptcha/api.js?render={{config('Cryptograph.recaptcha_public')}}&amp;lang=tr"></script>

    <link rel="shortcut icon" href="{{app('general_settings')->getFirstMediaUrl('site_favicon')}}" type="image/x-icon">
    <link rel="icon" href="{{app('general_settings')->getFirstMediaUrl('site_favicon')}}" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="57x57" href="{{app('general_settings')->getFirstMediaUrl('site_favicon', 'r_57x57')}}" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="60x60" href="{{app('general_settings')->getFirstMediaUrl('site_favicon', 'r_60x60')}}" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="72x72" href="{{app('general_settings')->getFirstMediaUrl('site_favicon', 'r_72x72')}}" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="76x76" href="{{app('general_settings')->getFirstMediaUrl('site_favicon', 'r_76x76')}}" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="114x114" href="{{app('general_settings')->getFirstMediaUrl('site_favicon', 'r_114x114')}}" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="120x120" href="{{app('general_settings')->getFirstMediaUrl('site_favicon', 'r_120x120')}}" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="144x144" href="{{app('general_settings')->getFirstMediaUrl('site_favicon', 'r_144x144')}}" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="152x152" href="{{app('general_settings')->getFirstMediaUrl('site_favicon', 'r_152x152')}}" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="180x180" href="{{app('general_settings')->getFirstMediaUrl('site_favicon', 'r_180x180')}}" type="image/x-icon">
    <link rel="icon" type="image/png" sizes="192x192"  href="{{app('general_settings')->getFirstMediaUrl('site_favicon', 'r_192x192')}}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{app('general_settings')->getFirstMediaUrl('site_favicon', 'r_32x32')}}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{app('general_settings')->getFirstMediaUrl('site_favicon', 'r_96x96')}}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{app('general_settings')->getFirstMediaUrl('site_favicon', 'r_16x16')}}">
    <link rel="manifest" href="{{config('app.url')}}/themes/Default/images/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="{{app('general_settings')->getFirstMediaUrl('site_favicon', 'r_144x144')}}">
    <meta name="theme-color" content="#ffffff">

    <script type='text/javascript' src='https://platform-api.sharethis.com/js/sharethis.js#property=656b066564d64c00127f1132&product=inline-share-buttons' async='async'></script>

    <!-- google developer schema coding -->
    <script type="application/ld+json">
        {
            "@context": "http://schema.org",
            "@type": "Organization",
            "name": "Niyazi.Net",
            "legalName" : "Niyazi.Net Yazılımcı Güncesi",
            "url": "https://niyazi.net",
            "logo": "{{config('app.url')}}/themes/Default/images/logo.svg",
            "sameAs": [
                "https://twitter.com/niyazialpay",
                "https://www.instagram.com/niyazialpay",
                "https://www.facebook.com/MNiyaziAlpay/"
            ]
        }

        {
            "@context":"http://schema.org",
            "@type":"WebSite",
            "url":"https://niyazi.net/",
            "name":"Niyazi.Net Yazılımcı Güncesi",
            "potentialAction":[{
                "@type":"SearchAction",
                "target":"https://niyazi.net/?s={search_term}",
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

    <script data-ad-client="ca-pub-6624557470838526" async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
</head>
<body >

<!-- The Modal -->
<div id="search_modal" class="custom-modal">
    <div class="custom-modal-header">
        <div id="search_header">Aramayan Bulamaz</div><span class="close">&times;</span>
        <div class="clearfix"></div>
    </div>
    <!-- Modal content -->
    <div class="custom-modal-content">
        <form action="javascript:void(0);" id="search_form" class="form-horizontal">
            <label for="search_input"></label>
            <input type="search" id="search_input" name="search_input" placeholder="Aranacak kelime..." class="form-control" autofocus required>
        </form>
    </div>
    <div class="custom-modal-footer">
        <div class="col-lg-10 col-md-10 col-sm-12" id="search_message"></div>
        <div class="col-lg-2 col-md-2 col-sm-12 align-right">
            <button type="button" class="btn btn-primary mr-auto" id="search_button">Ara</button>
        </div>
        <div class="clearfix"></div>
    </div>
</div>

<!-- preloader -->
<!--div id="preloader">
    <div id="status"> <img src="{{config('app.url')}}/themes/Default/images/logo.svg" id="loader" height="128" width="128" alt="Yükleniyor"> </div>
</div-->


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
            <a href="/" class="navbar-brand"><img src="{{config('app.url')}}/themes/Default/images/logo.svg" height="32" alt="logo" id="logo"></a>
        </div>
        <div class="collapse navbar-collapse" id="main_nav">
            <div class=" pull-right hidden-xs hidden-sm">
                <ul class="nav social-links">
                    <li><a href="javascript:void(0);" class="searchbutton" title="Ara"><i class="fa fa-search searchbutton" title="Ara"></i></a></li>
                    <li><a href="https://github.com/niyazialpay" target="_blank" rel="nofollow"><i class="fa fa-github"></i></a></li>
                    <li><a href="https://www.linkedin.com/in/niyazialpay/" target="_blank" rel="nofollow"><i class="fa fa-linkedin"></i></a></li>
                    <li><a href="https://facebook.com/MNiyaziAlpay" target="_blank" rel="nofollow"><i class="fa fa-facebook"></i></a></li>
                    <li><a href="https://twitter.com/niyazialpay" target="_blank" rel="nofollow"><i class="fa fa-twitter"></i></a></li>
                    <li><a href="https://instagram.com/niyazialpay" target="_blank" rel="nofollow"><i class="fa fa-instagram"></i> </a></li>
                    <li><a href="/rss" target="_blank"><i class="fa fa-rss"></i> </a></li>
                </ul>

            </div>
            <x-header-menu/>
        </div>
    </div>
</nav>

@yield('slider_breadcrumb')
<!-- End navigation -->
<div id="main"@yield("marginclass")>
    <div class="container">
        <div class="row">
            @yield('icerik')

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

                        <li><a href="https://github.com/niyazialpay" target="_blank" rel="nofollow"><i class="fa fa-github"></i></a></li>
                        <li><a href="https://www.linkedin.com/in/niyazialpay/" target="_blank" rel="nofollow"><i class="fa fa-linkedin"></i></a></li>
                        <li><a href="https://facebook.com/MNiyaziAlpay" target="_blank" rel="nofollow"><i class="fa fa-facebook"></i> </a></li>
                        <li><a href="https://twitter.com/niyazialpay" target="_blank" rel="nofollow"><i class="fa fa-twitter"></i></a></li>
                        <li><a href="https://instagram.com/niyazialpay" target="_blank" rel="nofollow"><i class="fa fa-instagram"></i> </a></li>
                        <li><a href="/rss" target="_blank"><i class="fa fa-rss"></i> </a></li>
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
<script src="{{config('app.url')}}/themes/Default/js/owl.carousel.min.js"></script>

<link href="{{config('app.url')}}/themes/Default/css/custombox.min.css" rel="stylesheet">
<script src="{{config('app.url')}}/themes/Default/js/custombox.min.js"></script>
<script src="{{config('app.url')}}/themes/Default/js/custombox.legacy.min.js"></script>

<script>
    function getRecaptchaToken() {
        grecaptcha.ready(function() {
            grecaptcha.execute('{{config('Cryptograph.recaptcha_public')}}', { action: '@yield("recaptcha_action")' }).then(function(token) {
                $('.recaptcha_response').val(token);
            });
        });
    }
</script>

<script src="{{config('app.url')}}/themes/Default/js/main.min.js"></script>

<script>

    getRecaptchaToken();
    // Slider
    jQuery('.owl-carousel').owlCarousel({
        loop:true,
        autoplay:true,
        margin:10,
        nav:true,
        navText: ['<i class="fa fa-angle-left"></i>','<i class="fa fa-angle-right"></i>'],
        responsiveClass: true,
        items:1,
        dots:false,
        responsive:{
            0:{
                items:1,
                nav:true
            },
            600:{
                items:2
            }
        }
    });
</script>
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-28792004-12"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'UA-28792004-12');
</script>

</body>
</html>
