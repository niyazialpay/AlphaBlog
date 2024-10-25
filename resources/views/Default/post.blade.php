@extends('Default.base')

@section('site_title', stripslashesNull($post->title))
@section('site_keywords', $post->meta_keywords)
@section('site_description', stripslashesNull($post->meta_description))

@if($post->is_published)
    @section('robots', 'index, follow')
@else
    @section('robots', 'noindex, nofollow')
@endif

@section('site_author', $post->user->nickname)

@section('canonical_url', url()->current())

@if($post->media->where('collection_name', 'posts')->last())
    @php($media = $post->media->where('collection_name', 'posts')->last())
    @section('og_image', route('image', [
    'path' => $media->id,
    'width' => 800,
    'height' => 400,
    'type' => 'cover',
    'image' => $media->file_name
]))
@else
    @section('og_image', $general_settings->getFirstMediaUrl('site_favicon'))
@endif
@if($post->href_lang)
    @section('href_lang')
        @foreach(app('languages') as $n => $language)
            @if(array_key_exists($language->code, json_decode($post->href_lang, true)))
                <link rel="alternate" hreflang="{{$language->code}}" href="{{config('app.url')}}{{json_decode($post->href_lang, true)[$language->code]}}"/>
            @endif
        @endforeach
    @endsection
@endif

@section('tags')
    @foreach(explode(',', $post->meta_keywords) as $item)
        <meta property="article:tag" content="{{trim($item)}}"/>
    @endforeach
@endsection

@section('marginclass'){!! ' class="top-margin"' !!}@endsection

