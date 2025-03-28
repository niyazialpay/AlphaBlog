@extends('panel.base')
@section('title', __('general.about'))
@section('breadcrumb_link')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{route('admin.index')}}">@lang('dashboard.dashboard')</a></li>
        <li class="breadcrumb-item active">@lang('general.about')</li>
    </ol>
@endsection
@section('content')
    @can('admin', 'App\Models\User')
        <div class="row justify-content-center">
            <div class="col-sm-8">
                <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th style="width: 30%">@lang('general.feature')</th>
                        <th style="width: 70%">@lang('general.version_info')</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($systemInfo as $key => $value)
                        <tr>
                            <td>{{ $key }}</td>
                            <td>{!! $value !!}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endcan
@endsection
