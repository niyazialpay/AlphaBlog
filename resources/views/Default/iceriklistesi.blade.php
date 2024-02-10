@extends('Default.base')

@section('baslik', $ayarlar->baslik)
@section('keywords', $ayarlar->keywords)
@section('description', $ayarlar->description)
@section('canonical_url', $canonical_url)
@section('og_image', config('app.url').'/public/images/logo.svg')

@section('slider_breadcrumb')
    {!! $slider_breadcrumb !!}
@endsection

@section('icerik')
    <div class="col-md-8">
        @if(count($pages)>0)
            @foreach($pages as $item)
                <article class="post">
                    <header>
                        <div class="title">
                            <h2><a href="/{{$item->url}}">{!! stripslashes($item->blog_baslik) !!}</a></h2>
                        </div>
                        <div class="meta">
                            <time class="published" datetime="{{dateformat($item->saat_tarih, 'Y-m-d')}}"><a
                                        href="/tarih/{{dateformat($item->saat_tarih, 'Y')}}/{{dateformat($item->saat_tarih, 'm')}}/{{dateformat($item->saat_tarih, 'd')}}">{{dateformat($item->saat_tarih, 'd M Y')}}</a>
                            </time>
                            <a href="/kullanicilar/{{$item->kullanici_adi}}" class="author"><span
                                        class="name">{{$item->kullanici_adi}}</span><img class="lazy"
                                                                                         src="{{config('Cryptograph.cdn_img')}}/temalar/Cryptograph/images/loading.svg"
                                                                                         data-src="/image/profil/34/34/{{$item->p_resim}}"
                                                                                         alt="{{$item->kullanici_adi}}"/></a>
                        </div>
                    </header>
                    <a href="/{{$item->url}}" class="image featured"><img class="lazy"
                                                                          src="{{config('Cryptograph.cdn_img')}}/temalar/Cryptograph/images/loading.svg"
                                                                          data-src="/image/blog/664/240/{{$item->resim}}"
                                                                          alt="{{$item->blog_baslik}}"/></a>
                    <p>{!! mb_substr(strip_tags(stripslashes($item->blog_icerik),"<br><strong><p>"),0,2000) !!}</p>
                    <footer>
                        <ul class="actions">
                            <li><a href="/{{$item->url}}" class="button big">Devamını Oku</a></li>
                        </ul>
                        <ul class="stats">
                            <li><a href="/kategoriler/{{$item->kat_url}}">{{$item->kat_isim}}</a></li>
                        </ul>
                    </footer>
                </article>

            @endforeach
            {!! $pages->links('Cryptograph.home_paginate') !!}
        @else
            <article class="post">
                <header>
                    <div class="title">
                        <em><u>{{app('request')->input('s')}}</u></em> arama sorgusuna ait herhangi bir içerik
                        bulunamamıştır
                    </div>
                </header>
                <form>

                </form>
            </article>
        @endif
    </div>
@endsection

@section('recaptcha_action', $recaptcha)

@if($snippet_active)
    @section('rich_snippet')
        <script type="application/ld+json">
            {
                "@context": "http://schema.org",
                "@type": "BreadcrumbList",
                "itemListElement": [{
                        "@type": "ListItem",
                        "position": 1,
                        "item": {
                            "@id": "{{config('app.url')}}",
                          "name": "{{stripslashes($ayarlar->baslik)}}"
                      }
                  },
                  {
                      "@type": "ListItem",
                      "position": 2,
                      "item": {
                          "@id": "{{config('app.url')}}/kategoriler",
                          "name": "Kategoriler"
                      }
                  },
                  {
                      "@type": "ListItem",
                      "position": 3,
                      "item": {
                          "@id": "{{config('app.url')}}/kategoriler/{{$kategori->kat_url}}",
                          "name": "{{stripslashes($kategori->kat_isim)}}"
                      }
                  },

              ]
          }
        </script>
    @endsection
@endif
