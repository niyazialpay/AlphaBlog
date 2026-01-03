<div class="modal fade" id="commentModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog  modal-dialog-centered modal-lg">
        <form class="modal-content" id="commentEditForm" method="post" action="javascript:void(0)">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="post_title">@lang('post.title')</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 mb-3" id="comment_name_input">
                        <input type="text" name="name" class="form-control" id="comment_name"
                               placeholder="@lang('user.name') @lang('user.surname')"
                               aria-label="@lang('user.name') @lang('user.surname')">
                    </div>
                    <div class="col-12 mb-3" id="comment_email_input">
                        <input type="email" name="email" class="form-control" id="comment_email"
                               placeholder="@lang('user.email')"
                               aria-label="@lang('user.email')">
                    </div>
                    <div class="col-12 mb-3" id="user_select_input">
                        <select name="user_id" class="form-control" id="user" aria-label="user">
                            <option value="">@lang('comments.select_user')</option>
                            @foreach($users as $user)
                                <option value="{{$user->id}}">{{$user->nickname}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 mb-3">
                        <textarea name="comment" class="form-control" id="comment"
                                  placeholder="@lang('comments.comment')" aria-label="@lang('comments.comment')"></textarea>
                    </div>
                    <div class="col-12 mb-3">
                        <label for="created_date">@lang('general.date')</label>
                        <input type="datetime-local" name="created_date" class="form-control" id="created_date"
                               placeholder="@lang('general.date')" aria-label="@lang('general.date')">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                @csrf
                <input type="hidden" name="post_id" id="post_id" value="">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('general.cancel')</button>
                <button type="submit" class="btn btn-primary">@lang('general.save')</button>
            </div>
        </form>
    </div>
</div>
