@extends('Default.base')

@section('site_title', __('archives.page_title'))
@section('site_keywords', implode(',',$seo_settings->keywords))
@section('site_description', $seo_settings->description)

@section('canonical_url', config('app.url'))
@section('og_image', $general_settings->logo)

@section('tags')
    @foreach($seo_settings->keywords as $item)
        <meta property="article:tag" content="{{trim($item)}}" />
    @endforeach
@endsection

@section('slider_breadcrumb')
    <section class="container content-posts">
        <div class="status-message">
            <div class="message">
                <strong>@lang('archives.page_title')  {{$date}}</strong>
            </div>
            <div class="breadcumbs">
                <ul>
                    <li>
                        <a href="{{route('home', ['language' => session('language')])}}">
                            <i class="fa-duotone fa-house"></i>
                        </a>
                    </li>
                    <li>
                        @lang('archives.page_title')
                    </li>
                </ul>
            </div>
        </div>
    </section>
@endsection

@section('content')
    <div class="col-md-8">
        <div class="clearfix"></div>
        @include('Default.components.posts.posts')
    </div>
    @include('Default.sidebar')
@endsection
