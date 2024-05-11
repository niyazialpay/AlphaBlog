@extends('panel.auth.layouts.app')
@section('content')
    <form method="post" action="javascript:void(0)" id="first_step">
        <div class="input-group mb-3">
            <input type="text" name="login" class="form-control"
                   required
                   placeholder="@lang('user.username_or_email')"
                   id="username"
                   aria-label="username">
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-envelope"></span>
                </div>
            </div>
        </div>
        <div class="row">
            @honeypot
            @csrf
            <div class="col-8">
            </div>
            <!-- /.col -->
            <div class="col-4">
                <button type="submit" class="btn btn-primary btn-block">@lang('user.login')</button>
            </div>
            <!-- /.col -->
        </div>
    </form>

    <form action="javascript:void(0)" id="login_panel" method="post" style="display: none">
        <div class="input-group mb-3">
            <input type="text" name="login" class="form-control"
                   id="login_username"
                   placeholder="@lang('user.username_or_email')"
                   aria-label="login">
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-envelope"></span>
                </div>
            </div>
        </div>
        <div class="input-group mb-3 hidden-item">
            <input type="password" name="password" class="form-control"
                   placeholder="@lang('user.password')" aria-label="password">
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-lock"></span>
                </div>
            </div>
        </div>
        <div class="row">
            <x-turnstile />
            <div class="col-8">
                <div class="icheck-primary">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">
                        @lang('user.remember_me')
                    </label>
                </div>
            </div>
            @honeypot
            @csrf
            <!-- /.col -->
            <div class="col-4">
                <button type="submit" class="btn btn-primary btn-block">@lang('user.login')</button>
            </div>
            <!-- /.col -->
        </div>
    </form>

    <p class="mb-1 hidden-item" style="display: none">
        <a href="{{route('forgot-password')}}">@lang('user.forgot_password')</a>
    </p>

@endsection
@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/@laragear/webpass@2/dist/webpass.js"></script>

    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">

    <script>
        function notify_alert(message, type, log, reload=false) {
            if(log.success){
                if (type === 'success') {
                    toastr.success(message, 'Success!', {
                        closeButton: true,
                        tapToDismiss: false
                    });
                    if(reload){
                        setTimeout(function(){
                            location.reload();
                        }, 1000);
                    }
                    else{
                        window.location.href = '{{route('admin.index')}}';
                    }
                } else {
                    toastr.error(message, 'Error!', {
                        closeButton: true,
                        tapToDismiss: false
                    });
                }
            }
            else{
                toastr.error(log.error.message, 'Error!', {
                    closeButton: true,
                    tapToDismiss: false
                });
            }
            console.log(log);
        }

        $(document).ready(function(){
            $('#first_step').submit(function(){
                $.ajax({
                    url: "{{route('login.first_step')}}",
                    type: 'post',
                    data: $(this).serialize(),
                    success: function (result){
                        if(result.status && result.webauthn){
                            const webauthnLogin = async event => {
                                const webpass = Webpass.assert({
                                    path: "{{route('webauthn.login.options')}}",
                                    body: {
                                        username: result.username,
                                        email: result.email
                                    }
                                }, "{{route('webauthn.login')}}")
                                    .then(response => notify_alert('{{__('Verification completed successfully!')}}', 'success', response))
                                    .catch(error => notify_alert('{{__('Something went wrong, try again!')}}', 'error', error))
                            }
                            webauthnLogin();
                        }
                        else{
                            $('#first_step').hide();
                            $('#login_username').val($('#username').val());
                            $('#login_panel').show();
                            $('.hidden-item').show();
                        }
                    }
                });
            });

            $('#login_panel').submit(function(){
                $.ajax({
                    url: "{{route('login')}}",
                    type: 'post',
                    data: $(this).serialize(),
                    success: function (result) {
                        if(result.status){
                            Swal.fire({
                                icon: 'success',
                                title: '@lang('user.login_request.success_title')',
                                text: '@lang('user.login_request.success')',
                                showConfirmButton: false,
                                timer: 1500
                            });
                            setTimeout(function(){
                                window.location.href = '{{route('admin.index')}}';
                            }, 1000);
                        }
                        else{
                            Swal.fire({
                                icon: 'warning',
                                title: '@lang('user.login_request.error_title')',
                                text: '@lang('user.login_request.error')',
                                showConfirmButton: false,
                                //timer: 1500
                            });
                        }
                    },
                    error: function (xhr) {
                        console.log(xhr);
                        $('#login_panel').trigger("reset");
                        $("#username").focus();
                        Swal.fire({
                            icon: 'warning',
                            title: '@lang('user.login_request.error_title')',
                            text: xhr.responseJSON.message,
                            showConfirmButton: false,
                            //timer: 1500
                        });
                    }
                });
            })
        });
    </script>
@endsection
