@extends('panel.base')
@section('title', __('firewall.logs'))

@section('breadcrumb_link')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{route('admin.index')}}">@lang('dashboard.dashboard')</a></li>
        <li class="breadcrumb-item">
            <a href="{{route('admin.firewall')}}">@lang('firewall.firewall')</a>
        </li>
        <li class="breadcrumb-item active">@lang('firewall.logs')</li>
    </ol>
@endsection
@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">@lang('firewall.logs')</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="logs_table" class="table table-bordered">
                    <thead>
                    <tr>
                        <th>@lang('general.created_at')</th>
                        <th>@lang('ip_filter.ip_range')</th>
                        <th>@lang('menu.url')</th>
                        <th>@lang('firewall.reason')</th>
                        <th>@lang('sessions.user_agent')</th>
                        <th>@lang('firewall.request_data')</th>
                        <th>@lang('general.actions')</th>
                    </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.3/css/responsive.bootstrap5.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.3/js/dataTables.responsive.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.3/js/responsive.bootstrap5.js"></script>
    <script>
        $(document).ready(function () {
            $('#logs_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{route('admin.firewall.logs.data')}}',
                    method: 'POST',
                    data: {
                        _token: '{{csrf_token()}}'
                    }
                },
                columns: [
                    { data: 'created_at', name: 'created_at', orderable: true },
                    { data: 'ip', name: 'ip', orderable: true },
                    { data: 'url', name: 'url', orderable: true },
                    { data: 'user_agent', name: 'user_agent', orderable: false },
                    { data: 'reason', name: 'reason', orderable: true },
                    { data: 'request_data', name: 'request_data', orderable: false },
                    { data: 'actions', name: 'actions', searchable: false, orderable: false },
                ],
                order: false,
                ordering: true,
                responsive: {
                    details: {
                        display: DataTable.Responsive.display.modal({
                            header: function (row) {
                                let data = row.data();
                                console.log(data);
                                return data.ip;
                            }
                        }),
                        renderer: DataTable.Responsive.renderer.tableAll({
                            tableClass: 'table table-responsive',

                        })
                    }
                },
                pageLength: @if(session()->has('post_datatable_length')) {{session('post_datatable_length')}} @else 10 @endif,
                lengthMenu: [10, 25, 50, 75, 100],
                language: {
                    url: '{{config('app.url')}}/themes/panel/js/datatable/lang/{{session('language')}}.json'
                },
                headers: {
                    'X-CSRF-TOKEN': '{{csrf_token()}}',
                    'X-XSRF-TOKEN': '{{csrf_token()}}',
                },
                drawCallback: function() {
                    // Tooltip'u yeniden başlat
                    $('[data-bs-toggle="tooltip"]').tooltip();
                }
            });
        });

        function addToWhitelist(ip){
            Swal.fire(
                {
                    title: "@lang('firewall.add_to_whitelist')",
                    text: "@lang('firewall.add_to_whitelist_text')",
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonText: "@lang('general.yes')",
                    cancelButtonText: "@lang('general.no')",
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        return fetch('{{route('admin.firewall.whitelist')}}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{csrf_token()}}'
                            },
                            body: JSON.stringify({
                                ip: ip
                            })
                        })
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error(response.statusText);
                                }
                                let dt = $('#logs_table').DataTable();
                                dt.ajax.reload();
                                return response.json();
                            })
                            .catch(error => {
                                Swal.showValidationMessage(
                                    `Request failed: ${error}`
                                );
                            });
                    },
                }
            )
        }

        function deleteFromBlacklist(ip){
            Swal.fire(
                {
                    title: "@lang('firewall.remove_from_blocklist')",
                    text: "@lang('firewall.remove_from_blocklist_text')",
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonText: "@lang('general.yes')",
                    cancelButtonText: "@lang('general.no')",
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        return fetch('{{route('admin.firewall.delete')}}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{csrf_token()}}'
                            },
                            body: JSON.stringify({
                                ip: ip
                            })
                        })
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error(response.statusText);
                                }
                                let dt = $('#logs_table').DataTable();
                                dt.ajax.reload();
                                return response.json();
                            })
                            .catch(error => {
                                Swal.showValidationMessage(
                                    `Request failed: ${error}`
                                );
                            });
                    },
                }
            )
        }
    </script>
@endsection
