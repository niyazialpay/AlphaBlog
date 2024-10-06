@extends('panel.base')
@section('title','Cloudflare')
@section('breadcrumb_link')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{route('admin.index')}}">@lang('dashboard.dashboard')</a></li>
        <li class="breadcrumb-item active">Cloudflare</li>
    </ol>
@endsection
@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Cloudflare</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-sm-12 col-md-4">
                    <strong>Nameservers</strong>
                    <ul>
                        @foreach($cloudflare->result[0]->name_servers as $name_server)
                            <li>{{$name_server}}</li>
                        @endforeach
                    </ul>
                </div>
                <div class="col-sm-12 col-md-8">
                    <table>
                        <tr>
                            <td>
                                <strong>@lang('language.status')</strong>
                            </td>
                            <td>:</td>
                            <td>
                                {{ $cloudflare->result[0]->status == 'active'? __('general.active') : __('general.passive') }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>@lang('cloudflare.paused')</strong>
                            </td>
                            <td>:</td>
                            <td>
                                {{ $cloudflare->result[0]->paused? __('general.yes') : __('general.no') }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>@lang('cloudflare.development_mode')</strong>
                            </td>
                            <td>:</td>
                            <td id="development_mode_status">
                                {{ $cloudflare->result[0]->development_mode>0? __('Active') : __('Passive') }}
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-12">
                    <a href="javascript:toggleDevelopment()" class="btn btn-primary" id="development_mode">
                        @if($cloudflare->result[0]->development_mode<0)
                            @lang('cloudflare.development_mode_active')
                        @else
                            @lang('cloudflare.development_mode_passive')
                        @endif
                    </a>
                    <a href="javascript:cacheClear()" class="btn btn-primary mx-3">@lang('cache.clear_cache')</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        function cacheClear(){
            $.ajax({
                url: '{{route('admin.cloudflare.cache.clear')}}',
                data: {
                    _token: '{{csrf_token()}}'
                },
                type: 'POST',
                success: function (response) {
                    if(response.status){
                        swal.fire({
                            title: '@lang('general.success')',
                            text: response.message,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        });
                    }else{
                        swal.fire({
                            title: 'Error',
                            text: response.message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                }
            });
        }

        function toggleDevelopment(){
            $.ajax({
                url: '{{route('admin.cloudflare.toggle.development')}}',
                data: {
                    _token: '{{csrf_token()}}'
                },
                type: 'POST',
                success: function (response) {
                    if(response.status){
                        swal.fire({
                            title: '@lang('general.success')',
                            text: response.message,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        });
                        if(response.mode) {
                            $('#development_mode').text('@lang('cloudflare.development_mode_passive')');
                            $('#development_mode_status').text('@lang('cloudflare.active')');
                        }
                        else {
                            $('#development_mode').text('@lang('cloudflare.development_mode_active')');
                            $('#development_mode_status').text('@lang('cloudflare.passive')');
                        }
                    }else{
                        swal.fire({
                            title: 'Error',
                            text: response.message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                }
            });
        }
    </script>
@endsection
