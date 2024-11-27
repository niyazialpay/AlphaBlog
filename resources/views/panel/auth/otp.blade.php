
<!DOCTYPE html>
<html lang="{{session('language')}}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{config('app.name')}} | @lang('user.lockscreen')</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="{{config('app.url')}}/themes/fontawesome/css/all.min.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65"
          crossorigin="anonymous">

    <!-- Theme style -->
    <link rel="stylesheet" href="{{config('app.url')}}/themes/panel/css/adminlte.min.css">
    <!-- Google Font: Source Sans Pro -->
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js"></script>
    <link rel="manifest" href="{{route('manifest.panel')}}">
</head>
<body class="hold-transition lockscreen">
<!-- Automatic element centering -->
<div class="lockscreen-wrapper">
    <div class="lockscreen-logo">
        <a href="{{route('admin.index')}}">{{config('app.name')}}</a>
    </div>
    <!-- User name -->
    <div class="lockscreen-name">{{auth()->user()->nickname}}</div>

    <!-- START LOCK SCREEN ITEM -->
    <div class="lockscreen-item">
        <!-- lockscreen image -->
        <div class="lockscreen-image">
            <img src="{{auth()->user()->profile_image}}&s=128"  width="128" alt="{{auth()->user()->nickname}}" />
        </div>
        <!-- /.lockscreen-image -->

        <!-- lockscreen credentials (contains the form) -->

        @if(auth()->user()->webauthn)
            @include('panel.auth.partials.webauthn')
        @elseif(auth()->user()->otp)
            @include('panel.auth.partials.otp')
        @endif

        <!-- /.lockscreen credentials -->

    </div>


    <div class="lockscreen-footer text-center">
        <strong>
            Copyright &copy; {{date('Y')}} <a href="https://niyazi.net">Niyazi.Net</a>.
        </strong> All rights reserved.
    </div>
</div>
<!-- /.center -->

<script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
        crossorigin="anonymous"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4"
        crossorigin="anonymous"></script>

<!-- AdminLTE App -->
<script src="{{config('app.url')}}/themes/panel/js/adminlte.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="https://cdn.jsdelivr.net/npm/@laragear/webpass@2/dist/webpass.js"></script>

<script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">

<script>
    function notify_alert(message, type, log) {
        if(log.success){
            if (type === 'success') {
                toastr.success(message, '@lang('general.success')', {
                    closeButton: true,
                    tapToDismiss: false
                });
                window.location.reload();
            } else {
                toastr.error(message, '@lang('general.error')!', {
                    closeButton: true,
                    tapToDismiss: false
                });
            }
        }
        else{
            toastr.error(log.error.message, '@lang('general.error')!', {
                closeButton: true,
                tapToDismiss: false
            });
        }
        console.log(log);
    }

    const webauthnLogin = async event => {
        const webpass = Webpass.assert({
            path: "{{route('webauthn.login.options')}}",
            body: {
                username: '{{Auth::user()->username}}'
            }
        }, "{{route('webauthn.login')}}")
            .then(response => notify_alert('{{__('webauthn.verification_success')}}', 'success', response))
            .catch(error => notify_alert('{{__('webauthn.verification_failed')}}', 'error', error))
    }
    document.getElementById('webauthn-login').addEventListener('submit', webauthnLogin);

    function otpSubmit(){
        $.ajax({
            type: 'POST',
            url: '{{route('two-factor.verify')}}',
            data: $('#otp-login').serialize(),
            success: function (response) {
                if (response.status === 'success') {
                    toastr.success(response.message, '@lang('general.success')', {
                        closeButton: true,
                        tapToDismiss: false
                    });
                    window.location.href = '{{ route('admin.index') }}';
                }
                else{
                    toastr.error(response.message, '@lang('general.error')!', {
                        closeButton: true,
                        tapToDismiss: false
                    });
                }
            },
            error: function(response){
                console.log(response)
                toastr.error('@lang('Something went wrong, try again!')', '@lang('general.error')!', {
                    closeButton: true,
                    tapToDismiss: false
                });
            }
        });
    }

    $(document).ready(function () {

        $('#otp-login').submit(function () {
            otpSubmit();
        });
    });
</script>

</body>
</html>


