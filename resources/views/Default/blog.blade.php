@extends('Default.base')

@section('baslik', stripslashes($pages->blog_baslik)." - ".$ayarlar->baslik)

@section('keywords', $pages->taglar)
@section('description', mb_substr(strip_tags($pages->blog_icerik),0,255))
@section('canonical_url', config('app.url')."/".$pages->url)
@section('og_image', config('Cryptograph.cdn_img').'/image/post/1620/682/'.$pages->resim)

@section('marginclass')
    {!! ' class="top-margin"' !!}
@endsection

@section('icerik')
    <div class="col-md-12">
        <article class="post">
            <header>
                <div class="title">
                    <h2><a href="/{{$pages->url}}">{{stripslashes($pages->blog_baslik)}}</a></h2>
                </div>
                <div class="meta">
                    <time class="published" datetime="{{dateformat($pages->saat_tarih, 'Y-m-d')}}"><a
                                href="/tarih/{{dateformat($pages->saat_tarih, 'Y')}}/{{dateformat($pages->saat_tarih, 'm')}}/{{dateformat($pages->saat_tarih, 'd')}}">{{dateformat($pages->saat_tarih, 'd M Y')}}</a>
                    </time>
                    <a href="/kullanicilar/{{$pages->kullanici_adi}}" class="author"><span
                                class="name">{{$pages->kullanici_adi}}</span><img class="lazy"
                                                                                  src="{{config('Cryptograph.cdn_img')}}/temalar/Cryptograph/images/loading.svg"
                                                                                  data-src="/image/profil/50/50/{{$pages->p_resim}}"
                                                                                  alt="{{$pages->kullanici_adi}}"/></a>
                </div>
            </header>
            <div class="image featured"><img class="lazy"
                                             src="{{config('Cryptograph.cdn_img')}}/temalar/Cryptograph/images/loading.svg"
                                             data-src="{{config('Cryptograph.cdn_img')}}/image/blog/1620/682/{{$pages->resim}}"
                                             alt="{{stripslashes($pages->blog_baslik)}}"/></div>
            {!! stripslashes($pages->blog_icerik) !!}
            <footer>
                <div class="social actions">
                    <div class="col-sm-12">
                        <span class="col-12">Etiketler:</span>
                        <ul class="tags stats col-12">
                            @foreach(explode(",",$tags) as $item)
                                <li><a href="/etiketler/{{str_replace(' ','%20',trim($item))}}">{{trim($item)}}</a></li>
                            @endforeach
                        </ul>
                    </div>
                    <hr>
                    <!-- ShareThis BEGIN -->
                    <div class="sharethis-inline-share-buttons"></div><!-- ShareThis END -->
                    <div class="addthis_inline_share_toolbox"></div>
                    <small>Paylaşmak güzeldir</small>
                </div>

                <ul class="stats">
                    <li><a href="/kategoriler/{{$pages->kat_url}}"><strong>{{$pages->kat_isim}}</strong></a></li>
                </ul>

            </footer>
        </article>
        @if(count($benzerkonular)>0)
            <div class="post-related post-block">
                <h4 class="heading"><span>Bunları da okumak isteyebilirsiniz</span></h4>
                <div class="related">
                    <ul class="row">
                        @foreach($benzerkonular as $item)
                            <li class="item col-lg-4 col-md-4 col-sm-4">
                                <div class="thporb">
                                    <a href="/{{$item->url}}"><img class="lazy"
                                                                   src="{{config('Cryptograph.cdn_img')}}/temalar/Cryptograph/images/loading.svg"
                                                                   data-src="{{config('Cryptograph.cdn_img')}}/image/blog/720/460/{{$item->resim}}"
                                                                   alt="{{stripslashes($item->blog_baslik)}}"></a>
                                </div>
                                <h5 class="item-title">
                                    <a href="/{{$item->url}}">{{stripslashes($item->blog_baslik)}}</a>
                                </h5>
                                <time class="published"
                                      datetime="{{dateformat($item->saat_tarih, 'Y-m-d')}}">{{dateformat($item->saat_tarih, 'd M Y')}}</time>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
        <!-- Blog Comments Begins -->
        <div class="blog-comments">
            <div class="blog-comment-main">
                <h3>{{count($yorumlar)}} Yorum</h3>
                @foreach($yorumlar as $item)
                    <div class="blog-comment">
                        <a id="yorum-{{$item->id}}"></a>
                        <a class="comment-avtar">
                            @if(empty($item->resim))
                                <img class="lazy"
                                     src="{{config('Cryptograph.cdn_img')}}/temalar/Cryptograph/images/loading.svg"
                                     data-src="{{config('Cryptograph.cdn_img')}}/image/profil/150/150/noprofileimage.jpg"
                                     alt="image">
                            @else
                                <img class="lazy"
                                     src="{{config('Cryptograph.cdn_img')}}/temalar/Cryptograph/images/loading.svg"
                                     data-src="{{config('Cryptograph.cdn_img')}}/image/profil/150/150/{{$item->resim}}"
                                     alt="image">
                            @endif
                        </a>
                        <div class="comment-text">
                            <h3>{{$item->isim}}</h3>
                            <h5>{{dateformat($item->saat_tarih, "d M Y H:i", 'Europe/Istanbul')}}</h5>
                            <p>{!! stripslashes($item->yorum) !!}</p>
                            <!--a href="javascript:void(0)" class="comment-reply"> Cevapla <i class="fa fa-angle-right" aria-hidden="true"></i> </a-->
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <!-- Blog Contact Form Begins -->
        <div class="contact-form pad-top-big pad-bottom-big">
            <h3>@if(count($yorumlar)>0)
                    Sen de bir yorum bırak
                @else
                    Bir Yorum Bırak
                @endif</h3>
            <form method="post" action="javascript:void(0);" id="yorum_panel">
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 no-pad-left">
                    <div class="form-group">
                        <label for="isim"></label>
                        <input type="text" id="isim" name="isim" placeholder="İsim Soyisim" required/>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 no-pad-right">
                    <div class="form-group">
                        <label for="email"></label>
                        <input type="email" id="email" name="email" placeholder="Eposta" required/>
                    </div>
                </div>
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 no-padding">
                    <div class="form-group">
                        <label for="yorum"></label>
                        <textarea id="yorum" placeholder="Yorum" name="yorum" required></textarea>
                    </div>
                </div>
                <input type="hidden" class="recaptcha_response" name="recaptcha_response">
                <input type="hidden" name="blog_id" value="{{$pages->id}}">
                <input type="hidden" name="_token" id="_token" value="{{ @csrf_token() }}">
                <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12 no-padding">
                    <div class="form-group contactus-btn">
                        <button type="submit" class="cntct-btn"> Yorum Yap</button>
                    </div>
                </div>
                <div class="col-lg-9 col-md-9 col-sm-12 col-xs-12">
                    <p id="response_message"></p>
                </div>
            </form>
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 no-padding">

            </div>
        </div>
        <!-- Blog Contact Form Ends -->
    </div>
