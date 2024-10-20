@extends('panel.base')
@section('title', __('user.users'))
@section('breadcrumb_link')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{route('admin.index')}}">@lang('dashboard.dashboard')</a></li>
        <li class="breadcrumb-item active">
            @lang('user.users')
        </li>
    </ol>
@endsection
@section('content')
    <div class="card">
        <div class="card-header d-flex">
            <h3 class="card-title">
                @lang('user.users')
            </h3>
            <div class="ml-auto">
                <a href="{{route('admin.user.create')}}"
                   class="btn btn-default"
                   data-bs-toggle="tooltip" data-bs-placement="top"
                   title="@lang('general.new')">
                    <i class="fa-duotone fa-comment-plus"></i>
                    @lang('general.new')
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <form class="row">
                        <div class="col-12">
                            <div class="form-group mb-3">
                                <label for="search" class="form-label"></label>
                                <input type="text" class="form-control" id="search" name="search" value="{{request()->get('search')}}" placeholder="@lang('general.search')">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-12 table-responsive">
                    <table class="table table-striped" aria-describedby="users">
                        <thead>
                        <tr>
                            <th>
                                @lang('user.name')
                            </th>
                            <th>
                                @lang('user.email')
                            </th>
                            <th>
                                @lang('user.role')
                            </th>
                            <th>
                                @lang('general.created_at')
                            </th>
                            <th>
                                @lang('general.updated_at')
                            </th>
                            <th>
                                @lang('general.actions')
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>
                                    {{$user->name}} {{$user->surname}}
                                    <br>
                                    {{$user->nickname}}
                                </td>
                                <td>
                                    {{$user->email}}
                                </td>
                                <td>
                                    {{__('user.role_'.$user->role)}}
                                </td>
                                <td>
                                    {{$user->created_at}}
                                </td>
                                <td>
                                    {{$user->updated_at}}
                                </td>
                                <td>
                                    <a href="{{route('admin.user.edit', $user->id)}}"
                                       class="btn btn-primary btn-sm">
                                        <i class="fa-duotone fa-edit"></i>
                                    </a>
                                    <a href="javascript:userDelete('{{$user->id}}');"
                                       class="btn btn-danger btn-sm">
                                        <i class="fa-duotone fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">
                                    @lang('user.no_user')
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="card-footer">
            {{$users->links()}}
        </div>
    </div>
@endsection
@section('script')
    <script>
        function userDelete(user_id){
            Swal.fire({
                title: '{{__('general.are_you_sure')}}',
                text: '{{__('general.you_wont_be_able_to_revert_this')}}',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '{{__('general.yes')}}',
                cancelButtonText: '{{__('general.cancel')}}',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{route('admin.user.delete')}}',
                        type: 'POST',
                        data: {
                            user_id: user_id,
                            _token: '{{csrf_token()}}'
                        },
                        success: function (response) {
                            if (response.status === 'success') {
                                Swal.fire(
                                    '{{__('general.deleted')}}',
                                    '{{__('user.user_deleted')}}',
                                    'success'
                                ).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.reload();
                                    }
                                });
                            } else {
                                Swal.fire(
                                    '{{__('general.error')}}',
                                    '{{__('user.something_wrong')}}',
                                    'error'
                                );
                            }
                        },
                        error: function (response) {
                            Swal.fire(
                                '{{__('general.error')}}',
                                '{{__('user.something_wrong')}}',
                                'error'
                            );
                        }
                    });
                }
            });
        }
    </script>
@endsection
