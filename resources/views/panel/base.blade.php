<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

    <title>{{config('app.name')}} | @yield('title')</title>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @if(config('settings.fontawesome_pro'))
        <!-- Font Awesome Icons -->
        <link rel="stylesheet" href="{{config('app.url')}}/themes/fontawesome/css/all.css">
    @else
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    @endif

    <!-- Theme style -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
          crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="{{config('app.url')}}/themes/panel/css/custom.css">
    <!-- Google Font: Source Sans Pro -->
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">

    <link rel="shortcut icon" href="{{$general_settings->getFirstMediaUrl('site_favicon')}}" type="image/x-icon">
    <link rel="icon" href="{{$general_settings->getFirstMediaUrl('site_favicon')}}" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="57x57"
          href="{{$general_settings->getFirstMediaUrl('site_favicon', 'r_57x57')}}"
          type="image/x-icon">

    <link rel="apple-touch-icon" sizes="60x60"
          href="{{$general_settings->getFirstMediaUrl('site_favicon', 'r_60x60')}}"
          type="image/x-icon">

    <link rel="apple-touch-icon" sizes="72x72"
          href="{{$general_settings->getFirstMediaUrl('site_favicon', 'r_72x72')}}"
          type="image/x-icon">

    <link rel="apple-touch-icon" sizes="76x76"
          href="{{$general_settings->getFirstMediaUrl('site_favicon', 'r_76x76')}}"
          type="image/x-icon">

    <link rel="apple-touch-icon" sizes="114x114"
          href="{{$general_settings->getFirstMediaUrl('site_favicon', 'r_114x114')}}"
          type="image/x-icon">

    <link rel="apple-touch-icon" sizes="120x120"
          href="{{$general_settings->getFirstMediaUrl('site_favicon', 'r_120x120')}}"
          type="image/x-icon">

    <link rel="apple-touch-icon" sizes="144x144"
          href="{{$general_settings->getFirstMediaUrl('site_favicon', 'r_144x144')}}"
          type="image/x-icon">

    <link rel="apple-touch-icon" sizes="152x152"
          href="{{$general_settings->getFirstMediaUrl('site_favicon', 'r_152x152')}}"
          type="image/x-icon">

    <link rel="apple-touch-icon" sizes="180x180"
          href="{{$general_settings->getFirstMediaUrl('site_favicon', 'r_180x180')}}"
          type="image/x-icon">

    <link rel="icon" type="image/png" sizes="192x192"
          href="{{$general_settings->getFirstMediaUrl('site_favicon', 'r_192x192')}}">

    <link rel="icon" type="image/png" sizes="32x32"
          href="{{$general_settings->getFirstMediaUrl('site_favicon', 'r_32x32')}}">

    <link rel="icon" type="image/png" sizes="96x96"
          href="{{$general_settings->getFirstMediaUrl('site_favicon', 'r_96x96')}}">

    <link rel="icon" type="image/png" sizes="16x16"
          href="{{$general_settings->getFirstMediaUrl('site_favicon', 'r_16x16')}}">

    @vite(['resources/js/app.js'])


    <link rel="manifest" href="{{route('manifest.panel')}}">
    @if(auth()->user()->role == 'owner' || auth()->user()->role == 'admin')
        {!! $admin_notification?->onesignal !!}
    @endif
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
<div class="wrapper">

    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake"
             src="{{app('general_settings')->getFirstMediaUrl('site_favicon')}}"
             alt="{{app('seo_settings')->site_name}}" height="60" width="60">
    </div>


    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-light" id="top-navbar" aria-label="top-menu">
        @include('panel.partials.header-navbar')
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="{{route('admin.index')}}" class="brand-link">
            <img src="{{app('general_settings')->getFirstMediaUrl('site_favicon')}}"
                 alt="{{app('seo_settings')->site_name}}" class="brand-image img-circle elevation-3"
                 style="opacity: .8">
            <span class="brand-text font-weight-light">{{app('seo_settings')->site_name}}</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <!-- Sidebar user panel (optional) -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img
                        src="{{replaceCDN(auth()->user()->profile_image)}}&s=34"
                        class="img-circle elevation-2" alt="{{auth()->user()->nickname}}">
                </div>
                <div class="info">
                    <a href="{{route('admin.profile.index')}}" class="d-block">{{auth()->user()->nickname}}</a>
                </div>
            </div>

            <!-- Sidebar Menu -->
            <nav class="mt-2" aria-label="main-menu">
                @include('panel.partials.menu')
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark">@yield('title')</h1>
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        @yield('breadcrumb_link')
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <div class="content">
            @if(session()->has('impersonated'))
            <div class="row" id="impersonated">
                <div class="col-12">
                    <div class="alert alert-warning text-center">
                        <p><i class=" @if(config('settings.fontawesome_pro')) fa-duotone @else fa-solid @endif fa-solid fa-user"></i> {{auth()->user()->email}}
                            <a href="{{route('admin.user.secret-logout')}}" class="btn btn-sm btn-outline-primary">@lang('user.logout')</a></p>
                    </div>
                </div>
            </div>
            @endif
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{session('success')}}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="@lang('general.close')"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{session('error')}}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="@lang('general.close')"></button>
                    </div>
                @endif
            @yield('content')
        </div>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->


    <!-- Main Footer -->
    <footer class="main-footer">
        <!-- To the right -->
        <div class="float-right d-none d-sm-inline">

        </div>
        <!-- Default to the left -->
        <strong>
            Copyright &copy; {{date('Y')}} <a href="https://niyazi.net">Niyazi.Net</a>.
        </strong> All rights reserved.
    </footer>
