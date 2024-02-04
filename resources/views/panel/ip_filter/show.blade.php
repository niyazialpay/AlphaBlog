@extends('panel.base')
@section('title',__('ip_filter.title'))
@section('breadcrumb_link')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{route('admin.index')}}">@lang('dashboard.dashboard')</a></li>
        <li class="breadcrumb-item"><a href="{{route('admin.ip-filter')}}">@lang('ip_filter.title')</li>
        <li class="breadcrumb-item active">@lang('general.add-edit')</li>
    </ol>
@endsection
@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">@lang('ip_filter.title')</h3>
        </div>
        <div class="card-body">
            <form class="row" method="post" id="ip_filter_form" action="javascript:void(0);">
                <div class="col-12 mb-3">
                    <label for="name">@lang('ip_filter.name')</label>
                    <input type="text" class="form-control" name="name" id="name"
                           placeholder="@lang('ip_filter.name')" value="{{$ip_filter->name}}">

                </div>
                <div class="col-12 mb-3">
                    <label for="ip_range">@lang('ip_filter.ip_range')</label>
                    <textarea name="ip_range" id="ip_range"
                              class="form-control"
                              placeholder="@lang('ip_filter.ip_range_placeholder')"></textarea>
                </div>
                <div class="col-12 mb-3">
                    <div class="form-check">
                        <input class="form-check-input route-list-options" type="radio"
                               name="route_type" id="routes_select_list" value="select" checked>

                        <label class="form-check-label" for="routes_select_list">
                            @lang('ip_filter.routes_select_box')
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input route-list-options" type="radio"
                               name="route_type" id="routes_manuel" value="manuel">

                        <label class="form-check-label" for="routes_manuel">
                            @lang('ip_filter.routes_select_manuel')
                        </label>
                    </div>
                </div>
                <div class="col-12 mb-3" id="route_list">

                </div>
                <div class="col-12 mb-3">
                    <label for="list_type">@lang('ip_filter.list_type')</label>
                    <select class="form-control" id="list_type" name="list_type">
                        <option value="whitelist" @if($ip_filter->list_type=="whitelist") selected @endif>
                            @lang('ip_filter.whitelist')
                        </option>
                        <option value="blacklist" @if($ip_filter->list_type=="blacklist") selected @endif>
                            @lang('ip_filter.blacklist')
                        </option>
                    </select>
                </div>
                <div class="col-12 mb-3">
                    <label for="is_active">@lang('ip_filter.status')</label>
                    <select class="form-control" id="is_active" name="is_active">
                        <option value="1" @if($ip_filter->is_active) selected @endif >
                            @lang('ip_filter.status_active')
                        </option>
                        <option value="0" @if(!$ip_filter->is_active) selected @endif>
                            @lang('ip_filter.status_passive')
                        </option>
                    </select>
                </div>
                @csrf
                <div class="col-12 mb-3">
                    <button type="submit" class="btn btn-primary">@lang('general.save')</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function () {
            $('#route_list').html(`
                <label for="routes">@lang('ip_filter.routes')</label>
                <select name="routes[]" id="routes" class="form-control select2" style="width: 100%" multiple>
                    @foreach($route_list as $item)
            <option value="{{$item->uri()}}">{{$item->uri()}} - {{$item->methods()[0]}}</option>
                    @endforeach
            </select>
`);


            $('.route-list-options').on('change', function () {
                if ($(this).val() === 'select') {
                    $('#route_list').html(`
                        <label for="routes">@lang('ip_filter.routes')</label>
                <select name="routes[]" id="routes" class="form-control select2" style="width: 100%" multiple>
                    @foreach($route_list as $item)
                    <option value="{{$item->uri()}}">{{$item->uri()}} - {{$item->methods()[0]}}</option>
                    @endforeach
                    </select>
`);
                } else {
                    $('#route_list').html(`
                        <label for="routes">@lang('ip_filter.routes')</label>
                        <textarea name="routes" id="routes"
                                  class="form-control"
                                  placeholder="@lang('ip_filter.routes_placeholder')"></textarea>
                    `);
                }
            });

            @if($ip_filter->id)
            $('#ip_range').html(`@foreach($ip_filter->ip_range as $item){{$item.PHP_EOL}}@endforeach`);
            @if($ip_filter->route_type === 'manuel')
            $('#routes_select_list').prop('checked', false);
            $('#routes_manuel').prop('checked', true).trigger('change');

            $('#routes').html(`@foreach($ip_filter->routes as $item){{$item.PHP_EOL}}@endforeach`);

            @else
            $('#routes_select_list').prop('checked', true).trigger('change');
            $('#routes_manuel').prop('checked', false);

            $('#routes').val({!! json_encode($ip_filter->routes) !!});
            @endif
            @endif

            $('#ip_filter_form').submit(function () {
                $.ajax({
                    url: '{{route('admin.ip-filter.save', $ip_filter)}}',
                    type: 'POST',
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
                            //window.location = "{{route('admin.ip-filter')}}";
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
