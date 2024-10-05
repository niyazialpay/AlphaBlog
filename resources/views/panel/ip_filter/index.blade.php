@extends('panel.base')
@section('title',__('ip_filter.ip_filter'))
@section('breadcrumb_link')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{route('admin.index')}}">@lang('dashboard.dashboard')</a></li>
        <li class="breadcrumb-item active">@lang('ip_filter.ip_filter')</li>
    </ol>
@endsection
@section('content')
    <div class="card">
        <div class="card-header d-flex">
            <h3 class="card-title">@lang('ip_filter.ip_filter')</h3>
            <div class="ml-auto">
                <a href="{{route('admin.ip-filter.create')}}"
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
                <div class="col-12 table-responsive">
                    <table class="table table-striped" aria-describedby="@lang('ip_filter.ip_filter')">
                        <tr>
                            <th scope="col">@lang('ip_filter.status')</th>
                            <th scope="col">@lang('ip_filter.name')</th>
                            <th scope="col">@lang('ip_filter.ip_range')</th>
                            <th scope="col">@lang('ip_filter.routes')</th>
                            <th scope="col">@lang('ip_filter.list_type')</th>
                            <th scope="col">@lang('ip_filter.code')</th>
                            <th scope="col">@lang('general.actions')</th>
                        </tr>
                        @forelse($IPFilter as $item)
                            <tr id="rule_{{$item->id}}">
                                <td id="rule_status_{{$item->id}}">
                                    @if($item->is_active)
                                        <a href="javascript:ruleToggle({{$item->id}})" class="px-2 py-1 rounded bg-success h4">
                                            <i class="fa-duotone fa-solid fa-toggle-on"></i>
                                        </a>
                                    @else
                                        <a href="javascript:ruleToggle({{$item->id}})" class="px-2 py-1 rounded bg-danger h4">
                                            <i class="fa-duotone fa-solid fa-toggle-off"></i>
                                        </a>
                                    @endif
                                </td>
                                <td>{{$item->name}}</td>
                                <td>
                                    @foreach($item->ipList as $ip)
                                        {{$ip->ip}}<br>
                                    @endforeach
                                </td>
                                <td>
                                    @foreach($item->routeList as $route)
                                        {{$route->route}}<br>
                                    @endforeach
                                </td>
                                <td>{{__('ip_filter.'.$item->list_type)}}</td>
                                <td>{{$item->code}}</td>
                                <td>
                                    <a href="{{route('admin.ip-filter.show',$item->id)}}"
                                       class="btn btn-sm btn-primary"
                                       data-bs-toggle="tooltip" data-bs-placement="top"
                                       title="@lang('general.edit')">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <a href="javascript:void(0);" onclick="Delete('{{$item->id}}')"
                                       class="btn btn-sm btn-danger"
                                       data-bs-toggle="tooltip" data-bs-placement="top"
                                       title="@lang('general.delete')">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">@lang('ip_filter.no_ip_filter')</td>
                            </tr>
                        @endforelse
                        <tr>
                            <th scope="col">@lang('ip_filter.status')</th>
                            <th scope="col">@lang('ip_filter.name')</th>
                            <th scope="col">@lang('ip_filter.ip_range')</th>
                            <th scope="col">@lang('ip_filter.routes')</th>
                            <th scope="col">@lang('ip_filter.list_type')</th>
                            <th scope="col">@lang('ip_filter.code')</th>
                            <th scope="col">@lang('general.actions')</th>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        function Delete(id) {
            //delete confirmation
            Swal.fire({
                title: "@lang('general.are_you_sure')",
                text: "@lang('general.you_wont_be_able_to_revert_this')",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "@lang('general.delete_confirm_yes')",
                cancelButtonText: "@lang('general.delete_confirm_no')",
                reverseButtons: true
            }).then(function (result) {
                if (result.value) {
                    $.ajax({
                        url: "{{route('admin.ip-filter.delete')}}",
                        type: "POST",
                        data: {
                            id: id,
                            _token: "{{csrf_token()}}"
                        },
                        success: function (response) {
                            if (response.status) {
                                swal.fire({
                                    title: "@lang('general.success')",
                                    text: response.message,
                                    icon: "success",
                                    showCancelButton: false,
                                    showConfirmButton: false,
                                    timer: 1500
                                })
                                $("#rule_" + id).remove();
                            } else {
                                swal.fire({
                                    title: "@lang('general.error')",
                                    text: response.message,
                                    icon: "error",
                                    showCancelButton: false,
                                    showConfirmButton: false,
                                    timer: 1500
                                })
                            }
                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                            swal.fire({
                                title: "Error",
                                text: xhr.responseJSON.message,
                                icon: "error",
                                showCancelButton: false,
                                showConfirmButton: false,
                                timer: 1500
                            })
                        }
                    });
                }
            });
        }

        function ruleToggle(id){
            $.ajax({
                url: "{{route('admin.ip-filter.toggle-status')}}",
                type: "POST",
                data: {
                    id: id,
                    _token: "{{csrf_token()}}"
                },
                success: function(data){
                    if(data.rule){
                        $("#rule_status_"+id).html(`<a href="javascript:ruleToggle(${id})" class="px-2 py-1 rounded bg-success h4">
                                <i class="fa-duotone fa-solid fa-toggle-on"></i>
                            </a>`);
                    } else{
                        $("#rule_status_"+id).html(`<a href="javascript:ruleToggle(${id})" class="px-2 py-1 rounded bg-danger h4">
                                <i class="fa-duotone fa-solid fa-toggle-off"></i>
                            </a>`);
                    }
                },
                error: function(xhr, ajaxOptions, thrownError){
                    swal.fire({
                        title: "Error",
                        text: xhr.responseJSON.message,
                        icon: "error",
                        showCancelButton: false,
                        showConfirmButton: false,
                        timer: 1500
                    })
                }
            });
        }

        $(document).ready(function () {
            $("#name").keyup(function () {
                let name = $(this).val();
                $("#slug").val(ToSeoUrl(name));
            });
            $("#category_create_form").submit(function () {
                $.ajax({
                    url: "{{request()->url()}}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function (response) {
                        if (response.status) {
                            swal.fire({
                                title: "@lang('general.success')",
                                text: response.message,
                                icon: "success",
                                showCancelButton: false,
                                showConfirmButton: false,
                                timer: 1500
                            })
                            location.reload();
                        } else {
                            swal.fire({
                                title: "@lang('general.error')",
                                text: response.message,
                                icon: "error",
                                showCancelButton: false,
                                showConfirmButton: false,
                                timer: 1500
                            })
                        }
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        swal.fire({
                            title: "Error",
                            text: xhr.responseJSON.message,
                            icon: "error",
                            showCancelButton: false,
                            showConfirmButton: false,
                            timer: 1500
                        })
                    }
                });
            });
        });
    </script>
@endsection
