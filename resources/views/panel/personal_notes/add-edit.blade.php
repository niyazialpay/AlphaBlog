@extends('panel.base')
@section('title',__('notes.notes'))
@section('breadcrumb_link')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{route('admin.index')}}">@lang('dashboard.dashboard')</a></li>
        <li class="breadcrumb-item">
            <a href="{{route('admin.notes')}}">@lang('notes.notes')</a>
        </li>
        @if($note->id)
            <li class="breadcrumb-item">
                <a href="{{route('admin.notes.show', $note)}}">{{$note->title}}</a>
            </li>
            <li class="breadcrumb-item active">@lang('general.edit')</li>
        @else
            <li class="breadcrumb-item active">@lang('general.new')</li>
        @endif
    </ol>
@endsection
@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">@lang('notes.notes')</h3>
        </div>
        <div class="card-body">
            <form class="row" id="noteSave" method="post" action="javascript:void(0)">
                <div class="col-12 mb-3">
                    <label for="title" class="form-label">@lang('post.title')</label>
                    <input type="text" class="form-control" id="title" name="title" placeholder="@lang('post.title')"
                           value="{{$note->title}}">
                </div>
                <div class="col-12 mb-3">
                    <label for="content">@lang('post.content')</label>
                    <textarea name="content" id="content" class="form-control"
                              placeholder="@lang('post.content')">{!! $note->content !!}</textarea>
                </div>
                <div class="col-12 mb-3">
                    <label for="category_id">@lang('notes.category')</label>
                    <select name="category_id" id="category_id" class="form-control" required>
                        <option value="">@lang('notes.select_category')</option>
                        @foreach($categories as $category)
                            <option value="{{$category->id}}"
                                    @if($note?->category_id == $category->id) selected @endif>
                                {{$category->name}}
                            </option>
                        @endforeach
                    </select>
                </div>
                @csrf
                <div class="col-12 mb-3">
                    <button type="submit" class="btn btn-primary">@lang('general.save')</button>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('script')
    <script>
        @if($note->id)
            let post_url = '{{route('admin.notes.edit.save', $note)}}';
            let image_post_url = '{{route('admin.notes.editor.image.upload')}}/{{ $note->id }}';
        @else
            let post_url = '{{route('admin.notes.save')}}';
            let image_post_url = '{{route('admin.notes.editor.image.upload')}}';
        @endif

        $(document).ready(function () {

            const post_image_upload_handler = (blobInfo, progress) => new Promise((resolve, reject) => {
                const xhr = new XMLHttpRequest();
                xhr.withCredentials = false;
                xhr.open('POST', image_post_url);

                xhr.upload.onprogress = (e) => {
                    progress(e.loaded / e.total * 100);
                };

                xhr.onload = () => {
                    if (xhr.status === 403) {
                        reject({message: 'HTTP Error: ' + xhr.status, remove: true});
                        return;
                    }

                    if (xhr.status < 200 || xhr.status >= 300) {
                        reject('HTTP Error: ' + xhr.status);
                        return;
                    }

                    const json = JSON.parse(xhr.responseText);

                    if (!json || typeof json.location != 'string') {
                        reject('Invalid JSON: ' + xhr.responseText);
                        return;
                    }

                    resolve(json.location);

                    post_url = '{{route('admin.notes.save')}}/' + json.note_id;
                    image_post_url = '{{route('admin.notes.editor.image.upload')}}' + '/' + json.note_id;
                };

                xhr.onerror = () => {
                    reject('Image upload failed due to a XHR Transport error. Code: ' + xhr.status);
                };

                const formData = new FormData();
                formData.append('file', blobInfo.blob(), blobInfo.filename());
                formData.append('_token', '{{csrf_token()}}');
                formData.append('title', $('#title').val());

                xhr.send(formData);
            });

            let tinymce_skin;
            let tinymce_content_css;

            if(localStorage.getItem("dark-mode") === "true"){
                tinymce_skin = 'oxide-dark';
                tinymce_content_css = 'dark';
            }
            else{
                tinymce_skin = 'oxide';
                tinymce_content_css = 'default';
            }

            let tinymce_settings = {
                selector: 'textarea#content',  // change this value according to your HTML
                language: '{{session('language')}}',
                branding: false,
                license_key: 'gpl',
                height: 600,
                mobile: {
                    theme: 'silver',
                    toolbar: 'undo | bold italic | link | image | font size select forecolor',
                    menubar: false
                },
                plugins: [
                    'advlist',
                    'autolink',
                    'lists',
                    'link',
                    'image',
                    'charmap',
                    'preview',
                    'anchor',
                    'pagebreak',
                    'searchreplace',
                    'wordcount',
                    'visualblocks',
                    'visualchars',
                    'code',
                    'fullscreen',
                    'insertdatetime',
                    'media',
                    'nonbreaking',
                    'table',
                    'directionality',
                    'emoticons',
                    'codesample',
                    'help',
                    'quickbars',
                    'emoticons',
                    'accordion'
                ],
                toolbar1: 'undo redo | bold italic | fontsize blocks forecolor backcolor | ' +
                    'alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link | help ',
                toolbar2: 'print preview media image | charmap emoticons codesample code | visualblocks',
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
                skin: tinymce_skin,
                content_css: tinymce_content_css,
                images_upload_handler: post_image_upload_handler
            }

            tinymce.init(tinymce_settings);

            $('#dark-mode-switcher-button').on('click', function(){

                if(localStorage.getItem("dark-mode") === "true"){
                    tinymce_settings.content_css = 'dark';
                    tinymce_settings.skin = 'oxide-dark';
                }
                else{
                    tinymce_settings.content_css = 'default';
                    tinymce_settings.skin = 'oxide';
                }
                tinymce.get('content').remove();
                tinymce.init(tinymce_settings);
            });

            $('#noteSave').submit(function () {
                $.ajax({
                    url: post_url,
                    type: 'POST',
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.status === 'success') {
                            window.location.href = '{{route('admin.notes')}}/show/' + response.id + '/edit';
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: response.message
                            });
                        }
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: xhr.responseJSON.message
                        });
                    }
                });
            });
        });
    </script>
@endsection