</div>
<!-- ./wrapper -->

<!-- Logout Modal Start -->
<div class="modal" tabindex="-1" id="logoutModal">
    <div class="modal-dialog">
        <form class="modal-content" method="post" action="{{route('admin.logout')}}">
            <div class="modal-header">
                <h5 class="modal-title">@lang('user.logout')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>@lang('user.are_you_sure')</p>
                @csrf
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('general.cancel')</button>
                <button type="submit" class="btn btn-primary">@lang('user.logout')</button>
            </div>
        </form>
    </div>
</div>
<!-- Logout Modal End -->

<div id="dark-mode-switcher">
    <button onclick="toggleDark()" id="dark-mode-switcher-button" class="btn btn-dark">
        <i class=" @if(config('settings.fontawesome_pro')) fa-duotone @else fa-solid @endif fa-moon"></i>
    </button>
</div>

<!-- REQUIRED SCRIPTS -->

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
        crossorigin="anonymous"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous"></script>

<!-- AdminLTE App -->

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="{{config('app.url')}}/themes/panel/css/select2-dark.css">
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="{{config('app.url')}}/themes/panel/js/custom.js"></script>
<script src="{{config('app.url')}}/themes/panel/js/tinymce/tinymce.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>

<script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">

<script>
    function toggleDark() {
        document.body.classList.toggle("dark-mode");
        localStorage.setItem("dark-mode", document.body.classList.contains("dark-mode"));
        checkDarkMode();
    }

    function checkDarkMode(){
        if(localStorage.getItem("dark-mode") === "true"){
            document.getElementById("dark-mode-switcher-button").innerHTML = '<i class=" @if(config('settings.fontawesome_pro')) fa-duotone @else fa-solid @endif fa-sun"></i>';
            $('body').addClass("dark-mode");
            $('#top-navbar').addClass("navbar-dark").removeClass("navbar-light");
            $('.table').addClass("table-dark");
            $('.input-group input').removeClass("form-control-navbar").addClass("form-control-navbar-dark");
            $('#dark-mode-switcher-button').removeClass('btn-dark').addClass('btn-light');

        }else{
            document.getElementById("dark-mode-switcher-button").innerHTML = '<i class=" @if(config('settings.fontawesome_pro')) fa-duotone @else fa-solid @endif fa-moon"></i>';
            $('body').removeClass("dark-mode");
            $('#top-navbar').addClass("navbar-light").removeClass("navbar-dark");
            $('.table').removeClass("table-dark");
            $('.input-group input').addClass("form-control-navbar").removeClass("form-control-navbar-dark");
            $('#dark-mode-switcher-button').addClass('btn-dark').removeClass('btn-light');
        }
    }


    function checkCollapse(){
        if(localStorage.getItem("sidebar-collapse") === "true"){
            $('body').addClass("sidebar-collapse");
        }
        else{
            $('body').removeClass("sidebar-collapse");
        }
    }

    function fetchResults(query, page = 1) {
        $.ajax({
            url: '{{route('general.search')}}',
            type: 'POST',
            data: { search: query, page: page, _token: '{{csrf_token()}}' },
            success: function(data) {
                let results = $('#searchResults');
                results.empty();

                let result_items = '<ul id="result-items">';
                if(data.posts.length > 0){
                    result_items += '<li><strong>@lang('post.blogs')</strong><ul class="sub-results">';
                    $.each(data.posts, function(index, item) {
                        result_items += '<li class="result-item"><a href="{{config('app.url')}}/{{config('settings.admin_panel_path')}}/blogs/' + item.id + '/edit">' + item.title.replace(/\\/g, '') + ' (' + item.language + ')</a></li>';
                    });
                    result_items += '</ul></li>';
                }
                if(data.page.length > 0){
                    result_items += '<li><strong>@lang('post.pages')</strong><ul class="sub-results">';
                    $.each(data.page, function(index, item) {
                        result_items += '<li class="result-item"><a href="{{config('app.url')}}/{{config('settings.admin_panel_path')}}/blogs/' + item.id + '/edit">' + item.title.replace(/\\/g, '') + ' (' + item.language + ')</a></li>';
                    });
                    result_items += '</ul></li>';
                }
                if(data.categories.length > 0){
                    result_items += '<li><strong>@lang('categories.categories')</strong><ul class="sub-results">';
                    $.each(data.categories, function(index, item) {
                        result_items += '<li class="result-item"><a href="{{config('app.url')}}/{{config('settings.admin_panel_path')}}/blogs/categories/' + item.id + '/edit">' + item.name.replace(/\\/g, '') + ' (' + item.language + ')</a></li>';
                    });
                    result_items += '</ul></li>';
                }
                if(data.personal_notes.length > 0){
                    result_items += '<li><strong>@lang('notes.notes')</strong><ul class="sub-results">';
                    $.each(data.personal_notes, function(index, item) {
                        result_items += '<li class="result-item"><a href="{{config('app.url')}}/{{config('settings.admin_panel_path')}}/notes/show/' + item.id + '">' + item.title.replace(/\\/g, '') + '</a></li>';
                    });
                    result_items += '</ul></li>';
                }
                if(data.users.length > 0){
                    result_items += '<li><strong>@lang('user.users')</strong><ul class="sub-results">';
                    $.each(data.users, function(index, item) {
                        result_items += '<li class="result-item"><a href="{{config('app.url')}}/{{config('settings.admin_panel_path')}}/users/' + item.id + '/edit">' + item.name.replace(/\\/g, '') + ' ' + item.surname.replace(/\\/g, '') + ' - ' + item.nickname.replace(/\\/g, '') + '</a></li>';
                    });
                    result_items += '</ul></li>';
                }
                result_items += '</ul>';
                results.append(result_items);
                results.show();
            }
        });
    }

    $(document).ready(function(){
        checkDarkMode();
        let tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        $('#navbar-header-search').submit(function(e){
            e.preventDefault();
            let query = $('#searchInput').val();
            fetchResults(query);
        });

        checkCollapse();
        $('#pushmenu').on('click', function(){
            localStorage.setItem("sidebar-collapse", !$('body').hasClass("sidebar-collapse"));
        });

        $('.tooltip-button').tooltip();

        $('.clear-cache').click(function(){
            Swal.fire({
                title: '@lang('cache.clear_cache')',
                text: '@lang('cache.are_you_sure')',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '@lang('general.yes')',
                cancelButtonText: '@lang('general.no')'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{route('admin.clear_cache')}}',
                        type: 'GET',
                        success: function(response){
                            if(response.status === 'success'){
                                Swal.fire({
                                    icon: 'success',
                                    title: response.message,
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                            }
                            else{
                                Swal.fire({
                                    icon: 'error',
                                    title: response.message,
                                    showConfirmButton: false,
                                    timer: 1500
                                })
                            }
                        }
                    });
                }
            });
        });


        $('#searchInput').on('keyup keydown keypress', function() {
            let query = $(this).val();
            fetchResults(query);
        });

        $(document).click(function(e) {
            if (!$(e.target).closest('#searchResults, #searchInput').length) {
                $('#searchResults').hide();
            }
        });
    });

    document.addEventListener("DOMContentLoaded", function () {
        const sidebar = document.getElementById("sidebar");

        const activeItem = document.querySelector(".nav-item .active");

        if (activeItem && sidebar) {
            sidebar.scrollTop = activeItem.offsetTop - sidebar.offsetTop;
        }
    });
</script>


@yield('script')

</body>
</html>
