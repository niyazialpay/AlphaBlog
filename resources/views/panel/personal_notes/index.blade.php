@extends('panel.base')
@section('title', 'Personal Notes')

@section('breadcrumb_link')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{route('admin.index')}}">@lang('dashboard.dashboard')</a></li>
        <li class="breadcrumb-item active">
            Notes
        </li>
    </ol>
@endsection
@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                Notes
            </h3>
        </div>
        <div class="card-body">
            <table>
                <thead>
                <tr>
                    <th scope="col">Title</th>
                    <th scope="col" class="text-center">Created At</th>
                    <th scope="col" class="text-center">Updated At</th>
                    <th scope="col">Actions</th>
                </thead>
                <tbody>
                <tr>

                </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
