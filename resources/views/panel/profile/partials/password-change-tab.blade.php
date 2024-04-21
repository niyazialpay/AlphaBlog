<div class="tab-pane active" id="password-change-tab">
    <form class="row" action="javascript:void(0)" id="passwordChangeForm">
        @if(!request()->route()->parameter('user'))
            <div class="col-12 mb-3">
                <label for="old_password" class="form-label">
                    @lang('profile.old_password.text')
                </label>
                <input type="password" class="form-control"
                       id="old_password" name="old_password"
                       placeholder="@lang('profile.old_password.placeholder')">
            </div>
        @endif
        <div class="col-12 mb-3">
            <label for="password" class="form-label">
                @lang('profile.new_password.text')
            </label>
            <input type="password" class="form-control"
                   id="password" name="password"
                   placeholder="@lang('profile.new_password_confirmation.placeholder')">
        </div>
        <div class="col-12 mb-3">
            <label for="password_confirmation" class="form-label">
                @lang('profile.new_password_confirmation.text')
            </label>
            <input type="password" class="form-control"
                   id="password_confirmation" name="password_confirmation"
                   placeholder="@lang('profile.new_password_confirmation.placeholder')">
        </div>
        <div class="col-12 mb-3">
            <button type="submit" class="btn btn-primary">@lang('general.save')</button>
        </div>
        @csrf
    </form>
</div>
