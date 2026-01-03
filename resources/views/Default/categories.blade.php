@extends('Default.base')

@section('site_title', $category->name)
@section('site_keywords', $category->meta_keywords)
@section('site_description', $category->meta_description)

@section('canonical_url', url()->current())

@if($category->href_lang)
    @section('href_lang')
        @foreach(app('languages') as $n => $language)
            @if(array_key_exists($language->code, json_decode($category->href_lang, true)))
                <link rel="alternate" hreflang="{{$language->code}}" href="{{json_decode($category->href_lang, true)[$language->code]}}"/>
            @endif
        @endforeach
    @endsection
@endif

@section('og_image', $general_settings->getFirstMediaUrl('site_favicon'))

@section('tags')
    @foreach(explode(",", $category->meta_keywords) as $item)
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
    @if(isset($category))
            @lang('post.category') <strong>{{$category->name}}</strong>
    @endif
            </div>
            <div class="breadcumbs">
                <ul>
                    <li>
                        <a href="{{route('home', ['language' => session('language')])}}">
                            <i class=" @if(config('settings.fontawesome_pro')) fa-duotone @else fa-solid @endif  fa-house"></i>
                        </a>
                    </li>
                    @if(isset($category))
                    <li>
                        <a href="{{route('post.categories', ['language' => session('language'), __('routes.categories'), $category->slug])}}">
                            {{$category->name}}
                        </a>
                    </li>
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
        <x-posts-components :category="$category" :paginate="10"/>
    </div>
    @include('Default.sidebar')
@endsection
