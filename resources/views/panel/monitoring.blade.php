@extends('panel.base')
@section('title', $title)
@section('breadcrumb_link')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{route('admin.index')}}">@lang('dashboard.dashboard')</a></li>
        <li class="breadcrumb-item active">{{$title}}</li>
    </ol>
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-11 col-md-11 col-lg-11 container py-2 px-4">
            <div class="card">
                <div class="card-body">
                    <iframe id="monitoring" src="{{$iframe_url}}" style="width: 100%; height: calc(100dvh - 325px); border: none;"></iframe>
                </div>
            </div>
        </div>
    </div>
@endsection

