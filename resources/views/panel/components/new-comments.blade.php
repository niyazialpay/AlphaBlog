<a class="nav-link" data-bs-toggle="dropdown"
   href="javascript:void(0);" id="top-comment-button"
   data-bs-placement="left"
   title="@lang('comments.new_comments')">

    <i class="fa-duotone fa-comments"></i>
    @if($newComments->count()>0)
        <span class="badge badge-danger navbar-badge">{{$newComments->count()}}</span>
    @endif
</a>
<div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
    @forelse($newComments as $comment)
        <a href="{{route('admin.post.edit', ['type' => 'blogs', 'post' => $comment->post_id])}}#comment-{{$comment->_id}}"
           class="dropdown-item" title="@lang('post.read_more')"
           data-bs-toggle="tooltip" data-bs-placement="top">
            <!-- Message Start -->
            <div class="media">
                @if($comment->user_id)
                    <img
                        src="https://www.gravatar.com/avatar/{{md5(strtolower(trim($comment->user->email)))}}?s=50"
                        class="img-size-50 mr-3 img-circle" alt="{{$comment->user->nickname}}">

                @else
                    <img
                        src="https://www.gravatar.com/avatar/{{md5(strtolower(trim($comment->email)))}}?s=50"
                        class="img-size-50 mr-3 img-circle" alt="{{$comment->name}}">
                @endif
                <div class="media-body">
                    <h3 class="dropdown-item-title">
                        @if($comment->user_id)
                            {{$comment->user->nickname}}
                        @else
                            {{$comment->name}}
                        @endif
                            <span class="float-right text-sm text-danger">
                                <i class="fa-duotone fa-book-open" title="@lang('post.read_more')" data-bs-toggle="tooltip" data-bs-placement="left"></i>
                            </span>
                    </h3>
                    <p class="text-sm">{{substr($comment->comment, 0,50)}}...</p>
                    <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> {{dateformat($comment->created_at, diff_for_humans: true)}}</p>
                </div>
            </div>
            <!-- Message End -->
        </a>
        <div class="dropdown-divider"></div>
    @empty
        <a href="javascript:void(0);" class="dropdown-item dropdown-footer">@lang('comments.no_new_comments')</a>
        <div class="dropdown-divider"></div>
    @endforelse
    <a href="{{route('admin.post.comments')}}" class="dropdown-item dropdown-footer">@lang('comments.all_comments')</a>
</div>
