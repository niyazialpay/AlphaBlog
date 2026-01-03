<form class="lockscreen-credentials" id="otp-login" method="post" action="javascript:otpSubmit()">
    <div class="input-group">
        @csrf
        @honeypot
        <input type="password" name="code" class="form-control" placeholder="OTP">
        <div class="input-group-append">
            <button type="submit" class="btn">
                <i class="fas fa-arrow-right text-muted"></i>
            </button>
        </div>
    </div>
</form>
