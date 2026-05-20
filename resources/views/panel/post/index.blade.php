@extends('panel.base')
@if($type == 'blogs')
    @section('title', __('post.blogs'))
@else
    @section('title', __('post.pages'))
@endif

@section('breadcrumb_link')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{route('admin.index')}}">@lang('dashboard.dashboard')</a></li>
        <li class="breadcrumb-item active">
            @if($type == 'blogs')
                @lang('post.blogs')
            @else
                @lang('post.pages')
            @endif
        </li>
    </ol>
@endsection
@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                @if($type == 'blogs')
                    @lang('post.blogs')
                @else
                    @lang('post.pages')
                @endif
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <ul class="nav nav-pills border-bottom mb-3">
                        @foreach(app('languages') as $language)
                            <li class="nav-item">
                                <a class="nav-link @if($language->code==request()->get('language')) active @endif "
                                   href="{{route('admin.posts', $type)}}?tab=contents&amp;language={{$language->code}}">
                                    {{$language->name}}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <ul class="tab-menu">
                        <!-- menu tabs start here -->
                        <li class="active-tab">
                            <a href="{{route('admin.posts', $type)}}?tab=contents&amp;language={{request()->get('language')}}">
                                <i class="fa-light fa-folder-grid"></i> İçerikler</a>
                        </li>
                        <li>
                            <a href="{{route('admin.posts', $type)}}?tab=trashed&amp;language={{request()->get('language')}}">
                                <i class="fa-light fa-trash-list"></i> Silinenler</a>
                        </li>
                    </ul>
                    <div class="tab_container">
                        <div class="tab_content table-responsive" id="contents">
                            <div class="mb-2">
                                <button id="bulk-index-btn" class="btn btn-sm btn-outline-success" disabled>
                                    <i class="fab fa-google"></i> Google'a Gönder (<span id="selected-count">0</span>)
                                </button>
                            </div>
                            <table id="posts-table" class="table table-striped" aria-describedby="contents">
                                <thead>
                                <tr>
                                    <th scope="col" style="width:30px"><input type="checkbox" id="select-all-posts"></th>
                                    <th scope="col">@lang('post.title')</th>
                                    @if($type == 'blogs')
                                    <th scope="col">@lang('post.category')</th>
                                    <th scope="col">@lang('post.views')</th>
                                    @endif
                                    <th scope="col" class="text-center">@lang('post.media')</th>
                                    <th scope="col" class="text-center">@lang('user.username')</th>
                                    <th scope="col" class="text-center">@lang('general.created_at')</th>
                                    <th scope="col" class="text-center">@lang('general.updated_at')</th>
                                    <th scope="col">@lang('general.actions')</th>
                                </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <th scope="col"></th>
                                    <th scope="col">@lang('post.title')</th>
                                    @if($type == 'blogs')
                                        <th scope="col">@lang('post.category')</th>
                                        <th scope="col">@lang('post.views')</th>
                                    @endif
                                    <th scope="col" class="text-center">@lang('post.media')</th>
                                    <th scope="col" class="text-center">@lang('user.username')</th>
                                    <th scope="col" class="text-center">@lang('general.created_at')</th>
                                    <th scope="col" class="text-center">@lang('general.updated_at')</th>
                                    <th scope="col">@lang('general.actions')</th>
                                </tr>
                                </tfoot>

                            </table>
                        </div>

                        <!-- Google Index History Modal -->
                        <div class="modal fade" id="indexHistoryModal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title"><i class="fab fa-google me-1"></i> Google Index Durumu</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body" id="index-history-body">
                                        <div class="text-center"><i class="fas fa-spinner fa-spin"></i> Yükleniyor...</div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" id="resend-index-btn" class="btn btn-success d-none">
                                            <i class="fab fa-google"></i> Tekrar Gönder
                                        </button>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab_content table-responsive" id="trashed">
                            <table class="table table-striped" aria-describedby="trashed">
                                <thead>
                                <tr>
                                    <th scope="col">@lang('post.title')</th>
                                    <th scope="col">@lang('post.category')</th>
                                    <th scope="col">@lang('general.created_at')</th>
                                    <th scope="col">@lang('general.deleted_at')</th>
                                    <th scope="col">@lang('general.actions')</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($trashed as $post)
                                    <tr>
                                        <td>
                                            <a href="{{route('admin.post.edit', [$type, $post])}}">
                                                {{stripslashes($post->title)}}
                                            </a>
                                        </td>
                                        <td>
                                            @foreach($post->categories as $category)
                                                <span class="badge badge-primary">
                                                    <a href="{{route('admin.post.category', [$type, $category->id])}}"
                                                       class="text-white">
                                                        {{stripslashes($category->name)}}
                                                    </a>
                                                </span>
                                            @endforeach
                                        </td>
                                        <td>{{$post->created_at}}</td>
                                        <td>{{$post->deleted_at}}</td>
                                        <td>
                                            <a href="javascript:RestoreBlog('{{$post->id}}')"
                                               class="btn btn-sm btn-success mx-1 my-2"
                                               data-bs-toggle="tooltip" data-bs-placement="top"
                                               title="@lang('general.restore')">
                                                <i class="fas fa-trash-restore"></i>
                                            </a>
                                            <a href="javascript:DeleteBlog('{{$post->id}}', true)"
                                               class="btn btn-sm btn-danger mx-1 my-2"
                                               data-bs-toggle="tooltip" data-bs-placement="top"
                                               title="@lang('general.delete')">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" style="text-align: center">@lang('post.no_posts_found')</td>
                                    </tr>
                                @endforelse
                                </tbody>
                                <tfoot>
                                <tr>
                                    <th scope="col">@lang('post.title')</th>
                                    <th scope="col">@lang('post.category')</th>
                                    <th scope="col">@lang('general.created_at')</th>
                                    <th scope="col">@lang('general.deleted_at')</th>
                                    <th scope="col">@lang('general.actions')</th>
                                </tr>
                                </tfoot>
                            </table>
                            {{$trashed->withQueryString()->links()}}
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <style>
        .tab-menu {
            list-style: none;
            margin:0;
            padding:0;
            border-bottom: 1px solid #999;
        }
        .tab-menu li {
            display: inline-block;
            padding: 10px;
        }
        .active-tab {
            box-shadow: inset -3px 0 8px -5px #111, inset 3px 0 8px -5px #111;
        }
        .active-tab a {
        }
    </style>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        function DeleteBlog(id, force = false){
            Swal.fire({
                title: '@lang('general.are_you_sure')',
                text: "@lang('general.you_wont_be_able_to_revert_this')",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '@lang('general.delete_confirm_yes')',
                cancelButtonText: '@lang('general.delete_confirm_no')',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{route('admin.posts', $type)}}/'+id+'/delete' + (force ? '/permanent' : ''),
                        type: 'POST',
                        data: {
                            _token: '{{csrf_token()}}'
                        },
                        success: function () {
                            Swal.fire(
                                '@lang('general.deleted')',
                                '@lang('post.post.success_delete')',
                                'success'
                            );
                            window.location.reload();
                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: xhr.responseJSON.message
                            });
                        }
                    });
                }
            }
            )
        }
        function RestoreBlog(id){
            Swal.fire({
                title: '@lang('general.are_you_sure')',
                text: "@lang('post.restore_sure')",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '@lang('general.restore_it')',
                cancelButtonText: '@lang('general.cancel')',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{route('admin.posts', ['type' => $type])}}/'+id+'/restore',
                        type: 'POST',
                        data: {
                            _token: '{{csrf_token()}}'
                        },
                        success: function () {
                            Swal.fire(
                                '@lang('general.restored')',
                                '@lang('')',
                                'success'
                            );
                            window.location.reload();
                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: xhr.responseJSON.message
                            });
                        }
                    });
                }
            });
        }
        function showQrModal(url, count) {
            Swal.fire({
                title: 'QR Kod',
                html: '<div id="modal-qr-code" style="display:flex;justify-content:center;margin-bottom:10px;"></div>' +
                      '<p style="margin:4px 0"><small class="text-muted">Okuma sayısı: <strong>' + count + '</strong></small></p>' +
                      '<p style="margin:4px 0;word-break:break-all"><small>' + url + '</small></p>',
                didOpen: function() {
                    new QRCode(document.getElementById('modal-qr-code'), {
                        text: url,
                        width: 200,
                        height: 200,
                        correctLevel: QRCode.CorrectLevel.H
                    });
                },
                showConfirmButton: false,
                showCloseButton: true
            });
        }

        $(document).ready(function(){
            $(document).on('click', '.qr-show-btn', function() {
                showQrModal($(this).data('qr-url'), $(this).data('scan-count'));
            });

            $(document).on('click', '.qr-generate-btn', function() {
                var postId = $(this).data('post-id');
                $.ajax({
                    url: '{{route('admin.posts', $type)}}/' + postId + '/qr/generate',
                    type: 'POST',
                    data: { _token: '{{csrf_token()}}' },
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'QR Kod oluşturuldu',
                                timer: 1200,
                                showConfirmButton: false
                            }).then(function() { window.location.reload(); });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Hata',
                            text: xhr.responseJSON ? xhr.responseJSON.message : 'Bir hata oluştu'
                        });
                    }
                });
            });
            @if(request()->get('tab') == 'trashed')
                $(".tab-menu li").removeClass("active-tab");
                $(".tab-menu li:nth-child(2)").addClass("active-tab");
                $(".tab_content").hide();
                $("#trashed").show();
            @else
                $(".tab-menu li").removeClass("active-tab");
                $(".tab-menu li:nth-child(1)").addClass("active-tab");
                $(".tab_content").hide();
                $("#contents").show();
            @endif


            var postsTable = $('#posts-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{!! $datatable_url !!}',
                    method: 'POST',
                    data: {
                        _token: '{{csrf_token()}}'
                    }
                },
                responsive: true,
                columns: [
                    { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false, className: 'text-center', width: '30px' },
                    { data: 'title', name: 'title' },
                    @if($type == 'blogs')
                    { data: 'categories', name: 'categories' },
                    { data: 'views', name: 'views', className: "text-center"},
                    @endif
                    { data: 'media', name: 'media', className: "text-center", orderable: false, searchable: false },
                    { data: 'user', name: 'user', className: "text-center" },
                    { data: 'created_at', name: 'created_at', className: "text-center" },
                    { data: 'updated_at', name: 'updated_at', className: "text-center" },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                order: [[@if($type == 'blogs') 6 @else 4 @endif, 'desc']],
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
                    $('[data-bs-toggle="tooltip"]').tooltip();
                    updateBulkButton();
                }
            });

            function updateBulkButton() {
                var count = $('.post-checkbox:checked').length;
                $('#selected-count').text(count);
                $('#bulk-index-btn').prop('disabled', count === 0);
            }

            $(document).on('change', '.post-checkbox', function() {
                updateBulkButton();
            });

            $('#select-all-posts').on('change', function() {
                var checked = $(this).is(':checked');
                postsTable.$('.post-checkbox').prop('checked', checked);
                updateBulkButton();
            });

            $('#bulk-index-btn').on('click', function() {
                var ids = [];
                postsTable.$('.post-checkbox:checked').each(function() {
                    ids.push($(this).val());
                });
                if (ids.length === 0) { return; }

                Swal.fire({
                    title: 'Google\'a Gönder',
                    text: ids.length + ' yazı Google\'a indexleme için gönderilecek. Zaten indexlenenler atlanır.',
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'Gönder',
                    cancelButtonText: '@lang('general.cancel')',
                }).then(function(result) {
                    if (!result.isConfirmed) { return; }
                    var formData = { _token: '{{csrf_token()}}' };
                    ids.forEach(function(id, i) { formData['post_ids[' + i + ']'] = id; });
                    $.ajax({
                        url: '{{route('admin.post.index.bulk', $type)}}',
                        type: 'POST',
                        data: formData,
                        success: function(res) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Kuyruğa Alındı',
                                html: '<b>' + res.queued + '</b> yazı kuyruğa alındı.<br><b>' + res.skipped + '</b> yazı zaten indexlendiği için atlandı.',
                            });
                            $('#select-all-posts').prop('checked', false);
                            postsTable.$('.post-checkbox').prop('checked', false);
                            updateBulkButton();
                        },
                        error: function(xhr) {
                            Swal.fire({ icon: 'error', title: 'Hata', text: xhr.responseJSON ? xhr.responseJSON.message : 'Bir hata oluştu' });
                        }
                    });
                });
            });

            var currentIndexPostId = null;
            var basePostUrl = '{{route('admin.posts', $type)}}';

            function fmtDate(str) {
                if (!str) { return ''; }
                var d = new Date(str);
                if (isNaN(d.getTime())) { return str; }
                var p = function(n) { return n.toString().padStart(2, '0'); };
                return p(d.getDate()) + '.' + p(d.getMonth() + 1) + '.' + d.getFullYear()
                    + ' ' + p(d.getHours()) + ':' + p(d.getMinutes()) + ':' + p(d.getSeconds());
            }

            function fmtMsg(msg) {
                if (!msg) { return ''; }
                try {
                    var obj = JSON.parse(msg);
                    if (obj.urlNotificationMetadata) {
                        var lu = obj.urlNotificationMetadata.latestUpdate;
                        return lu ? 'Bildirildi (' + fmtDate(lu.notifyTime) + ')' : 'URL bildirildi';
                    }
                    if (obj.error && obj.error.message) { return obj.error.message; }
                    return JSON.stringify(obj, null, 2);
                } catch (e) {
                    return msg.substring(0, 200);
                }
            }

            $(document).on('click', '.index-history-btn', function() {
                currentIndexPostId = $(this).data('post-id');
                $('#index-history-body').html('<div class="text-center py-3"><i class="fas fa-spinner fa-spin fa-lg text-primary"></i><div class="mt-2 text-muted small">Google sorgulanıyor...</div></div>');
                $('#resend-index-btn').addClass('d-none');
                var modal = new bootstrap.Modal(document.getElementById('indexHistoryModal'));
                modal.show();

                $.when(
                    $.get(basePostUrl + '/' + currentIndexPostId + '/index/status'),
                    $.get(basePostUrl + '/' + currentIndexPostId + '/index/history')
                ).done(function(statusRes, historyRes) {
                    var status = statusRes[0];
                    var logs = historyRes[0];
                    var html = '';

                    if (!status.error) {
                        var icon = status.indexed
                            ? '<i class="fas fa-check-circle me-2"></i>'
                            : '<i class="fas fa-clock me-2"></i>';
                        var label = status.indexed ? 'İndexlenmiş' : 'İndexlenmemiş';
                        var cardColor = status.indexed ? 'border-success' : 'border-warning';
                        var textColor = status.indexed ? 'text-success' : 'text-warning';
                        html += '<div class="card ' + cardColor + ' mb-3" style="border-width:2px">';
                        html += '<div class="card-body py-2 px-3">';
                        html += '<span class="fw-bold ' + textColor + '">' + icon + label + '</span>';
                        html += '<span class="text-muted ms-2">— ' + status.coverage_state + '</span>';
                        if (status.from_cache) {
                            html += '<span class="badge bg-secondary ms-2" style="font-size:0.7rem">önbellekten</span>';
                        }
                        if (status.last_crawl_time) {
                            html += '<div class="mt-1"><small class="text-muted"><i class="fas fa-spider me-1"></i>Son tarama: ' + fmtDate(status.last_crawl_time) + '</small></div>';
                        }
                        if (status.cached_at) {
                            html += '<div><small class="text-muted"><i class="fas fa-database me-1"></i>Kaydedildi: ' + fmtDate(status.cached_at) + '</small></div>';
                        }
                        html += '</div></div>';
                        if (!status.indexed) {
                            $('#resend-index-btn').removeClass('d-none');
                        }
                    } else {
                        html += '<div class="card border-danger mb-3" style="border-width:2px"><div class="card-body py-2 px-3">';
                        html += '<span class="fw-bold text-danger"><i class="fas fa-times-circle me-2"></i>Durum alınamadı</span>';
                        html += '<div class="mt-1"><small class="text-muted">' + status.coverage_state + '</small></div>';
                        html += '</div></div>';
                        $('#resend-index-btn').removeClass('d-none');
                    }

                    if (logs.length > 0) {
                        html += '<div class="small fw-bold text-muted mb-1 mt-2"><i class="fas fa-history me-1"></i>Gönderim Geçmişi</div>';
                        html += '<div class="table-responsive"><table class="table table-sm table-bordered mb-0">';
                        html += '<thead class="table-light"><tr><th>Tarih</th><th>Tip</th><th>Durum</th><th>Kod</th><th>Mesaj</th></tr></thead><tbody>';
                        logs.forEach(function(log) {
                            var statusBg = log.status === 'success' ? 'bg-success' : 'bg-danger';
                            var typeBg = log.type === 'URL_INSPECTED' ? 'bg-info' : 'bg-secondary';
                            html += '<tr>';
                            html += '<td style="white-space:nowrap" class="small">' + fmtDate(log.created_at) + '</td>';
                            html += '<td><span class="badge ' + typeBg + ' text-white">' + log.type + '</span></td>';
                            html += '<td><span class="badge ' + statusBg + ' text-white">' + log.status + '</span></td>';
                            html += '<td class="small">' + (log.response_code || '') + '</td>';
                            html += '<td><small class="text-muted">' + fmtMsg(log.message) + '</small></td>';
                            html += '</tr>';
                        });
                        html += '</tbody></table></div>';
                    } else {
                        html += '<p class="text-muted mb-0 small"><i class="fas fa-info-circle me-1"></i>Henüz gönderim geçmişi yok.</p>';
                    }

                    $('#index-history-body').html(html);
                }).fail(function() {
                    $('#index-history-body').html('<p class="text-danger"><i class="fas fa-times me-1"></i>Bilgiler yüklenemedi.</p>');
                });
            });

            $('#resend-index-btn').on('click', function() {
                if (!currentIndexPostId) { return; }
                $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Gönderiliyor...');
                $.ajax({
                    url: basePostUrl + '/' + currentIndexPostId + '/index',
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{csrf_token()}}' },
                    success: function() {
                        Swal.fire({ icon: 'success', title: 'Kuyruğa Alındı', text: 'Indexleme isteği gönderildi.', timer: 2000, showConfirmButton: false });
                        $('#resend-index-btn').prop('disabled', false).html('<i class="fab fa-google"></i> Tekrar Gönder');
                    },
                    error: function(xhr) {
                        Swal.fire({ icon: 'error', title: 'Hata', text: xhr.responseJSON ? xhr.responseJSON.message : 'Bir hata oluştu' });
                        $('#resend-index-btn').prop('disabled', false).html('<i class="fab fa-google"></i> Tekrar Gönder');
                    }
                });
            });
        });
    </script>
@endsection
