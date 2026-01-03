@extends('panel.base')
@section('title', __('user.users'))
@section('breadcrumb_link')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{route('admin.index')}}">@lang('dashboard.dashboard')</a></li>
        <li class="breadcrumb-item active">
            <a href="{{route('admin.users')}}">
                @lang('user.users')
            </a>
        </li>
        <li class="breadcrumb-item active">
            @lang('general.add-edit')
        </li>
    </ol>
@endsection
@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                @lang('user.new_user')
            </h3>
        </div>
        <div class="card-body">
            <form class="row" id="userSaveForm" method="post" action="javascript:void(0);">
                <div class="col-12 mb-3">
                    <label for="nickname">@lang('profile.nickname.text')</label>
                    <input type="text" class="form-control" id="nickname" name="nickname"
                           placeholder="@lang('profile.nickname.placeholder')">
                </div>
                <div class="col-12 mb-3">
                    <label for="name">@lang('profile.name.text')</label>
                    <input type="text" class="form-control" id="name" name="name"
                           placeholder="@lang('profile.name.placeholder')">
                </div>
                <div class="col-12 mb-3">
                    <label for="surname">@lang('profile.surname.text')</label>
                    <input type="text" class="form-control" id="surname" name="surname"
                           placeholder="@lang('profile.surname.placeholder')">
                </div>
                <div class="col-12 mb-3">
                    <label for="username">@lang('user.username')</label>
                    <input type="text" class="form-control" id="username" name="username"
                           placeholder="@lang('user.username')">
                </div>
                <div class="col-12 mb-3">
                    <label for="email">@lang('profile.email.text')</label>
                    <input type="email" class="form-control" id="email" name="email"
                           placeholder="@lang('profile.email.placeholder')">
                </div>
                <div class="col-12 mb-3">
                    <label for="password">@lang('user.password')</label>
                    <input type="password" class="form-control" id="password" name="password"
                           placeholder="@lang('user.password')">
                </div>
                <div class="col-12 mb-3">
                    <label for="password_confirmation">@lang('user.password_confirmation')</label>
                    <input type="password" class="form-control" id="password_confirmation"
                           name="password_confirmation"
                           placeholder="@lang('user.password_confirmation')">
                </div>
                <div class="col-12 mb-3">
                    <label for="role">@lang('user.role')</label>
                    <select name="role" id="role" class="form-control">
                        <option value="admin">@lang('user.role_admin')</option>
                        <option value="author">@lang('user.role_author')</option>
                        <option value="user" selected>@lang('user.role_user')</option>
                    </select>
                </div>
                @csrf
                <div class="col-12-mb-3">
                    <button type="submit" class="btn btn-primary">
                        @lang('general.save')
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $('#userSaveForm').submit(function (e) {
            e.preventDefault();
            let formData = new FormData(this);
            $.ajax({
                type: 'POST',
                url: '{{route('admin.user.create')}}',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function (data) {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: data.title,
                            text: data.message,
                            showConfirmButton: false,
                            timer: 1500
                        });
                        setTimeout(function () {
                            window.location.href = '{{route('admin.users')}}';
                        }, 1000);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: data.title,
                            text: data.message,
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }
                },
                error: function (data) {
                    let errors = data.responseJSON.errors;
                    let error_message = '';
                    $.each(errors, function (key, value) {
                        error_message += value + '<br>';
                    });
                    Swal.fire({
                        icon: 'error',
                        title: data.responseJSON.title,
                        html: error_message,
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
            });
        });
    </script>
@endsection
