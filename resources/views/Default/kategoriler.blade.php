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
        @foreach($categories as $item)
            <article class="post">
                <header>
                    <div class="title">
                        <h2><a href="/kategoriler/{{$item->kat_url}}">{{stripslashes($item->kat_isim)}}</a></h2>
                    </div>
                </header>
            </article>

        @endforeach
    </div>
@endsection

@section('recaptcha_action', "kategoriler")

@section('reach_snippet')
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
                  }
              ]
          }
    </script>
@endsection
