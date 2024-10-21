<!-- Left navbar links -->
<ul class="navbar-nav" id="top-menu-navbar">
    <li class="nav-item">
        <a class="nav-link" id="pushmenu" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{config('app.url')}}">
            <i class=" @if(config('settings.fontawesome_pro')) fa-duotone @else fa-solid @endif fa-house"></i>
        </a>
    </li>
    @if(config('services.openai.key') || config('gemini.api_key'))
        <li class="nav-item d-none d-md-block">
            <a class="nav-link" href="{{route('chatbot')}}">
                <i class=" @if(config('settings.fontawesome_pro')) fa-duotone @else fa-solid @endif fa-robot"></i>
            </a>
        </li>
    @endif
    @can('createPost', 'App\Models\Post\Posts')
        <li class="nav-item d-none d-md-block">
            <a href="{{route('admin.post.create', ['type' => 'blogs'])}}"
               class="nav-link">
                <i class=" @if(config('settings.fontawesome_pro')) fa-duotone @else fa-solid @endif fa-file top-icon"></i>
                <small>
                    @lang('post.new_post')
                </small>
            </a>
        </li>
        <li class="nav-item d-block d-md-none">
            <a href="{{route('admin.post.create', ['type' => 'blogs'])}}"
               data-bs-toggle="tooltip"
               data-bs-placement="right"
               title="@lang('post.new_post')"
               class="nav-link">
                <i class=" @if(config('settings.fontawesome_pro')) fa-duotone @else fa-solid @endif fa-file"></i>
            </a>
        </li>
    @endcan
    <li class="nav-item d-none d-md-block">
        <a class="nav-link clear-cache" href="javascript:void(0);">
            <i class=" @if(config('settings.fontawesome_pro')) fa-duotone @else fa-solid @endif fa-trash-can top-icon"></i>
            <small>
                @lang('cache.clear_cache')
            </small>
        </a>
    </li>
</ul>


<!-- Right navbar links -->
<ul class="navbar-nav ml-auto">
    <li class="nav-item">
        <a class="nav-link" data-widget="navbar-search" href="#" role="button">
            <i class="fas fa-search"></i>
        </a>
        <div class="navbar-search-block">
            <form class="form-inline" id="navbar-header-search" method="post" action="javascript:void(0)">
                <div class="input-group input-group-sm">
                    <input class="form-control form-control-navbar" id="searchInput" type="search" name="search" placeholder="@lang('post.search_placeholder')" aria-label="Search" autocomplete="off">
                    <div class="input-group-append">
                        <button class="btn btn-navbar" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                        <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div id="searchResults"></div>
                </div>
            </form>
        </div>
    </li>
    <li class="nav-item dropdown">
        <a class="nav-link tooltip-button d-none d-md-block" data-bs-toggle="dropdown"
           href="javascript:void(0);"
           data-bs-placement="top"
           title="{{session('language_name')}}">
            <small>
                <strong class="me-1">@lang('general.language') :</strong>
            </small>
            <img src="{{config('app.url')}}/themes/flags/{{session('language_flag')}}.webp"
                 class="elevation-2" alt="{{session('language_name')}}" height="12">
        </a>
        <a class="nav-link tooltip-button d-block d-md-none" data-bs-toggle="dropdown"
           href="javascript:void(0);"
           data-bs-placement="left"
           title="{{session('language_name')}}">
            <img src="{{config('app.url')}}/themes/flags/{{session('language_flag')}}.webp"
                 class="elevation-2" alt="{{session('language_name')}}" height="12">
        </a>
        <div class="dropdown-menu">
            @foreach($languages as $language)
                @if(session('language') != $language->code)
                    <a href="{{route('admin.change_language', ['language' => $language->code])}}"
                       class="dropdown-item">

                        <div class="media">
                            <div class="media-body">
                                        <span class="dropdown-item-title">
                                            <img src="{{config('app.url')}}/themes/flags/{{$language->flag}}.webp"
                                                 alt="{{$language->name}}" height="12" class="elevation-2 me-1">
                                                    {{$language->name}}
                                        </span>
                            </div>
                        </div>
                    </a>
                @endif
            @endforeach
        </div>
    </li>
    <!-- Messages Dropdown Menu -->
    <li class="nav-item dropdown">
        <x-new-comments />
    </li>
    <li class="nav-item dropdown">
        <x-notifications />
    </li>
    <li class="nav-item dropdown">
        <a class="nav-link" data-bs-toggle="dropdown" href="#">
            <img
                src="https://www.gravatar.com/avatar/{{hash('sha256', strtolower(trim(auth()->user()->email)))}}?s=20"
                class="img-circle elevation-2" alt="{{auth()->user()->nickname}}">
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
            <a href="{{route('admin.profile.index')}}" class="dropdown-item">
                <i class=" @if(config('settings.fontawesome_pro')) fa-duotone @else fa-solid @endif fa-user top-icon"></i> @lang('user.profile')
            </a>
            <a>
                <a href="{{route('admin.profile.index')}}?tab=security" class="dropdown-item">
                    <i class=" @if(config('settings.fontawesome_pro')) fa-duotone @else fa-solid @endif fa-shield-halved top-icon"></i> @lang('profile.security')
                </a>
            </a>
            <div class="dropdown-divider"></div>
            @if(auth()->user()->webauthn || auth()->user()->otp)
                <a href="{{route('lockscreen')}}" class="dropdown-item">
                    <i class=" @if(config('settings.fontawesome_pro')) fa-duotone @else fa-solid @endif fa-lock top-icon"></i> @lang('user.lockscreen')
                </a>
            @endif
            <a href="javascript:void(0)" class="dropdown-item"
               data-bs-toggle="modal" data-bs-target="#logoutModal">
                <i class=" @if(config('settings.fontawesome_pro')) fa-duotone @else fa-solid @endif fa-right-from-bracket top-icon"></i> @lang('user.logout')
            </a>
        </div>
    </li>
</ul>
