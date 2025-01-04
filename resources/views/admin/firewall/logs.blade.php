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
        </div>
    </div>
@endsection
