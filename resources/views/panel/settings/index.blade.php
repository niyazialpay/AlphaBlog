@extends('panel.base')
@section('title',__('settings.settings'))
@section('breadcrumb_link')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{route('admin.index')}}">@lang('dashboard.dashboard')</a></li>
        <li class="breadcrumb-item active">@lang('settings.settings')</li>
    </ol>
@endsection
@section('content')
    <div class="card">
        <div class="card-header d-flex">
            <h3 class="card-title">@lang('settings.settings')</h3>
        </div>
        <div class="card-body">
            <div class="row">
            </div>
        </div>
    </div>
@endsection