@section('content')
    <div class="col-md-12">
        <article class="post">
            <header>
                <div class="title">
                    <h2>
                        <a href="{{route('page', ['language' => $post->language, $post])}}">{{stripslashes($post->title)}}</a>
                    </h2>
                </div>
                <div class="meta">
                    <time class="published" datetime="{{dateformat($post->created_at, 'Y-m-d')}}">
                        <a href="{{route('post.archives', [
                            session('language'),
                            __('routes.archives'),
                            'year' => dateformat($post->created_at, 'Y'),
                            'month' => dateformat($post->created_at, 'm'),
                            'day' => dateformat($post->created_at, 'd')
                            ])}}">
                            {{dateformat($post->created_at, 'd M. Y', locale: session('language'))}}
                        </a>
                    </time>
                    <a href="{{route('user.posts', [
                            'language' => $post->language, __('routes.user'), $post->user->nickname
                            ])}}" class="author">
                        <span class="name">{{$post->user->nickname}}</span>
                        <img class="lazy"
                             src="{{route('cdn', '/themes/Default/images/loading.svg')}}"
                             data-src="https://www.gravatar.com/avatar/{{hash('sha256', strtolower(trim($post->user->email)))}}"
                             alt="{{$post->user->nickname}}"/>
                    </a>
                </div>
            </header>
            @if($post->media->where('collection_name', 'posts')->last())
            @php($media = $post->media->where('collection_name', 'posts')->last())
            <div class="image featured">
                <img class="lazy"
                     src="{{route('cdn', '/themes/Default/images/loading.svg')}}"
                     data-src="{{route('image', [
                        'path' => $media->id,
                        'width' => 800,
                        'height' => 400,
                        'type' => 'cover',
                        'image' => $media->file_name
                    ])}}" alt="{{stripslashesNull($post->title)}}"/>
            </div>
            @endif
            {!! stripslashesNull($post->content) !!}
            <footer>
                <div class="social actions">
                    <div class="col-sm-12">
                        <span class="col-12">@lang('post.tags'):</span>
                        <ul class="tags stats col-12">
                            @foreach(explode(',', $post->meta_keywords) as $item)
                                <li>
                                    <a href="{{route('post.tags',
                                        [
                                            'language' => session('language'),
                                            __('routes.tags'),
                                            stripslashesNull(trim($item))
                                        ])}}">
                                        {{stripslashesNull(trim($item))}}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <hr>
                    <div class="sharethis-inline-share-buttons"></div>
                </div>

                <ul class="stats">
                    @foreach($post->categories as $category)
                        <span class="badge badge-primary">
                            <a href="{{route('post.categories',
                                ['language' => session('language'), __('routes.categories'), $category->slug])}}"
                               class="text-white">
                                {{stripslashesNull($category->name)}}
                            </a>
                        </span>
                    @endforeach
                </ul>

            </footer>
        </article>

        <x-similar-posts :post="$post" :limit="3"/>

        @if($post->post_type=="post")
        <!-- Blog Comments Begins -->
        <div class="blog-comments">
            <div class="blog-comment-main">
                <h3>{{trans_choice('post.comments', $post->comments->count())}}</h3>
                @foreach($post->comments as $item)
                    <div class="blog-comment">
                        <a id="comment-{{$item->id}}"></a>
                        <a class="comment-avtar">
                            <img class="lazy"
                                 src="{{route('cdn', '/themes/Default/images/loading.svg')}}"
                                 data-src="https://www.gravatar.com/avatar/{{hash('sha256', strtolower(trim($item->user?->email ?? $item->email)))}}"
                                 alt="{{$item->user?->nickname ?? $item->name}}"/>
                        </a>
                        <div class="comment-text">
                            <h3>{{$item->user?->nickname ?? $item->name}}</h3>
                            <h5>{{dateformat($item->created_at, "d M Y H:i", locale: session('language'), timezone: config('app.timezone'))}}</h5>
                            <p>{!! stripslashes($item->comment) !!}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <!-- Blog Contact Form Begins -->
        <div class="contact-form pad-top-big pad-bottom-big">
            <h3>
                @if($post->comments->count()>0)
                    @lang('post.leave_comment_too')
                @else
                    @lang('post.leave_comment')
                @endif
            </h3>
            <form method="post" action="javascript:void(0);" id="commentPanel">
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 no-pad-left">
                    <div class="form-group">
                        <label for="name"></label>
                        <input type="text" id="name" name="name" placeholder="@lang('user.name') @lang('user.surname')" required>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 no-pad-right">
                    <div class="form-group">
                        <label for="email"></label>
                        <input type="email" id="email" name="email" placeholder="@lang('user.email')" required/>
                    </div>
                </div>
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 no-padding">
                    <div class="form-group">
                        <label for="comment"></label>
                        <textarea id="comment" placeholder="@lang('comments.comment')" name="comment" required></textarea>
                    </div>
                </div>
                <input type="hidden" name="post_id" value="{{$post->id}}">
                @csrf
                @honeypot
                <div class="col-12">
                    <div class="form-group">
                        <x-turnstile/>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12 no-padding">
                    <div class="form-group contactus-btn">
                        <button type="submit" class="cntct-btn"> @lang('post.leave_comment')</button>
                    </div>
                </div>
                <div class="col-lg-9 col-md-9 col-sm-12 col-xs-12">

                </div>
            </form>
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 no-padding">

            </div>
        </div>
        <!-- Blog Contact Form Ends -->
        @endif
    </div>
@endsection

