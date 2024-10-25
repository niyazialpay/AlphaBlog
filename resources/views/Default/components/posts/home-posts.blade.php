@forelse($posts as $item)
    <article class="post">
        <header>
            <div class="title">
                <h2>
                    <a href="{{route('page', ['language' => session('language'), $item])}}">
                        {{stripslashes($item->title)}}
                    </a>
                </h2>
            </div>
            <div class="meta">
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
                            'language' => $item->language, __('routes.user'), $item->nickname
                            ])}}" class="author">
                    <span class="name">
                        {{$item->nickname}}
                    </span>
                    <img class="lazy"
                         src="{{route('cdn', '/themes/Default/images/loading.svg')}}"
                         data-src="https://www.gravatar.com/avatar/{{hash('sha256', strtolower(trim($item->email)))}}"
                         alt="{{$item->nickname}}" />
                </a>
            </div>
        </header>
        @if($item->media->where('collection_name', 'posts')->last())
            @php($media = $item->media->where('collection_name', 'posts')->last())
            <a href="{{route('page', ['language' => session('language'), $item->id])}}" class="image featured">
                <img class="lazy" src="{{route('cdn', '/themes/Default/images/loading.svg')}}" data-src="{{route('image', [
                'path' => $item->media_id,
                'width' => 800,
                'height' => 400,
                'type' => 'cover',
                'image' => $media->file_name
            ])}}" alt="{{stripslashes($item->title)}}" />
            </a>
        @endif
        <p>{!! mb_substr(strip_tags(stripslashesNull($item->content),"<br><p>"),0,2000) !!}</p>
        <footer>
            <ul class="actions">
                <li>
                    <a href="{{route('page', ['language' => $item->language, $item])}}" class="button big">
                        @lang('post.read_more')</a>
                </li>
            </ul>
            <ul class="stats">
                <li>
                    @foreach($item->categories as $category)
                        <span class="badge badge-primary">
                            <a href="{{route('post.categories', ['language' => session('language'), __('routes.categories'), $category->slug])}}"
                               class="text-white">
                                {{stripslashesNull($category->name)}}
                            </a>
                        </span>
                    @endforeach
                </li>
            </ul>
        </footer>
    </article>
@empty
    <article class="post">
        <h2 class="text-center">
            @lang('post.no_posts_found')
        </h2>
    </article>
@endforelse
{!! $posts->links('Default.components.posts.paginate') !!}
