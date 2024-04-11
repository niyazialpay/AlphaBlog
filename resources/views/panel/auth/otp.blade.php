
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AdminLTE 3 | Lockscreen</title>

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
            <img src="https://www.gravatar.com/avatar/{{md5(strtolower(trim(auth()->user()->email)))}}?d=https://www.gravatar.com/avatar
&amp;s=128"  width="128" alt="{{auth()->user()->nickname}}" />
        </div>
        <!-- /.lockscreen-image -->

        <!-- lockscreen credentials (contains the form) -->
        <form class="lockscreen-credentials" id="otp-login" method="post" action="javascript:void(0)">
            <div class="input-group">
                @csrf
                <input type="password" name="code" class="form-control" placeholder="OTP">

                <div class="input-group-append">
                    <button type="button" class="btn">
                        <i class="fas fa-arrow-right text-muted"></i>
                    </button>
                </div>
            </div>
        </form>
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

<script>
    function notify_alert(message, type) {
        if (type === 'success') {
            Swal.fire({
                title: '@lang('general.success')!',
                text: message,
                icon: 'success',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location = '{{route('admin.index')}}'
                }
            })
        } else {
            Swal.fire({
                title: '@lang('general.error')!',
                text: message,
                icon: 'error',
                confirmButtonText: 'OK'
            })
        }
    }

    $(document).ready(function () {

        $('#otp-login').submit(function () {
            $.ajax({
                type: 'POST',
                url: '{{route('two-factor.verify')}}',
                data: $('#otp-login').serialize(),
                success: function (response) {
                    if (response.status === 'success') {
                        notify_alert(response.message, 'success');
                        window.location.href = '{{ route('admin.index') }}';
                    }
                    else{
                        notify_alert(response.message, 'error');
                    }
                },
                error: function(response){
                    console.log(response)
                    notify_alert('Something went wrong, try again!', 'error');
                }
            });
        });
    });
</script>

</body>
</html>