@endsection

@section('rich_snipped')
    <link rel="stylesheet" href="{{config('Cryptograph.cdn_url')}}/temalar/Cryptograph/css/custom.min.css">
    <script type="application/ld+json">
        {
            "@context" : "http://schema.org",
            "@type" : "Article",
            "inLanguage": "tr-TR",
            "name" : "{{stripslashes($pages->blog_baslik)}}",
    "author" : {
        "@type" : "Person",
        "name" : "{{$pages->kullanici_adi}}"
    },
    "mainEntityOfPage": {
        "@type": "WebPage",
        "@id": "{{config('app.url')."/".$pages->url}}"
    },
    "headline": "{{stripslashes($pages->blog_baslik)}}",
    "alternativeHeadline": "{{stripslashes($pages->blog_baslik)}}",
    "keywords": "{{$pages->taglar}}",
    "image": {
        "@type": "ImageObject",
        "url": "{{config('Cryptograph.cdn_img')."/image/blogs/840/341/".$pages->resim}}",
        "width": "840",
        "height": "341"
    },
    "datePublished" : "{{$pages->saat_tarih}}",
    "dateModified" : "{{$pages->saat_tarih}}",
    "articleBody" : "{{ rich_snipped_fix($pages->blog_icerik) }}",
    "url" : "{{config('app.url')."/".$pages->url}}",
    "publisher" : {
        "@type" : "Organization",
        "name" : "Cryptograph",
        "logo": {
            "url": "{{config('Cryptograph.cdn_img')."/image/profil/48/48/".$pages->p_resim}}",
            "type": "ImageObject",
            "width": "48",
            "height": "48"
        }
    }
}
    </script>
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
                          "@id": "{{config('app.url')}}/kategoriler/{{$pages->kat_url}}",
                          "name": "{{stripslashes($pages->kat_isim)}}"
                      }
                  },
                                    {
                    "@type": "ListItem",
                      "position":3,
                      "item": {
                          "@id": "{{config('app.url')}}/{{$pages->url}}",
                          "name": "{{stripslashes($pages->blog_baslik)}}"
                      }
                  }
              ]
          }
    </script>
    @if(count($yorumlar)>0)
        <script type="application/ld+json">
            {
              "@context": "http://schema.org/",
              "@graph":
                [@foreach($yorumlar as $n => $item)
                {
                    "@type": "Comment",
                    "@id":"{{config('app.url')}}/{{$pages->url}}#yorum-{{$item->id}}",
                    "text":"{{ rich_snipped_fix($item->yorum)}}",
                    "downvoteCount":"0",
                    "upvoteCount":"0",
                    "dateCreated":"{{$item->saat_tarih}}",
                    "author":[{
                        "@type":"Thing",
                        "name":""
                    }]
                }@if((count($yorumlar)-1)!=$n)
                    ,
                @endif
            @endforeach]
        }
        </script>
    @endif
@endsection

@section('recaptcha_action', recaptcha_action_fix($pages->url))
