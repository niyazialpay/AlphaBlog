@extends('panel.base')
@section('title',__('ip_filter.title'))
@section('breadcrumb_link')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{route('admin.index')}}">@lang('dashboard.dashboard')</a></li>
        <li class="breadcrumb-item"><a href="{{route('admin.ip-filter')}}">@lang('ip_filter.title')</a></li>
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
                              rows="5"
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
                    <label for="code">@lang('ip_filter.code')</label>
                    <select class="form-control" id="code" name="code" required>
                        <option value="">---</option>
                        <option value="403" @if($ip_filter->code=="403") selected @endif>
                            403
                        </option>
                        <option value="404" @if($ip_filter->code=="404") selected @endif>
                            404
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
    @if($ip_filter->id)
        <div class="card mt-4">
            <div class="card-header d-flex align-items-center gap-2 flex-wrap">
                <h3 class="card-title mb-0">@lang('ip_filter.ip_list')</h3>
                <span class="badge bg-secondary" id="ip-count">{{$ip_filter->ipList->count()}}</span>
                <div class="ms-auto w-100 w-md-auto">
                    <input type="text" id="ip-search" class="form-control" placeholder="@lang('ip_filter.ip_search_placeholder')">
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive" style="max-height: 360px">
                    <table class="table table-striped table-sm align-middle" id="ip_list_table"
                           data-destroy-template="{{route('admin.ip-filter.ips.destroy', [$ip_filter->id, '__id__'])}}">
                        <tbody>
                        @forelse($ip_filter->ipList as $ip)
                            <tr data-ip="{{$ip->ip}}">
                                <td class="font-monospace">{{$ip->ip}}</td>
                                <td class="text-end">
                                    <button type="button"
                                            class="btn btn-outline-danger btn-sm btn-delete-ip"
                                            data-url="{{route('admin.ip-filter.ips.destroy', [$ip_filter->id, $ip->id])}}">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-muted">@lang('ip_filter.no_ip_filter')</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title mb-0">@lang('ip_filter.ip_bulk_add')</h3>
            </div>
            <div class="card-body">
                <form id="bulk_ip_form">
                    @csrf
                    <div class="mb-3">
                        <label for="bulk_ips_input" class="form-label">@lang('ip_filter.ip_range')</label>
                        <textarea class="form-control font-monospace"
                                  id="bulk_ips_input"
                                  name="ips"
                                  rows="4"
                                  placeholder="@lang('ip_filter.ip_bulk_help')"></textarea>
                        <small class="text-muted d-block mt-2">@lang('ip_filter.ip_bulk_help')</small>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-outline-primary" id="bulk_ip_submit">
                            @lang('ip_filter.ip_bulk_add')
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="bulk_ip_clear">
                            @lang('general.clear')
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
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
                                  rows="5"
                                  class="form-control"
                                  placeholder="@lang('ip_filter.routes_placeholder')"></textarea>
                    `);
                }
            });

            @if($ip_filter->id)
            $('#ip_range').html(`@foreach($ip_filter->ipList->pluck('ip') as $item){{$item.PHP_EOL}}@endforeach`);
            @if($ip_filter->route_type === 'manuel')
            $('#routes_select_list').prop('checked', false);
            $('#routes_manuel').prop('checked', true).trigger('change');

            $('#routes').html(`@foreach($ip_filter->routeList->pluck('route') as $item){{$item.PHP_EOL}}@endforeach`);

            @else
            $('#routes_select_list').prop('checked', true).trigger('change');
            $('#routes_manuel').prop('checked', false);

            $('#routes').val({!! json_encode($ip_filter->routeList->pluck('route')) !!});
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
                            window.location = "{{route('admin.ip-filter')}}";
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

        @if($ip_filter->id)
        $(function () {
            const ipTable = $('#ip_list_table');
            const ipBody = ipTable.find('tbody');
            const ipCountBadge = $('#ip-count');
            const searchInput = $('#ip-search');
            const bulkForm = $('#bulk_ip_form');
            const bulkTextarea = $('#bulk_ips_input');
            const bulkSubmitButton = $('#bulk_ip_submit');

            const updateIpCount = () => {
                ipCountBadge.text(ipBody.find('tr').length);
            };

            updateIpCount();

            searchInput.on('input', function () {
                const term = this.value.trim().toLowerCase();
                ipBody.find('tr').each(function () {
                    const value = ($(this).data('ip') || '').toLowerCase();
                    $(this).toggle(value.includes(term));
                });
            });

            $('#bulk_ip_clear').on('click', function () {
                bulkTextarea.val('');
                bulkTextarea.trigger('focus');
            });

            const addRows = (items) => {
                if (!items || !items.length) {
                    return;
                }
                const template = ipTable.data('destroy-template');
                const rows = items.map(function (item) {
                    const destroyUrl = template.replace('__id__', item.id);
                    return `<tr data-ip="${item.ip}">
                                <td class="font-monospace">${item.ip}</td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-outline-danger btn-sm btn-delete-ip" data-url="${destroyUrl}">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>`;
                });
                ipBody.prepend(rows.join(''));
                updateIpCount();
            };

            const summarizeBulkResponse = (response) => {
                const messages = [];
                if (response.added && response.added.length) {
                    messages.push("@lang('ip_filter.ip_added_success', ['count' => '__count__'])".replace('__count__', response.added.length));
                }
                if (response.duplicates && response.duplicates.length) {
                    messages.push("@lang('ip_filter.ip_duplicates_skipped', ['count' => '__count__'])".replace('__count__', response.duplicates.length));
                }
                if (response.invalid && response.invalid.length) {
                    messages.push("@lang('ip_filter.ip_invalid_skipped', ['count' => '__count__'])".replace('__count__', response.invalid.length));
                }
                return messages.join('<br>');
            };

            bulkForm.on('submit', function (e) {
                e.preventDefault();
                const payload = bulkTextarea.val().trim();
                if (!payload.length) {
                    swal.fire({
                        icon: 'warning',
                        title: "@lang('general.warning')",
                        text: "@lang('ip_filter.ip_range_required')",
                    });
                    return;
                }

                bulkSubmitButton.prop('disabled', true);

                $.ajax({
                    url: '{{route('admin.ip-filter.ips.bulk', $ip_filter)}}',
                    type: 'POST',
                    data: {
                        ips: payload,
                        _token: '{{csrf_token()}}',
                    },
                    success: function (response) {
                        if (response.status) {
                            addRows(response.added || []);
                            bulkTextarea.val('');
                            const summary = summarizeBulkResponse(response);
                            swal.fire({
                                icon: 'success',
                                html: summary || "@lang('general.success')",
                                timer: 2500,
                            });
                        } else {
                            swal.fire({
                                icon: 'error',
                                text: response.message || "@lang('general.error')",
                            });
                        }
                    },
                    error: function (xhr) {
                        swal.fire({
                            icon: 'error',
                            text: xhr.responseJSON?.message || "@lang('general.error')",
                        });
                    },
                    complete: function () {
                        bulkSubmitButton.prop('disabled', false);
                    },
                });
            });

            ipBody.on('click', '.btn-delete-ip', function () {
                const button = $(this);
                const url = button.data('url');
                const row = button.closest('tr');

                swal.fire({
                    icon: 'warning',
                    title: "@lang('general.are_you_sure')",
                    showCancelButton: true,
                    confirmButtonText: "@lang('general.delete')",
                    cancelButtonText: "@lang('general.cancel')",
                }).then((result) => {
                    if (!result.isConfirmed) {
                        return;
                    }

                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: '{{csrf_token()}}',
                        },
                        success: function (response) {
                            if (response.status) {
                                row.remove();
                                updateIpCount();
                            } else {
                                swal.fire({
                                    icon: 'error',
                                    text: response.message || "@lang('general.error')",
                                });
                            }
                        },
                        error: function (xhr) {
                            swal.fire({
                                icon: 'error',
                                text: xhr.responseJSON?.message || "@lang('general.error')",
                            });
                        },
                    });
                });
            });
        });
        @endif
    </script>
@endsection
