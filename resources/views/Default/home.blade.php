@extends('Default.base')

@section('site_title', $seo_settings->title)
@section('site_keywords', $seo_settings->keywords)
@section('site_description', $seo_settings->description)
@section('robots', $seo_settings->robots)
@section('site_author', $seo_settings->site_author)

@if($default_language == session('language')))
    @section('canonical_url', config('app.url'))
@else
    @section('canonical_url', config('app.url') . '/' . session('language'))
@endif

@section('href_lang')
@foreach($languages as $language)
    @if($language->code != session('language'))
        <link rel="alternate" hreflang="{{ $language->code }}" href="{{config('app.url')}}/{{ $language->code }}" />
    @endif
@endforeach
@endsection

@section('og_image', $general_settings->getFirstMediaUrl('site_favicon'))

@section('tags')
    @foreach(explode(',',$seo_settings->keywords) as $item)
        <meta property="article:tag" content="{{trim($item)}}" />
    @endforeach
@endsection

@section('slider_breadcrumb')
    <x-slider/>
@endsection

@section('content')
    <div class="col-md-8">
        <!-- Slider -->
        <div class="clearfix"></div>
        <x-home-posts :paginate="10" :skip="5"/>
    </div>
    @include('Default.sidebar')
@endsection

@section('scripts')
    <script src="{{config('app.url')}}/themes/Default/js/owl.carousel.min.js"></script>

    <script>
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
@endsection

