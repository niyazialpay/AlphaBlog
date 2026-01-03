@extends('panel.base')
@section('title', __('search.searched_words'))

@section('breadcrumb_link')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item">
            <a href="{{route('admin.index')}}">@lang('dashboard.dashboard')</a>
        </li>
        <li class="breadcrumb-item active">
            @lang('search.searched_words')
        </li>
    </ol>
@endsection
@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">@lang('search.searched_words')</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <div class="my-3">
                    <form method="post" id="searchForm" action="javascript:void(0)">
                        <div class="input-group mb-3">
                            <input class="form-control form-control-navbar" type="search" name="search"
                                   placeholder="@lang('general.search')" aria-label="@lang('general.search')"
                                   value="{{GetPost(request()->search)}}">
                            <div class="input-group-append">
                                <button class="btn btn-navbar search-button" type="submit">
                                    <i class=" @if(config('settings.fontawesome_pro')) fa-duotone @else fa-solid @endif fa-magnifying-glass"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <table class="table table table-striped">
                    <tr>
                        <th>
                            @lang('search.searched_words')
                        </th>
                        <th>
                            @lang('search.search_date')
                        </th>
                        <th>
                            @lang('search.search_ip_and_browser')
                        </th>
                        <th>
                            @lang('general.actions')
                        </th>
                    </tr>
                    @foreach($search as $item)
                        <tr>
                            <td>
                                @if($item->checked)
                                    {{$item->search}}
                                @else
                                    <strong>{{$item->search}}</strong>
                                @endif
                            </td>
                            <td>
                                {{dateformat($item->created_at, 'd.m.Y H:i:s')}}
                            </td>
                            <td>
                                <small>{{$browser::platformName($item->user_agent)}}</small>
                                <br>
                                <small>{{$browser::browserName($item->user_agent)}}</small>
                                <br>
                                <small>{{$item->ip}}</small>
                            </td>
                            <td>
                                <a href="javascript:think('{{$item->id}}')"
                                   id="word-{{$item->id}}"
                                   style="font-size:20px;"
                                   data-bs-toggle="tooltip"
                                   data-bs-placement="left"
                                   data-bs-title="@if($item->think) @lang('search.thinking') @else @lang('search.think') @endif ">
                                    @if($item->think)
                                        <i class=" @if(config('settings.fontawesome_pro')) fa-duotone @else fa-solid @endif fa-face-monocle"></i>
                                    @else
                                        <i class=" @if(config('settings.fontawesome_pro')) fa-duotone @else fa-solid @endif fa-face-thinking"></i>
                                    @endif
                                </a>

                                <a href="javascript:deleteWord('{{$item->id}}')" class="text-danger"
                                   style="font-size:20px;"
                                   data-bs-toggle="tooltip"
                                   data-bs-placement="right"
                                   data-bs-title="@lang('general.delete')">
                                    <i class=" @if(config('settings.fontawesome_pro')) fa-duotone @else fa-solid @endif fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
            <div class="row">
                <div class="col-6">
                    {{$search->withQueryString()->links()}}
                </div>
                <div class="col-6 text-right">
                    <strong>@lang('search.total'):</strong> {{$search->total()}}
                </div>
            </div>
        </div>
        <div class="card-footer text-right">
            <a href="javascript:deleteAll()" class="btn btn-danger">
                <i class=" @if(config('settings.fontawesome_pro')) fa-duotone @else fa-solid @endif fa-trash"></i>
                @lang('search.delete.delete_all')
            </a>
            <a href="javascript:deleteNotInterested()" class="btn btn-danger">
                <i class=" @if(config('settings.fontawesome_pro')) fa-duotone @else fa-solid @endif fa-trash"></i>
                @lang('search.delete.delete_not_think')
            </a>
        </div>
    </div>
