@extends('Default.base')

@section('baslik', $ayarlar->baslik. " | İletişim")

@section('keywords', $ayarlar->keywords)
@section('description', $ayarlar->description)
@section('canonical_url', config('app.url')."/iletisim")
@section('og_image', config('Cryptograph.cdn_img').'/temalar/Cryptograph/images/logo.svg')

@section('marginclass')
    {!! ' class="top-margin"' !!}
@endsection

@section('icerik')
    <div class="col-md-8">
        <!--////////////////////////////////////Container-->
        <div id="container">
            <div class="wrap-container">
                <!-- Content-Box -->
                <section class="content-box contact-form">
                    <div class="row wrap-box"><!--Start Box-->
                        <h3 class="text-center">İletişim Formu</h3>
                        <div class="contact-form ">

                            <form name="sentMessage" id="contactForm" method="post" action="javascript:void(0);">
                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 no-pad-left">
                                    <div class="form-group">
                                        <input id="name" name="name" type="text" placeholder="İsim Soyisim"
                                               required="required"/>
                                        <p class="help-block text-danger"></p>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 no-pad-right">
                                    <div class="form-group">
                                        <input id="email" type="email" name="email" placeholder="Eposta"
                                               required="required"/>
                                        <p class="help-block text-danger"></p>
                                    </div>
                                </div>
                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 no-pad-right">
                                    <div class="form-group">
                                        <input id="subject" name="subject" type="text" placeholder="Konu"
                                               required="required"/>
                                        <p class="help-block text-danger"></p>
                                    </div>
                                </div>
                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 no-padding">
                                    <div class="form-group">
                                        <textarea name="message" id="message" placeholder="Mesaj" required></textarea>
                                        <p class="help-block text-danger"></p>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12 no-padding">
                                    <div class="form-group contactus-btn">
                                        <input type="hidden" name="_token" id="_token" value="{{@csrf_token()}}">
                                        <input type="hidden" name="recaptcha_response" id="recaptcha_response"
                                               class="recaptcha_response">
                                        <button type="submit" class="cntct-btn"> Gönder</button>
                                    </div>
                                </div>
                                <div class="col-lg-9 col-md-9 col-sm-12 col-xs-12 no-padding">
                                    <div id="success" class="text-center"></div>
                                </div>
                            </form>
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 no-padding">
                            </div>
                        </div>
                    </div>
                </section>

            </div>
        </div>
    </div> <!-- End col-8 -->

    <div class="col-md-4">
        <div class="sidebar" id="sidebar">
            <!-- About -->
            <section class="blurb">
                <h2 class="title">Hakkımda</h2>

                <!--img class="img-responsive" src="images/aboutme.jpg" alt="about me" /-->
                <div class="author-widget">
                    <h4 class="author-name">Muhammed Niyazi ALPAY</h4>
                    <p>Çok küçük yaştan itibaren bilgisayar&nbsp;sistemleriyle ilgileniyorum ve 2005 yılından beri
                        programlama ile uğraşıyorum, PHP &amp; MySQL, Python ve MongoDB&nbsp;bilgim var.</p>
                    <p><a title="Muhammed Niyazi Alpay Kimdir" class="siteLink" href="http://about.me/Cryptograph"
                          target="_blank" rel="nofollow">about.me/Cryptograph</a></p>
                </div>
                <div class="social">
                    <ul class="icons">
                        <li><a href="https://github.com/niyazialpay" target="_blank" rel="nofollow"><i
                                        class="fa fa-github"></i></a></li>
                        <li><a href="https://www.linkedin.com/in/niyazialpay/" target="_blank" rel="nofollow"><i
                                        class="fa fa-linkedin"></i></a></li>
                        <li><a href="https://facebook.com/MNiyaziAlpay" target="_blank" rel="nofollow"><i
                                        class="fa fa-facebook"></i> </a></li>
                        <li><a href="https://twitter.com/niyazialpay" target="_blank" rel="nofollow"><i
                                        class="fa fa-twitter"></i></a></li>
                        <li><a href="https://instagram.com/niyazialpay" target="_blank" rel="nofollow"><i
                                        class="fa fa-instagram"></i> </a></li>
                    </ul>
                </div>
            </section>
        </div> <!-- End Sidebar -->
    </div><!-- End-col-md-4 -->
@endsection

@section('recaptcha_action', 'iletisim')
