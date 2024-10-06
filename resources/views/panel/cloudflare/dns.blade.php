@extends('panel.base')
@section('title', 'Cloudflare DNS')
@section('breadcrumb_link')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{route('admin.index')}}">@lang('dashboard.dashboard')</a></li>
        <li class="breadcrumb-item">Cloudflare</li>
        <li class="breadcrumb-item active">DNS</li>
    </ol>
@endsection
@section('content')
    <div class="row justify-content-center">
        <div class="col-11">
            <div class="card">
                <div class="card-header d-flex">
                    <h3 class="card-title">{{__('DNS')}}</h3>
                    <div class="ms-auto">
                        <a class="btn btn-primary" href="javascript:DNSCreateModal()"><i class="fa-solid fa-square-plus"></i></a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">

                        <div class="col-9" id="datatable-error-message"></div>
                        <hr class="mt-1">
                        <table class="table table-striped table-hover" id="datatable" style="width: 100%">
                            <thead>
                            <tr>
                                <th>@lang('cloudflare.proxied')</th>
                                <th>@lang('cloudflare.type')</th>
                                <th>@lang('cloudflare.name')</th>
                                <th>@lang('cloudflare.content')</th>
                                <th>@lang('cloudflare.ttl')</th>
                                <th><i class="fa-solid fa-gears"></i></th>
                            </tr>
                            </thead>

                            <tfoot>
                            <tr>
                                <th>@lang('cloudflare.proxied')</th>
                                <th>@lang('cloudflare.type')</th>
                                <th>@lang('cloudflare.name')</th>
                                <th>@lang('cloudflare.content')</th>
                                <th>@lang('cloudflare.ttl')</th>
                                <th><i class="fa-solid fa-gears"></i></th>
                            </tr>
                            </tfoot>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="dnsAddEditModal"  tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal_title">{{__('cloudflare.add_record')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="row" id="dnsAddEditForm" action="javascript:void(0)" method="post">
                        <div class="col-12 mb-1">
                            <label for="type">{{__('cloudflare.type')}}</label>
                            <select class="form-select" name="record_type" id="record_type">
                                <option value="A">A</option>
                                <option value="AAAA">AAAA</option>
                                <option value="CNAME">CNAME</option>
                                <option value="MX">MX</option>
                                <option value="TXT">TXT</option>
                                <option value="SRV">SRV</option>
                                <option value="HTTPS">HTTPS</option>
                                <option value="CAA">CAA</option>
                            </select>
                        </div>
                        <div class="col-12 mb-1">
                            <label for="name">{{__('cloudflare.name')}}</label>
                            <input type="text" class="form-control" name="name" id="name" placeholder="{{__('cloudflare.name')}}">
                        </div>
                        <div class="col-12 mb-1">
                            <div class="row" id="content_html">

                            </div>
                        </div>
                        <div class="col-6 mb-1">
                            <label for="ttl">{{__('cloudflare.ttl')}}</label>
                            <input type="number" class="form-control" name="ttl" id="ttl" placeholder="{{__('cloudflare.ttl')}}" value="1">
                        </div>
                        <div class="col-6 mb-1" id="proxied"></div>
                        <input type="hidden" name="dns_id" id="dns_id">
                        <input type="hidden" name="type" id="type">
                        @csrf
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('general.close')</button>
                    <button type="submit" class="btn btn-primary" onclick="$('#dnsAddEditForm').submit()">{{__('general.save')}}</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="dnsDeleteModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal_title">{{__('cloudflare.delete_record')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="row" id="dnsDeleteForm" action="javascript:void(0)" method="post">
                        <div class="col-12 mb-1">
                            <p>{{__('cloudflare.delete_warning')}}</p>
                            <span id="dns_record"></span>
                        </div>
                        <input type="hidden" name="dns_id" id="dns_id_delete">
                        @csrf
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('general.close')</button>
                    <button type="submit" class="btn btn-danger" onclick="$('#dnsDeleteForm').submit()">{{__('general.delete')}}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <style>
        .cloud {
            border: 1px solid transparent;
            display: block;
            height: 35px;
        }
        .wordbreak{
            -ms-word-break: break-all;
            word-break: break-all;
        }
    </style>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.2.0/css/fixedHeader.dataTables.min.css" />
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.4.0/css/responsive.dataTables.min.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/fixedheader/3.3.1/css/fixedHeader.dataTables.min.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/rowreorder/1.3.1/css/rowReorder.dataTables.min.css"/>




    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/fixedheader/3.2.0/js/dataTables.fixedHeader.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.0/js/dataTables.responsive.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/fixedheader/3.3.1/js/dataTables.fixedHeader.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/rowreorder/1.3.1/js/dataTables.rowReorder.min.js"></script>

    <script>
        function DNSCreateModal() {
            $('#dnsAddEditForm').trigger("reset");
            $('#modal_title').text('{{__('cloudflare.add_record')}}');
            $('button[type="submit"]').text('{{__('general.save')}}');
            $('#type').val('add');
            $('#dnsAddEditModal').modal('show');
        }
        $(document).ready(function () {
            let proxied_html = '<label for="status">{{__('cloudflare.proxied')}}</label>' +
                '<select class="form-select" name="status" id="status">' +
                '<option value="1">{{__('general.active')}}</option>' +
                '<option value="0">{{__('general.passive')}}</option>' +
                '</select>';
            let dns_type = $('#record_type');
            let proxied = $('#proxied');
            let content_html = $('#content_html');
            let content_a_aaa_cname = '<div class="col-12">' +
                '<label for="content">{{__('cloudflare.content')}}</label>' +
                '<input type="text" class="form-control" name="content" id="content" placeholder="{{__('cloudflare.content')}}">' +
                '</div>';
            let content_txt = '<div class="col-12">' +
                '<label for="content">{{__('cloudflare.content')}}</label>' +
                '<textarea class="form-control" name="cloudflare.content" id="content" placeholder="{{__('cloudflare.content')}}"></textarea>' +
                '</div>';
            let content_mx = '<div class="col-6 mb-1">' +
                '<label for="priority">{{__('cloudflare.priority')}}</label>' +
                '<input type="number" class="form-control" name="priority" id="priority" placeholder="{{__('cloudflare.priority')}}">' +
                '</div>' +
                '<div class="col-6 mb-1">' +
                '<label for="content">{{__('cloudflare.content')}}</label>' +
                '<input type="text" class="form-control" name="content" id="content" placeholder="{{__('cloudflare.content')}}">' +
                '</div>';
            let content_caa = '<div class="col-6 mb-1">' +
                '<label for="flags">{{__('cloudflare.flags')}}</label>' +
                '<input type="number" class="form-control" name="flags" id="flags" placeholder="{{__('cloudflare.flags')}}">' +
                '</div>' +
                '<div class="col-6 mb-1">' +
                '<label for="tag">{{__('cloudflare.tag')}}</label>' +
                '<input type="text" class="form-control" name="tag" id="tag" placeholder="{{__('cloudflare.tag')}}">' +
                '</div>' +
                '<div class="col-12 mb-1">' +
                '<label for="value">{{__('cloudflare.content')}}</label>' +
                '<input type="text" class="form-control" name="content" id="content" placeholder="{{__('cloudflare.content')}}">' +
                '</div>';
            let content_srv = '<div class="col-4 mb-1">' +
                '<label for="priority">{{__('cloudflare.priority')}}</label>' +
                '<input type="number" class="form-control" name="priority" id="priority" placeholder="{{__('cloudflare.priority')}}">' +
                '</div>' +
                '<div class="col-4 mb-1">' +
                '<label for="weight">{{__('cloudflare.weight')}}</label>' +
                '<input type="number" class="form-control" name="weight" id="weight" placeholder="{{__('cloudflare.weight')}}">' +
                '</div>' +
                '<div class="col-4 mb-1">' +
                '<label for="port">{{__('cloudflare.port')}}</label>' +
                '<input type="number" class="form-control" name="port" id="port" placeholder="{{__('cloudflare.port')}}">' +
                '</div>' +
                '<div class="col-6 mb-1">' +
                '<label for="value">{{__('cloudflare.service')}}</label>' +
                '<input type="text" class="form-control" name="service" id="service" placeholder="{{__('cloudflare.service')}}">' +
                '</div>' +
                '<div class="col-6 mb-1">' +
                '<label for="protocol">{{__('cloudflare.protocol')}}</label>' +
                '<select class="form-select" name="protocol" id="protocol">' +
                '<option value="_tls">TLS</option>' +
                '<option value="_tcp">TCP</option>' +
                '<option value="_udp">UDP</option>' +
                '</select>' +
                '</div>' +
                '<div class="col-12 mb-1">' +
                '<label for="target">{{__('cloudflare.target')}}</label>' +
                '<input type="text" class="form-control" name="target" id="target" placeholder="{{__('cloudflare.target')}}">' +
                '</div>';
            let content_https = '<div class="col-6 mb-1">' +
                '<label for="priority">{{__('cloudflare.priority')}}</label>' +
                '<input type="number" class="form-control" name="priority" id="priority" placeholder="{{__('cloudflare.priority')}}">' +
                '</div>' +
                '<div class="col-6 mb-1">' +
                '<label for="target">{{__('cloudflare.target')}}</label>' +
                '<input type="text" class="form-control" name="target" id="target" placeholder="{{__('cloudflare.target')}}">' +
                '</div>' +
                '<div class="col-12 mb-1">' +
                '<label for="content">{{__('cloudflare.content')}}</label>' +
                '<input type="text" class="form-control" name="content" id="content" placeholder="{{__('cloudflare.content')}}">' +
                '</div>';
            if(dns_type.val()==='A' || dns_type.val()==='AAAA' || dns_type.val()==='CNAME'){
                proxied.html(proxied_html);
                content_html.html(content_a_aaa_cname);
            }
            else if(dns_type.val()==='TXT'){
                content_html.html(content_txt);
                proxied.html('');
            }
            else if(dns_type.val()==='MX'){
                content_html.html(content_mx);
                proxied.html('');
            }
            else if(dns_type.val()==='CAA'){
                content_html.html(content_caa);
                proxied.html('');
            }
            else if(dns_type.val()==='SRV'){
                content_html.html(content_srv);
                proxied.html('');
            }
            else if(dns_type.val()==='HTTPS'){
                content_html.html(content_https);
                proxied.html('');
            }
            else{
                proxied.html('');
                content_html.html('');
            }
            dns_type.on('change', function () {
                if (dns_type.val() === 'A' || dns_type.val() === 'AAAA' || dns_type.val() === 'CNAME') {
                    proxied.html(proxied_html);
                    content_html.html(content_a_aaa_cname);
                }
                else if(dns_type.val()==='TXT'){
                    content_html.html(content_txt);
                    proxied.html('');
                }
                else if(dns_type.val()==='MX'){
                    content_html.html(content_mx);
                    proxied.html('');
                }
                else if(dns_type.val()==='CAA'){
                    content_html.html(content_caa);
                    proxied.html('');
                }
                else if(dns_type.val()==='SRV'){
                    content_html.html(content_srv);
                    proxied.html('');
                }
                else if(dns_type.val()==='HTTPS'){
                    content_html.html(content_https);
                    proxied.html('');
                }
                else {
                    proxied.html('');
                    content_html.html('');
                }
            });
            let datatable = $('#datatable');
            $.fn.dataTable.ext.errMode = 'none';
            datatable.DataTable( {
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{route('cf.dns')}}",
                    type: "POST",
                    data: {
                        _token: "{{csrf_token()}}"
                    }
                },
                columns: [
                    { data: "status", width: '55px' },
                    { data: "type", width: '75px' },
                    { data: "name", width: '25%' },
                    { data: "content", width: '35%' },
                    { data: "ttl", width: '15%' },
                    { data: "action", width: '75px' }
                ],
                autoWidth: false,
                select: true,
                responsive: {
                    breakpoints: [
                        { name: 'desktop', width: Infinity },
                        { name: 'tablet',  width: 1024 },
                        { name: 'phone',   width: 480 }
                    ],
                },
                fixedHeader: {
                    header: false,
                    footer: false
                },
                columnDefs: [
                    { className: "wordbreak", "targets": [2, 3] }
                ],
                paging: false,
                language: {
                    url: '{{config('app.url')}}/themes/panel/js/datatable/lang/{{session('language')}}.json'
                }
            } ).on( 'error.dt', function ( e, settings, techNote, message ) {
                console.log(settings.jqXHR.responseJSON.error);
                $('#datatable-error-message').html('<h3 class="text-danger">' + settings.jqXHR.responseJSON.error + '</h3>');
                toastr.error(settings.jqXHR.responseJSON.error);
            } );

            let row;
            if(window.matchMedia("(max-width: 782px)").matches){
                row = "li";
            } else{
                row = "tr";
            }

            datatable.on('click', 'a.delete',function () {
                let table = datatable.DataTable();
                let data = table.row($(this).parents(row)).data();
                $('#dns_record').text(data.name + '  ' + data.type + '  ' + data.content);
                $('#dns_id_delete').val(data.id);
                $('button[type="submit"]').text('{{__('general.delete')}}');
                $('#dnsDeleteModal').modal('show');
            });

            datatable.on('click', 'a.edit',function () {
                let table = datatable.DataTable();
                let data = table.row($(this).parents(row)).data();
                console.log(data);

                $('#dnsAddEditForm').trigger("reset");

                dns_type.val(data.type);

                if (data.type === 'A' || data.type === 'AAAA' || data.type === 'CNAME') {
                    proxied.html(proxied_html);
                    content_html.html(content_a_aaa_cname);
                    if(data.proxied===true){
                        //proxied.find('input').prop('checked', true);
                        $('#status').val(1);
                    }
                    else{
                        //proxied.find('input').prop('checked', false);
                        $('#status').val(0);
                    }

                    $('#name').val(data.name);
                    $('#content').val(data.content);
                }
                else if(data.type==='TXT'){
                    content_html.html(content_txt);
                    proxied.html('');
                    $('#name').val(data.name);
                    $('#content').val(data.content);
                }
                else if(data.type==='MX'){
                    content_html.html(content_mx);
                    proxied.html('');
                    $('#name').val(data.name);
                    $('#content').val(data.all_data.content);
                    $('#priority').val(data.all_data.priority);
                }
                else if(data.type==='CAA'){
                    content_html.html(content_caa);
                    proxied.html('');
                    $('#name').val(data.name);
                    $('#content').val(data.all_data.data.value);
                    $('#tag').val(data.all_data.data.tag);
                    $('#flags').val(data.all_data.data.flags);
                }
                else if(data.type==='SRV'){
                    content_html.html(content_srv);
                    proxied.html('');
                    $('#name').val(data.all_data.data.name);
                    $('#target').val(data.all_data.data.target);
                    $('#priority').val(data.all_data.data.priority);
                    $('#weight').val(data.all_data.data.weight);
                    $('#port').val(data.all_data.data.port);
                    $('#service').val(data.all_data.data.service);
                    $('#protocol').val(data.all_data.data.proto);

                }
                else if(data.type==='HTTPS'){
                    content_html.html(content_https);
                    proxied.html('');
                    $('#name').val(data.name);
                    $('#priority').val(data.all_data.data.priority);
                    $('#target').val(data.all_data.data.target);
                    $('#content').val(data.all_data.data.value);
                }
                else {
                    proxied.html('');
                    content_html.html('');
                }

                $('#dns_id').val(data.id);
                $('#type').val('edit');

                $('#modal_title').text('{{__('cloudflare.edit_record')}}');
                $('button[type="submit"]').text('{{__('general.save')}}');
                $('#dnsAddEditModal').modal('show');
            });

            $('#dnsAddEditForm').on('submit', function () {
                $.ajax({
                    url: '{{route('cf.dns.save')}}',
                    method: 'POST',
                    data: $('#dnsAddEditForm').serialize(),
                    success: function (response) {
                        if (response.status === 'success') {
                            $('#dnsAddEditModal').modal('hide');
                            datatable.DataTable().ajax.reload();
                            toastr.success(response.message);
                        }
                        else {
                            toastr.error(response.message);
                        }
                    },
                    error: function (error) {
                        console.log(error);
                        toastr.error(error.responseJSON.message);
                    }
                });
            });

            $('#dnsDeleteModal').on('submit', function (){
                $.ajax({
                    url: '{{route('cf.dns.delete')}}',
                    method: 'POST',
                    data: $('#dnsDeleteForm').serialize(),
                    success: function (response) {
                        if (response.status === 'success') {
                            $('#dnsDeleteModal').modal('hide');
                            datatable.DataTable().ajax.reload();
                            toastr.success(response.message);
                        }
                        else {
                            toastr.error(response.message);
                        }
                    },
                    error: function (error) {
                        console.log(error);
                        toastr.error(error.responseJSON.message);
                    }
                });
            });
        });
    </script>
@endsection