@endsection
@section('script')
    <script>
        function think(id){
            $.ajax({
                url: '{{route('admin.search.think')}}/' + id,
                type: 'post',
                data: {
                    _token: '{{csrf_token()}}',
                },
                success: function(data){
                    if(data.status){
                        let word = $('#word-' + id);
                        if(data.think) {
                            word
                                .html('<i class=" @if(config('settings.fontawesome_pro')) fa-duotone @else fa-solid @endif fa-face-monocle"></i>')
                                .attr('data-bs-title', '@lang('search.thinking')');
                            const tooltip = bootstrap.Tooltip.getInstance('#word-' + id);
                            tooltip.setContent({ '.tooltip-inner': '@lang('search.thinking')' });
                        }
                        else{
                            word
                                .html('<i class=" @if(config('settings.fontawesome_pro')) fa-duotone @else fa-solid @endif fa-face-thinking"></i>')
                                .attr('data-bs-title', '@lang('search.think')');
                            const tooltip = bootstrap.Tooltip.getInstance('#word-' + id);
                            tooltip.setContent({ '.tooltip-inner': '@lang('search.think')' });
                        }
                        Swal.fire({
                            icon: 'success',
                            title: data.message,
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }else{
                        Swal.fire({
                            icon: 'error',
                            title: data.message,
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }
                }
            });
        }
        function deleteWord(id){
            Swal.fire({
                title: '@lang('general.are_you_sure')',
                text: '@lang('search.delete.warning')',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '@lang('search.delete.yes_delete')',
                cancelButtonText: '@lang('general.cancel')'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{route('admin.search.delete')}}/' + id,
                        type: 'post',
                        data: {
                            _token: '{{csrf_token()}}',
                        },
                        success: function(data){
                            if(data.status){
                                $('#word-' + id).parent().parent().remove();
                                Swal.fire({
                                    icon: 'success',
                                    title: '@lang('search.delete.success')',
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                            }else{
                                Swal.fire({
                                    icon: 'error',
                                    title: '@lang('search.delete.error')',
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                            }
                        }
                    });
                }
            });
        }
        function deleteAll(){
            Swal.fire({
                title: '@lang('general.are_you_sure')',
                text: '@lang('search.delete.delete_all_warning')',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '@lang('search.delete.yes_delete')',
                cancelButtonText: '@lang('general.cancel')'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{route('admin.search.delete.all')}}',
                        type: 'post',
                        data: {
                            _token: '{{csrf_token()}}',
                        },
                        success: function(data){
                            if(data.status){
                                Swal.fire({
                                    icon: 'success',
                                    title: '@lang('search.delete.success')',
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                            }else{
                                Swal.fire({
                                    icon: 'error',
                                    title: '@lang('search.delete.error')',
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                            }
                            window.location.reload();
                        }
                    });
                }
            });
        }
        function deleteNotInterested(){
            Swal.fire({
                title: '@lang('general.are_you_sure')',
                text: '@lang('search.delete.delete_all_warning')',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '@lang('search.delete.yes_delete')',
                cancelButtonText: '@lang('general.cancel')'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{route('admin.search.delete.not-interested')}}',
                        type: 'post',
                        data: {
                            _token: '{{csrf_token()}}',
                        },
                        success: function(data){
                            if(data.status){
                                Swal.fire({
                                    icon: 'success',
                                    title: '@lang('search.delete.success')',
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                            }else{
                                Swal.fire({
                                    icon: 'error',
                                    title: '@lang('search.delete.error')',
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                            }
                            window.location.reload();
                        }
                    });
                }
            });
        }
        $(document).ready(function(){
            $('#searchForm').submit(function(){
                let search = $(this).find('input[name="search"]').val();
                window.location.href = '{{route('admin.search.index')}}?search='+search;
            });

            $.ajax({
                url: '{!! request()->getRequestUri() !!}',
                type: 'post',
                data: {
                    _token: '{{csrf_token()}}',
                },
                success: function(data){
                    console.log(data);
                }
            });
        });
    </script>
@endsection
