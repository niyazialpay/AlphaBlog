@extends('panel.base')
@section('title',__('user.profile'))
@section('breadcrumb_link')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{route('admin.index')}}">@lang('dashboard.dashboard')</a></li>
        <li class="breadcrumb-item active">@lang('user.profile')</li>
    </ol>
@endsection
@section('content')
    <div class="card">
        <div class="card-header d-flex">
            <h3 class="card-title">@lang('user.profile')</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">

                    <!-- Profile Image -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">@lang('profile.about-me')</h3>
                        </div>
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <img class="profile-user-img img-fluid img-circle" src="https://www.gravatar.com/avatar/{{md5(strtolower(trim(auth()->user()->email)))}}?s=128" alt="{{auth()->user()->nickname}}">
                            </div>

                            <h3 class="profile-username text-center">{{auth()->user()->name}} {{auth()->user()->surname}}</h3>

                            <p class="text-muted text-center">{{auth()->user()->nickname}}</p>


                            <strong><i class="fa-duotone fa-briefcase mr-1"></i> @lang('profile.job_title')</strong>

                            <p class="text-muted">
                                {{auth()->user()->job_title}}
                            </p>

                            <strong><i class="fa-duotone fa-book mr-1"></i> @lang('profile.education')</strong>

                            <p class="text-muted">
                                {{auth()->user()->education}}
                            </p>

                            <hr>

                            <strong><i class="fa-duotone fa-location-dot mr-1"></i> @lang('profile.location')</strong>

                            <p class="text-muted">{{auth()->user()->location}}</p>

                            <hr>

                            <strong><i class="fa-duotone fa-pencil mr-1"></i> @lang('profile.skills')</strong>

                            <p class="text-muted">
                                @foreach(explode(',',auth()->user()->skills) as $skill)
                                    <span class="badge bg-primary">{{$skill}}</span>
                                @endforeach
                            </p>

                            <hr>

                            <strong><i class="fa-duotone fa-book-user mr-1"></i> @lang('profile.about-me')</strong>

                            <p class="text-muted">{{auth()->user()->about}}</p>

                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <div class="col-md-9">
                    <div class="card">
                        <div class="card-header">
                            <ul class="nav nav-pills">
                                <li class="nav-item"><a class="nav-link active" href="#about-me" data-bs-toggle="tab">@lang('profile.about-me')</a></li>
                                <li class="nav-item"><a class="nav-link" href="#security" data-bs-toggle="tab">Security</a></li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                <div class="tab-pane active" id="about-me">

                                    <form method="post" action="javascript:void(0)" id="profileUpdate" class="row">
                                        <div class="col-12 mb-3">
                                            <label for="name" class="form-label">@lang('profile.name.text')</label>
                                            <input type="text" class="form-control"
                                                   id="name" name="name"
                                                   placeholder="@lang('profile.name.placeholder')"
                                                   value="{{auth()->user()->name}}" required>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label for="surname" class="form-label">@lang('profile.surname.text')</label>
                                            <input type="text" class="form-control"
                                                   id="surname" name="surname"
                                                   placeholder="@lang('profile.surname.placeholder')"
                                                   value="{{auth()->user()->surname}}" required>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label for="nickname" class="form-label">@lang('profile.nickname.text')</label>
                                            <input type="text" class="form-control"
                                                   id="nickname" name="nickname"
                                                   placeholder="@lang('profile.nickname.placeholder')"
                                                   value="{{auth()->user()->nickname}}" required>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label for="education" class="form-label">@lang('profile.education.text')</label>
                                            <input type="text" class="form-control"
                                                   id="education" name="education"
                                                   placeholder="@lang('profile.education.placeholder')"
                                                   value="{{auth()->user()->education}}">
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label for="location" class="form-label">@lang('profile.location.text')</label>
                                            <input type="text" class="form-control"
                                                   id="location" name="location"
                                                   placeholder="@lang('profile.location.placeholder')"
                                                   value="{{auth()->user()->location}}">
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label for="skills" class="form-label">@lang('profile.skills.text')</label>
                                            <input type="text" class="form-control"
                                                   id="skills" name="skills"
                                                   placeholder="@lang('profile.skills.placeholder')"
                                                   value="{{auth()->user()->skills}}">
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label for="about" class="form-label">@lang('profile.about.text')</label>
                                            <textarea class="form-control"
                                                      id="about" name="about"
                                                      placeholder="@lang('profile.about.placeholder')">{{auth()->user()->about}}</textarea>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label for="job_title" class="form-label">@lang('profile.job_title.text')</label>
                                            <input type="text" class="form-control"
                                                   id="job_title" name="job_title"
                                                   placeholder="@lang('profile.job_title.placeholder')"
                                                   value="{{auth()->user()->job_title}}">
                                        </div>
                                        <div class="col-12 mb-3">
                                            <button type="submit" class="btn btn-primary">@lang('general.save')</button>
                                        </div>
                                    </form>

                                </div>
                                <div class="tab-pane" id="security">
                                    <div class="card">
                                        <div class="card-header">
                                            <ul class="nav nav-pills">
                                                <li class="nav-item"><a class="nav-link active" href="#password-change-tab" data-bs-toggle="tab">@lang('profile.change-password')</a></li>
                                                <li class="nav-item"><a class="nav-link" href="#two-fa-tab" data-bs-toggle="tab">Security</a></li>
                                            </ul>
                                        </div>
                                        <div class="card-body">
                                            <div class="tab-content">
                                                <div class="tab-pane active" id="password-change-tab">
                                                    <form class="row" action="javascript:void(0)" id="passwordChangeForm">
                                                        <div class="col-12 mb-3">
                                                            <label for="old_password" class="form-label">
                                                                @lang('profile.old_password.text')
                                                            </label>
                                                            <input type="password" class="form-control"
                                                                   id="old_password" name="old_password"
                                                                   placeholder="@lang('profile.old_password.placeholder')">
                                                        </div>
                                                        <div class="col-12 mb-3">
                                                            <label for="password" class="form-label">
                                                                @lang('profile.new_password.text')
                                                            </label>
                                                            <input type="password" class="form-control"
                                                                   id="password" name="password"
                                                                   placeholder="@lang('profile.new_password_confirmation.placeholder')">
                                                        </div>
                                                        <div class="col-12 mb-3">
                                                            <label for="password_confirmation" class="form-label">
                                                                @lang('profile.new_password_confirmation.text')
                                                            </label>
                                                            <input type="password" class="form-control"
                                                                   id="password_confirmation" name="password_confirmation"
                                                                   placeholder="@lang('profile.new_password_confirmation.placeholder')">
                                                        </div>
                                                        <div class="col-12 mb-3">
                                                            <button type="submit" class="btn btn-primary">@lang('general.save')</button>
                                                        </div>
                                                    </form>
                                                </div>
                                                <div class="tab-pane" id="two-fa-tab">
                                                    <div class="row">
                                                        <div class="col-12" id="webauthn_list">

                                                        </div>


                                                        <form id="login-form" action="javascript:void(0)">
                                                            @csrf
                                                            <button type="submit" id="webauthn_button" class="btn btn-primary">authenticator test</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="webauthnRenameModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{__('Rename Device')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="row" id="rename-form" action="javascript:void(0)" method="post">
                        <input id="rename_device_name" name="device_name" class="form-control">
                        <input type="hidden" id="rename_webauthn_id" name="webauthn_id">
                        @csrf
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" onclick="$('#rename-form').submit()">{{__('Rename Device')}}</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal" id="webauthnDeleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{__('Delete Device')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="row" id="delete-form" action="javascript:void(0)" method="post">
                        Are you want to sure delete this device?
                        <em id="delete_device_name"></em>
                        <input type="hidden" id="delete_webauthn_id" name="webauthn_id">
                        @csrf
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" onclick="$('#delete-form').submit()">{{__('Delete Device')}}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script defer src="https://pro-1804292700719907030.auth.corbado.com/auth.js" integrity="sha512-Lheoahk42FC+CLlDtLfPonF+0NLSOhP0kYqKZIYUKt/fDBdzwc16RELW1+C7dX0HcFmJBL/rjN4J0I8ws+XxWw==" crossorigin="anonymous"></script>

    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    <script>

        function notify_alert(message, type) {
            if (type === 'success') {
                toastr.success(message, 'Success!', {
                    closeButton: true,
                    tapToDismiss: false
                });
                window.location = '{{route('home')}}'
            } else {
                toastr.error(message, 'Error!', {
                    closeButton: true,
                    tapToDismiss: false
                });
            }
        }


        function deleteWebauthn(id, name) {
            $('#delete_webauthn_id').val(id);
            $('#delete_device_name').html(name);
            $('#webauthnDeleteModal').modal('show');
        }

        function renameWebauthn(id, name) {
            $('#rename_webauthn_id').val(id);
            $('#rename_device_name').val(name);
            $('#webauthnRenameModal').modal('show');
        }

        function listWebauthn() {
            $.ajax({
                url: '{{ route('user.security.webauthn') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function (data) {
                    console.log(data);
                    let devices = '<ul>';
                    $.each(data, function (index, value) {
                        devices += '<li class="mt-1 pb-1 border-bottom">' +
                            '<div class="row"> ' +
                            '<div class="col-sm-12 col-md-7 mt-1">' + value.name + '</div>' +
                            '<div class="col-sm-12 col-md-5">' +
                            '<a href="javascript:renameWebauthn(\'' + value.id + '\', \'' + value.name + '\')"  class="btn btn-primary">Rename</a> ' +
                            '<a href="javascript:deleteWebauthn(\'' + value.id + '\', \'' + value.name + '\')" class="btn btn-danger">Delete</a> ' +
                            '</div> ' +
                            '</div>' +
                            '</li>';
                    });
                    devices += '</ul>';
                    $('#webauthn_list').html(devices);
                }
            });
        }

        $(document).ready(function () {
            listWebauthn();
            $('#delete-form').submit(function(){
                $.ajax({
                    url: '{{ route('user.security.webauthn.delete') }}',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function (data) {
                        if(data.status){
                            $('#webauthnDeleteModal').modal('hide');
                            listWebauthn();
                        }
                    }
                });
            });
            $('#rename-form').submit(function(){
                $.ajax({
                    url: '{{ route('user.security.webauthn.rename') }}',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function (data) {
                        if(data.status){
                            $('#webauthnRenameModal').modal('hide');
                            listWebauthn();
                        }
                    }
                });
            });
            $('#passwordChangeForm').submit(function (e) {
                e.preventDefault();
                let data = $(this).serialize();
                $.ajax({
                    url: '{{route('admin.profile.password')}}',
                    type: 'POST',
                    data: data,
                    success: function (response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                title: "@lang('general.success')",
                                text: response.message,
                                icon: "success",
                                showCancelButton: false,
                                confirmButtonText: "@lang('general.ok')",
                                reverseButtons: true
                            });
                            $('#passwordChangeForm').trigger('reset');
                        } else {
                            Swal.fire({
                                title: "@lang('general.error')",
                                text: response.message,
                                icon: "error",
                                showCancelButton: false,
                                confirmButtonText: "@lang('general.ok')",
                                reverseButtons: true
                            });
                        }
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        Swal.fire({
                            title: "Error",
                            text: xhr.responseJSON.message,
                            icon: "error",
                            showCancelButton: false,
                            confirmButtonText: "@lang('general.ok')",
                            reverseButtons: true
                        });
                    }
                });
            });

            $('#profileUpdate').submit(function(){
                $.ajax({
                    url: '{{route('admin.profile.save')}}',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function (response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                title: "@lang('general.success')",
                                text: response.message,
                                icon: "success",
                                showCancelButton: false,
                                confirmButtonText: "@lang('general.ok')",
                                reverseButtons: true
                            });
                        } else {
                            Swal.fire({
                                title: "@lang('general.error')",
                                text: response.message,
                                icon: "error",
                                showCancelButton: false,
                                confirmButtonText: "@lang('general.ok')",
                                reverseButtons: true
                            });
                        }
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        Swal.fire({
                            title: "Error",
                            text: xhr.responseJSON.message,
                            icon: "error",
                            showCancelButton: false,
                            confirmButtonText: "@lang('general.ok')",
                            reverseButtons: true
                        });
                    }
                });
            });
        })
    </script>
@endsection
