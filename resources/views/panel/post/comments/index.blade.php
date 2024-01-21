@extends('panel.base')
@section('title',__('comments.comments'))
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
        <li class="breadcrumb-item active">@lang('comments.comments')</li>
    </ol>
@endsection
@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">@lang('comments.comments')</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <ul class="tab-menu">
                        <!-- menu tabs start here -->
                        <li @if(request()->get('tab')!='trashed') class="active-tab" @endif>
                            <a href="{{route('admin.post.comments')}}?tab=comments"><i class="fa-light fa-folder-grid"></i> @lang('comments.comments')</a>
                        </li>
                        <li @if(request()->get('tab')=='trashed') class="active-tab" @endif>
                            <a href="{{route('admin.post.comments')}}?tab=trashed"><i class="fa-light fa-trash-list"></i> @lang('general.trashed')</a>
                        </li>
                    </ul>
                    <div class="tab_container table-responsive">
                        <div class="tab_content" id="contents">
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
                                    <th scope="col" style="width: 250px;">@lang('general.author')</th>
                                    <th scope="col">@lang('comments.comment')</th>
                                    <th scope="col">@lang('post.title')</th>
                                    <th scope="col" style="width: 250px;">@lang('general.created_at')</th>
                                    <th scope="col" style="width: 250px;">@lang('general.actions')</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($comments as $comment)
                                    <tr>
                                        <td>
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
                                            <small>{{$comment->ip_address}}</small>
                                        </td>
                                        <td>
                                            {{$comment->comment}}
                                        </td>
                                        <td>
                                            @if($comment->post)
                                                <a href="{{route('admin.post.edit', ['type' => 'blogs', $comment->post->id])}}">{{$comment->post->title}}</a>
                                            @endif
                                        </td>
                                        <td>{{dateformat($comment->created_at, 'd M. Y D. H:i:s')}}</td>
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
                                                <a href="javascript:NewComment('{{$comment->post_id}}', '{{$comment->post->title}}')"
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
                                        <td colspan="5" style="text-align: center">@lang('comments.no_comments_found')</td>
                                    </tr>
                                @endforelse
                                </tbody>
                                <tfoot>
                                <tr>
                                    <th scope="col">@lang('general.author')</th>
                                    <th scope="col">@lang('comments.comment')</th>
                                    <th scope="col">@lang('post.title')</th>
                                    <th scope="col">@lang('general.created_at')</th>
                                    <th scope="col">@lang('general.actions')</th>
                                </tr>
                                </tfoot>

                            </table>
                            {{$comments->withQueryString()->links()}}
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    @include('panel.post.comments.modal')
@endsection

@section('script')
    @include('panel.post.comments.js')
@endsection
