<ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
    <li class="nav-item">
        <a href="{{route('admin.index')}}" class="nav-link
        @if(request()->is(config('settings.admin_panel_path'))) active @endif ">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>
                @lang('dashboard.dashboard')
            </p>
        </a>
    </li>
    @can('admin', 'App\Models\User')
        <li class="nav-item">
            <a href="{{route('admin.analytics')}}" class="nav-link
        @if(request()->is(config('settings.admin_panel_path').'/analytics')) active @endif ">
                @if(config('settings.fontawesome_pro'))
                <i class="fa-duotone fa-chart-mixed nav-icon"></i>
                @else
                    <i class="fa-regular fa-chart-bar nav-icon"></i>
                @endif
                <p>
                    @lang('dashboard.analytics')
                </p>
            </a>
        </li>
    @endcan
    @if(config('services.openai.key') || config('gemini.api_key'))
        <li class="nav-item">
            <a href="{{route('chatbot')}}" class="nav-link
        @if(request()->is(config('settings.admin_panel_path').'/ai-chatbot*')) active @endif ">
                <i class=" @if(config('settings.fontawesome_pro')) fa-duotone @else fa-solid @endif fa-robot nav-icon"></i>
                <p>
                    @lang('chatbot.chatbot')
                </p>
            </a>
        </li>
    @endif
    <li class="nav-header">@lang('post.blog')</li>
    <li class="nav-item has-treeview
        @if(request()->is(config('settings.admin_panel_path').'/blogs*', config('settings.admin_panel_path').'/search*')) menu-open @endif ">
        <a href="{{route('admin.posts', ['type' => 'blogs', 'language' => app('default_language')->code])}}"
           class="nav-link @if(request()->is(config('settings.admin_panel_path').'/blogs*')) active @endif ">
            <i class=" @if(config('settings.fontawesome_pro')) fa-duotone @else fa-solid @endif fa-file-lines nav-icon"></i>
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
                        <i class=" @if(config('settings.fontawesome_pro')) fa-duotone @else fa-solid @endif fa-file nav-icon"></i>
                        <p>@lang('general.new')</p>
                    </a>
                </li>
            @endcan
            <li class="nav-item">
                <a href="{{route('admin.posts', ['type' => 'blogs', 'language' => app('default_language')->code])}}"
                   class="nav-link
                   @if(request()->is(config('settings.admin_panel_path').'/blogs', config('settings.admin_panel_path').'/blogs/*/edit')) active @endif ">
                    <i class=" @if(config('settings.fontawesome_pro')) fa-duotone @else fa-solid @endif fa-file-lines nav-icon"></i>
                    <p>@lang('post.all_blogs')</p>
                </a>
            </li>
            @can('category', 'App\Models\Post\Categories')
                <li class="nav-item">
                    <a href="{{route('admin.categories')}}"
                       class="nav-link
                   @if(request()->is(config('settings.admin_panel_path').'/blogs/categories*')) active @endif ">
                        <i class=" @if(config('settings.fontawesome_pro')) fa-duotone @else fa-solid @endif fa-list nav-icon"></i>
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
                        <i class=" @if(config('settings.fontawesome_pro')) fa-duotone @else fa-solid @endif fa-comments nav-icon"></i>
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
                        @if(config('settings.fontawesome_pro'))
                        <i class="fa-duotone fa-file-magnifying-glass nav-icon"></i>
                        @else
                        <i class="fa-solid fa-magnifying-glass nav-icon"></i>
                        @endif
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
                @if(config('settings.fontawesome_pro'))
                <i class="fa-duotone fa-page nav-icon"></i>
                @else
                <i class="fa-solid fa-file nav-icon"></i>
                @endif
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
                        @if(config('settings.fontawesome_pro'))
                        <i class="fa-duotone fa-page nav-icon"></i>
                        @else
                        <i class="fa-solid fa-file nav-icon"></i>
                        @endif
                        <p>@lang('general.new')</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{route('admin.posts', ['type' => 'pages', 'language' => app('default_language')->code])}}"
                       class="nav-link
                   @if(request()->is(config('settings.admin_panel_path').'/pages',
                    config('settings.admin_panel_path').'/pages/*/edit')) active @endif ">
                        <i class=" @if(config('settings.fontawesome_pro')) fa-duotone @else fa-solid @endif fa-file-lines nav-icon"></i>
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
                @if(config('settings.fontawesome_pro'))
                <i class="fa-duotone fa-message-text nav-icon"></i>
                @else
                <i class="fa-solid fa-envelope nav-icon"></i>
                @endif
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
            @if(config('settings.fontawesome_pro'))
            <i class="fa-duotone fa-file-lock nav-icon"></i>
            @else
                <i class="fa-solid fa-file-shield nav-icon"></i>
            @endif
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
                    @if(config('settings.fontawesome_pro'))
                    <i class="fa-duotone fa-page nav-icon"></i>
                    @else
                        <i class="fa-solid fa-file nav-icon"></i>
                    @endif
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
                    @if(config('settings.fontawesome_pro'))
                    <i class="fa-duotone fa-file-lock nav-icon"></i>
                    @else
                        <i class="fa-solid fa-file-shield nav-icon"></i>
                    @endif
                    <p>@lang('notes.all_notes')</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{route('admin.notes.categories')}}"
                   class="nav-link
                   @if(request()->is(config('settings.admin_panel_path').'/notes/categories*')) active @endif ">
                    @if(config('settings.fontawesome_pro'))
                    <i class="fa-duotone fa-list nav-icon"></i>
                    @else
                        <i class="fa-solid fa-file-lines nav-icon"></i>
                    @endif
                    <p>
                        @lang('categories.categories')
                    </p>
                </a>
            </li>
        </ul>
    </li>
    @if(Module::find('Podcast')?->isEnabled())
        @can('moderator', 'App\Models\User')
            <li class="nav-header">Podcast</li>
            <li class="nav-item has-treeview
            @if(request()->is(config('settings.admin_panel_path').'/podcast*')) menu-open @endif ">
                <a class="nav-link
                    @if(request()->is(config('settings.admin_panel_path').'/notes*')) active @endif "
                   href="javascript:void(0);">
                    @if(config('settings.fontawesome_pro'))
                        <i class="fa-duotone fa-solid fa-podcast nav-icon"></i>
                    @else
                        <i class="fa-solid fa-podcast nav-icon"></i>
                    @endif
                    <p>
                        Podcast
                        <i class="right fas fa-angle-left"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview shadow rounded py-2">
                    <li class="nav-item">
                        <a href="{{route('panel.podcast.index')}}"
                           class="nav-link
                            @if(request()->is(config('settings.admin_panel_path').'/podcast/admin*')) active @endif ">
                            @if(config('settings.fontawesome_pro'))
                                <i class="fa-duotone fa-page nav-icon"></i>
                            @else
                                <i class="fa-solid fa-file nav-icon"></i>
                            @endif
                            <p>@lang('podcast::panel.podcast_list')</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{route('panel.podcast.channels')}}"
                           class="nav-link
                            @if(request()->is(config('settings.admin_panel_path').'/podcast/channels*')) active @endif ">
                            @if(config('settings.fontawesome_pro'))
                                <i class="fa-duotone fa-page nav-icon"></i>
                            @else
                                <i class="fa-solid fa-file nav-icon"></i>
                            @endif
                            <p>@lang('podcast::panel.channels')</p>
                        </a>
                    </li>
                </ul>
            </li>
        @endcan
    @endif
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
                <i class=" @if(config('settings.fontawesome_pro')) fa-duotone @else fa-solid @endif fa-route nav-icon"></i>
                <p>
                    @lang('redirects.redirects')
                </p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{route('admin.users')}}" class="nav-link
        @if(request()->is(config('settings.admin_panel_path').'/users*')) active @endif ">
                <i class=" @if(config('settings.fontawesome_pro')) fa-duotone @else fa-solid @endif fa-user nav-icon"></i>
                <p>
                    @lang('user.users')
                </p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{route('admin.menu.index')}}" class="nav-link
            @if(request()->is(config('settings.admin_panel_path').'/menu*')) active @endif ">
                <i class=" @if(config('settings.fontawesome_pro')) fa-duotone @else fa-solid @endif fa-bars nav-icon"></i>
                <p>
                    @lang('menu.menu')
                </p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{route('admin.settings')}}"
               class="nav-link @if(request()->is(config('settings.admin_panel_path').'/settings*')) active @endif ">
                <i class=" @if(config('settings.fontawesome_pro')) fa-duotone @else fa-solid @endif fa-gear nav-icon"></i>
                <p>
                    @lang('settings.settings')
                </p>
            </a>
        </li>
        @can('cloudflare', 'App\Models\User')
            <li class="nav-item has-treeview @if(request()->is(config('settings.admin_panel_path').'/cloudflare*')) menu-open @endif">
                <a href="javascript:void(0);"
                   class="nav-link @if(request()->is(config('settings.admin_panel_path').'/cloudflare*')) active @endif ">
                    <i class="fa-brands fa-cloudflare nav-icon"></i>
                    <p>
                        Cloudflare
                        <i class="right fas fa-angle-left"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview shadow rounded py-2">
                    <li class="nav-item">
                        <a href="{{route('cf.dashboard')}}" class="nav-link
                    @if(request()->is(config('settings.admin_panel_path').'/cloudflare')) active @endif ">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>
                                @lang('dashboard.dashboard')
                            </p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{route('cf.dns')}}"
                           class="nav-link
                   @if(request()->is(config('settings.admin_panel_path').'/cloudflare/dns')) active @endif ">
                            <i class=" @if(config('settings.fontawesome_pro')) fa-duotone @else fa-solid @endif fa-solid fa-globe nav-icon"></i>
                            <p>DNS</p>
                        </a>
                    </li>
                </ul>
            </li>
        @endcan
        <li class="nav-item">
            <a class="nav-link clear-cache" href="javascript:void(0);">
                <i class=" @if(config('settings.fontawesome_pro')) fa-duotone @else fa-solid @endif fa-trash-can nav-icon"></i>
                <p>
                    @lang('cache.clear_cache')
                </p>
            </a>
        </li>
        <li class="nav-header">
            @lang('settings.monitoring')
        </li>
        <li class="nav-item">
            <a href="{{route('admin.monitoring.pulse')}}"
               class="nav-link @if(request()->is(config('settings.admin_panel_path').'/monitoring/pulse')) active @endif ">
                @if(config('settings.fontawesome_pro'))
                <i class="fa-duotone fa-wave-pulse nav-icon"></i>
                @else
                    <i class="fa-solid fa-heart-pulse nav-icon"></i>
                @endif
                <p>
                    Pulse
                </p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{route('admin.monitoring.telescope')}}"
               class="nav-link @if(request()->is(config('settings.admin_panel_path').'/monitoring/telescope')) active @endif ">
                @if(config('settings.fontawesome_pro'))
                <i class="fa-duotone fa-telescope nav-icon"></i>
                @else
                    <i class="fa-solid fa-binoculars nav-icon"></i>
                @endif
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
                <i class=" @if(config('settings.fontawesome_pro')) fa-duotone @else fa-solid @endif fa-clipboard-list nav-icon"></i>
                <p>
                    Log Viewer
                </p>
            </a>
        </li>
    @endcan
</ul>
