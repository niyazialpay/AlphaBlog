<!-- Posts List -->
<section>
    <h2 class="title">@lang('post.last_comments')</h2>
    <ul class="posts">
        @foreach($lastComments as $item)
            <li>
                <article>
                    <header>
                        <h3>
                            <a href="{{route('page', ['language' => $item->post->language, $item->post])}}">
                                {{stripslashes($item->post->title)}} - {{$item->nickname}}
                            </a>
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
                    </header>
                    @if($item->post->media->last())
                    @php($media = $item->post->media->last())
                    <a href="{{route('page', ['language' => $item->post->language, $item->post])}}" class="image">
                        <img class="lazy" src="{{config('app.url')}}/themes/Default/images/loading.svg"
                             data-src="{{route('image', [
                                'path' => $media->_id,
                                'width' => 300,
                                'height' => 200,
                                'type' => 'cover',
                                'image' => $media->file_name
                            ])}}" alt="{{stripslashes($item->post->title)}}" />
                    </a>
                    @endif
                </article>
            </li>
        @endforeach
    </ul>
</section>
<x-advertise.vertical-advertise/>
