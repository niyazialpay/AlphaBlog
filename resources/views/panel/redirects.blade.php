@extends('panel.base')
@section('title', __('redirects.redirects'))
@section('breadcrumb_link')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{route('admin.index')}}">@lang('dashboard.dashboard')</a></li>
        <li class="breadcrumb-item active">
            @lang('redirects.redirects')
        </li>
    </ol>
@endsection
@section('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex">
                @lang('redirects.redirects')
                <div class="ms-auto">
                    <a href="javascript:addRoute()" class="mb-5"><button class="btn btn-default"><i class="fas fa-file-word"></i></button></a>
                </div>
            </div>
            <div class="card-body">
                <form method="get" action="{{route('adminRoutes')}}">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" name="search" value="{{request()->has('search')?request()->get('search'):''}}" placeholder="@lang('post.search')" aria-label="@lang('post.search')" aria-describedby="basic-addon2">
                        <button class="btn btn-outline-secondary" type="submit">@lang('post.search')</button>
                    </div>
                </form>
                <table class="table table-hover table-striped">
                    <tr>
                        <th>@lang('redirects.old_url')</th>
                        <th>@lang('redirects.new_url')</th>
                        <th>@lang('redirects.redirect_code')</th>
                        <th>@lang('general.actions')</th>
                    </tr>
                    @forelse($routes as $route)
                        <tr>
                            <td><a href="{{config('app.url')}}/{{$route->old_url}}" target="_blank">{{$route->old_url}}</a></td>
                            <td><a href="{{config('app.url')}}{{$route->new_url}}" target="_blank">{{$route->new_url}}</a></td>
                            <td>{{$route->redirect_code}}</td>
                            <td>
                                <a href="javascript:editRoute('{{$route->id}}')" class="btn btn-sm btn-primary">
                                    <i class="fa fa-edit"></i>
                                </a>
                                <a href="javascript:deleteRoute('{{$route->id}}', '{{$route->old_url}}')" class="btn btn-sm btn-danger">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">@lang('redirects.no_data')</td>
                        </tr>
                    @endforelse
                </table>
            </div>
            <div class="card-footer">
                <div id="routes-pagination" class="mt-3">
                    {{$routes->links()}}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="deleteRoute" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteRouteLabel">@lang('redirects.delete_confirmation_title')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @lang('redirects.delete_confirmation_message')
                    <div><i id="deleting_item"></i></div>
                    <form id="delete_route" action="javascript:void(0)">
                        @csrf
                        <input type="hidden" id="route_id" name="route_id">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('general.cancel')</button>
                    <button type="button" class="btn btn-danger" onclick="$('#delete_route').submit()">@lang('general.delete')</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addEditRoute" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addEditRouteLabel"></h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="add_edit_route" action="javascript:void(0)">
                        @csrf
                        <div class="form-group">
                            <label for="old_url">@lang('redirects.old_url')</label>
                            <input type="text" class="form-control" id="old_url" name="old_url" placeholder="@lang('redirects.old_url')" required>
                        </div>
                        <div class="form-group">
                            <label for="new_url">@lang('redirects.new_url')</label>
                            <input type="text" class="form-control" id="new_url" name="new_url" placeholder="@lang('redirects.new_url')" required>
                        </div>
                        <div class="form-group">
                            <label for="redirect_code">@lang('redirects.redirect_code')</label>
                            <select name="redirect_code" id="redirect_code" class="form-select form-control">
                                <option value="301">301</option>
                                <option value="302">302</option>
                                <option value="303">303</option>
                                <option value="307">307</option>
                                <option value="308">308</option>
                                <option value="404">404</option>
                            </select>
                        </div>
                        <input type="hidden" id="add_edit_route_id" name="route_id">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('general.cancel')</button>
                    <button type="button" class="btn btn-danger" onclick="$('#add_edit_route').submit()">@lang('general.save')</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        function deleteRoute(id, route_name) {
            $('#route_id').val(id);
            $('#deleting_item').text(route_name);
            $('#deleteRoute').modal('show');
        }

        function addRoute() {
            $('#add_edit_route').trigger('reset');
            $('#add_edit_route_id').val('');
            $('#addEditRouteLabel').html('@lang('redirects.add_route')');
            $('#addEditRoute').modal('show');
        }

        function editRoute(id) {
            $('#addEditRouteLabel').html('@lang('redirects.edit_route')');

            $.ajax({
                url: '{{route('adminRoutesShow')}}/' + id,
                type: 'post',
                data: {
                    _token: '{{csrf_token()}}'
                },
                success: function (response) {
                    $('#old_url').val(response.old_url);
                    $('#new_url').val(response.new_url);
                    $('#redirect_code').val(response.redirect_code);
                }
            });

            $('#add_edit_route_id').val(id);
            $('#addEditRoute').modal('show');
        }
        $(document).ready(function () {
            $('#add_edit_route').submit(function(){
                let url = '{{route('adminRouteSave')}}';
                let route_id = $('#add_edit_route_id').val();
                if(route_id){
                    url += '/' + route_id;
                }
                $.ajax({
                    url: url,
                    type: 'post',
                    data: $('#add_edit_route').serialize(),
                    success: function () {
                        location.reload();
                    },
                    error: function (xhr) {
                        console.log(xhr);
                        Swal.fire({
                            icon: 'warning',
                            title: '@lang('general.error')',
                            text: xhr.responseJSON.message,
                            showConfirmButton: false,
                        });
                    }
                });
            });

            $('#delete_route').submit(function(){
                $.ajax({
                    url: '{{route('adminRoutesDelete')}}',
                    type: 'post',
                    data: $('#delete_route').serialize(),
                    success: function(){
                        location.reload();
                    }
                });
            });
        });
    </script>
@endsection
