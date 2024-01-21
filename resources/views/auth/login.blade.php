@extends('layouts.app')

@section('content')
    <corbado-auth project-id="{{config('corbado.project_id')}}" conditional="yes">
        <input name="username" id="corbado-username" required autocomplete="webauthn" aria-label=""/>
    </corbado-auth>
    <p class="mb-1">
        <a href="{{route('forgot-password')}}">@lang('user.forgot_password')</a>
    </p>
@endsection
@section('scripts')
	<script src="https://pro-1804292700719907030.auth.corbado.com/auth.js" integrity="sha512-3la1X8sJCsbv8Q/onIuacBoRKCSTzXMB4bF3nzioJustbhe60gBAvYpgjuh/Bpj6BaRXEp3dg5fFvLCXsbcW4A==" crossorigin="anonymous"></script>	
    <script src="{{config('app.url')}}/themes/panel/js/webauthn.min.js"></script>
    <script>
        $(document).ready(function(){
            $('#login_panel').submit(function(){
                $.ajax({
                    url: '{{route('login')}}',
                    type: 'post',
                    data: $(this).serialize(),
                    success: function (result) {
                        turnstile.reset();
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
                            }, 1500);
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
