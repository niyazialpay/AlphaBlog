@extends('Default.base')

@section('baslik', $ayarlar->baslik. " | Hakkimda")

@section('keywords', $ayarlar->keywords)
@section('description', $ayarlar->description)
@section('canonical_url', config('app.url')."/hakkimda")
@section('og_image', config('Cryptograph.cdn_img').'/temalar/Cryptograph/images/logo.svg')

@section('marginclass')
    {!! ' class="top-margin"' !!}
@endsection

@section('icerik')
    <div class="col-md-12">
        <!-- Post -->
        <article class="post">
            <div class="image featured"><img src="{{config('Cryptograph.cdn_img')}}/temalar/Cryptograph/images/logo.svg"
                                             height="128" alt="logo"></div>
            <p>Sanırım buraya şu tarihde doğmuştur şurda okumuştur gibisinden çok resmi bir yazı yazmam gerekiyor fakat
                inanın hiç samimi gelmiyor :) Kimse sizin diploma notunuza bakmaz neler yaptığınıza bakar o yüzden neler
                yaptığımdan bahsedeceğim:<br/>Bu alandaki bir çok arkadaşım gibi bende <small>hacking</small> merakıyla
                girdim bu sektöre ve sonu olmayan bu yolda bilgime bilgi katıp web alanına yöneldim. <br/>- Yazılım ve
                bilgi güvenliği hep ilgi alanım olmuştur, özgürlüğüne aşırı düşkün birisiyim. Başladığım işi her zaman
                bitiririm. Sanırım hakkımda bilmeniz gereken en önemli şey bu.<br/>- <a href="http://niyazi.net"
                                                                                        target="_blank">Blog</a> yazmayı
                bilgilerimi paylaşmayı çok seven birisiyim o yüzden hakkımda daha fazla şey öğrenmek için <a
                        href="https://niyazi.net">Blog</a> sayfama bakabilirsiniz.</p>
            <div class="span4 ">
                <p>Bildiklerim,</p>
                <div class="progress progress-danger progress-striped active">
                    <div class="bar" style="width: 90%;">PHP & MySQL</div>
                </div>
                <div class="progress progress-danger progress-striped active">
                    <div class="bar" style="width: 70%;">HTML5 & CSS3</div>
                </div>
                <div class="progress progress-danger progress-striped active">
                    <div class="bar" style="width: 70%;">Python</div>
                </div>
                <div class="progress progress-danger progress-striped active">
                    <div class="bar" style="width: 70%;">Linux</div>
                </div>
                <div class="progress progress-danger progress-striped active">
                    <div class="bar" style="width: 60%;">Bash</div>
                </div>
                <div class="progress progress-danger progress-striped active">
                    <div class="bar" style="width: 40%;">JS & AJAX</div>
                </div>
                <div class="progress progress-danger progress-striped active">
                    <div class="bar" style="width: 40%;">WORDPRESS</div>
                </div>
                <div class="progress progress-danger progress-striped active">
                    <div class="bar" style="width: 30%;">MongoDB</div>
                </div>
                <div class="progress progress-danger progress-striped active">
                    <div class="bar" style="width: 25%;">C#</div>
                </div>

            </div><!-- /span6 -->
            <div class="inner  unright" style="text-align:center">
                <blockquote><h2><span>Eğitim</span>, insanın okulda öğrendiği her şeyi unuttuğunda arta kalandır.</h2>
                    <br/>- Albert Einstein
                </blockquote>
            </div>
        </article>
    </div> <!-- End col-12 -->
@endsection

@section('recaptcha_action', 'hakkimda')
