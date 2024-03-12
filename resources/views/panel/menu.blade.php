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
    <li class="nav-item has-treeview
        @if(request()->is(config('settings.admin_panel_path').'/blogs*')) menu-open @endif ">
        <a href="{{route('admin.posts', ['type' => 'blogs', 'language' => app('default_language')->code])}}"
           class="nav-link @if(request()->is(config('settings.admin_panel_path').'/blogs*')) active @endif ">
            <i class="fa-duotone fa-file-lines nav-icon"></i>
            <p>
                @lang('post.blogs')
            </p>
            <i class="right fas fa-angle-left"></i>
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
                        <p>
                            @lang('comments.comments')
                        </p>
                    </a>
                </li>
            @endcan
        </ul>
    </li>
    @can('viewPages', 'App\Models\Post\Posts')
        <li class="nav-item has-treeview
        @if(request()->is(config('settings.admin_panel_path').'/pages*')) menu-open @endif ">
            <a href="{{route('admin.posts', ['type' => 'pages', 'language' => app('default_language')->code])}}"
               class="nav-link
           @if(request()->is(config('settings.admin_panel_path').'/pages*')) active @endif ">
                <i class="fa-duotone fa-page  nav-icon"></i>
                <p>
                    @lang('post.pages')
                </p>
                <i class="right fas fa-angle-left"></i>
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
    <li class="nav-item">
        <a class="nav-link
        @if(request()->is(config('settings.admin_panel_path').'/notes*')) active @endif "
           href="{{route('admin.notes')}}">
            <i class="fa-duotone fa-file-lock nav-icon"></i>
            @lang('notes.notes')
        </a>
    </li>
    @can('admin', 'App\Models\User')
        <li class="nav-item">
            <a href="{{route('admin.contact_page')}}" class="nav-link @if(request()->is(config('settings.admin_panel_path').'/contact*')) active @endif ">
                <i class="fa-duotone fa-message-text nav-icon"></i>
                @lang('contact.contact_page')
            </a>
        </li>
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
        <li class="nav-item
        @if(request()->is(config('settings.admin_panel_path').'/settings*')) menu-open @endif ">
            <a href="{{route('admin.settings')}}"
               class="nav-link @if(request()->is(config('settings.admin_panel_path').'/settings*')) active @endif ">
                <i class="fa-duotone fa-gear nav-icon"></i>
                <p>
                    @lang('settings.settings')
                </p>
            </a>
        </li>
    @endcan
</ul>
