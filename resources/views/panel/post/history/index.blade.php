@extends('panel.base')
@section('title', __('post.history'))

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
        <li class="breadcrumb-item active">@lang('post.history')</li>
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
                    <div class="tab_container">
                        <div class="tab_content table-responsive" id="contents">
                            <div class="my-3">
                                <form method="get">
                                    <div class="input-group mb-3">
                                        <input class="form-control form-control-navbar" type="search" name="search" placeholder="@lang('general.search')" aria-label="@lang('general.search')" value="{{GetPost(request()->search)}}">
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
                                    <th scope="col" class="text-center">@lang('general.created_at')</th>
                                    <th scope="col" class="text-center">@lang('general.updated_at')</th>
                                    <th scope="col">@lang('general.actions')</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($posts->history as $post)
                                    <tr>
                                        <td>
                                            <a href="{{route('admin.post.history.show', [$type, $posts, $post])}}">
                                                {{stripslashes($post->title)}}
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            {{dateformat($post->created_at, format: 'd M. Y D. H:i:s', timezone: config('app.timezone'), locale: session('language'))}}
                                        </td>
                                        <td class="text-center">
                                            {{dateformat($post->updated_at, format: 'd M. Y D. H:i:s', timezone: config('app.timezone'), locale: session('language'))}}
                                        </td>
                                        <td>
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
                                        <td colspan="4"  style="text-align: center">
                                            @lang('post.no_posts_found')
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                                <tfoot>
                                <tr>
                                    <th scope="col">@lang('post.title')</th>
                                    <th scope="col" class="text-center">@lang('general.created_at')</th>
                                    <th scope="col" class="text-center">@lang('general.updated_at')</th>
                                    <th scope="col">@lang('general.actions')</th>
                                </tr>
                                </tfoot>

                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
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
                            url: '{{route('admin.post.history', [$type, $posts])}}/'+id+'/delete',
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
    </script>
@endsection
