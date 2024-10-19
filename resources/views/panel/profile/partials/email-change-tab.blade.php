<div class="tab-pane" id="email-change-tab">
    <form class="row" action="javascript:void(0)" id="emailChangeForm">
        @csrf
        <div class="col-md-12 mb-3">
            <label for="email" class="form-label">
                @lang('profile.email.text')
            </label>
            @php
            if($user->id == auth()->id()){
                $email = auth()->user()->email;
            }
            else{
                $email = $user->email;
            }
            @endphp
            <input type="email" class="form-control"
                   id="email" name="email"
                   placeholder="@lang('profile.email.placeholder')"
                   value="{{$email}}">
        </div>

        <div class="col-12 mb-3">
            <button type="submit" class="btn btn-primary">@lang('general.save')</button>
        </div>
    </form>
</div>
