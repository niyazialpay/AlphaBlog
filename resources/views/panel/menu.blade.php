<ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
    <!-- Add icons to the links using the .nav-icon class
         with font-awesome or any other icon font library -->
    <li class="nav-item">
        <a href="{{route('admin.index')}}" class="nav-link
        @if(request()->is(config('settings.admin_panel_path'))) active @endif ">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>
                @lang('dashboard.dashboard')
            </p>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{route('alphabot')}}" class="nav-link"
           @if(request()->is(config('settings.admin_panel_path').'/alphabot*')) active @endif ">
            <i class="fa-duotone fa-robot nav-icon"></i>
            <p>
                @lang('chatbot.chatbot')
            </p>
        </a>
    </li>
    <li class="nav-header">@lang('post.blog')</li>
    <li class="nav-item has-treeview
        @if(request()->is(config('settings.admin_panel_path').'/blogs*', config('settings.admin_panel_path').'/search*')) menu-open @endif ">
        <a href="{{route('admin.posts', ['type' => 'blogs', 'language' => app('default_language')->code])}}"
           class="nav-link @if(request()->is(config('settings.admin_panel_path').'/blogs*')) active @endif ">
            <i class="fa-duotone fa-file-lines nav-icon"></i>
            @can('moderator', 'App\Models\Post\Posts')
            @if($newCommentsCount>0 || $searchedWordsCount>0)
                <span class="badge badge-danger right">
                    @if($newCommentsCount+$searchedWordsCount>99)
                        99+
                    @else
                        {{$newCommentsCount+$searchedWordsCount}}
                    @endif
                </span>
            @endif
            @endcan
            <p>
                @lang('post.blogs')
                <i class="right fas fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview shadow rounded py-2">

            @can('createPost', 'App\Models\Post\Posts')
                <li class="nav-item">
                    <a href="{{route('admin.post.create', ['type' => 'blogs'])}}"
                       class="nav-link @if(request()->is(config('settings.admin_panel_path').'/blogs/create')) active @endif ">
                        <i class="fa-duotone fa-file nav-icon"></i>
                        <p>@lang('general.new')</p>
                    </a>
                </li>
            @endcan
            <li class="nav-item">
                <a href="{{route('admin.posts', ['type' => 'blogs', 'language' => app('default_language')->code])}}"
                   class="nav-link
                   @if(request()->is(config('settings.admin_panel_path').'/blogs', config('settings.admin_panel_path').'/blogs/*/edit')) active @endif ">
                    <i class="fa-duotone fa-file-lines nav-icon"></i>
                    <p>@lang('post.all_blogs')</p>
                </a>
            </li>
            @can('category', 'App\Models\Post\Categories')
                <li class="nav-item">
                    <a href="{{route('admin.categories')}}"
                       class="nav-link
                   @if(request()->is(config('settings.admin_panel_path').'/blogs/categories*')) active @endif ">
                        <i class="fa-duotone fa-list nav-icon"></i>
                        <p>
                            @lang('categories.categories')
                        </p>
                    </a>
                </li>
            @endcan
            @can('view', 'App\Models\Post\Comments')
                <li class="nav-item">
                    <a href="{{route('admin.post.comments')}}"
                       class="nav-link
                   @if(request()->is(config('settings.admin_panel_path').'/blogs/comments*')) active @endif ">
                        <i class="fa-duotone fa-comments nav-icon"></i>
                        @if($newCommentsCount>0)
                            <span class="badge badge-danger right">
                                @if($newCommentsCount>99)
                                    99+
                                @else
                                    {{$newCommentsCount}}
                                @endif
                            </span>
                        @endif
                        <p>
                            @lang('comments.comments')
                        </p>
                    </a>
                </li>
            @endcan
            @can('createPost', 'App\Models\Post\Posts')
                <li class="nav-item">
                    <a href="{{route('admin.search.index')}}"
                       class="nav-link
                   @if(request()->is(config('settings.admin_panel_path').'/search*')) active @endif ">
                        <i class="fa-duotone fa-file-magnifying-glass nav-icon"></i>
                        @if($searchedWordsCount>0)
                            <span class="badge badge-danger right">
                                @if($searchedWordsCount>99)
                                    99+
                                @else
                                    {{$searchedWordsCount}}
                                @endif
                            </span>
                        @endif
                        <p>
                            @lang('search.searched_words')
                        </p>
                    </a>
                </li>
            @endcan
        </ul>
    </li>
    @can('viewPages', 'App\Models\Post\Posts')
        <li class="nav-header">@lang('post.page')</li>
        <li class="nav-item has-treeview
        @if(request()->is(config('settings.admin_panel_path').'/pages*')) menu-open @endif ">
            <a href="{{route('admin.posts', ['type' => 'pages', 'language' => app('default_language')->code])}}"
               class="nav-link
           @if(request()->is(config('settings.admin_panel_path').'/pages*')) active @endif ">
                <i class="fa-duotone fa-page  nav-icon"></i>
                <p>
                    @lang('post.pages')
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview shadow rounded py-2">
                <li class="nav-item">
                    <a href="{{route('admin.post.create', ['type' => 'pages', 'language' => app('default_language')->code])}}"
                       class="nav-link
                   @if(request()->is(config('settings.admin_panel_path').'/pages/create')) active @endif ">
                        <i class="fa-duotone fa-page  nav-icon"></i>
                        <p>@lang('general.new')</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{route('admin.posts', ['type' => 'pages', 'language' => app('default_language')->code])}}"
                       class="nav-link
                   @if(request()->is(config('settings.admin_panel_path').'/pages',
                    config('settings.admin_panel_path').'/pages/*/edit')) active @endif ">
                        <i class="fa-duotone fa-file-lines nav-icon"></i>
                        <p>@lang('post.all_pages')</p>
                    </a>
                </li>
            </ul>
        </li>
    @endcan
    @can('admin', 'App\Models\User')
        <li class="nav-item">
            <a href="{{route('admin.contact_page')}}" class="nav-link
            @if(request()->is(config('settings.admin_panel_path').'/contact*')) active @endif ">
                <i class="fa-duotone fa-message-text nav-icon"></i>
                <p>
                    @lang('contact.contact_page')
                </p>
            </a>
        </li>
    @endcan
    <li class="nav-header">@lang('notes.note') / @lang('notes.journal')</li>
    <li class="nav-item has-treeview
    @if(request()->is(config('settings.admin_panel_path').'/notes*')) menu-open @endif ">
        <a class="nav-link
        @if(request()->is(config('settings.admin_panel_path').'/notes*')) active @endif "
           href="javascript:void(0);">
            <i class="fa-duotone fa-file-lock nav-icon"></i>
            <p>
                @lang('notes.notes')
                <i class="right fas fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview shadow rounded py-2">
            <li class="nav-item">
                <a href="{{route('admin.notes.create')}}"
                   class="nav-link
                   @if(request()->is(config('settings.admin_panel_path').'/notes/create')) active @endif ">
                    <i class="fa-duotone fa-page  nav-icon"></i>
                    <p>@lang('general.new')</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{route('admin.notes')}}"
                   class="nav-link
                   @if(request()->is(
                        config('settings.admin_panel_path').'/notes',
                        config('settings.admin_panel_path').'/notes/show/*'
                    )) active @endif ">
                    <i class="fa-duotone fa-file-lock nav-icon"></i>
                    <p>@lang('notes.all_notes')</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{route('admin.notes.categories')}}"
                   class="nav-link
                   @if(request()->is(config('settings.admin_panel_path').'/notes/categories*')) active @endif ">
                    <i class="fa-duotone fa-list nav-icon"></i>
                    <p>
                        @lang('categories.categories')
                    </p>
                </a>
            </li>
        </ul>
    </li>
    @can('admin', 'App\Models\User')
        <li class="nav-header">@lang('settings.management')</li>
        <li class="nav-item">
            <a href="{{route('admin.ip-filter')}}" class="nav-link
        @if(request()->is(config('settings.admin_panel_path').'/ip-filter*')) active @endif">
                <i class="fa-solid fa-shield-halved nav-icon"></i>
                <p>
                    @lang('ip_filter.ip_filter')
                </p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{route('adminRoutes')}}"
               class="nav-link @if(request()->is(config('settings.admin_panel_path').'/routes*')) active @endif ">
                <i class="fa-duotone fa-route nav-icon"></i>
                <p>
                    @lang('redirects.redirects')
                </p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{route('admin.users')}}" class="nav-link
        @if(request()->is(config('settings.admin_panel_path').'/users*')) active @endif ">
                <i class="fa-duotone fa-user nav-icon"></i>
                <p>
                    @lang('user.users')
                </p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{route('admin.menu.index')}}" class="nav-link
            @if(request()->is(config('settings.admin_panel_path').'/menu*')) active @endif ">
                <i class="fa-duotone fa-bars nav-icon"></i>
                <p>
                    @lang('menu.menu')
                </p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{route('admin.settings')}}"
               class="nav-link @if(request()->is(config('settings.admin_panel_path').'/settings*')) active @endif ">
                <i class="fa-duotone fa-gear nav-icon"></i>
                <p>
                    @lang('settings.settings')
                </p>
            </a>
        </li>
        <li class="nav-header">
            @lang('settings.monitoring')
        </li>
        <li class="nav-item">
            <a href="{{route('admin.monitoring.pulse')}}"
               class="nav-link @if(request()->is(config('settings.admin_panel_path').'/monitoring/pulse')) active @endif ">
                <i class="fa-duotone fa-wave-pulse nav-icon"></i>
                <p>
                    Pulse
                </p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{route('admin.monitoring.telescope')}}"
               class="nav-link @if(request()->is(config('settings.admin_panel_path').'/monitoring/telescope')) active @endif ">
                <i class="fa-duotone fa-telescope nav-icon"></i>
                <p>
                    Telescope
                </p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{route('admin.monitoring.horizon')}}"
               class="nav-link @if(request()->is(config('settings.admin_panel_path').'/monitoring/horizon')) active @endif ">
                <i class="fa-brands fa-laravel nav-icon"></i>
                <p>
                    Horizon
                </p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{route('admin.monitoring.logs')}}"
               class="nav-link @if(request()->is(config('settings.admin_panel_path').'/monitoring/logs')) active @endif ">
                <i class="fa-duotone fa-clipboard-list nav-icon"></i>
                <p>
                    Log Viewer
                </p>
            </a>
        </li>
    @endcan
</ul>
