<!-- Mini Posts -->
<section>
    <h2 class="title">@lang('post.most_read_posts')</h2>
    <div class="mini-posts">
        <x-advertise.square-advertise/>
        @foreach($posts as $item)
            <!-- Mini Post -->
            <article class="mini-post">
                <header>
                    <h3>
                        <a href="{{route('page', ['language' => $item->language, $item])}}">{{stripslashes($item->title)}}</a>
                    </h3>
                    <time class="published" datetime="{{dateformat($item->created_at, 'Y-m-d')}}">
                        <a href="{{route('post.archives', [
                            session('language'),
                            __('routes.archives'),
                            'year' => dateformat($item->created_at, 'Y'),
                            'month' => dateformat($item->created_at, 'm'),
                            'day' => dateformat($item->created_at, 'd')
                            ])}}">
                            {{dateformat($item->created_at, 'd M. Y', locale: session('language'))}}
                        </a>
                    </time>
                    <a href="{{route('user.posts', [
                            'language' => session('language'), __('routes.user'), $item->nickname
                            ])}}" class="author">
                        <img class="lazy" src="{{config('app.url')}}/themes/Default/images/loading.svg" data-src="https://www.gravatar.com/avatar/{{hash('sha256', strtolower(trim($item->email)))}}" alt="{{$item->nickname}}" />
                    </a>
                </header>
                @if($item->media->where('collection_name', 'posts')->last())
                    @php($media = $item->media->where('collection_name', 'posts')->last())
                <a href="{{route('page', ['language' => $item->language, $item])}}" class="image">
                    <img class="lazy" src="{{config('app.url')}}/themes/Default/images/loading.svg" data-src="{{route('image', [
                        'path' => $item->media_id,
                        'width' => 300,
                        'height' => 150,
                        'type' => 'cover',
                        'image' => $media->file_name
                    ])}}" alt="{{stripslashes($item->title)}}" />
                </a>
                @endif
            </article>
        @endforeach
    </div>
</section>
