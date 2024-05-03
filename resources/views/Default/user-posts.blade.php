@extends('Default.base')

@section('site_title', $user->nickname)
@section('site_keywords', $seo_settings->keywords)
@section('site_description', $seo_settings->description)
@section('site_author', $user->nickname)

@section('canonical_url', url()->current())

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
                {{$user->nickname}}'s Posts
            </div>
            <div class="breadcumbs">
                <ul>
                    <li>
                        <a href="{{route('home', ['language' => session('language')])}}">
                            <i class="fa-duotone fa-house"></i>
                        </a>
                    </li>
                    <li>
                        @lang('user.user')
                    </li>
                    <li>
                        {{$user->nickname}}
                    </li>
                </ul>
            </div>
        </div>
    </section>
@endsection

@section('content')
    <div class="col-md-8">
        <!-- Slider -->
        <div class="clearfix"></div>
        <x-posts-components :user="$user" :paginate="10"/>
    </div>
    @include('Default.sidebar')
@endsection
