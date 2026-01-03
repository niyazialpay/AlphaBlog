@extends('panel.base')
@section('title',__('menu.menu'))
@section('breadcrumb_link')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{route('admin.index')}}">@lang('dashboard.dashboard')</a></li>
        <li class="breadcrumb-item active">@lang('menu.menu')</li>
    </ol>
@endsection
@section('content')
    <div class="card">
        <div class="card-header d-flex">
            <h3 class="card-title">@lang('menu.menu')</h3>
            <div class="ml-auto">
                <a href="{{route('admin.menu.index')}}" class="btn btn-sm btn-outline-primary">
                    <i class="fa-solid fa-plus"></i>
                    @lang('menu.add_menu')
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-5 border rounded p-3 ms-5">
                    <ul class="nav nav-pills border-bottom">
                        @foreach(app('languages') as $n => $language)
                            <li class="nav-item"><a class="nav-link @if($n==0) active @endif " href="#form_{{$language->code}}" data-bs-toggle="tab">{{$language->name}}</a></li>
                        @endforeach
                    </ul>
                    <div class="tab-content">
                        @foreach(app('languages') as $n => $language)
                            <div class="tab-pane @if($n==0) active @endif" id="form_{{$language->code}}">
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <ul class="list-group">
                                            @foreach($all_menus->where('language', $language->code)->get() as $item)
                                                <li class="list-group-item d-flex">
                                                    <a href="{{route('admin.menu.show', $item)}}">
                                                        {{$item->title}}
                                                    </a>
                                                    <div class="ml-auto">
                                                        <a href="{{route('admin.menu.index', $item)}}" class="btn btn-sm btn-outline-primary">
                                                            <i class="fa-solid fa-edit"></i>
                                                        </a>
                                                        <a href="javascript:DeleteMenu('{{$item->id}}')" class="btn btn-sm btn-outline-danger">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="col-5 border rounded p-3 ms-5">
                    <form class="row" method="post" id="menu" action="javascript:void(0)">
                        <div class="col-12 mb-3">
                            <label for="title" class="form-label">@lang('menu.title')</label>
                            <input type="text" class="form-control" id="title" name="title" placeholder="@lang('menu.title')" value="{{$menu->title}}">
                        </div>
                        <div class="col-12 mb-3">
                            <label for="menu_position" class="form-label">@lang('menu.menu_position')</label>
                            <select class="form-select" id="menu_position" name="menu_position">
                                <option value="header" @if($menu->menu_position == 'header') selected @endif>
                                    @lang('menu.header')
                                </option>
                                <option value="footer" @if($menu->menu_position == 'footer') selected @endif>
                                    @lang('menu.footer')
                                </option>
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label for="language" class="form-label">@lang('menu.language')</label>
                            <select class="form-select" id="language" name="language">
                                @foreach(app('languages') as $language)
                                    <option value="{{$language->code}}" @if($menu->language == $language->code) selected @endif >{{$language->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        @csrf
                        <div class="col-12 mb-3">
                            <button type="submit" class="btn btn-primary">@lang('general.save')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $('#menu').submit(function () {
            $.ajax({
                url: '{{route('admin.menu.save', $menu)}}',
                type: 'post',
                data: $(this).serialize(),
                success: function (response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: '@lang('general.saved')',
                            text: response.message
                        });
                        window.location.reload();
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: xhr.responseJSON.message
                    });
                }
            });
        });

        function DeleteMenu(menu_id){
            Swal.fire({
                title: '@lang('general.are_you_sure')',
                text: '@lang('menu.delete_warning')',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '@lang('general.yes')',
                cancelButtonText: '@lang('general.no')'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{route('admin.menu.delete')}}',
                        data: {
                            menu_id: menu_id,
                            _token: '{{csrf_token()}}'
                        },
                        type: 'post',
                        success: function (response) {
                            if (response.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: '@lang('general.deleted')',
                                    text: response.message
                                });
                                window.location.reload();
                            }
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
    </script>
@endsection
