@extends('Default.base')

@section('baslik', " 404 Bulunamadı! - ")


@section('icerik')
    <div class="col-md-12 text-center">
        <div class="error-page text-align-center pad-top-big pad-bottom-big">
            <h1>404</h1>
            <h2>Oops, This Page Not Be Found!</h2>
            <h3>We are really sorry but the page you requested is missing</h3>
            <div class="error-bottom">
                <a href="/" class="home-page-link">go to home page<i class="fa fa-angle-right"
                                                                     aria-hidden="true"></i></a>
            </div>
        </div>
    </div>
@endsection
@section('recaptcha_action', "404page")
