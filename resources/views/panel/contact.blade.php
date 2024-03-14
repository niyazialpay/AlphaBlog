@extends('panel.base')
@section('title', __('contact.contact_page'))

@section('breadcrumb_link')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{route('admin.index')}}">@lang('dashboard.dashboard')</a></li>
        <li class="breadcrumb-item active">
            <a href="{{route('admin.contact_page')}}">@lang('contact.contact_page')</a>
        </li>
    </ol>
@endsection
@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">@lang('contact.contact_page')</h3>
        </div>
        <div class="card-body">
            <form action="{{route('admin.contact_page')}}" method="post">
                @csrf
                @if(session('success'))
                    <div class="alert alert-success">
                        {{session('success')}}
                    </div>
                @endif
                <ul class="nav nav-pills border-bottom">
                    @foreach(app('languages') as $n => $language)
                        <li class="nav-item"><a class="nav-link @if($n==0) active @endif " href="#form_{{$language->code}}" data-bs-toggle="tab">{{$language->name}}</a></li>
                    @endforeach
                </ul>
                <div class="tab-content row">
                    @foreach(app('languages') as $n => $language)
                        <div class="tab-pane @if($n==0) active @endif" id="form_{{$language->code}}">
                            @php($contact = $contactPage->where('language', $language->code)->first())
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="meta_description_{{$language->code}}">@lang('post.meta_description') ({{$language->code}})</label>
                                    <textarea class="form-control" name="meta_description_{{$language->code}}"
                                              id="meta_description_{{$language->code}}"
                                              placeholder="@lang('post.meta_description') ({{$language->code}})">{{$contact->meta_description}}</textarea>
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="meta_keywords_{{$language->code}}">@lang('post.meta_keywords') ({{$language->code}})</label>
                                    <input type="text" class="form-control" name="meta_keywords_{{$language->code}}"
                                              id="meta_keywords_{{$language->code}}"
                                              placeholder="@lang('post.meta_keywords') ({{$language->code}})" value="{{$contact->meta_keywords}}">
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="description_{{$language->code}}">@lang('contact.description') ({{$language->code}})</label>
                                    <textarea class="form-control description" name="description_{{$language->code}}"
                                              id="description_{{$language->code}}"
                                              placeholder="@lang('contact.description') ({{$language->code}})">{{$contact->description}}</textarea>
                                </div>
                            </div>
                        </div>
                    @endforeach
                        <div class="col-12 mb-3">
                            <label for="maps">@lang('contact.maps')</label>
                            <input type="text" class="form-control" name="maps"
                                   id="maps" value="{{$contact->maps}}"
                                   placeholder="@lang('contact.maps')">
                        </div>
                    <div class="col-12 mb-3">
                        <button type="submit" class="btn btn-primary">@lang('general.save')</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(document).ready(function(){
            tinymce.init({
                selector: 'textarea.description',  // change this value according to your HTML
                language: '{{app('default_language')->code}}',
                branding: false,
                //skin: (window.matchMedia("(prefers-color-scheme: dark)").matches ? "oxide-dark" : ""),
                //content_css: (window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : ""),
                height: 600,
                mobile: {
                    theme: 'silver',
                    toolbar: 'undo | bold italic | link | image | font size select forecolor',
                    menubar: false
                },
                plugins: [
                    'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview', 'anchor', 'pagebreak',
                    'searchreplace', 'wordcount', 'visualblocks', 'visualchars', 'code', 'fullscreen',
                    'insertdatetime', 'media', 'nonbreaking', 'table', 'directionality',
                    'emoticons', 'codesample', 'help'
                ],
                toolbar1: 'undo redo | insert | style select | bold italic | fontselect | fontsize select | ' +
                    'alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link ',
                toolbar2: 'print preview media image | forecolor backcolor | size select | emoticons | codesample',
                image_advtab: true,
                fontsize_formats: "8pt 10pt 12pt 14pt 18pt 24pt 36pt",
                extended_valid_elements: "a[class|name|href|target|title|onclick|rel]," +
                    "script[type|src]iframe[src|style|" +
                    "width|height|scrolling|marginwidth|marginheight|frameborder]" +
                    "img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name]",
                contextmenu: "undo redo | link image imagetools table spellchecker | " +
                    "inserttable cell row column deletetable | help",
                required: true,
                entity_encoding: "raw",
                promotion: false,
                relative_urls: false,
                remove_script_host: false,
                convert_urls: true,

            });
        });
    </script>
@endsection
