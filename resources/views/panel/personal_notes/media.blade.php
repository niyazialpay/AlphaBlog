@extends('panel.base')
@section('title', $note->title. ' - Media')
@section('breadcrumb_link')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{route('admin.index')}}">@lang('dashboard.dashboard')</a></li>
        <li class="breadcrumb-item">
            <a href="{{route('admin.notes')}}">@lang('notes.notes')</a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{route('admin.notes.show', $note)}}">{{$note->title}}</a>
        </li>
        <li class="breadcrumb-item active">@lang('post.media')</li>
    </ol>
@endsection
@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                {{$note->title}}
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <ul id="media">
                        @foreach($note->getMedia('*') as $media)
                            <li id="{{$media->id}}" data-src="{{$media->getUrl()}}">
                                <figure class="mediaPhoto blue">
                                    <img src="{{$media->getUrl()}}" width="250" alt="{{$note->title}}">
                                    <a href="javascript:CopyToClipboard('{{$media->getUrl()}}');"
                                       data-bs-toggle="tooltip" data-bs-placement="top"
                                       data-bs-title="{{__('media.copy_to_clipboard')}}">
                                        <i class="fa-duotone fa-clipboard clipboard"></i>
                                    </a>
                                    <a data-fancybox="galeri" href="{{$media->getUrl()}}"
                                       title="{{$note->title}}" data-bs-toggle="tooltip" data-bs-placement="top"
                                       data-bs-title="@lang('media.show')">
                                        <i class="fa-duotone fa-magnifying-glass open"></i>
                                    </a>
                                    <a href="javascript:imageDelete('{{$media->id}}');"
                                       title="{{$note->title}}" data-bs-toggle="tooltip" data-bs-placement="top"
                                       data-bs-title="@lang('general.delete')">
                                        <i class="fa-duotone fa-trash delete"></i>
                                    </a>
                                </figure>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css"
          integrity="sha512-B0SXtacbXxcsbeU/+wPUJZ/cI8li66QGwowBgC1ZkbC+qPJO0oLm5+Inm20I9oYBVLU10jihQ+5uHXsEtXaJjQ==" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"
            integrity="sha512-OHypLu0g2YzbekzJgCyb3Smdm0PN5kxWA3FZ4OLeQI3ebuH+xtw+RmHbZ/8bvS5KE8k0C9WDLgQLhYlObFm/BQ==" crossorigin="anonymous"></script>

    <script>
        Fancybox.bind('[data-fancybox]', {
        });
        function imageDelete(media_id){
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
                            url: '{{route('admin.notes.media.delete', $note)}}',
                            type: 'post',
                            data: {
                                _token: '{{csrf_token()}}',
                                media_id: media_id
                            },
                            success: function (result) {
                                if(result.success){
                                    Swal.fire({
                                        icon: 'success',
                                        title: '@lang('post.delete_image_success_title')',
                                        text: '@lang('post.delete_image_success')',
                                        showConfirmButton: false,
                                        timer: 1500
                                    });
                                    $('#'+media_id).remove();
                                }
                                else{
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

        function CopyToClipboard(media_url){
            navigator.clipboard.writeText(media_url);
            toastr.success('@lang('media.url_copied')');
        }
    </script>
    <style>
        #media {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        #media li {
            float: left;
            margin: 5px;
        }
        figure.mediaPhoto.blue {
            background-color: #2472a4;
            border-radius: 10px;
        }
        figure.mediaPhoto {
            position: relative;
            text-align: center;
        }
        #media li img {
            width: 225px;
            height: 200px;
            border-radius: 10px;
            object-fit: cover;
            box-shadow: 0 0 5px #dedede;
            transition: all 1s;
        }
        figure.mediaPhoto:hover img, figure.mediaPhoto.hover img {
            opacity: 0.3;
            -webkit-filter: grayscale(100%);
            filter: grayscale(100%);
        }
        figure.mediaPhoto img {
            max-width: 100%;
            vertical-align: top;
        }
        figure.mediaPhoto * {
            -webkit-box-sizing: border-box;
            box-sizing: border-box;
            -webkit-transition: all 0.4s ease-in-out;
            transition: all 0.4s ease-in-out;
        }
        #media li img:hover {
            box-shadow: 0 0 5px #b1b1b1;
        }
        figure.mediaPhoto:hover i, figure.mediaPhoto.hover i {
            top: 50%;
            transition: all 500ms 100ms cubic-bezier(.175,.885,.32,1.275);
            opacity: 1;
        }
        figure.mediaPhoto i.open {
            left: 20%;
        }
        figure.mediaPhoto i.delete {
            left: 50%;
        }
        figure.mediaPhoto i.clipboard{
            left: 80%
        }
        figure.mediaPhoto i {
            position: absolute;
            top: 100%;
            border-radius: 50%;
            font-size: 34px;
            color: #094e9a;
            width: 60px;
            height: 60px;
            line-height: 60px;
            background: #fff;
            box-shadow: 0 0 5px rgba(0,0,0,.15);
            opacity: 0;
            -webkit-transform: translate(-50%,-50%);
            transform: translate(-50%,-50%);
            transition: all 300ms 0ms cubic-bezier(.6,-.28,.735,.045);
        }
        figure.mediaPhoto * {
            -webkit-box-sizing: border-box;
            box-sizing: border-box;
            -webkit-transition: all .4s ease-in-out;
            transition: all .4s ease-in-out;
        }
    </style>
@endsection