@section('scripts')
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js"></script>
    <link rel="stylesheet" href="{{config('app.url')}}/themes/Default/css/custom.min.css">
    <script type="application/ld+json">
        {
            "@context" : "https://schema.org",
            "@type" : "Article",
            "inLanguage": "{{$post->language}}",
            "name" : "{{stripslashes($post->title)}}",
            "author" : {
                "@type" : "Person",
                "name" : "{{$post->user->nickname}}",
                "url" : "{{route('user.posts', ['language' => $post->language, __('routes.user'), $post->user->nickname])}}"
            },
            "mainEntityOfPage": {
                "@type": "WebPage",
                "@id": "{{route('page', ['language' => $post->language, $post])}}"
            },
            "headline": "{{stripslashes($post->title)}}",
            "alternativeHeadline": "{{stripslashes($post->title)}}",
            "keywords": "{{$post->meta_keywords}}",
            "image": {
                "@type": "ImageObject",
                "url": "@if($post->media->last())
            {{route('image', [
                'path' => $media->id,
                'width' => 840,
                'height' => 341,
                'type' => 'cover',
                'image' => $media->file_name
            ])}}
        @endif",
            "width": "840",
            "height": "341"
        },
            "datePublished" : "{{dateformat($post->created_at, 'Y-m-d\TH:i:sP', timezone: config('app.timezone'))}}",
            "dateModified" : "{{dateformat($post->updated_at, 'Y-m-d\TH:i:sP', timezone: config('app.timezone'))}}",
            "articleBody" : "{{ stripslashes(strip_tags(preg_replace('/\s+/', ' ', trim($post->content)))) }}",
            "url" : "{{route('page', ['language' => $post->language, $post])}}",
            "publisher" : {
                "@type" : "Organization",
                "name" : "{{$post->user->nickname}}",
                "logo": {
                    "url": "https://www.gravatar.com/avatar/{{hash('sha256', strtolower(trim($post->user->email)))}}",
                    "type": "ImageObject",
                    "width": "48",
                    "height": "48"
                }
            }
        }
    </script>
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "BreadcrumbList",
            "name": "{{stripslashes($post->title)}}",
            "itemListElement": [{
                "@type": "ListItem",
                "position": 1,
                "name": "{{__('home.home')}}",
                "item": "{{route('home', ['language' => session('language')])}}"
            },
        @php($breadcrumb_n = 1)
        @foreach($post->categories as $n => $category)
            {
                "@type": "ListItem",
                "position": {{$n+2}},
                "name": "{{stripslashes($category->name)}}",
                "item": "{{route('post.categories', [
                        'language' => session('language'),
                        __('routes.categories'),
                        $category->slug])}}"
            },
            @php($breadcrumb_n = $n+1)
        @endforeach
        {
            "@type": "ListItem",
            "position": {{$breadcrumb_n}},
                "name": "{{stripslashes($post->title)}}",
                "item": "{{route('page', ['language' => $post->language, $post])}}"
            }]
        }
    </script>
    @if($post->comments->count() > 0)
        <script type="application/ld+json">
            {
              "@context": "https://schema.org/",
              "@graph":
                [@foreach($post->comments as $n => $item)
                {
                    "@type": "Comment",
                    "name": "@if($item->user) {{$item->user->nickname}}@else{{$item->name}}@endif",
                    "@id":"{{route('page', ['language' => $post->language, $post])}}#comment-{{$item->_id}}",
                    "text":"{{stripslashes(strip_tags(preg_replace('/\s+/', ' ', trim($item->comment))))}}",
                    "dateCreated":"{{dateformat($item->created_at, 'Y-m-d\TH:i:sP', timezone: config('app.timezone'))}}",
                    "author":{
                        "@type":"Person",
                        "name":"@if($item->user) {{$item->user->nickname}}@else{{$item->name}}@endif",
                        "url": "{{route('page', ['language' => $post->language, $post])}}#comment-{{$item->_id}}"
                    }

                }@if(($post->comments->count())-1!=$n)
                    ,
                @endif
            @endforeach]
        }
        </script>
    @endif

    <script>
        $(document).ready(function () {
            $('#commentPanel').submit(function(){
                let form = $(this);
                $.ajax({
                    type: 'POST',
                    url: '{{route('comment.save', ['language' => session('language')])}}',
                    data: form.serialize(),
                    success: function(data){
                        if(data.status === 'success'){
                            form[0].reset();
                            Swal.fire({
                                icon: 'success',
                                title: data.message,
                                showConfirmButton: false,
                                timer: 1500
                            });
                        }else{
                            Swal.fire({
                                icon: 'error',
                                title: data.message,
                                showConfirmButton: false,
                                timer: 1500
                            });
                        }
                    },
                    error: function(data){
                        Swal.fire({
                            icon: 'error',
                            title: data.responseJSON.message,
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }
                });
            });
        })
    </script>
@endsection
