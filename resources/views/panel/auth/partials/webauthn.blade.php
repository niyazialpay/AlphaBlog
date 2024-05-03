<form class="lockscreen-credentials" id="webauthn-login" method="post" action="javascript:void(0)">
    @csrf
    <button type="submit" id="webauthn_button"
            class="btn btn-light" style="height: 35px; margin-left:15px">
        @lang('webauthn.login_with_device')
        <i class="fa-duotone fa-usb-drive ms-2"></i>
    </button>
</form>
