@extends('panel.base')
@if($type == 'blogs')
    @section('title',__('post.blogs'))
@else
    @section('title',__('post.pages'))
@endif

@section('breadcrumb_link')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{route('admin.index')}}">@lang('dashboard.dashboard')</a></li>
        <li class="breadcrumb-item">
            @if($type == 'blogs')
                <a href="{{route('admin.posts', ['type' => 'blogs'])}}">@lang('post.blogs')</a>
            @else
                <a href="{{route('admin.posts', ['type' => 'pages'])}}">@lang('post.pages')</a>
            @endif
        </li>
        <li class="breadcrumb-item active">@lang('general.add-edit')</li>
    </ol>
@endsection
@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                @if($type == 'blogs')
                    @lang('post.blogs')
                @else
                    @lang('post.pages')
                @endif
            </h3>
        </div>
        <div class="card-body">
            <form class="row" method="POST" id="blogSave" enctype="multipart/form-data" action="javascript:void(0)">
                <div class="col-12 mb-3">
                    <label for="title">@lang('post.title')</label>
                    <input type="text" class="form-control" name="title" id="title" placeholder="@lang('post.title')"
                           value="{{stripslashes($post->title)}}">
                </div>
                <div class="col-12 mb-3">
                    <label for="slug">@lang('post.slug')</label>
                    <input type="text" class="form-control" name="slug" id="slug" placeholder="@lang('post.slug')"
                           value="{{stripslashes($post->slug)}}">
                </div>
                @if($type=='blogs')
                    <div class="col-12 mb-3">
                        <label for="category_id">@lang('post.category')</label>
                        <select name="category_id[]" id="category_id" class="form-control select2" style="width: 100%"
                                multiple>
                            @foreach($categories as $item)
                                <option value="{{$item->id}}">{{stripslashes($item->name)}} ({{$item->language}})</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-12 mb-3" id="image_input">
                    <label for="image">@lang('post.image')</label>
                    @if($post->getFirstMediaUrl('posts', 'thumb'))
                        <img src="{{$post->getFirstMediaUrl('posts', 'thumb')}}" id="image"
                             alt="{{stripslashes($post->title)}}" class="img-fluid" width="450">
                        <a href="javascript:imageDelete('{{$post->id}}')" class="text-danger">
                            <i class="fa fa-trash"></i>
                        </a>
                    @else
                        <input type="file" class="form-control" name="image" id="image">
                    @endif
                </div>
                <div class="col-12 mb-3">
                    <label for="content">@lang('post.content')</label>
                    <textarea name="content" id="content" class="form-control"
                              placeholder="@lang('post.content')">{!! stripslashes($post->content) !!}</textarea>
                </div>
                <div class="col-12 mb-3">
                    <label for="meta_keywords">@lang('post.meta_keywords')</label>
                    <input type="text" class="form-control" name="meta_keywords" id="meta_keywords"
                           placeholder="@lang('post.meta_keywords')" value="{{$post->meta_keywords}}">
                </div>
                <div class="col-12 mb-3">
                    <label for="meta_description">@lang('post.meta_description')</label>
                    <input type="text" class="form-control" name="meta_description" id="meta_description"
                           placeholder="@lang('post.meta_description')"
                           value="{{stripslashes($post->meta_description)}}">
                </div>
                <div class="col-12 mb-3">
                    <label for="is_published">@lang('post.is_published')</label>
                    <select name="is_published" id="is_published" class="form-control">
                        <option value="1" @if($post->is_published) selected @endif>@lang('post.status_active')</option>
                        <option value="0" @if(!$post->is_published) selected @endif>@lang('post.status_passive')</option>
                    </select>
                </div>
                <div class="col-12 mb-3">
                    <label for="user_id">@lang('post.author')</label>
                    <select name="user_id" id="user_id" class="form-control">
                        @foreach($users as $user)
                            <option value="{{$user->id}}"
                                    @if($post->user_id)
                                        @if($post->user_id==$user->id) selected @endif
                                    @else
                                        @if(auth()->user()->id==$user->id) selected @endif
                                @endif
                            >
                                {{$user->name}} {{$user->surname}} ({{$user->nickname}})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 mb-3">
                    <label for="language">@lang('general.language')</label>
                    <select name="language" id="language" class="form-control">
                        @foreach($languages as $language)
                            <option value="{{$language->code}}"
                                    @if($post->language == $language->code) selected @endif>{{$language->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 mb-3 border rounded p-3">
                    <ul class="nav nav-pills border-bottom">
                        @foreach($languages as $n => $language)
                            <li class="nav-item"><a class="nav-link @if($n==0) active @endif " href="#form_{{$language->code}}" data-bs-toggle="tab">{{$language->name}}</a></li>
                        @endforeach
                    </ul>
                    <div class="tab-content">
                        @foreach($languages as $n => $language)

                            <div class="tab-pane @if($n==0) active @endif" id="form_{{$language->code}}">
                                <input type="hidden" name="hreflang[{{$language->code}}]" value="{{$post->hreflang[$language->code] ?? ''}}">
                                <label for="hreflang_url[{{$language->code}}]">Href Lang ({{$language->code}})</label>
                                <input type="text" class="form-control" name="hreflang_url[{{$language->code}}]"
                                       id="hreflang_url[{{$language->code}}]"
                                       placeholder="Href Lang ({{$language->code}})"
                                       @if($post->href_lang)
                                       @if(array_key_exists($language->code, $post->href_lang))
                                           value="{{$post->href_lang[$language->code]}}"
                                    @endif @endif>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="col-12 mb-3">
                    <label for="published_at">@lang('post.published_at')</label>
                    <input type="datetime-local" class="form-control" name="published_at" id="published_at"
                           placeholder="@lang('post.published_at')"
                           value="{{dateformat($post->created_at, 'Y-m-d H:i:s', config('app.timezone'))}}">
                </div>
                @csrf
                <input type="hidden" name="id" value="{{$post->id}}">

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">@if($post->id)
                            @lang('general.update')
                        @else
                            @lang('general.create')
                        @endif </button>
                </div>
            </form>
        </div>
        @if($post->id)
            @if($post->history->count()>0)
                <div class="card-footer">
                    <a href="{{route('admin.post.history', [$type, $post])}}">History ({{$post->history->count()}})</a>
                </div>
            @endif
        @endif
    </div>

    @if($post->id && $post->post_type=='post')
        <div class="card">
            <div class="card-header d-flex">
                <h3 class="card-title">@lang('comments.comments')</h3>
                <div class="ml-auto">
                    <a href="javascript:NewComment('{{$post->id}}', '{{$post->title}}')"
                       class="btn btn-default"
                       data-bs-toggle="tooltip" data-bs-placement="top"
                       title="@lang('general.new')">
                        <i class="fa-duotone fa-comment-plus"></i> @lang('general.new')
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 table-responsive">
                        <table class="table table-striped" aria-describedby="contents">
                            <thead>
                            <tr>
                                <th scope="col" style="width: 250px;">@lang('general.author')</th>
                                <th scope="col">@lang('comments.comment')</th>
                                <th scope="col" style="width: 250px;">@lang('general.created_at')</th>
                                <th scope="col" style="width: 250px;">@lang('general.actions')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($post->comments as $comment)
                                <tr>
                                    <td>
                                        <a id="comment-{{$comment->id}}"></a>
                                        @if($comment->user_id)
                                            <img
                                                src="https://www.gravatar.com/avatar/{{md5(strtolower(trim($comment->user->email)))}}?s=34"
                                                class="img-circle elevation-2" alt="{{$comment->user->nickname}}">

                                            {{$comment->user->nickname}}
                                            <br>
                                            <small>{{$comment->user->email}}</small>
                                        @else
                                            <img
                                                src="https://www.gravatar.com/avatar/{{md5(strtolower(trim($comment->email)))}}?s=34"
                                                class="img-circle elevation-2" alt="{{$comment->name}}">
                                            {{$comment->name}}
                                            <br>
                                            <small>{{$comment->email}}</small>
                                        @endif
                                            <br>
                                            <small>{{$comment->email}}</small>
                                        <br>
                                        <small>{{$comment->ip_address}}</small>
                                    </td>
                                    <td>
                                        {{$comment->comment}}
                                    </td>
                                    <td>{{$comment->created_at}}</td>
                                    <td>
                                        @if($comment->trashed())
                                            <a href="javascript:RestoreComment('{{$comment->id}}')"
                                               class="btn btn-sm btn-success mx-1 my-2"
                                               data-bs-toggle="tooltip" data-bs-placement="top"
                                               title="@lang('general.restore')">
                                                <i class="fa fa-trash-restore nav-icon"></i>
                                            </a>
                                            <a href="javascript:DeleteComment('{{$comment->id}}', true)"
                                               class="btn btn-sm btn-danger mx-1 my-2"
                                               data-bs-toggle="tooltip" data-bs-placement="top"
                                               title="@lang('general.delete')">
                                                <i class="fa fa-trash nav-icon"></i>
                                            </a>
                                        @else
                                            @if($comment->is_approved)
                                                <a href="javascript:ApproveComment('{{$comment->id}}', false)"
                                                   class="btn btn-sm btn-danger mx-1 my-2"
                                                   data-bs-toggle="tooltip" data-bs-placement="top"
                                                   title="@lang('comments.disapprove')">
                                                    <i class="fa-duotone fa-ban nav-icon"></i>
                                                </a>
                                            @else
                                                <a href="javascript:ApproveComment('{{$comment->id}}', true)"
                                                   class="btn btn-sm btn-success mx-1 my-2"
                                                   data-bs-toggle="tooltip" data-bs-placement="top"
                                                   title="@lang('comments.approve')">
                                                    <i class="fa-duotone fa-check nav-icon"></i>
                                                </a>
                                            @endif
                                            <a href="javascript:NewComment('{{$post->id}}', '{{$post->title}}')"
                                               class="btn btn-sm btn-primary mx-1 my-2"
                                               data-bs-toggle="tooltip" data-bs-placement="top"
                                               title="@lang('comments.reply')">
                                                <i class="fa-duotone fa-reply nav-icon"></i>
                                            </a>
                                            <a href="javascript:EditComment('{{$comment->id}}')"
                                               class="btn btn-sm btn-primary mx-1 my-2"
                                               data-bs-toggle="tooltip" data-bs-placement="top"
                                               title="@lang('general.edit')">
                                                <i class="fa fa-edit nav-icon"></i>
                                            </a>
                                            <a href="javascript:DeleteComment('{{$comment->id}}')"
                                               class="btn btn-sm btn-danger mx-1 my-2"
                                               data-bs-toggle="tooltip" data-bs-placement="top"
                                               title="@lang('general.delete')">
                                                <i class="fa fa-trash nav-icon"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align: center">@lang('comments.no_comments_found')</td>
                                </tr>
                            @endforelse
                            </tbody>
                            <tfoot>
                            <tr>
                                <th scope="col">@lang('general.author')</th>
                                <th scope="col">@lang('comments.comment')</th>
                                <th scope="col">@lang('general.created_at')</th>
                                <th scope="col">@lang('general.actions')</th>
                            </tr>
                            </tfoot>

                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
    @include('panel.post.comments.modal')
@endsection

@section('script')
    <style>
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            color: #000000 !important;
        }
    </style>
    <script>
        let post_url;
        @if($post->id)
            post_url = '{{route('admin.post.update', [$type, $post])}}';
        @else
            post_url = '{{route('admin.post.save', [$type])}}';
        @endif

        function imageDelete(image_id) {
            Swal.fire({
                title: "@lang('general.are_you_sure')",
                text: "@lang('general.you_wont_be_able_to_revert_this')",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "@lang('general.delete_confirm_yes')",
                cancelButtonText: "@lang('general.delete_confirm_no')",
                reverseButtons: true
            }).then(function (result) {
                if (result.value) {
                    $.ajax({
                        url: "{{route('admin.post.image.delete', [$type, $post])}}",
                        type: "POST",
                        data: {
                            id: image_id,
                            _token: "{{csrf_token()}}"
                        },
                        success: function (response) {
                            if (response.status) {
                                swal.fire({
                                    title: "@lang('general.success')",
                                    text: response.message,
                                    icon: "success",
                                    showCancelButton: false,
                                    showConfirmButton: false,
                                    timer: 1500
                                })
                                $('image').remove();
                                $('#image_input').html('<input type="file" class="form-control" name="image" id="image">');
                            } else {
                                swal.fire({
                                    title: "@lang('general.error')",
                                    text: response.message,
                                    icon: "error",
                                    showCancelButton: false,
                                    showConfirmButton: false,
                                    timer: 1500
                                })
                            }
                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                            swal.fire({
                                title: "Error",
                                text: xhr.responseJSON.message,
                                icon: "error",
                                showCancelButton: false,
                                showConfirmButton: false,
                                timer: 1500
                            })
                        }
                    });
                }
            });
        }

        $(document).ready(function () {
            $("#title").keyup(function () {
                let name = $(this).val();
                $("#slug").val(ToSeoUrl(name));
            });

            const post_image_upload_handler = (blobInfo, progress) => new Promise((resolve, reject) => {
                const xhr = new XMLHttpRequest();
                xhr.withCredentials = false;
                xhr.open('POST', '{{route('admin.post.editor.image.upload', [$type, $post])}}');

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

                    post_url = '{{route('admin.post.create', $type)}}/' + json.blog_id;
                };

                xhr.onerror = () => {
                    reject('Image upload failed due to a XHR Transport error. Code: ' + xhr.status);
                };

                const formData = new FormData();
                formData.append('file', blobInfo.blob(), blobInfo.filename());
                formData.append('_token', '{{csrf_token()}}');

                xhr.send(formData);
            });

            tinymce.init({
                selector: 'textarea#content',  // change this value according to your HTML
                language: '{{$default_language->code}}',
                branding: false,
                //skin: (window.matchMedia("(prefers-color-scheme: dark)").matches ? "oxide-dark" : ""),
                //content_css: (window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : ""),
                height: 600,
                mobile: {
                    theme: 'silver',
                    toolbar: 'undo | bold italic | link | image | font size select forecolor | shorturl',
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
                //images_upload_url: '{{route('admin.post.editor.image.upload', [$type, $post])}}',

                images_upload_handler: post_image_upload_handler
            });

            $('.select2').select2();

            @if($post->id)
            $('#category_id').val({!! json_encode($post->category_id) !!}).trigger('change');
            @endif

            $('#blogSave').submit(function () {
                $.ajax({
                    url: post_url,
                    type: 'POST',
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.status === 'success') {
                            window.location.href = '{{route('admin.posts', $type)}}/' + response.id + '/edit';
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
    @include('panel.post.comments.js')
@endsection
