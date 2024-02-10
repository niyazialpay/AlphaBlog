@extends('Default.base')

@section('baslik', $ayarlar->baslik." | Kategorile")
@section('keywords', $ayarlar->keywords)
@section('description', $ayarlar->description)
@section('canonical_url', config('app.url')."/kategoriler")
@section('og_image', config('Cryptograph.cdn_img').'/temalar/Cryptograph/images/logo.svg')

@section('slider_breadcrumb')
    {!! $slider_breadcrumb !!}
@endsection

@section('icerik')
    <div class="col-md-8">
        <!-- Slider -->
        <div class="clearfix"></div>
        @foreach($kullanicilar as $item)
            <article class="post">
                <header>
                    <div class="title">
                        <h2><a href="/kullanicilar/{{$item->kullanici_adi}}">{{stripslashes($item->kullanici_adi)}}</a>
                        </h2>
                    </div>
                    <div class="meta">

                        <a href="/kullanicilar/{{$item->kullanici_adi}}" class="author">
                            @if(empty($item->p_resim))
                                <img src="{{config('Cryptograph.cdn_img')}}/image/profil/34/34/noprofileimage.jpg"
                                     alt="{{$item->kullanici_adi}}">
                            @else
                                <img src="{{config('Cryptograph.cdn_img')}}/image/profil/34/34/{{$item->p_resim}}"
                                     alt="{{$item->kullanici_adi}}">
                            @endif</a>
                    </div>
                </header>
                <p>{{$item->konu_sayi}} adet içeriği bulunmaktadır.</p>
            </article>

        @endforeach
    </div>
@endsection

@section('recaptcha_action', "kullanicilar")
