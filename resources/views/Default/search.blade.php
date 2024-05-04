@extends('Default.base')

@section('site_title', __('post.search_results_for'). ' ' . $search)
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

@section('og_image', $general_settings->logo)

@section('tags')
    @foreach(explode(',', $seo_settings->keywords) as $item)
        <meta property="article:tag" content="{{trim($item)}}" />
    @endforeach
@endsection

@section('slider_breadcrumb')
    <section class="container content-posts">
        <div class="status-message">
            <div class="message">
                @if(isset($search))
                    @lang('post.search_results_for') <strong>{{$search}}</strong>
                @endif
            </div>
            <div class="breadcumbs">
                <ul>
                    <li>
                        <a href="{{route('home', ['language' => session('language')])}}">
                            <i class="fa-duotone fa-house"></i>
                        </a>
                    </li>
                    @if(isset($search))
                        <li>
                            <a href="{{route('search.result', ['language' => session('language'), __('routes.search'), $search])}}">
                                @lang('home.search')
                            </a>
                        </li>
                        <li>{{$search}}</li>
                    @endif
                </ul>
            </div>
        </div>
    </section>
@endsection

@section('content')
    <div class="col-md-8">
        <!-- Slider -->
        <div class="clearfix"></div>
        <x-posts-compone    nts :search="$search" :paginate="10"/>
    </div>
    @include('Default.sidebar')
@endsection
