<div class="slider container">
    <div class="featured-area">

        <div class="owl-carousel owl-theme">
            @foreach($slider as $item)
                <div class="feat-item item slider">
                    @if($item->media->last())
                    @php($media = $item->media->last())
                    <img class="lazy" src="{{config('app.url')}}/themes/Default/images/loading.svg"
                         data-src="{{route('image', [
                            'path' => $media->_id,
                            'width' => 800,
                            'height' => 400,
                            'type' => 'cover',
                            'image' => $media->file_name
                        ])}}"
                         alt="{{stripslashes($item->title)}}">
                    @endif
                    <div class="feat-overlay">
                        <div class="feat-inner">

                            <h2>
                                <a href="{{route('page', ['language' => session('language'), $item])}}">
                                    {{stripslashes($item->title)}}
                                </a>
                            </h2>
                            <a href="{{route('page', ['language' => session('language'), $item])}}" class="feat-more">
                                @lang('post.read_more')
                            </a>
                        </div>
                    </div>

                </div>
            @endforeach
        </div> <!-- End owl-carousel -->
    </div> <!-- End featured-area -->
</div><!-- End slider -->
