@extends('panel.base')
@section('title',__('settings.settings'))
@section('breadcrumb_link')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{route('admin.index')}}">@lang('dashboard.dashboard')</a></li>
        <li class="breadcrumb-item active">@lang('settings.settings')</li>
    </ol>
@endsection
@section('content')
    <div class="card">
        <div class="card-header d-flex">
            <h3 class="card-title">@lang('settings.settings')</h3>
        </div>
        <div class="card-body">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-pills border-bottom">
                        <li class="nav-item">
                            <a class="nav-link settings-links @if(request()->get('tab')=='general') active @endif"
                               href="javascript:ChangeTab('general')"
                               id="general-menu">
                                <i class="fa-duotone fa-gear"></i>
                                @lang('settings.general_settings')
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link settings-links @if(request()->get('tab')=='seo') active @endif"
                               href="javascript:ChangeTab('seo')"
                               id="seo-menu">
                                <i class="fa-duotone fa-magnifying-glass"></i>
                                SEO
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link settings-links @if(request()->get('tab')=='analytics') active @endif"
                               href="javascript:ChangeTab('analytics')"
                               id="analytics-menu">
                                <i class="fa-duotone fa-magnifying-glass-chart"></i>
                                @lang('settings.analytics')
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link settings-links @if(request()->get('tab')=='advertisement') active @endif"
                               href="javascript:ChangeTab('advertisement')"
                               id="advertisement-menu">
                                <i class="fad fa-rectangle-ad"></i>
                                @lang('settings.advertisement')
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link settings-links
                            @if(request()->get('tab')=='social-networks') active @endif"
                               href="javascript:ChangeTab('social-networks')"
                               id="social-networks-menu">
                                <i class="fa-duotone fa-share-nodes"></i>
                                @lang('social.social_networks')
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link settings-links @if(request()->get('tab')=='themes') active @endif"
                               href="javascript:ChangeTab('themes')"
                               id="themes-menu">
                                <i class="fa-duotone fa-brush"></i>
                                @lang('settings.themes')
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link settings-links @if(request()->get('tab')=='languages') active @endif"
                               href="javascript:ChangeTab('languages')"
                               id="languages-menu">
                                <i class="fa-duotone fa-language"></i>
                                @lang('settings.languages')
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link settings-links @if(request()->get('tab')=='notifications') active @endif"
                               href="javascript:ChangeTab('notifications')"
                               id="notifications-menu">
                                <i class="fa-duotone fa-bell"></i>
                                @lang('settings.notifications')
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane settings-tabs @if(request()->get('tab')=='general') active @endif"
                             id="general">
                            @if(session('success'))
                                <div class="alert alert-success">
                                    {{session('success')}}
                                </div>
                            @endif
                            @if(session('error'))
                                <div class="alert alert-danger">
                                    {{session('error')}}
                                </div>
                            @endif
                            <form class="row" id="generalSave" enctype="multipart/form-data" method="post"
                                  action="{{route('admin.settings.general.save')}}">
                                @csrf
                                <div class="col-12 mb-3">
                                    <label for="site_logo">@lang('settings.site_logo_light')</label>
                                    @if(app('general_settings')->getFirstMediaUrl('site_logo_light'))
                                        <img src="{{app('general_settings')->getFirstMediaUrl('site_logo_light')}}"
                                             alt="logo"
                                             class="img-fluid mx-5" style="height: 100px;">
                                        <a href="javascript:imageDelete('{{app('general_settings')
                                            ->getMedia('site_logo_light')[0]->id}}', 'site_logo_light')"
                                           class="btn btn-sm btn-danger">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    @else
                                        <input type="file" class="form-control" id="site_logo_light"
                                               name="site_logo_light">
                                    @endif
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="site_logo">@lang('settings.site_logo_dark')</label>
                                    @if(app('general_settings')->getFirstMediaUrl('site_logo_dark'))
                                        <img src="{{app('general_settings')->getFirstMediaUrl('site_logo_dark')}}"
                                             alt="logo"
                                             class="img-fluid mx-5" style="height: 100px;">
                                        <a href="javascript:imageDelete('{{app('general_settings')
                                           ->getMedia('site_logo_dark')[0]->id}}', 'site_logo_dark')"
                                           class="btn btn-sm btn-danger">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    @else
                                        <input type="file" class="form-control" id="site_logo_dark"
                                               name="site_logo_dark">
                                    @endif
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="site_favicon">@lang('settings.site_favicon')</label>
                                    @if(app('general_settings')->getFirstMediaUrl('site_favicon'))
                                        <img src="{{app('general_settings')->getFirstMediaUrl('site_favicon')}}"
                                             alt="site_favicon"
                                             class="img-fluid mx-5" style="height: 100px;">
                                        <a href="javascript:imageDelete('{{app('general_settings')
                                            ->getMedia('site_favicon')[0]->id}}', 'site_favicon')"
                                           class="btn btn-sm btn-danger">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    @else
                                        <input type="file" class="form-control" id="site_favicon" name="site_favicon">
                                    @endif
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="app_icon">@lang('settings.app_icon')</label>
                                    @if(app('general_settings')->getFirstMediaUrl('app_icon'))
                                        <img src="{{app('general_settings')->getFirstMediaUrl('app_icon')}}"
                                             alt="site_favicon"
                                             class="img-fluid mx-5" style="height: 100px;">
                                        <a href="javascript:imageDelete('{{app('general_settings')
                                            ->getMedia('app_icon')[0]->id}}', 'app_icon')"
                                           class="btn btn-sm btn-danger">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    @else
                                        <input type="file" class="form-control" id="app_icon" name="app_icon">
                                    @endif
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="contact_email">@lang('settings.contact_email')</label>
                                    <input type="email" class="form-control" id="contact_email" name="contact_email"
                                           value="{{app('general_settings')->contact_email}}">
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="sharethis">Sharethis</label>
                                    <textarea type="text" class="form-control" id="sharethis"
                                              name="sharethis">{{app('general_settings')->sharethis}}</textarea>
                                </div>
                                <div class="col-12 mb-3">
                                    <button type="submit" class="btn btn-primary">@lang('general.save')</button>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane settings-tabs @if(request()->get('tab')=='seo') active @endif"
                             id="seo">
                            <form class="card" id="seoSave" method="post" action="javascript:void(0)">
                                @csrf
                                <div class="card-header">
                                    <ul class="nav nav-pills border-bottom">
                                        @foreach(app('languages') as $n => $language)
                                            <li class="nav-item">
                                                <a class="nav-link @if($n==0) active @endif "
                                                   href="#form_{{$language->code}}" data-bs-toggle="tab">
                                                    {{$language->name}}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div class="card-body">
                                    <div class="tab-content">
                                        @foreach(app('languages') as $n => $language)
                                            @php($seo = $seo_settings->where('language',$language->code)->first())
                                            <div class="tab-pane @if($n==0) active @endif"
                                                 id="form_{{$language->code}}">
                                                <div class="row">
                                                    <div class="col-12 mb-3">
                                                        <label for="site_name_{{$language->code}}">
                                                            @lang('settings.site_name') ({{$language->name}})
                                                        </label>
                                                        <input type="text" class="form-control"
                                                               id="site_name_{{$language->code}}"
                                                               name="site_name_{{$language->code}}"
                                                               value="{{$seo->site_name}}">
                                                    </div>
                                                    <div class="col-12 mb-3">
                                                        <label for="site_title_{{$language->code}}">
                                                            @lang('settings.site_title') ({{$language->name}})
                                                        </label>
                                                        <input type="text" class="form-control"
                                                               id="site_title_{{$language->code}}"
                                                               name="site_title_{{$language->code}}"
                                                               value="{{$seo->title}}">
                                                    </div>

                                                    <div class="col-12 mb-3">
                                                        <label for="site_description_{{$language->code}}">
                                                            @lang('settings.site_description') ({{$language->name}})
                                                        </label>
                                                        <textarea class="form-control"
                                                                  id="site_description_{{$language->code}}"
                                                                  name="site_description_{{$language->code}}"
                                                                  maxlength="160"
                                                                  rows="3">{{$seo->description}}</textarea>
                                                    </div>
                                                    <div class="col-12 mb-3">
                                                        <label for="site_keywords_{{$language->code}}">
                                                            @lang('settings.site_keywords') ({{$language->name}})
                                                        </label>
                                                        <input type="text" class="form-control"
                                                               id="site_keywords_{{$language->code}}"
                                                               name="site_keywords_{{$language->code}}"
                                                               value="{{implode(',', $seo->keywords)}}">
                                                    </div>
                                                    <div class="col-12 mb-3">
                                                        <label for="site_author_{{$language->code}}">
                                                            @lang('settings.site_author') ({{$language->name}})
                                                        </label>
                                                        <input type="text" class="form-control"
                                                               id="site_author_{{$language->code}}"
                                                               name="site_author_{{$language->code}}"
                                                               value="{{$seo->author}}">
                                                    </div>
                                                    <div class="col-12 mb-3">
                                                        <label for="robots_{{$language->code}}">
                                                            @lang('settings.robots') ({{$language->name}})
                                                        </label>
                                                        <select name="robots_{{$language->code}}"
                                                                id="robots_{{$language->code}}" class="form-control">
                                                            <option value="index,follow"
                                                                    @if($seo->robots == 'index,follow') selected @endif>
                                                                @lang('settings.index_follow')
                                                            </option>

                                                            <option
                                                                value="noindex,nofollow"
                                                                @if($seo->robots == 'noindex,nofollow')selected @endif>
                                                                @lang('settings.noindex_nofollow')
                                                            </option>

                                                            <option
                                                                value="index,nofollow"
                                                                @if($seo->robots == 'index,nofollow') selected @endif>
                                                                @lang('settings.index_nofollow')
                                                            </option>

                                                            <option
                                                                value="noindex,follow"
                                                                @if($seo->robots == 'noindex,follow') selected @endif>
                                                                @lang('settings.noindex_follow')
                                                            </option>
                                                        </select>
                                                    </div>
                                                    <div class="col-12 mb-3">
                                                        <button type="submit" class="btn btn-primary">
                                                            @lang('general.save')
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </form>
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">@lang('settings.robots_txt')</h3>
                                </div>
                                <div class="card-body">
                                    <form id="robotsTxtSave" method="post" action="javascript:void(0)">
                                        @csrf
                                        <div class="row">
                                            <div class="col-12 mb-3">
                                                <textarea class="form-control"
                                                  id="robots_txt"
                                                  name="robots_txt"
                                                  rows="10"
                                                  aria-label="@lang('settings.robots_txt')">{{$robots_txt}}</textarea>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <button type="submit" class="btn btn-primary">
                                                    @lang('general.save')
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane settings-tabs @if(request()->get('tab')=='analytics') active @endif"
                             id="analytics">
                            <form class="row" id="analyticsForm" method="post" action="javascript:void(0);">
                                <div class="col-12 mb-3">
                                    <label for="google_analytics">Google Analytics</label>
                                    <textarea class="form-control" id="google_analytics"
                                           name="google_analytics">{{$analytics_settings->google_analytics}}</textarea>
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="yandex_metrica">Yandex Metrica</label>
                                    <textarea class="form-control" id="yandex_metrica"
                                              name="yandex_metrica">{{$analytics_settings->yandex_metrica}}</textarea>
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="fb_pixel">Facebook Pixel</label>
                                    <textarea class="form-control" id="fb_pixel"
                                              name="fb_pixel">{{$analytics_settings->fb_pixel}}</textarea>
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="log_rocket">Log Rocket</label>
                                    <textarea class="form-control" id="log_rocket"
                                           name="log_rocket">{{$analytics_settings->log_rocket}}</textarea>
                                </div>
                                @csrf
                                <div class="col-12 mb-3">
                                    <button type="submit" class="btn btn-primary">@lang('general.save')</button>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane settings-tabs @if(request()->get('tab')=='advertisement') active @endif"
                             id="advertisement">
                            <form method="post" action="javascript:void(0);" id="advertisePost" class="row">
                                <div class="col-12 mb-3">
                                    <label for="google_ad_manager">
                                        Google Ad Manager
                                    </label>
                                    <textarea
                                        class="form-control" id="google_ad_manager"
                                        name="google_ad_manager">{{$advertise_settings?->google_ad_manager}}</textarea>
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="square_display_advertise">
                                        @lang('advertise.square_display_advertise')
                                    </label>
                                    <textarea
                                        class="form-control" id="square_display_advertise"
                                        name="square_display_advertise">
                                        {{$advertise_settings?->square_display_advertise}}
                                    </textarea>
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="vertical_display_advertise">
                                        @lang('advertise.vertical_display_advertise')
                                    </label>
                                    <textarea
                                        class="form-control" id="vertical_display_advertise"
                                        name="vertical_display_advertise">
                                        {{$advertise_settings?->vertical_display_advertise}}
                                    </textarea>
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="horizontal_display_advertise">
                                        @lang('advertise.horizontal_display_advertise')
                                    </label>
                                    <textarea
                                        class="form-control" id="horizontal_display_advertise"
                                        name="horizontal_display_advertise">
                                        {{$advertise_settings?->horizontal_display_advertise}}
                                    </textarea>
                                </div>
                                @csrf
                                <div class="col-12 mb-3">
                                    <button type="submit" class="btn btn-primary">@lang('general.save')</button>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane settings-tabs @if(request()->get('tab')=='social-networks') active @endif"
                             id="social-networks">
                            <form class="row mb-3" id="showInHeader" method="post" action="javascript:void(0);">
                                <div class="col-12 mb-3">
                                    <p>
                                        @lang('social.multiple_select')
                                    </p>
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="social_networks_header">@lang('social.show_header')</label>
                                    <select class="form-control" name="social_networks_header[]"
                                            id="social_networks_header" multiple style="height: 250px">
                                        @foreach(social_list() as $key => $value)
                                            <option value="{{$key}}"
                                                @if(in_array($key, $social_settings?->social_networks_header))
                                                    selected
                                                @endif>
                                                {{$value}}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="social_networks_footer">@lang('social.show_footer')</label>
                                    <select class="form-control" name="social_networks_footer[]"
                                            id="social_networks_footer" multiple style="height: 250px">
                                        @foreach(social_list() as $key => $value)
                                            <option value="{{$key}}"
                                                    @if(in_array($key, $social_settings?->social_networks_footer))
                                                        selected
                                                @endif>
                                                {{$value}}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @csrf
                                <div class="col-12 mb-3">
                                    <button type="submit" class="btn btn-primary" id="showHeaderSave">
                                        @lang('general.save')
                                    </button>
                                </div>
                                <div class="col-12">
                                    <hr>
                                </div>
                            </form>
                            <form class="row" method="post" id="socialNetworkForm" action="javascript:void(0)">
                                <div class="col-12 mb-3">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <i class="fa-brands fa-linkedin"
                                               data-bs-toggle="tooltip"
                                               data-bs-placement="left"
                                               data-bs-title="Linkedin"></i>
                                        </div>
                                        <input type="text" class="form-control"
                                               id="linkedin" name="linkedin"
                                               placeholder="@ @lang('social.linkedin_username')"
                                               value="{{app('social_networks')?->linkedin}}" aria-label="Linkedin">
                                    </div>
                                </div>

                                <hr class="col-12 mb-3">

                                <div class="col-12 mb-3">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <i class="fa-brands fa-github"
                                               data-bs-toggle="tooltip"
                                               data-bs-placement="left"
                                               data-bs-title="Github"></i>
                                        </div>
                                        <input type="text" class="form-control"
                                               id="github" name="github"
                                               placeholder="@ @lang('social.github_username')"
                                               value="{{app('social_networks')?->github}}" aria-label="Github">
                                    </div>

                                </div>

                                <hr class="col-12 mb-3">

                                <div class="col-12 mb-3">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <i class="fa-brands fa-instagram"
                                               data-bs-toggle="tooltip"
                                               data-bs-placement="left"
                                               data-bs-title="Instagram"></i>
                                        </div>
                                        <input type="text" class="form-control"
                                               id="instagram" name="instagram"
                                               placeholder="@ @lang('social.instagram_username')"
                                               value="{{app('social_networks')?->instagram}}" aria-label="Instagram">
                                    </div>
                                </div>

                                <hr class="col-12 mb-3">

                                <div class="col-12 mb-3">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <i class="fa-brands fa-x-twitter"
                                               data-bs-toggle="tooltip"
                                               data-bs-placement="left"
                                               data-bs-title="X"></i>
                                        </div>
                                        <input type="text" class="form-control"
                                               id="x" name="x"
                                               placeholder="@ @lang('social.x_username')"
                                               value="{{app('social_networks')?->x}}" aria-label="X">
                                    </div>
                                </div>

                                <hr class="col-12 mb-3">

                                <div class="col-12 mb-3">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <i class="fa-brands fa-facebook"
                                               data-bs-toggle="tooltip"
                                               data-bs-placement="left"
                                               data-bs-title="Facebook"></i>
                                        </div>
                                        <input type="text" class="form-control"
                                               id="facebook" name="facebook"
                                               placeholder="@ @lang('social.facebook_username')"
                                               value="{{app('social_networks')?->facebook}}" aria-label="Facebook">
                                    </div>
                                </div>

                                <hr class="col-12 mb-3">

                                <div class="col-12 mb-3">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <i class="fa-brands fa-dev"
                                               data-bs-toggle="tooltip"
                                               data-bs-placement="left"
                                               data-bs-title="Dev.to"></i>
                                        </div>
                                        <input type="text" class="form-control"
                                               id="devto" name="devto"
                                               placeholder="@ @lang('social.devto_username')"
                                               value="{{app('social_networks')?->devto}}" aria-label="Dev.to">
                                    </div>
                                </div>

                                <hr class="col-12 mb-3">

                                <div class="col-12 mb-3">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <i class="fa-brands fa-medium"
                                               data-bs-toggle="tooltip"
                                               data-bs-placement="left"
                                               data-bs-title="Medium"></i>
                                        </div>
                                        <input type="text" class="form-control"
                                               id="medium" name="medium"
                                               placeholder="@ @lang('social.medium_username')"
                                               value="{{app('social_networks')?->medium}}" aria-label="Medium">
                                    </div>
                                </div>

                                <hr class="col-12 mb-3">

                                <div class="col-12 mb-3">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <i class="fa-brands fa-youtube"
                                               data-bs-toggle="tooltip"
                                               data-bs-placement="left"
                                               data-bs-title="YouTube"></i>
                                        </div>
                                        <input type="text" class="form-control"
                                               id="youtube" name="youtube"
                                               placeholder="@ @lang('social.youtube_username')"
                                               value="{{app('social_networks')?->youtube}}" aria-label="YouTube">
                                    </div>
                                </div>

                                <hr class="col-12 mb-3">

                                <div class="col-12 mb-3">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <i class="fa-brands fa-reddit-alien"
                                               data-bs-toggle="tooltip"
                                               data-bs-placement="left"
                                               data-bs-title="Reddit"></i>
                                        </div>
                                        <input type="text" class="form-control"
                                               id="reddit" name="reddit"
                                               placeholder="@ @lang('social.reddit_username')"
                                               value="{{app('social_networks')?->reddit}}" aria-label="Reddit">
                                    </div>
                                </div>

                                <hr class="col-12 mb-3">

                                <div class="col-12 mb-3">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <i class="fa-brands fa-xbox"
                                               data-bs-toggle="tooltip"
                                               data-bs-placement="left"
                                               data-bs-title="Xbox"></i>
                                        </div>
                                        <input type="text" class="form-control"
                                               id="xbox" name="xbox"
                                               placeholder="@ @lang('social.xbox_username')"
                                               value="{{app('social_networks')?->xbox}}" aria-label="Xbox">
                                    </div>
                                </div>

                                <hr class="col-12 mb-3">

                                <div class="col-12 mb-3">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <i class="fa-brands fa-deviantart"
                                               data-bs-toggle="tooltip"
                                               data-bs-placement="left"
                                               data-bs-title="Deviantart"></i>
                                        </div>
                                        <input type="text" class="form-control"
                                               id="deviantart" name="deviantart"
                                               placeholder="@ @lang('social.deviantart_username')"
                                               value="{{app('social_networks')?->deviantart}}" aria-label="Deviantart">
                                    </div>
                                </div>

                                <hr class="col-12 mb-3">


                                @csrf
                                <div class="col-12 mt-3">
                                    <button type="submit" class="btn btn-primary">@lang('general.save')</button>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane settings-tabs @if(request()->get('tab')=='themes') active @endif"
                             id="themes">
                            @if(session('success'))
                                <div class="alert alert-success">
                                    {{session('success')}}
                                </div>
                            @endif
                            @if(session('error'))
                                <div class="alert alert-danger">
                                    {{session('error')}}
                                </div>
                            @endif
                            <table class="table" aria-describedby="table">
                                <tr>
                                    <th>@lang('themes.theme')</th>
                                    <th>@lang('language.default')</th>
                                    <th>@lang('general.actions')</th>
                                </tr>
                                @foreach($themes as $theme)
                                    <tr>
                                        <td>
                                            {{$theme->name}}
                                        </td>
                                        <td>
                                            @if($theme->is_default)
                                                <span class="badge bg-primary">@lang('general.yes')</span>
                                            @else
                                                <span class="badge bg-secondary">@lang('general.no')</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(!$theme->is_default)
                                                <a href="{{route('admin.settings.themes.default', $theme)}}"
                                                   data-bs-toggle="tooltip"
                                                   data-bs-placement="top"
                                                   title="@lang('themes.make_default')"
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fa-duotone fa-check"></i>
                                                </a>
                                                <a href="javascript:deleteTheme('{{$theme->id}}')"
                                                   data-bs-toggle="tooltip"
                                                   data-bs-placement="top"
                                                   title="@lang('general.delete')"
                                                   class="btn btn-sm btn-danger">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                            <a href="#"
                               data-bs-toggle="modal"
                               data-bs-target="#themeUploadModal"
                               class="btn btn-primary">
                                @lang('general.new')
                            </a>
                        </div>

                        <div class="tab-pane settings-tabs @if(request()->get('tab')=='languages') active @endif"
                             id="languages">
                            <table class="table" aria-describedby="table">
                                <tr>
                                    <th>@lang('language.language')</th>
                                    <th>@lang('language.code')</th>
                                    <th>@lang('language.icon')</th>
                                    <th>@lang('language.status')</th>
                                    <th>@lang('language.default')</th>
                                    <th>@lang('general.actions')</th>
                                </tr>
                                @foreach($all_languages as $language)
                                    <tr>
                                        <td>
                                            {{$language->name}}
                                        </td>
                                        <td>
                                            {{$language->code}}
                                        </td>
                                        <td>
                                            <img src="{{config('app.url')}}/themes/flags/{{$language->flag}}.webp"
                                                 alt="{{$language->name}}"
                                                 class="img-fluid" style="height: 30px;">
                                        </td>
                                        <td>
                                            @if($language->is_active)
                                                <span class="badge bg-success">@lang('general.active')</span>
                                            @else
                                                <span class="badge bg-danger">@lang('general.passive')</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($language->is_default)
                                                <span class="badge bg-primary">@lang('general.yes')</span>
                                            @else
                                                <span class="badge bg-secondary">@lang('general.no')</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="javascript:editLanguage('{{$language->id}}')"
                                               data-bs-toggle="tooltip"
                                               data-bs-placement="top"
                                               title="@lang('general.edit')"
                                               class="btn btn-sm btn-primary">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <a href="javascript:deleteLanguage('{{$language->id}}')"
                                               data-bs-toggle="tooltip"
                                               data-bs-placement="top"
                                               title="@lang('general.delete')"
                                               class="btn btn-sm btn-danger">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                            <a href="javascript:openLanguageModal()" class="btn btn-primary">@lang('general.new')</a>
                        </div>

                        <div class="tab-pane settings-tabs @if(request()->get('tab')=='notifications') active @endif"
                             id="notifications">
                            @if(session('success'))
                                <div class="alert alert-success">
                                    {{session('success')}}
                                </div>
                            @endif
                            @if(session('error'))
                                <div class="alert alert-danger">
                                    {{session('error')}}
                                </div>
                            @endif
                            <form class="row" id="notificationsForm" method="post"
                                  action="{{route('admin.settings.notifications.save')}}">
                                <div class="col-12 mb-3">
                                    <label for="onesignal">OneSignal</label>
                                    <textarea class="form-control" id="onesignal"
                                              rows="15"
                                              name="onesignal">{!! $admin_notification?->onesignal !!}</textarea>
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="app_id">App ID</label>
                                    <input type="text" class="form-control" id="app_id" name="app_id"
                                           value="{{$onesignal?->app_id}}">
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="auth_key">Auth Key</label>
                                    <input type="text" class="form-control" id="auth_key" name="auth_key"
                                           value="{{$onesignal?->auth_key}}">
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="safari_web_id">Safari Web ID</label>
                                    <input type="text" class="form-control" id="safari_web_id" name="safari_web_id"
                                           value="{{$onesignal?->safari_web_id}}">
                                </div>
                                <div class="col-12 mb-3">
                                    <button type="submit" class="btn btn-primary">@lang('general.save')</button>
                                </div>
                                @csrf
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" tabindex="-1" id="languageModal">
        <div class="modal-dialog">
            <form class="modal-content" method="post" id="languageForm" action="javascript:void(0)">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('language.add_edit')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="name">@lang('language.language')</label>
                            <input type="text" class="form-control" id="name" name="name">
                        </div>
                        <div class="col-12 mb-3">
                            <label for="code">@lang('language.code')</label>
                            <input type="text" class="form-control" id="code" name="code">
                        </div>
                        <div class="col-12 mb-3">
                            <label for="flag">@lang('language.flag')</label>
                            <input type="text" class="form-control" id="flag" name="flag">
                        </div>
                        <div class="col-12 mb-3">
                            <label for="is_active">@lang('language.status')</label>
                            <select name="is_active" id="is_active" class="form-control">
                                <option value="1">@lang('general.active')</option>
                                <option value="0">@lang('general.passive')</option>
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label for="is_default">@lang('language.default')</label>
                            <select name="is_default" id="is_default" class="form-control">
                                <option value="1">@lang('general.yes')</option>
                                <option value="0">@lang('general.no')</option>
                            </select>
                        </div>
                        @csrf
                        <input type="hidden" name="id" id="language_id">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">@lang('general.close')</button>
                    <button type="submit" class="btn btn-primary">@lang('general.save')</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="themeUploadModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" method="post" enctype="multipart/form-data"
                  id="themeUploadForm" action="javascript:void(0)">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">@lang('themes.upload_theme')</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="theme">@lang('themes.theme')</label>
                            <input type="file" class="form-control" id="theme" name="theme">
                        </div>
                        @csrf
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">@lang('general.close')</button>
                    <button type="submit" class="btn btn-primary">@lang('general.save')</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
    <script>
        let languageSaveUrl = '{{route('admin.settings.languages.save')}}';

        function imageDelete(media_id, media_type) {
            let post_url;
            if (media_type === "site_logo_light") {
                post_url = '{{route('admin.settings.general.logo.delete', ['type' => 'light'])}}';
            }
            else if(media_type === "site_logo_dark") {
                post_url = '{{route('admin.settings.general.logo.delete', ['type' => 'dark'])}}';
            }
            else if(media_type === "app_icon") {
                post_url = '{{route('admin.settings.general.app_icon.delete')}}';
            }
            else {
                post_url = '{{route('admin.settings.general.favicon.delete')}}';
            }
            Swal.fire(
                {
                    title: "@lang('post.delete_image')",
                    text: "@lang('post.delete_image_text')",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "@lang('general.yes')",
                    cancelButtonText: "@lang('general.no')",
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: post_url,
                            type: 'post',
                            data: {
                                _token: '{{csrf_token()}}'
                            },
                            success: function (result) {
                                if (result.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: '@lang('post.delete_image_success_title')',
                                        text: '@lang('post.delete_image_success')',
                                        showConfirmButton: false,
                                        timer: 1500
                                    });
                                    window.location.reload();
                                } else {
                                    Swal.fire({
                                        icon: 'warning',
                                        title: '@lang('post.delete_image_error_title')',
                                        text: '@lang('post.delete_image_error')',
                                        showConfirmButton: false,
                                        //timer: 1500
                                    });
                                }
                            },
                            error: function (xhr) {
                                console.log(xhr);
                                Swal.fire({
                                    icon: 'warning',
                                    title: '@lang('post.delete_image_error_title')',
                                    text: xhr.responseJSON.message,
                                    showConfirmButton: false,
                                    //timer: 1500
                                });
                            }
                        });
                    }
                }
            );
        }

        function saveSettings(url, formId) {
            $.ajax({
                type: 'POST',
                url: url,
                data: $('#' + formId).serialize(),
                success: function (response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        });
                    } else {
                        Swal.fire(
                            'Error!',
                            response.message,
                            'error'
                        );
                    }
                },
                error: function (response) {
                    Swal.fire(
                        'Error!',
                        response.message,
                        'error'
                    );
                }
            });
        }

        function editLanguage(id) {
            languageSaveUrl = '{{route('admin.settings.languages.save')}}/' + id;
            $.ajax({
                type: 'POST',
                url: '{{route('admin.settings.languages.show')}}',
                data: {
                    id: id,
                    _token: '{{csrf_token()}}'
                },
                success: function (response) {
                    if (response) {
                        $('#name').val(response.name);
                        $('#code').val(response.code);
                        $('#flag').val(response.flag);
                        $('#is_active').val(response.is_active ? 1 : 0);
                        $('#is_default').val(response.is_default ? 1 : 0);
                        $('#language_id').val(response._id).attr('disabled', false);
                        $('#languageModal').modal('show');
                    } else {
                        Swal.fire(
                            'Error!',
                            response.message,
                            'error'
                        );
                    }
                },
                error: function (response) {
                    Swal.fire(
                        'Error!',
                        response.message,
                        'error'
                    );
                }
            });
        }

        function openLanguageModal() {
            languageSaveUrl = '{{route('admin.settings.languages.save')}}';
            $('#languageForm').trigger('reset');
            $('#language_id').val('').attr('disabled', true);
            $('#languageModal').modal('show');
        }

        function deleteLanguage(id) {
            Swal.fire({
                title: "@lang('general.are_you_sure')",
                text: "@lang('language.delete_warning')",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "@lang('general.yes')",
                cancelButtonText: "@lang('general.no')",
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'POST',
                        url: '{{route('admin.settings.languages.delete')}}',
                        data: {
                            id: id,
                            _token: '{{csrf_token()}}'
                        },
                        success: function (response) {
                            if (response.status) {
                                Swal.fire({
                                    icon: 'success',
                                    title: '@lang('language.delete_success')',
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                                window.location.reload();
                            } else {
                                Swal.fire(
                                    'Error!',
                                    response.message,
                                    'error'
                                );
                            }
                        },
                        error: function (response) {
                            Swal.fire(
                                'Error!',
                                response.message,
                                'error'
                            );
                        }
                    });
                }
            })
        }

        function deleteTheme(id) {
            Swal.fire({
                title: "@lang('general.are_you_sure')",
                text: "@lang('themes.delete_warning')",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "@lang('general.yes')",
                cancelButtonText: "@lang('general.no')",
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'POST',
                        url: '{{route('admin.settings.themes.delete')}}',
                        data: {
                            id: id,
                            _token: '{{csrf_token()}}'
                        },
                        success: function (response) {
                            if (response.status === "success") {
                                Swal.fire({
                                    icon: 'success',
                                    title: '@lang('themes.delete_success')',
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                                //window.location.reload();
                            } else {
                                Swal.fire(
                                    'Error!',
                                    response.message,
                                    'error'
                                );
                            }
                        },
                        error: function (response) {
                            Swal.fire(
                                'Error!',
                                response.message,
                                'error'
                            );
                        }
                    });
                }
            })
        }

        function ChangeTab(tab) {
            window.history.pushState("", "", '{{route('admin.settings')}}?tab=' + tab);
            $('.settings-tabs').removeClass('active');
            $('#' + tab).addClass('active').click();
            $('.settings-links').removeClass('active');
            $('#' + tab + '-menu').addClass('active');
        }


        $(document).ready(function () {
            let urlParams = new URLSearchParams(window.location.search);

            if (urlParams.has('tab')) {
                ChangeTab(urlParams.get('tab'));
            } else {
                ChangeTab('general');
            }

            $('#seoSave').submit(function () {
                saveSettings('{{route('admin.settings.seo.save')}}', 'seoSave');
            });

            $('#robotsTxtSave').submit(function () {
                saveSettings('{{route('admin.settings.seo.robots.save')}}', 'robotsTxtSave');
            });

            $('#socialNetworkForm').submit(function () {
                saveSettings('{{route('admin.settings.social.save')}}', 'socialNetworkForm');
            });

            $('#analyticsForm').submit(function () {
                saveSettings('{{route('admin.settings.analytics.save')}}', 'analyticsForm');
            });

            $('#advertisePost').submit(function () {
                saveSettings('{{route('admin.settings.advertisement.save')}}', 'advertisePost');
            });

            $('#showInHeader').submit(function () {
                saveSettings('{{route('admin.settings.social.header.save')}}', 'showInHeader');
            });

            $('#languageForm').submit(function () {
                saveSettings(languageSaveUrl, 'languageForm');
                window.location.reload();
            });

            $('#themeUploadForm').submit(function () {
                $.ajax({
                    type: 'POST',
                    url: '{{route('admin.settings.themes.upload')}}',
                    data: new FormData($('#themeUploadForm')[0]),
                    contentType: false,
                    processData: false,
                    success: function (response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            });
                            window.location.reload();
                        } else {
                            Swal.fire(
                                'Error!',
                                response.message,
                                'error'
                            );
                        }
                    },
                    error: function (response) {
                        Swal.fire(
                            'Error!',
                            response.message,
                            'error'
                        );
                    }
                });
            });
        });
    </script>
@endsection
