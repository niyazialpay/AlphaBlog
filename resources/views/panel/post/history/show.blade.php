@extends('panel.base')
@section('title', __('post.history').' - '.$history->created_at)

@section('breadcrumb_link')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{route('admin.index')}}">@lang('dashboard.dashboard')</a></li>
        <li class="breadcrumb-item">
            <a href="{{route('admin.posts', $type)}}">
                @if($type == 'blogs')
                    @lang('post.blogs')
                @else
                    @lang('post.pages')
                @endif
            </a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{route('admin.post.edit', [$type, $posts])}}">
                {{$posts->title}}
            </a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{route('admin.post.history', [$type, $posts])}}">
                @lang('post.history')
            </a>
        </li>
        <li class="breadcrumb-item active">{{$history->created_at}}</li>
    </ol>
@endsection
@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                @lang('post.history')
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                {!! $title !!}
                                <hr>
                                {!! $slug !!}
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    {!! stripslashesNull($content) !!}
                                </div>
                            </div>
                        </div>
                        <form class="card-footer" method="post" action="javascript:void(0)">
                            @csrf
                            <button class="btn btn-sm btn-primary text-white" type="submit">
                                <i class="fa-duotone fa-clock-rotate-left"></i> @lang('post.revert')
                            </button>
                            <a class="btn btn-sm btn-danger text-white"
                               data-bs-toggle="tooltip"
                               data-bs-placement="top"
                               data-bs-title="@lang('general.delete')" href="javascript:DeleteBlog('{{$history->id}}')">
                                <i class="fa fa-trash"></i>
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <style>
        table.diff tbody tr td:nth-child(2) {
            width: 4%
        }
        table.diff {
            border-collapse: separate;
            border-spacing: 2px;
            table-layout: fixed;
            width: 100%;
            white-space: pre-wrap;
        }
        table.diff col.content {
            width: auto;
        }
        table.diff col.ltype {
            width: 30px;
        }
        table.diff tr {
            background-color: transparent;
        }
        table.diff td, table.diff th {
            font-family: Consolas, Monaco, monospace;
            font-size: 14px;
            line-height: 1.618;
            padding: .5em;
            vertical-align: top;
            word-wrap: break-word;
        }
        table.diff td h1, table.diff td h2, table.diff td h3, table.diff td h4, table.diff td h5, table.diff td h6 {
            margin: 0;
        }
        table.diff .diff-addedline ins, table.diff .diff-deletedline del {
            text-decoration: none;
        }
        table.diff .diff-deletedline {
            background-color: #ffe9e9;
        }
        table.diff .diff-deletedline del {
            background-color: #faa;
        }
        table.diff .diff-addedline {
            background-color: #e9ffe9;
        }
        table.diff .diff-addedline ins {
            background-color: #afa;
        }

        .dark-mode table.diff .diff-deletedline,
        .dark-mode table.diff .diff-addedline{
            color: #000;
        }

    </style>
    <script>
        function DeleteBlog(id){
            Swal.fire({
                title: '@lang('general.are_you_sure')',
                text: "@lang('general.you_wont_be_able_to_revert_this')",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '@lang('general.delete_confirm_yes')',
                cancelButtonText: '@lang('general.delete_confirm_no')',
            }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{route('admin.post.history', [$type, $posts])}}/' + id + '/delete',
                            type: 'POST',
                            data: {
                                _token: '{{csrf_token()}}'
                            },
                            success: function () {
                                Swal.fire(
                                    '@lang('general.deleted')',
                                    '@lang('post.post.success_delete')',
                                    'success'
                                )
                                window.location = '{{route('admin.post.edit', [$type, $posts])}}'
                            },
                            error: function (xhr, ajaxOptions, thrownError) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: xhr.responseJSON.message
                                });
                            }
                        });
                    }
                }
            )
        }
        $(document).ready(function () {
            $('form').submit(function (e) {
                e.preventDefault();
                Swal.fire({
                    title: '@lang('general.are_you_sure')',
                    text: "@lang('post.revert_sure')",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: '@lang('general.yes')',
                    cancelButtonText: '@lang('general.no')',
                }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: '{{route('admin.post.history.revert', [$type, $posts, $history])}}',
                                type: 'POST',
                                data: {
                                    _token: '{{csrf_token()}}'
                                },
                                success: function () {
                                    Swal.fire(
                                        '@lang('post.reverted')',
                                        '@lang('post.revert_success')',
                                        'success'
                                    )
                                    window.location = '{{route('admin.post.edit', [$type, $posts])}}'
                                },
                                error: function (xhr, ajaxOptions, thrownError) {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Oops...',
                                        text: xhr.responseJSON.message
                                    });
                                }
                            });
                        }
                    }
                )
            })
        })
    </script>
@endsection
