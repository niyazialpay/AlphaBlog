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
                        <li class="nav-item"><a class="nav-link active" href="#general" data-bs-toggle="tab">@lang('settings.general_settings')</a></li>
                        <li class="nav-item"><a class="nav-link" href="#seo" data-bs-toggle="tab">SEO</a></li>
                        <li class="nav-item"><a class="nav-link" href="#social" data-bs-toggle="tab">@lang('social.social_networks')</a></li>
                        <li class="nav-item"><a class="nav-link" href="#themes" data-bs-toggle="tab">@lang('settings.themes')</a></li>
                        <li class="nav-item"><a class="nav-link" href="#languages" data-bs-toggle="tab">@lang('settings.languages')</a></li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane active" id="general">

                        </div>
                        <div class="tab-pane" id="seo">
                            <form class="card" id="seoSave" method="post" action="javascript:void(0)">
                                @csrf
                                <div class="card-header">
                                    <ul class="nav nav-pills border-bottom">
                                        @foreach($languages as $n => $language)
                                            <li class="nav-item"><a class="nav-link @if($n==0) active @endif " href="#form_{{$language->code}}" data-bs-toggle="tab">{{$language->name}}</a></li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div class="card-body">
                                    <div class="tab-content">
                                        @foreach($languages as $n => $language)
                                            @php($seo = $seo_settings->where('language',$language->code)->first())
                                            <div class="tab-pane @if($n==0) active @endif" id="form_{{$language->code}}">
                                                <div class="row">
                                                    <div class="col-12 mb-3">
                                                        <label for="site_title_{{$language->code}}">@lang('settings.site_title') ({{$language->name}})</label>
                                                        <input type="text" class="form-control"
                                                               id="site_title_{{$language->code}}"
                                                               name="site_title_{{$language->code}}"
                                                               value="{{$seo->title}}">
                                                    </div>

                                                    <div class="col-12 mb-3">
                                                        <label for="site_description_{{$language->code}}">@lang('settings.site_description') ({{$language->name}})</label>
                                                        <textarea class="form-control" id="site_description_{{$language->code}}"
                                                                  name="site_description_{{$language->code}}" maxlength="160"
                                                                  rows="3">{{$seo->description}}</textarea>
                                                    </div>
                                                    <div class="col-12 mb-3">
                                                        <label for="site_keywords_{{$language->code}}">@lang('settings.site_keywords') ({{$language->name}})</label>
                                                        <input type="text" class="form-control"
                                                               id="site_keywords_{{$language->code}}"
                                                               name="site_keywords_{{$language->code}}"
                                                               value="{{$seo->keywords}}">
                                                    </div>
                                                    <div class="col-12 mb-3">
                                                        <label for="site_author_{{$language->code}}">@lang('settings.site_author') ({{$language->name}})</label>
                                                        <input type="text" class="form-control"
                                                               id="site_author_{{$language->code}}"
                                                               name="site_author_{{$language->code}}"
                                                               value="{{$seo->author}}">
                                                    </div>
                                                    <div class="col-12 mb-3">
                                                        <label for="robots_{{$language->code}}">@lang('settings.robots') ({{$language->name}})</label>
                                                        <select name="robots_{{$language->code}}" id="robots_{{$language->code}}" class="form-control">
                                                            <option value="index,follow" @if($seo->robots == 'index,follow') selected @endif>
                                                                @lang('settings.index_follow')
                                                            </option>

                                                            <option value="noindex,nofollow" @if($seo->robots == 'noindex,nofollow') selected @endif>
                                                                @lang('settings.noindex_nofollow')
                                                            </option>

                                                            <option value="index,nofollow" @if($seo->robots == 'index,nofollow') selected @endif>
                                                                @lang('settings.index_nofollow')
                                                            </option>

                                                            <option value="noindex,follow" @if($seo->robots == 'noindex,follow') selected @endif>
                                                                @lang('settings.noindex_follow')
                                                            </option>
                                                        </select>
                                                    </div>
                                                    <div class="col-12 mb-3">
                                                        <button type="submit" class="btn btn-primary">@lang('general.save')</button>
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
                                                <textarea class="form-control" id="robots_txt" name="robots_txt" rows="10" aria-label="@lang('settings.robots_txt')">{{$robots_txt}}</textarea>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <button type="submit" class="btn btn-primary">@lang('general.save')</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="social">
                        </div>

                        <div class="tab-pane" id="themes">
                        </div>

                        <div class="tab-pane" id="languages">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function(){
            $('#seoSave').submit(function(){
                $.ajax({
                    type: 'POST',
                    url: '{{route('admin.settings.seo.save')}}',
                    data: $(this).serialize(),
                    success: function(response){
                        if(response.status === 'success'){
                            Swal.fire({
                                icon: 'success',
                                title: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            });
                        }else{
                            Swal.fire(
                                'Error!',
                                response.message,
                                'error'
                            );
                        }
                    },
                    error: function(response){
                        Swal.fire(
                            'Error!',
                            response.message,
                            'error'
                        );
                    }
                });
            });

            $('#robotsTxtSave').submit(function(){
                $.ajax({
                    type: 'POST',
                    url: '{{route('admin.settings.seo.robots.save')}}',
                    data: $(this).serialize(),
                    success: function(response){
                        if(response.status === 'success'){
                            Swal.fire({
                                icon: 'success',
                                title: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            });
                        }else{
                            Swal.fire(
                                'Error!',
                                response.message,
                                'error'
                            );
                        }
                    },
                    error: function(response){
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
