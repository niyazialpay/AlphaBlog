@extends('panel.base')
@section('title',__('menu.add_menu'))
@section('breadcrumb_link')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{route('admin.index')}}">@lang('dashboard.dashboard')</a></li>
        <li class="breadcrumb-item">
            <a href="{{route('admin.menu.index')}}">@lang('menu.menu')</a>
        </li>
        <li class="breadcrumb-item active">@lang('menu.add_menu')</li>
    </ol>
@endsection
@section('content')
    <div class="card">
        <div class="card-header d-flex">
            <h3 class="card-title">{{$menu->title}} - @lang('menu.'.$menu->menu_position) - {{$menu->language}}</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-5 border rounded p-3 ms-5">
                    <ul class="nav nav-pills border-bottom">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="pill" href="#home">
                                @lang('menu.special_link')
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="pill" href="#pages">
                                @lang('post.pages')
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="pill" href="#post">
                                @lang('post.blogs')
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="pill" href="#category">
                                @lang('categories.categories')
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="home">
                            <form id="add-item" class="row">
                                <div class="col-12 mb-3">
                                    <label for="title" class="form-label">@lang('menu.title')</label>
                                    <input type="text" class="form-control"
                                           id="title" name="title" placeholder="@lang('menu.title')">
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="url">@lang('menu.url')</label>
                                    <input type="text" name="url" id="url"
                                           class="form-control" placeholder="@lang('menu.url')">
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="target">@lang('menu.target')</label>
                                    <select name="target" id="target" class="form-control">
                                        <option value="_self">@lang('menu.same_tab')</option>
                                        <option value="_blank">@lang('menu.new_tab')</option>
                                    </select>
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="icon">@lang('menu.icon')</label>
                                    <input type="text" name="icon" id="icon"
                                           class="form-control" placeholder="@lang('menu.icon')">
                                </div>
                                <div class="col-12 mb-3">
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        @lang('menu.add_menu_item')
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="tab-pane" id="pages">
                            <ul class="list-group">
                                @foreach($pages as $page)
                                    <li class="list-group-item border">
                                        <a href="javascript:menuAdd(
                                        '{{route('page', [
                                            $menu->language,$page->slug
                                            ])}}', '{{$page->title}}', '_self');"
                                           class="list-group-item-action d-flex">
                                            {{$page->title}}
                                            <div class="ml-auto">
                                                <i class="fa-duotone fa-plus"></i>
                                            </div>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="tab-pane" id="post">
                            <ul class="list-group">
                                @foreach($posts as $post)
                                    <li class="list-group-item border">
                                        <a href="javascript:menuAdd(
                                        '{{route('page', [
                                            $menu->language,$post->slug
                                            ])}}', '{{$post->title}}', '_self');"
                                           class="list-group-item-action d-flex">
                                            {{$post->title}}
                                            <div class="ml-auto">
                                                <i class="fa-duotone fa-plus"></i>
                                            </div>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="tab-pane" id="category">
                            <ul class="list-group">
                                @foreach($categories as $category)
                                    <li class="list-group-item border">
                                        <a href="javascript:menuAdd('{{route('post.categories', [
                                        $menu->language,
                                        __('routes.categories', locale:$menu->language),
                                        $category->slug]
                                        )}}',
                                        '{{$category->name}}', '_self');"
                                           class="list-group-item-action d-flex">
                                            {{$category->name}}
                                            <div class="ml-auto">
                                                <i class="fa-duotone fa-plus"></i>
                                            </div>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-5 border rounded p-3 ms-5">
                    <div class="dd" id="nestable">
                        {!! (empty($html_menu)) ? '<ol class="dd-list"></ol>' : $html_menu !!}
                    </div>

                    <form action="javascript:void(0)" id="menu_save" method="post">
                        <input type="hidden" id="nestable-output" name="menu">
                        @csrf
                        <input type="hidden" name="menu_id" value="{{$menu->id}}">
                        <button type="submit" class="btn btn-sm btn-primary">@lang('general.save')</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <link rel="stylesheet" href="{{config('app.url')}}/themes/panel/css/jquery.nestable.css">
    <link rel="stylesheet" href="{{config('app.url')}}/themes/panel/css/nestable.css">
    <script src="{{config('app.url')}}/themes/panel/js/jquery.nestable.js"></script>
    <script>
        function menuAdd(url, title, target) {
            $("input[name='title']").val(title);
            $("input[name='url']").val(url);
            $("select[name='target']").val(target);
            $("#add-item").submit();
        }

        $(document).ready(function () {
            let updateOutput = function () {
                $('#nestable-output').val(JSON.stringify($('#nestable').nestable('serialize')));
            };

            $('#nestable').nestable().on('change', updateOutput);

            updateOutput();

            $("#add-item").submit(function (e) {
                e.preventDefault();
                id = Date.now();
                let label = $("#title");
                let url = $("#url");
                let target = $("#target");
                let icon = $("#icon");
                if ((url === "") || (label === "")) return;
                let item =
                    '<li class="dd-item dd3-item" data-id="' + id + '" data-title="' + label.val() + '" data-url="' + url.val() + '"  data-language="{{$menu->language}}" data-nav_target="' + target.val() + '" data-icon="' + icon.val() + '" data-menu_id="{{$menu->id}}">' +
                    '<div class="dd-handle dd3-handle" > Drag</div>' +
                    '<div class="dd3-content"><span>' + label.val() + '</span>' +
                    '<div class="item-edit"><i class="fa-duotone fa-pen-to-square"></i></div>' +
                    '</div>' +
                    '<div class="item-settings d-none">' +
                    '<p><label for="">@lang('menu.title')<br><input type="text" name="navigation_title" value="' + label.val() + '"></label></p>' +
                    '<p><label for="">@lang('menu.url')<br><input type="text" name="navigation_url" value="' + url.val() + '"></label></p>' +

                    '<p><label for="">@lang('menu.target')<br> ' +
                    '<select name="navigation_target" class="form-control">' +
                    '<option value="_self" ' + ((target.val() === "_self") ? "selected" : "") + '>@lang('menu.same_tab')</option>' +
                    '<option value="_blank" ' + ((target.val() === "_blank") ? "selected" : "") + '>@lang('menu.new_tab')</option>' +
                    '</select>' +
                    '</label></p>' +

                    '<p><label for="">@lang('menu.icon')<br><input type="text" name="navigation_icon" value="' + icon.val() + '"></label></p>' +

                    '<p><a class="item-delete" href="javascript:;"> <i class="fa-duotone fa-trash"></i> </a> |' +
                    '<a class="item-close" href="javascript:;"><i class="fa-duotone fa-circle-xmark"></i></a></p>' +
                    '</div>' +
                    '</li>';

                $("#nestable > .dd-list").append(item);
                $("#nestable").find('.dd-empty').remove();
                label.val("");
                url.val("");
                target.val("");
                icon.val("");
                updateOutput();
            });

            let body = $("body");

            body.delegate(".item-delete", "click", function (e) {
                $(this).closest(".dd-item").remove();
                updateOutput();
            });


            body.delegate(".item-edit, .item-close", "click", function (e) {
                let item_setting = $(this).closest(".dd-item").find(".item-settings");
                if (item_setting.hasClass("d-none")) {
                    item_setting.removeClass("d-none");
                } else {
                    item_setting.addClass("d-none");
                }
            });

            body.delegate("input[name='navigation_title']", "change paste keyup", function (e) {
                $(this).closest(".dd-item").data("title", $(this).val());
                $(this).closest(".dd-item").find(".dd3-content span").text($(this).val());
            });

            body.delegate("input[name='navigation_url']", "change paste keyup", function (e) {
                $(this).closest(".dd-item").data("url", $(this).val());
            });

            body.delegate("select[name='navigation_target']", "change paste keyup", function (e) {
                console.log($(this).val());
                $(this).closest(".dd-item").data("nav_target", $(this).val());
            });

            body.delegate("input[name='navigation_icon']", "change paste keyup", function (e) {
                $(this).closest(".dd-item").data("icon", $(this).val());
            });


            $('#menu_save').submit(function () {
                updateOutput();
                $.ajax({
                    url: '{{route('admin.menu-item.save')}}',
                    type: 'post',
                    data: $(this).serialize(),
                    success: function (response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: '@lang('general.saved')',
                                text: response.message
                            });
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

        });

    </script>
@endsection
