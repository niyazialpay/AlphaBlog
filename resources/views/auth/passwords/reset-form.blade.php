@extends('layouts.app')

@section('content')
    <form action="javascript:void(0)" id="reset_panel" method="post">
        <div class="input-group mb-3">
            <label for="password"></label>
            <input id="password" type="password" class="form-control"
                   name="password" required autocomplete="current-password" placeholder="@lang('user.password')">

            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-envelope"></span>
                </div>
            </div>
        </div>
        <div class="input-group mb-3">
            <label for="password_confirmation"></label>
            <input id="password_confirmation" type="password" class="form-control"
                   name="password_confirmation" required autocomplete="current-password" placeholder="@lang('user.password_confirmation')">
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-lock"></span>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-12 mb-3">
                <x-turnstile/>
            </div>
        </div>
        <div class="row">
            @csrf
            @honeypot
            <input type="hidden" name="token" value="{{request()->route()->parameter('token')}}">
            <input type="hidden" name="email" value="{{request()->get('email')}}">
            <!-- /.col -->
            <div class="col-12">
                <button type="submit" class="btn btn-primary btn-block">@lang('user.change_password')</button>
            </div>
            <!-- /.col -->
        </div>
    </form>
@endsection
@section('scripts')
    <script>
        $(document).ready(function(){
            $('#reset_panel').submit(function(){
                $.ajax({
                    url: '{{route('password.update')}}',
                    type: 'post',
                    data: $(this).serialize(),
                    success: function (result) {
                        turnstile.reset();
                        if(result.status){
                            Swal.fire({
                                icon: 'success',
                                //title: '@lang('user.login_request.success_title')',
                                text: result.message,
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
                                //title: '@lang('user.login_request.error_title')',
                                text: result.message,
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
                            //title: '@lang('user.login_request.error_title')',
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
