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
                            <div class="my-3">
                                <form method="post" id="searchForm" action="javascript:void(0)">
                                    <div class="input-group mb-3">
                                        <input class="form-control form-control-navbar" type="search" name="search"
                                               placeholder="@lang('general.search')" aria-label="@lang('general.search')"
                                               value="{{GetPost(request()->search)}}">
                                        <div class="input-group-append">
                                            <button class="btn btn-navbar search-button" type="submit">
                                                <i class="fa-duotone fa-magnifying-glass"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <table class="table table-striped" aria-describedby="contents">
                                <thead>
                                <tr>
                                    <th scope="col">@lang('post.title')</th>
                                    @if($type == 'blogs')
                                    <th scope="col">@lang('post.category')</th>
                                    @endif
                                    <th scope="col" class="text-center">@lang('post.media')</th>
                                    <th scope="col" class="text-center">@lang('user.username')</th>
                                    <th scope="col" class="text-center">@lang('general.created_at')</th>
                                    <th scope="col" class="text-center">@lang('general.updated_at')</th>
                                    <th scope="col">@lang('general.actions')</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($posts as $post)
                                    <tr>
                                        <td>
                                            <a href="{{route('admin.post.edit', [$type, $post])}}">
                                                {{stripslashes($post->title)}} @if(!$post->is_published)
                                                    <em>(@lang('post.draft'))</em> @endif
                                            </a>
                                        </td>
                                        @if($type == 'blogs')
                                        <td>
                                            @foreach($post->categories as $category)
                                                <span class="badge badge-primary">
                                                    <a href="{{route('admin.post.category', [
                                                            $type,
                                                            $category->id,
                                                            'language' => request()->get('language')
                                                        ])}}"
                                                       class="text-white">
                                                        {{stripslashes($category->name)}}
                                                    </a>
                                                </span>
                                            @endforeach
                                        </td>
                                        @endif
                                        <td class="text-center">
                                            <a href="{{route('admin.post.media', [$type, $post])}}"
                                               data-bs-toggle="tooltip" data-bs-placement="right"
                                               data-bs-title="@lang('post.media')"
                                               class="h1">
                                                <i class="fa-duotone fa-images"></i>
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            {{$post->user->username}}
                                        </td>
                                        <td class="text-center">
                                            {{dateformat($post->created_at, format: 'd M. Y D. H:i:s', timezone: config('app.timezone'), locale: session('language'))}}
                                        </td>
                                        <td class="text-center">
                                            {{dateformat($post->updated_at, format: 'd M. Y D. H:i:s', timezone: config('app.timezone'), locale: session('language'))}}
                                        </td>
                                        <td>
                                            <a href="{{route('admin.post.edit', [$type, $post])}}"
                                               class="btn btn-sm btn-primary mx-1 my-2"
                                               data-bs-toggle="tooltip" data-bs-placement="top"
                                               title="@lang('general.edit')">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <a href="javascript:DeleteBlog('{{$post->id}}')"
                                               class="btn btn-sm btn-danger mx-1 my-2"
                                               data-bs-toggle="tooltip" data-bs-placement="top"
                                               title="@lang('general.delete')">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td @if($type == 'blogs') colspan="7" @else colspan="6" @endif style="text-align: center">
                                            @lang('post.no_posts_found')
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                                <tfoot>
                                <tr>
                                    <th scope="col">@lang('post.title')</th>
                                    @if($type == 'blogs')
                                    <th scope="col">@lang('post.category')</th>
                                    @endif
                                    <th scope="col" class="text-center">@lang('post.media')</th>
                                    <th scope="col" class="text-center">@lang('user.username')</th>
                                    <th scope="col" class="text-center">@lang('general.created_at')</th>
                                    <th scope="col" class="text-center">@lang('general.updated_at')</th>
                                    <th scope="col">@lang('general.actions')</th>
                                </tr>
                                </tfoot>

                            </table>
                            {{$posts->withQueryString()->links()}}
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
            background: #eee;
            border-bottom: 1px solid #999;
        }
        .tab-menu li {
            display: inline-block;
            padding: 10px;
        }
        .active-tab {
            background: #999;
            box-shadow: inset -3px 0 8px -5px #111, inset 3px 0 8px -5px #111;
        }
        .active-tab a {
            color: #fff;
        }
    </style>
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
                            )
                            window.location.reload()
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
                            )
                            window.location.reload()
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
            );
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

            $('#searchForm').submit(function(){
                let search = $(this).find('input[name="search"]').val();
                window.location.href = '{{route('admin.posts', $type)}}?tab={{request()->get('tab')}}&language={{request()->get('language')}}&search='+search;
            });
        });
    </script>
@endsection
