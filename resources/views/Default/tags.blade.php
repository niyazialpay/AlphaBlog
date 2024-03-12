@extends('Default.base')

@section('site_title', __('tags.page_title'))
@section('site_keywords', implode(',', $seo_settings->keywords))
@section('site_description', $seo_settings->description)

@section('canonical_url', url()->current())
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
                <strong>@lang('tags.page_title'):  {{$tag}}</strong>
            </div>
            <div class="breadcumbs">
                <ul>
                    <li>
                        <a href="{{route('home', ['language' => session('language')])}}">
                            <i class="fa-duotone fa-house"></i>
                        </a>
                    </li>
                    <li>
                        <a href="{{route('post.tags', ['language' => session('language'), __('routes.tags'), $tag])}}">
                        @lang('tags.page_title')
                        </a>
                    </li>
                    <li>
                        {{$tag}}
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
