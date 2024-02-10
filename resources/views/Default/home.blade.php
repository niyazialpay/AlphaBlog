@extends('Default.base')

@section('site_title', $seo_settings->title." | Anasayfa")
@section('site_keywords', $seo_settings->keywords)
@section('site_description', $seo_settings->description)

@section('canonical_url', config('app.url'))
@section('og_image', $general_settings->logo)

@section('tags')
    @foreach(explode(",", $seo_settings->keywords) as $item)
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

    </div>
@endsection

@section('recaptcha_action', "home")
