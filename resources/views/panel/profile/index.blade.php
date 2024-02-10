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
                                <img class="profile-user-img img-fluid img-circle" src="https://www.gravatar.com/avatar/{{md5(strtolower(trim($user->email)))}}?s=128" alt="{{$user->nickname}}">
                            </div>

                            <h3 class="profile-username text-center">{{$user->name}} {{$user->surname}}</h3>

                            <p class="text-muted text-center">{{$user->nickname}}</p>


                            <strong><i class="fa-duotone fa-briefcase mr-1"></i> @lang('profile.job_title')</strong>

                            <p class="text-muted">
                                {{$user->job_title}}
                            </p>

                            <strong><i class="fa-duotone fa-book mr-1"></i> @lang('profile.education')</strong>

                            <p class="text-muted">
                                {{$user->education}}
                            </p>

                            <hr>

                            <strong><i class="fa-duotone fa-location-dot mr-1"></i> @lang('profile.location')</strong>

                            <p class="text-muted">{{$user->location}}</p>

                            <hr>

                            <strong><i class="fa-duotone fa-pencil mr-1"></i> @lang('profile.skills')</strong>

                            <p class="text-muted">
                                @foreach(explode(',',$user->skills) as $skill)
                                    <span class="badge bg-primary">{{$skill}}</span>
                                @endforeach
                            </p>

                            <hr>

                            <strong><i class="fa-duotone fa-book-user mr-1"></i> @lang('profile.about-me')</strong>

                            <p class="text-muted">{{$user->about}}</p>

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
                                <li class="nav-item"><a class="nav-link" href="#social-networks" data-bs-toggle="tab">@lang('social.social_networks')</a></li>
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
                                                   value="{{$user->name}}" required>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label for="surname" class="form-label">@lang('profile.surname.text')</label>
                                            <input type="text" class="form-control"
                                                   id="surname" name="surname"
                                                   placeholder="@lang('profile.surname.placeholder')"
                                                   value="{{$user->surname}}" required>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label for="nickname" class="form-label">@lang('profile.nickname.text')</label>
                                            <input type="text" class="form-control"
                                                   id="nickname" name="nickname"
                                                   placeholder="@lang('profile.nickname.placeholder')"
                                                   value="{{$user->nickname}}" required>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label for="education" class="form-label">@lang('profile.education.text')</label>
                                            <input type="text" class="form-control"
                                                   id="education" name="education"
                                                   placeholder="@lang('profile.education.placeholder')"
                                                   value="{{$user->education}}">
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label for="location" class="form-label">@lang('profile.location.text')</label>
                                            <input type="text" class="form-control"
                                                   id="location" name="location"
                                                   placeholder="@lang('profile.location.placeholder')"
                                                   value="{{$user->location}}">
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label for="skills" class="form-label">@lang('profile.skills.text')</label>
                                            <input type="text" class="form-control"
                                                   id="skills" name="skills"
                                                   placeholder="@lang('profile.skills.placeholder')"
                                                   value="{{$user->skills}}">
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label for="about" class="form-label">@lang('profile.about.text')</label>
                                            <textarea class="form-control"
                                                      id="about" name="about"
                                                      placeholder="@lang('profile.about.placeholder')">{{$user->about}}</textarea>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label for="job_title" class="form-label">@lang('profile.job_title.text')</label>
                                            <input type="text" class="form-control"
                                                   id="job_title" name="job_title"
                                                   placeholder="@lang('profile.job_title.placeholder')"
                                                   value="{{$user->job_title}}">
                                        </div>
                                        @if(request()->route()->parameter('user'))
                                            <div class="col-12 mb-3">
                                                <label for="role">@lang('user.role')</label>
                                                <select name="role" id="role" class="form-control">
                                                    <option value="admin" @if($user->role == 'admin') selected @endif>
                                                        @lang('user.role_admin')
                                                    </option>
                                                    <option value="author" @if($user->role == 'author') selected @endif>
                                                        @lang('user.role_author')
                                                    </option>
                                                    <option value="user" @if($user->role == 'user') selected @endif>
                                                        @lang('user.role_user')
                                                    </option>
                                                </select>
                                            </div>
                                        @endif
                                        <div class="col-12 mb-3">
                                            <button type="submit" class="btn btn-primary">@lang('general.save')</button>
                                        </div>
                                    </form>

                                </div>
                                <div class="tab-pane" id="social-networks">
                                    <form class="row" method="post" id="socialNetworkForm" action="javascript:void(0)">
                                        <div class="col-12 mb-3">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <i class="fa-brands fa-linkedin"
                                                       data-bs-toggle="tooltip"
                                                       data-bs-placement="left"
                                                       data-bs-title="Linkedin"></i>
                                                </div>
                                                <input type="text" class="form-control"
                                                       id="linkedin" name="linkedin"
                                                       placeholder="@ @lang('social.linkedin_username')"
                                                       value="{{$user->social?->linkedin}}" aria-label="Linkedin">
                                            </div>
                                        </div>

                                        <hr class="col-12 mb-3">

                                        <div class="col-12 mb-3">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <i class="fa-brands fa-github"
                                                       data-bs-toggle="tooltip"
                                                       data-bs-placement="left"
                                                       data-bs-title="Github"></i>
                                                </div>
                                                <input type="text" class="form-control"
                                                       id="github" name="github"
                                                       placeholder="@ @lang('social.github_username')"
                                                       value="{{$user->social?->github}}" aria-label="Github">
                                            </div>

                                        </div>

                                        <hr class="col-12 mb-3">

                                        <div class="col-12 mb-3">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <i class="fa-brands fa-instagram"
                                                       data-bs-toggle="tooltip"
                                                       data-bs-placement="left"
                                                       data-bs-title="Instagram"></i>
                                                </div>
                                                <input type="text" class="form-control"
                                                       id="instagram" name="instagram"
                                                       placeholder="@ @lang('social.instagram_username')"
                                                       value="{{$user->social?->instagram}}" aria-label="Instagram">
                                            </div>
                                        </div>

                                        <hr class="col-12 mb-3">

                                        <div class="col-12 mb-3">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <i class="fa-brands fa-x-twitter"
                                                       data-bs-toggle="tooltip"
                                                       data-bs-placement="left"
                                                       data-bs-title="X"></i>
                                                </div>
                                                <input type="text" class="form-control"
                                                       id="x" name="x"
                                                       placeholder="@ @lang('social.x_username')"
                                                       value="{{$user->social?->x}}" aria-label="X">
                                            </div>
                                        </div>

                                        <hr class="col-12 mb-3">

                                        <div class="col-12 mb-3">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <i class="fa-brands fa-facebook"
                                                       data-bs-toggle="tooltip"
                                                       data-bs-placement="left"
                                                       data-bs-title="Facebook"></i>
                                                </div>
                                                <input type="text" class="form-control"
                                                       id="facebook" name="facebook"
                                                       placeholder="@ @lang('social.facebook_username')"
                                                       value="{{$user->social?->facebook}}" aria-label="Facebook">
                                            </div>
                                        </div>

                                        <hr class="col-12 mb-3">

                                        <div class="col-12 mb-3">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <i class="fa-brands fa-dev"
                                                       data-bs-toggle="tooltip"
                                                       data-bs-placement="left"
                                                       data-bs-title="Dev.to"></i>
                                                </div>
                                                <input type="text" class="form-control"
                                                       id="devto" name="devto"
                                                       placeholder="@ @lang('social.devto_username')"
                                                       value="{{$user->social?->devto}}" aria-label="Dev.to">
                                            </div>
                                        </div>

                                        <hr class="col-12 mb-3">

                                        <div class="col-12 mb-3">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <i class="fa-brands fa-medium"
                                                       data-bs-toggle="tooltip"
                                                       data-bs-placement="left"
                                                       data-bs-title="Medium"></i>
                                                </div>
                                                <input type="text" class="form-control"
                                                       id="medium" name="medium"
                                                       placeholder="@ @lang('social.medium_username')"
                                                       value="{{$user->social?->medium}}" aria-label="Medium">
                                            </div>
                                        </div>

                                        <hr class="col-12 mb-3">

                                        <div class="col-12 mb-3">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <i class="fa-brands fa-youtube"
                                                       data-bs-toggle="tooltip"
                                                       data-bs-placement="left"
                                                       data-bs-title="YouTube"></i>
                                                </div>
                                                <input type="text" class="form-control"
                                                       id="youtube" name="youtube"
                                                       placeholder="@ @lang('social.youtube_username')"
                                                       value="{{$user->social?->youtube}}" aria-label="YouTube">
                                            </div>
                                        </div>

                                        <hr class="col-12 mb-3">

                                        <div class="col-12 mb-3">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <i class="fa-brands fa-reddit-alien"
                                                       data-bs-toggle="tooltip"
                                                       data-bs-placement="left"
                                                       data-bs-title="Reddit"></i>
                                                </div>
                                                <input type="text" class="form-control"
                                                       id="reddit" name="reddit"
                                                       placeholder="@ @lang('social.reddit_username')"
                                                       value="{{$user->social?->reddit}}" aria-label="Reddit">
                                            </div>
                                        </div>

                                        <hr class="col-12 mb-3">

                                        <div class="col-12 mb-3">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <i class="fa-brands fa-xbox"
                                                       data-bs-toggle="tooltip"
                                                       data-bs-placement="left"
                                                       data-bs-title="Xbox"></i>
                                                </div>
                                                <input type="text" class="form-control"
                                                       id="xbox" name="xbox"
                                                       placeholder="@ @lang('social.xbox_username')"
                                                       value="{{$user->social?->xbox}}" aria-label="Xbox">
                                            </div>
                                        </div>

                                        <hr class="col-12 mb-3">

                                        <div class="col-12 mb-3">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <i class="fa-brands fa-deviantart"
                                                       data-bs-toggle="tooltip"
                                                       data-bs-placement="left"
                                                       data-bs-title="Deviantart"></i>
                                                </div>
                                                <input type="text" class="form-control"
                                                       id="deviantart" name="deviantart"
                                                       placeholder="@ @lang('social.deviantart_username')"
                                                       value="{{$user->social?->deviantart}}" aria-label="Deviantart">
                                            </div>
                                        </div>

                                        <hr class="col-12 mb-3">

                                        <div class="col-12 mb-3">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <i class="fa-duotone fa-globe-pointer"
                                                       data-bs-toggle="tooltip"
                                                       data-bs-placement="left"
                                                       data-bs-title="Web"></i>
                                                </div>
                                                <input type="url" class="form-control"
                                                       id="website" name="website"
                                                       placeholder="@lang('social.website')"
                                                       value="{{$user->social?->website}}" aria-label="Website">
                                            </div>
                                        </div>

                                        @csrf
                                        <div class="col-12 mt-3">
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
                                                        @if(!request()->route()->parameter('user'))
                                                        <div class="col-12 mb-3">
                                                            <label for="old_password" class="form-label">
                                                                @lang('profile.old_password.text')
                                                            </label>
                                                            <input type="password" class="form-control"
                                                                   id="old_password" name="old_password"
                                                                   placeholder="@lang('profile.old_password.placeholder')">
                                                        </div>
                                                        @endif
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
                                                        @csrf
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
@endsection

@section('script')
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

        $(document).ready(function () {

            let profile_update_url;
            let social_network_update_url;
            let password_change_url;
            @if(request()->route()->parameter('user'))
                profile_update_url = '{{route('admin.user.edit', $user)}}';
                social_network_update_url = '{{route('admin.user.social.save', $user)}}';
                password_change_url = '{{route('admin.user.password', $user)}}';
            @else
                profile_update_url = '{{route('admin.profile.save')}}';
                social_network_update_url = '{{route('admin.profile.social.save')}}'
                password_change_url = '{{route('admin.profile.password')}}';
            @endif

            $('#passwordChangeForm').submit(function (e) {
                e.preventDefault();
                let data = $(this).serialize();
                $.ajax({
                    url: password_change_url,
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
                    url: profile_update_url,
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

            $('#socialNetworkForm').submit(function(){
                $.ajax({
                    url: social_network_update_url,
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
