@if($posts->count() > 0)
<div class="post-related post-block">
    <h4 class="heading">
        <span>
            @lang('post.you_may_also_want_to_read_these')
        </span>
    </h4>
    <div class="related">
        <ul class="row">
            @foreach($posts as $item)
                <li class="item col-lg-4 col-md-4 col-sm-4">
                    <div class="thporb">
                        @if($item->media->where('collection_name', 'posts')->last())
                            @php($media = $item->media->where('collection_name', 'posts')->last())
                        <a href="{{route('page', ['language' => session('language'), $item])}}">
                            <img class="lazy"
                                 src="{{config('app.url')}}/themes/Default/images/loading.svg"
                                 data-src="{{route('image', [
                                        'path' => $media->id,
                                        'width' => 720,
                                        'height' => 460,
                                        'type' => 'cover',
                                        'image' => $media->file_name
                                    ])}}"
                                 alt="{{stripslashes($item->title)}}">
                        </a>
                        @endif
                    </div>
                    <h5 class="item-title">
                        <a href="{{route('page', ['language' => session('language'), $item])}}">
                            {{stripslashes($item->title)}}
                        </a>
                    </h5>
                    <time class="published"
                          datetime="{{dateformat($item->created_at, 'Y-m-d')}}">
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
                </li>
            @endforeach
        </ul>
    </div>
</div>
@endif
