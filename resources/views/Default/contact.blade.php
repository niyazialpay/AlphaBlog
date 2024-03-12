@extends('Default.base')

@section('site_title', __('contact.title'))
@section('site_keywords', $contact->keywords)
@section('site_description', $contact->description)

@section('canonical_url', url()->current())
@section('og_image', $general_settings->logo)

@section('tags')
    @foreach(explode(',', $contact->keywords) as $item)
        <meta property="article:tag" content="{{trim($item)}}" />
    @endforeach
@endsection

@section('marginclass'){!! ' class="top-margin"' !!}@endsection

@section('content')
    <div class="col-md-8">
        <div id="container">
            <div class="wrap-container">
                <!-- Content-Box -->
                <section class="content-box contact-form">
                    <div class="row wrap-box"><!--Start Box-->
                        <h3 class="text-center">@lang('contact.contact_form')</h3>
                        <div class="contact-form ">

                            <form name="sentMessage" id="contactForm" method="post" action="{{route('contact.send', ['language' => session('language'), __('routes.contact')])}}">
                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 no-pad-left">
                                    <div class="form-group">
                                        <input id="name" name="name" type="text" placeholder="@lang('contact.name_surname')"
                                               required="required" aria-label="@lang('contact.name_surname')"/>
                                        <p class="help-block text-danger"></p>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 no-pad-right">
                                    <div class="form-group">
                                        <input id="email" type="email" name="email" placeholder="@lang('contact.email')"
                                               required="required" aria-label="@lang('contact.email')"/>
                                        <p class="help-block text-danger"></p>
                                    </div>
                                </div>
                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 no-pad-right">
                                    <div class="form-group">
                                        <input id="subject" name="subject" type="text" placeholder="@lang('contact.subject')"
                                               required="required" aria-label="@lang('contact.subject')"/>
                                        <p class="help-block text-danger"></p>
                                    </div>
                                </div>
                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 no-padding">
                                    <div class="form-group">
                                        <textarea name="message" id="message" placeholder="@lang('contact.message')" required aria-label="@lang('contact.message')"></textarea>
                                        <p class="help-block text-danger"></p>
                                    </div>
                                </div>

                                @honeypot
                                <div class="col-12">
                                    <div class="form-group">
                                        <x-turnstile/>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12 no-padding">
                                    <div class="form-group contactus-btn">
                                        @csrf
                                        <button type="submit" class="cntct-btn"> @lang('contact.send')</button>
                                    </div>
                                </div>
                            </form>
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 no-padding">
                                @if(session('success'))
                                    <div id="success" class="text-center alert alert-success">{{session('success')}}</div>
                                @endif
                                @if(session('error'))
                                        <div id="error" class="text-center alert alert-danger">{{session('error')}}</div>
                                @endif
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
                <h2 class="title">@lang('profile.about-me')</h2>
                <div class="author-widget">
                    {!! $contact->description !!}
                </div>
                <div class="social">
                    <ul class="icons">
                        <x-menu.social-menu :show="$social_settings->social_networks_header"/>
                    </ul>
                </div>
            </section>
        </div> <!-- End Sidebar -->
    </div>
@endsection

@section('scripts')
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js"></script>
@endsection
