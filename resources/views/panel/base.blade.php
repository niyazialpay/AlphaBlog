<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

    <title>{{config('app.name')}} | @yield('title')</title>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="{{config('app.url')}}/themes/fontawesome/css/all.min.css">

    <!-- Theme style -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

    <link rel="stylesheet" href="{{config('app.url')}}/themes/panel/css/adminlte.min.css">
    <link rel="stylesheet" href="{{config('app.url')}}/themes/panel/css/custom.css">
    <!-- Google Font: Source Sans Pro -->
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
</head>
<body class="hold-transition sidebar-mini dark-mode">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light" aria-label="top-menu">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
        </ul>


        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <!-- Messages Dropdown Menu -->
            <li class="nav-item dropdown">
                <x-new-comments />
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link" data-bs-toggle="dropdown" href="#">
                    <img
                        src="https://www.gravatar.com/avatar/{{md5(strtolower(trim(auth()->user()->email)))}}?s=20"
                        class="img-circle elevation-2" alt="{{auth()->user()->nickname}}">
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                    <a href="{{route('admin.profile.index')}}" class="dropdown-item">
                        <i class="fa-duotone fa-user top-icon"></i> @lang('user.profile')
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="javascript:void(0)" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#logoutModal">
                        <i class="fa-duotone fa-right-from-bracket top-icon"></i> @lang('user.logout')
                    </a>
                </div>
            </li>
        </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="{{route('admin.index')}}" class="brand-link">
            <img src="{{config('app.url')}}/themes/Cryptograph/images/apple-icon-180x180.png"
                 alt="{{config('app.name')}}" class="brand-image img-circle elevation-3"
                 style="opacity: .8">
            <span class="brand-text font-weight-light">{{config('app.name')}}</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar user panel (optional) -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img
                        src="https://www.gravatar.com/avatar/{{md5(strtolower(trim(auth()->user()->email)))}}?s=34"
                        class="img-circle elevation-2" alt="{{auth()->user()->nickname}}">
                </div>
                <div class="info">
                    <a href="{{route('admin.profile.index')}}" class="d-block">{{auth()->user()->nickname}}</a>
                </div>
            </div>

            <!-- Sidebar Menu -->
            <nav class="mt-2" aria-label="main-menu">
                @include('panel.menu')
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
        <strong>Copyright &copy; {{date('Y')}} <a href="https://niyazi.net">Niyazi.Net</a>.</strong> All rights reserved.
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

<!-- REQUIRED SCRIPTS -->

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

<!-- AdminLTE App -->

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script src="{{config('app.url')}}/themes/panel/js/adminlte.min.js"></script>
<script src="{{config('app.url')}}/themes/panel/js/custom.js"></script>
<script src="{{config('app.url')}}/themes/panel/js/tinymce/tinymce.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>

<script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">

<script>
    $(document).ready(function(){
        let tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        let tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        $('#top-comment-button').tooltip();
    });
</script>


@yield('script')

</body>
</html>
