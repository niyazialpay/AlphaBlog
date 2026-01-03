<div class="tab-pane" id="webauthn-tab">
    <div class="row">
        <div class="col-12" id="webauthn_list">

        </div>
        @if(auth()->id() == $user->id)
            <form id="register-form" method="post" action="javascript:void(0);">
                @csrf
                <button type="submit" class="btn btn-primary">@lang('webauthn.register_device')</button>
            </form>
        @endif
    </div>
</div>
