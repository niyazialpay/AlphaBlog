@extends('panel.base')
@if($type == 'blogs')
    @section('title', __('post.blogs'))
@else
    @section('title', __('post.pages'))
@endif

@section('breadcrumb_link')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{route('admin.index')}}">@lang('dashboard.dashboard')</a></li>
        <li class="breadcrumb-item active">
            @if($type == 'blogs')
                @lang('post.blogs')
            @else
                @lang('post.pages')
            @endif
        </li>
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
            <div class="row">
                <div class="col-12">
                    <ul class="nav nav-pills border-bottom mb-3">
                        @foreach(app('languages') as $language)
                            <li class="nav-item">
                                <a class="nav-link @if($language->code==request()->get('language')) active @endif "
                                   href="{{route('admin.posts', $type)}}?tab=contents&amp;language={{$language->code}}">
                                    {{$language->name}}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <ul class="tab-menu">
                        <!-- menu tabs start here -->
                        <li class="active-tab">
                            <a href="{{route('admin.posts', $type)}}?tab=contents&amp;language={{request()->get('language')}}">
                                <i class="fa-light fa-folder-grid"></i> İçerikler</a>
                        </li>
                        <li>
                            <a href="{{route('admin.posts', $type)}}?tab=trashed&amp;language={{request()->get('language')}}">
                                <i class="fa-light fa-trash-list"></i> Silinenler</a>
                        </li>
                    </ul>
                    <div class="tab_container">
                        <div class="tab_content table-responsive" id="contents">
                            <table id="posts-table" class="table table-striped" aria-describedby="contents">
                                <thead>
                                <tr>
                                    <th scope="col">@lang('post.title')</th>
                                    @if($type == 'blogs')
                                    <th scope="col">@lang('post.category')</th>
                                    <th scope="col">@lang('post.views')</th>
                                    @endif
                                    <th scope="col" class="text-center">@lang('post.media')</th>
                                    <th scope="col" class="text-center">@lang('user.username')</th>
                                    <th scope="col" class="text-center">@lang('general.created_at')</th>
                                    <th scope="col" class="text-center">@lang('general.updated_at')</th>
                                    <th scope="col">@lang('general.actions')</th>
                                </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <th scope="col">@lang('post.title')</th>
                                    @if($type == 'blogs')
                                        <th scope="col">@lang('post.category')</th>
                                        <th scope="col">@lang('post.views')</th>
                                    @endif
                                    <th scope="col" class="text-center">@lang('post.media')</th>
                                    <th scope="col" class="text-center">@lang('user.username')</th>
                                    <th scope="col" class="text-center">@lang('general.created_at')</th>
                                    <th scope="col" class="text-center">@lang('general.updated_at')</th>
                                    <th scope="col">@lang('general.actions')</th>
                                </tr>
                                </tfoot>

                            </table>
                        </div>
                        <div class="tab_content table-responsive" id="trashed">
                            <table class="table table-striped" aria-describedby="trashed">
                                <thead>
                                <tr>
                                    <th scope="col">@lang('post.title')</th>
                                    <th scope="col">@lang('post.category')</th>
                                    <th scope="col">@lang('general.created_at')</th>
                                    <th scope="col">@lang('general.deleted_at')</th>
                                    <th scope="col">@lang('general.actions')</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($trashed as $post)
                                    <tr>
                                        <td>
                                            <a href="{{route('admin.post.edit', [$type, $post])}}">
                                                {{stripslashes($post->title)}}
                                            </a>
                                        </td>
                                        <td>
                                            @foreach($post->categories as $category)
                                                <span class="badge badge-primary">
                                                    <a href="{{route('admin.post.category', [$type, $category->id])}}"
                                                       class="text-white">
                                                        {{stripslashes($category->name)}}
                                                    </a>
                                                </span>
                                            @endforeach
                                        </td>
                                        <td>{{$post->created_at}}</td>
                                        <td>{{$post->deleted_at}}</td>
                                        <td>
                                            <a href="javascript:RestoreBlog('{{$post->id}}')"
                                               class="btn btn-sm btn-success mx-1 my-2"
                                               data-bs-toggle="tooltip" data-bs-placement="top"
                                               title="@lang('general.restore')">
                                                <i class="fas fa-trash-restore"></i>
                                            </a>
                                            <a href="javascript:DeleteBlog('{{$post->id}}', true)"
                                               class="btn btn-sm btn-danger mx-1 my-2"
                                               data-bs-toggle="tooltip" data-bs-placement="top"
                                               title="@lang('general.delete')">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" style="text-align: center">@lang('post.no_posts_found')</td>
                                    </tr>
                                @endforelse
                                </tbody>
                                <tfoot>
                                <tr>
                                    <th scope="col">@lang('post.title')</th>
                                    <th scope="col">@lang('post.category')</th>
                                    <th scope="col">@lang('general.created_at')</th>
                                    <th scope="col">@lang('general.deleted_at')</th>
                                    <th scope="col">@lang('general.actions')</th>
                                </tr>
                                </tfoot>
                            </table>
                            {{$trashed->withQueryString()->links()}}
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <style>
        .tab-menu {
            list-style: none;
            margin:0;
            padding:0;
            border-bottom: 1px solid #999;
        }
        .tab-menu li {
            display: inline-block;
            padding: 10px;
        }
        .active-tab {
            box-shadow: inset -3px 0 8px -5px #111, inset 3px 0 8px -5px #111;
        }
        .active-tab a {
        }
    </style>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
    <script>
        function DeleteBlog(id, force = false){
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
                        url: '{{route('admin.posts', $type)}}/'+id+'/delete' + (force ? '/permanent' : ''),
                        type: 'POST',
                        data: {
                            _token: '{{csrf_token()}}'
                        },
                        success: function () {
                            Swal.fire(
                                '@lang('general.deleted')',
                                '@lang('post.post.success_delete')',
                                'success'
                            );
                            window.location.reload();
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
        function RestoreBlog(id){
            Swal.fire({
                title: '@lang('general.are_you_sure')',
                text: "@lang('post.restore_sure')",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '@lang('general.restore_it')',
                cancelButtonText: '@lang('general.cancel')',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{route('admin.posts', ['type' => $type])}}/'+id+'/restore',
                        type: 'POST',
                        data: {
                            _token: '{{csrf_token()}}'
                        },
                        success: function () {
                            Swal.fire(
                                '@lang('general.restored')',
                                '@lang('')',
                                'success'
                            );
                            window.location.reload();
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
            });
        }
        $(document).ready(function(){
            @if(request()->get('tab') == 'trashed')
                $(".tab-menu li").removeClass("active-tab");
                $(".tab-menu li:nth-child(2)").addClass("active-tab");
                $(".tab_content").hide();
                $("#trashed").show();
            @else
                $(".tab-menu li").removeClass("active-tab");
                $(".tab-menu li:nth-child(1)").addClass("active-tab");
                $(".tab_content").hide();
                $("#contents").show();
            @endif


            $('#posts-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{!! $datatable_url !!}',
                responsive: true,
                columns: [
                    { data: 'title', name: 'title' },
                    @if($type == 'blogs')
                    { data: 'categories', name: 'categories' },
                    { data: 'views', name: 'views', className: "text-center"},
                    @endif
                    { data: 'media', name: 'media', className: "text-center", orderable: false, searchable: false },
                    { data: 'user', name: 'user', className: "text-center" },
                    { data: 'created_at', name: 'created_at', className: "text-center" },
                    { data: 'updated_at', name: 'updated_at', className: "text-center" },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                order: [[@if($type == 'blogs') 5 @else 3 @endif, 'desc']],
                pageLength: @if(session()->has('post_datatable_length')) {{session('post_datatable_length')}} @else 10 @endif,
                lengthMenu: [10, 25, 50, 75, 100],
                language: {
                    url: '{{config('app.url')}}/themes/panel/js/datatable/lang/{{session('language')}}.json'
                }
            });
        });
    </script>
@endsection
