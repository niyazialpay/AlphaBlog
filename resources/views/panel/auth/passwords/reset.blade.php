@extends('panel.auth.layouts.app')

@section('content')
    <form action="javascript:void(0)" id="reset_panel" method="post">
        <div class="input-group mb-3">
            <label for="email"></label>
            <input id="login" type="text"
                   class="form-control"
                   name="login" value="{{ old('username') ?: old('email') }}" required autofocus
                   placeholder="@lang('user.username_or_email')">

            <div class="input-group-append">
                <div class="input-group-text">
                    <i class=" @if(config('settings.fontawesome_pro')) fa-duotone @else fa-solid @endif fa-user"></i>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-12 d-flex justify-content-center">
                <x-turnstile />
            </div>
        </div>
        <div class="row">
            <div class="col-8">
                <div class="warning">

                </div>
            </div>
            @csrf
            @honeypot
            <!-- /.col -->
            <div class="col-4">
                <button type="submit" class="btn btn-primary btn-block">@lang('general.check')</button>
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
                    url: '{{route('forgot-password')}}',
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
