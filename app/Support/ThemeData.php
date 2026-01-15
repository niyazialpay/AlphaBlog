<?php

namespace App\Support;

use App\Models\ContactPage;
use App\Models\Menu\Menu;
use App\Models\Menu\MenuItems;
use App\Models\Post\Categories;
use App\Models\Post\Comments;
use App\Models\Post\Posts;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Spatie\Honeypot\Honeypot;

class ThemeData
{
    public static function site(): array
    {
        $seo = app()->bound('seo_settings') ? app('seo_settings') : null;
        $general = app()->bound('general_settings') ? app('general_settings') : null;
        $language = self::currentLanguage();

        $searchSlug = self::firstRouteVariant('route_search', $language, 'search');

        return [
            'name' => $seo?->site_name,
            'title' => $seo?->title,
            'description' => $seo?->description,
            'robots' => $seo?->robots,
            'keywords' => $seo?->keywords,
            'contactEmail' => $general?->contact_email,
            'logo' => [
                'light' => $general ? self::mediaUrl($general->getFirstMediaUrl('site_logo_light')) : null,
                'dark' => $general ? self::mediaUrl($general->getFirstMediaUrl('site_logo_dark')) : null,
            ],
            'favicon' => $general ? self::mediaUrl($general->getFirstMediaUrl('site_favicon')) : null,
            'appIcon' => $general ? self::mediaUrl($general->getFirstMediaUrl('app_icon')) : null,
            'ogImage' => $general ? self::mediaUrl(
                $general->getFirstMediaUrl('site_og_image') ?: $general->getFirstMediaUrl('site_favicon')
            ) : null,
            'icons' => self::faviconVariants($general),
            'manifest' => route('manifest'),
            'homeUrl' => route('home', ['language' => $language]),
            'themeColor' => '#ffffff',
            'msTile' => [
                'color' => '#ffffff',
                'image' => $general ? self::mediaUrl($general->getFirstMediaUrl('site_favicon', 'r_144x144')) : null,
            ],
            'dnsPrefetch' => self::dnsPrefetchHosts(),
            'rss' => route('rss', ['language' => $language]),
            'shareThis' => $general?->sharethis,
            'searchUrl' => route('search.result', [
                'language' => $language,
                'search_result' => $searchSlug,
                'search_term' => null,
            ]),
        ];
    }

    public static function baseStructuredData(): array
    {
        $site = self::site();
        $languages = app()->bound('languages') ? collect(app('languages')) : collect();
        $language = self::currentLanguage();
        $homeUrl = route('home', ['language' => $language]);

        $searchSlug = self::firstRouteVariant('route_search', $language, 'search');

        $organizationImage = $site['logo']['light']
            ?? $site['logo']['dark']
            ?? null;

        $sameAs = $languages
            ->map(function ($lang) {
                $code = $lang->code ?? null;
                if (! $code) {
                    return null;
                }

                return route('home', ['language' => $code]);
            })
            ->filter()
            ->values()
            ->toArray();

        $organization = self::filterEmpty([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'image' => $organizationImage,
            'url' => config('app.url'),
            'sameAs' => $sameAs,
            'logo' => $organizationImage,
            'name' => $site['name'] ?? $site['title'] ?? config('app.name'),
            'description' => $site['description'] ?? null,
            'email' => $site['contactEmail'] ?? null,
        ]);

        $searchPlaceholder = '__SEARCH_TERM_PLACEHOLDER__';

        $searchUrl = route('search.result', [
            'language' => $language,
            'search_result' => $searchSlug,
            'search_term' => $searchPlaceholder,
        ]);

        $searchUrl = str_replace($searchPlaceholder, '{search_term}', $searchUrl);

        $website = self::filterEmpty([
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $site['name'] ?? $site['title'] ?? config('app.name'),
            'url' => $homeUrl,
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => $searchUrl,
                'query-input' => [
                    '@type' => 'PropertyValueSpecification',
                    'valueRequired' => true,
                    'valueMaxlength' => 150,
                    'valueName' => 'search_term',
                ],
            ],
        ]);

        return self::filterEmpty([
            'organization' => $organization,
            'website' => $website,
        ]);
    }

    public static function analytics(): array
    {
        $analytics = app()->bound('analytic_settings') ? app('analytic_settings') : null;

        if (! $analytics) {
            return [];
        }

        return [
            'googleAnalytics' => $analytics->google_analytics,
            'yandexMetrica' => $analytics->yandex_metrica,
            'facebookPixel' => $analytics->fb_pixel,
            'logRocket' => $analytics->log_rocket,
        ];
    }

    public static function theme(): array
    {
        $adminPath = trim(config('settings.admin_panel_path'), '/');

        return [
            'name' => app()->bound('theme') ? app('theme')->name : null,
            'renderer' => config('theme.renderer'),
            'adminPath' => $adminPath,
            'profilePath' => $adminPath !== '' ? $adminPath.'/profile' : 'profile',
        ];
    }

    public static function languages(): array
    {
        $current = self::currentLanguage();
        $items = app()->bound('languages') ? app('languages') : collect();
        $flagResolver = fn (?string $flag): ?string => self::flagAsset($flag);

        return [
            'current' => [
                'code' => $current,
                'name' => session('language_name'),
                'flag' => session('language_flag'),
                'flagUrl' => $flagResolver(session('language_flag')),
            ],
            'items' => $items->map(function ($language) use ($flagResolver) {
                $params = ['language' => $language->code];

                return [
                    'code' => $language->code,
                    'name' => $language->name,
                    'flag' => $language->flag,
                    'isDefault' => (bool) $language->is_default,
                    'url' => route('home', $params),
                    'flagUrl' => $flagResolver($language->flag),
                ];
            })->values()->toArray(),
        ];
    }

    public static function social(): array
    {
        $social = app()->bound('social_networks') ? app('social_networks') : null;

        if (! $social) {
            return [];
        }

        $socialSettings = app()->bound('social_settings') ? app('social_settings') : null;
        $headerAllow = self::decodeSocialList($socialSettings?->social_networks_header);
        $footerAllow = self::decodeSocialList($socialSettings?->social_networks_footer);

        $allNetworks = self::socialNetworksMap();
        $rawLinks = [];
        $headerLinks = [];
        $footerLinks = [];

        foreach ($allNetworks as $key => $meta) {
            $value = $social->{$key} ?? null;
            if (! $value) {
                continue;
            }

            $showInHeader = empty($headerAllow) || in_array($key, $headerAllow, true);
            $showInFooter = empty($footerAllow) || in_array($key, $footerAllow, true);
            $builtUrl = self::buildSocialUrl($key, $value, $meta['base']);

            $entry = [
                'key' => $key,
                'label' => $meta['label'],
                'icon' => $meta['icon'],
                'url' => $builtUrl,
                'showInHeader' => $showInHeader,
                'showInFooter' => $showInFooter,
            ];

            $rawLinks[$key] = $entry;

            $filteredEntry = [
                'key' => $entry['key'],
                'label' => $entry['label'],
                'icon' => $entry['icon'],
                'url' => $entry['url'],
            ];

            if ($showInHeader) {
                $headerLinks[] = $filteredEntry;
            }

            if ($showInFooter) {
                $footerLinks[] = $filteredEntry;
            }
        }

        $language = self::currentLanguage();
        $rssUrl = route('rss', ['language' => $language]);
        $rssEntry = [
            'key' => 'rss',
            'label' => 'RSS',
            'icon' => 'fa-solid fa-rss',
            'url' => $rssUrl,
        ];

        $rawLinks['rss'] = [
            'key' => 'rss',
            'label' => 'RSS',
            'icon' => 'fa-solid fa-rss',
            'url' => $rssUrl,
            'showInHeader' => false,
            'showInFooter' => empty($footerAllow) || in_array('rss', $footerAllow, true),
        ];

        if ($rawLinks['rss']['showInFooter']) {
            $footerLinks[] = $rssEntry;
        }

        $mobileLinks = [];
        foreach ($rawLinks as $entry) {
            if ($entry['showInHeader'] || $entry['showInFooter']) {
                $mobileLinks[$entry['key']] = [
                    'key' => $entry['key'],
                    'label' => $entry['label'],
                    'icon' => $entry['icon'],
                    'url' => $entry['url'],
                ];
            }
        }

        $result = [
            'links' => array_values($rawLinks),
            'headerLinks' => $headerLinks,
            'footerLinks' => $footerLinks,
            'mobileLinks' => array_values($mobileLinks),
        ];

        foreach ($allNetworks as $key => $meta) {
            $result[$key] = $social->{$key} ?? null;
        }

        $result['rss'] = $rssUrl;

        return $result;
    }

    public static function headerMenu(): array
    {
        $language = request()->route('language')
            ?? session('language')
            ?? app()->getLocale();
        $cacheKey = config('cache.prefix').'header_menu_tree_'.$language;

        return Cache::rememberForever($cacheKey, function () use ($language) {
            $menu = Menu::with([
                'menuItems' => function ($query) {
                    $query->whereNull('parent_id')
                        ->orderBy('order')
                        ->with([
                            'children' => function ($query) {
                                $query->orderBy('order')
                                    ->with([
                                        'children' => function ($query) {
                                            $query->orderBy('order');
                                        },
                                    ]);
                            },
                        ]);
                },
            ])
                ->where('language', $language)
                ->where('menu_position', 'header')
                ->first();

            if (! $menu) {
                return [];
            }

            return $menu->menuItems
                ->map(fn (MenuItems $item) => self::transformMenuItem($item))
                ->values()
                ->toArray();
        });
    }

    public static function navigationCategories(int $limit = 8): array
    {
        $language = self::currentLanguage();
        $cacheKey = config('cache.prefix').'navigation_categories_'.$language.'_'.$limit;

        return Cache::remember($cacheKey, now()->addHours(12), function () use ($language, $limit) {
            $categories = Categories::with(['children' => function ($query) {
                $query->withCount('posts')->orderByDesc('posts_count');
            }])
                ->withCount('posts')
                ->whereNull('parent_id')
                ->where('language', $language)
                ->orderByDesc('posts_count')
                ->limit($limit)
                ->get();

            return $categories
                ->map(fn (Categories $category) => self::transformCategory($category, true))
                ->values()
                ->toArray();
        });
    }

    public static function topCategories(int $limit = 6): array
    {
        $language = self::currentLanguage();
        $cacheKey = config('cache.prefix').'top_categories_vue_'.$language.'_'.$limit;

        return Cache::remember($cacheKey, now()->addHours(12), function () use ($language, $limit) {
            $categories = Categories::withCount('posts')
                ->where('language', $language)
                ->orderByDesc('posts_count')
                ->limit($limit)
                ->get();

            return $categories
                ->map(fn (Categories $category) => self::transformCategory($category))
                ->values()
                ->toArray();
        });
    }

    public static function featuredPosts(int $limit = 6): array
    {
        $language = self::currentLanguage();
        $cacheKey = config('cache.prefix').'featured_posts_vue_v2_'.$language.'_'.$limit;

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($language, $limit) {
            $posts = Posts::with(['categories', 'user.social', 'media'])
                ->where('post_type', 'post')
                ->where('language', $language)
                ->where('is_published', true)
                ->whereDate('created_at', '<=', now()->format('Y-m-d H:i:s'))
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return self::postsCollection($posts);
        });
    }

    public static function recentPosts(int $limit = 6, int $skip = 0): array
    {
        $language = self::currentLanguage();
        $cacheKey = config('cache.prefix').'recent_posts_vue_v2_'.$language.'_'.$limit.'_skip_'.$skip;

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($language, $limit, $skip) {
            $posts = Posts::with(['categories', 'user.social', 'media'])
                ->where('post_type', 'post')
                ->where('language', $language)
                ->where('is_published', true)
                ->whereDate('created_at', '<=', now()->format('Y-m-d H:i:s'))
                ->orderBy('created_at', 'desc')
                ->skip($skip)
                ->take($limit)
                ->get();

            return self::postsCollection($posts);
        });
    }

    public static function paginatedPosts(int $perPage = 10, int $skip = 0): array
    {
        $language = self::currentLanguage();
        $page = (int) (request()->get('page') ?? 1);

        $cacheKey = config('cache.prefix').'paginated_posts_vue_'.$language.'_page_'.$page.'_'.$perPage.'_skip_'.$skip;

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($language, $perPage, $page, $skip) {
            $query = Posts::with(['categories', 'user.social', 'media'])
                ->where('post_type', 'post')
                ->where('language', $language)
                ->where('is_published', true)
                ->whereDate('created_at', '<=', now()->format('Y-m-d H:i:s'))
                ->orderBy('created_at', 'desc');

            $total = (clone $query)->count();

            $posts = $query->skip(($page - 1) * $perPage + $skip)
                ->take($perPage)
                ->get();

            $paginator = new LengthAwarePaginator(
                $posts,
                $total,
                $perPage,
                $page,
                ['path' => request()->url(), 'query' => request()->query()]
            );

            return self::postsPaginatorToArray($paginator);
        });
    }

    public static function relatedPosts(Posts $post, int $limit = 4): array
    {
        $language = $post->language ?? session('language') ?? app()->getLocale();
        $categoryIds = $post->categories->pluck('id');

        if ($categoryIds->isEmpty()) {
            return [];
        }

        $related = Posts::with(['categories', 'user.social', 'media'])
            ->where('post_type', 'post')
            ->where('language', $language)
            ->where('is_published', true)
            ->where('id', '!=', $post->id)
            ->whereHas('categories', function ($query) use ($categoryIds) {
                $query->whereIn('categories.id', $categoryIds);
            })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return self::postsCollection($related);
    }

    public static function postsCollection(Collection|array $posts): array
    {
        return collect($posts)
            ->map(fn (Posts $post) => self::postSummary($post))
            ->values()
            ->toArray();
    }

    public static function postsPaginatorToArray(LengthAwarePaginator $paginator): array
    {
        $paginator->getCollection()->loadMissing(['categories', 'user.social', 'media']);

        return [
            'items' => self::postsCollection($paginator->getCollection()),
            'meta' => self::paginationMeta($paginator),
        ];
    }

    protected static function paginationMeta(LengthAwarePaginator $paginator): array
    {
        $links = method_exists($paginator, 'linkCollection')
            ? collect($paginator->linkCollection())->map(function ($link) {
                return [
                    'url' => $link['url'] ?? null,
                    'label' => isset($link['label']) ? strip_tags($link['label']) : null,
                    'active' => $link['active'] ?? false,
                ];
            })->toArray()
            : [];

        return [
            'currentPage' => $paginator->currentPage(),
            'lastPage' => $paginator->lastPage(),
            'perPage' => $paginator->perPage(),
            'total' => $paginator->total(),
            'nextPageUrl' => $paginator->nextPageUrl(),
            'prevPageUrl' => $paginator->previousPageUrl(),
            'links' => $links,
        ];
    }

    public static function postSummary(Posts $post): array
    {
        $post->loadMissing(['categories', 'user.social', 'media']);

        $primaryCategory = $post->categories->first();

        return [
            'id' => $post->id,
            'title' => stripslashesNull($post->title),
            'slug' => $post->slug,
            'type' => $post->post_type,
            'excerpt' => Str::limit(strip_tags(stripslashesNull($post->content)), 220),
            'image' => self::postImage($post, 'resized'),
            'category' => $primaryCategory ? self::categorySummary($primaryCategory) : null,
            'categories' => $post->categories->map(fn (Categories $category) => self::categorySummary($category))->toArray(),
            'author' => $post->user ? self::authorSummary($post->user) : null,
            'publishedAt' => optional($post->created_at)->toIso8601String(),
            'readTime' => self::estimateReadTime($post->content),
            'views' => (int) $post->views,
            'archiveUrl' => self::postArchiveUrl($post),
            'url' => route('page', [
                'language' => $post->language,
                'showPost' => $post->slug,
            ]),
        ];
    }

    public static function postDetail(Posts $post): array
    {
        $post->loadMissing(['categories', 'user.social', 'media']);

        return array_merge(self::postSummary($post), [
            'content' => stripslashesNull($post->content),
            'meta' => [
                'description' => stripslashesNull($post->meta_description),
                'keywords' => $post->meta_keywords,
            ],
            'tags' => self::postTags($post),
            'hrefLang' => $post->href_lang ? json_decode($post->href_lang, true) : null,
            'comments' => self::postComments($post),
            'commentForm' => self::commentFormConfig($post),
            'structuredData' => self::postStructuredData($post),
        ]);
    }

    public static function postStructuredData(Posts $post): array
    {
        $post->loadMissing(['categories', 'user', 'user.social', 'media', 'comments.user']);

        $language = session('language') ?? $post->language ?? app()->getLocale();
        $site = self::site();

        $articleUrl = route('page', [
            'language' => $language,
            'showPost' => $post->slug,
        ]);

        $author = $post->user;
        $authorName = $author
            ? ($author->display_name ?: $author->full_name ?: $author->nickname)
            : null;

        $userSlug = self::firstRouteVariant('route_user', $language, 'user');

        $authorUrl = $author
            ? route('user.posts', [
                'language' => $language,
                'user' => $userSlug,
                'users' => $author->nickname,
            ])
            : null;

        $imageUrl = null;
        $imageWidth = null;
        $imageHeight = null;

        $media = $post->media
            ->where('collection_name', 'posts')
            ->last();

        if ($media) {
            $imageUrl = route('image', [
                'path' => $media->id,
                'width' => 840,
                'height' => 341,
                'type' => 'cover',
                'image' => $media->file_name,
            ]);
            $imageWidth = 840;
            $imageHeight = 341;
        } else {
            $imageUrl = self::postImage($post);
        }

        $article = self::filterEmpty([
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'inLanguage' => $post->language ?? $language,
            'name' => stripslashesNull($post->title),
            'headline' => stripslashesNull($post->title),
            'alternativeHeadline' => stripslashesNull($post->title),
            'keywords' => $post->meta_keywords ?: null,
            'articleSection' => optional($post->categories->first())->name ?? null,
            'articleBody' => self::sanitizeForJsonLd($post->content),
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $articleUrl,
            ],
            'author' => $authorName ? self::filterEmpty([
                '@type' => 'Person',
                'name' => $authorName,
                'url' => $authorUrl,
            ]) : null,
            'image' => $imageUrl ? self::filterEmpty([
                '@type' => 'ImageObject',
                'url' => $imageUrl,
                'width' => $imageWidth,
                'height' => $imageHeight,
            ]) : null,
            'datePublished' => optional($post->created_at)->toIso8601String(),
            'dateModified' => optional($post->updated_at ?: $post->created_at)->toIso8601String(),
            'url' => $articleUrl,
            'publisher' => self::filterEmpty([
                '@type' => 'Organization',
                'name' => $site['name'] ?? $site['title'] ?? config('app.name'),
                'logo' => ($site['logo']['light'] ?? $site['logo']['dark']) ? [
                    '@type' => 'ImageObject',
                    'url' => $site['logo']['light'] ?? $site['logo']['dark'],
                ] : null,
            ]),
        ]);

        $homeLabel = Lang::get('home.home', [], $language);
        if (! is_string($homeLabel) || trim($homeLabel) === '') {
            $homeLabel = 'Home';
        }

        $breadcrumbItems = [];
        $position = 1;
        $breadcrumbItems[] = [
            '@type' => 'ListItem',
            'position' => $position++,
            'name' => $homeLabel,
            'item' => route('home', ['language' => $language]),
        ];

        $categorySlug = self::firstRouteVariant('route_categories', $language, 'categories');

        foreach ($post->categories as $category) {
            $breadcrumbItems[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => stripslashesNull($category->name),
                'item' => route('post.categories', [
                    'language' => $language,
                    'categories' => $categorySlug,
                    'showCategory' => $category->slug,
                ]),
            ];
        }

        $breadcrumbItems[] = [
            '@type' => 'ListItem',
            'position' => $position,
            'name' => stripslashesNull($post->title),
            'item' => $articleUrl,
        ];

        $breadcrumb = self::filterEmpty([
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'name' => stripslashesNull($post->title),
            'itemListElement' => $breadcrumbItems,
        ]);

        $comments = $post->comments
            ->filter(fn (Comments $comment) => (bool) $comment->is_approved)
            ->map(function (Comments $comment) use ($articleUrl) {
                $authorName = $comment->user?->nickname ?? $comment->name;
                $anchor = '#comment-'.$comment->id;

                return self::filterEmpty([
                    '@type' => 'Comment',
                    'name' => $authorName,
                    '@id' => $articleUrl.$anchor,
                    'text' => self::sanitizeForJsonLd($comment->comment),
                    'dateCreated' => optional($comment->created_at)->toIso8601String(),
                    'author' => self::filterEmpty([
                        '@type' => 'Person',
                        'name' => $authorName,
                        'url' => $articleUrl.$anchor,
                    ]),
                ]);
            })
            ->filter()
            ->values()
            ->toArray();

        return self::filterEmpty([
            'article' => $article,
            'breadcrumb' => $breadcrumb,
            'comments' => $comments,
        ]);
    }

    protected static function postComments(Posts $post): array
    {
        $post->loadMissing(['comments.user']);

        return $post->comments
            ->filter(fn (Comments $comment) => (bool) $comment->is_approved)
            ->map(function (Comments $comment) use ($post) {
                return [
                    'id' => $comment->id,
                    'anchor' => 'comment-'.$comment->id,
                    'author' => [
                        'name' => $comment->user?->nickname ?? $comment->name,
                        'profileUrl' => $comment->user
                            ? route('user.posts', [
                                'language' => session('language') ?? $post->language ?? app()->getLocale(),
                                'user' => __('routes.user'),
                                'users' => $comment->user->nickname,
                            ])
                            : null,
                        'email' => $comment->user?->email ?? $comment->email,
                        'avatar' => self::commentAvatar($comment),
                    ],
                    'createdAt' => optional($comment->created_at)->toIso8601String(),
                    'dateFormatted' => $comment->created_at
                        ? dateformat($comment->created_at, 'd M Y H:i', locale: session('language'), timezone: config('app.timezone'))
                        : null,
                    'content' => stripslashesNull($comment->comment),
                ];
            })
            ->values()
            ->toArray();
    }

    protected static function commentFormConfig(Posts $post): array
    {
        $language = session('language') ?? $post->language ?? app()->getLocale();
        /** @var Honeypot $honeypot */
        $honeypot = app(Honeypot::class);
        $honeypotFields = $honeypot->toArray();

        return [
            'action' => route('comment.save', ['language' => $language]),
            'method' => 'POST',
            'postId' => $post->id,
            'csrfToken' => csrf_token(),
            'honeypot' => $honeypotFields,
            'turnstileSiteKey' => config('cloudflare.turnstile_site_key'),
        ];
    }

    protected static function commentAvatar(Comments $comment): string
    {
        if ($comment->user) {
            $mediaAvatar = self::mediaUrl($comment->user->getFirstMediaUrl('profile'));
            if ($mediaAvatar) {
                return $mediaAvatar;
            }

            if ($comment->user->profile_image) {
                return replaceCDN($comment->user->profile_image);
            }
        }

        if ($comment->email) {
            return 'https://www.gravatar.com/avatar/'.hash('sha256', strtolower(trim($comment->email))).'?d=mp';
        }

        return 'https://www.gravatar.com/avatar/?d=mp';
    }

    public static function postsForCategory(Categories $category, int $perPage = 10): array
    {
        $paginator = Posts::with(['categories', 'user.social', 'media'])
            ->where('post_type', 'post')
            ->where('language', $category->language)
            ->where('is_published', true)
            ->whereHas('categories', function ($query) use ($category) {
                $query->where('categories.id', $category->id);
            })
            ->whereDate('created_at', '<=', now()->format('Y-m-d H:i:s'))
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return self::postsPaginatorToArray($paginator);
    }

    public static function postsForUser(User $user, int $perPage = 10): array
    {
        $language = session('language') ?? $user->preferredLocale() ?? app()->getLocale();

        $paginator = Posts::with(['categories', 'user.social', 'media'])
            ->where('post_type', 'post')
            ->where('language', $language)
            ->where('is_published', true)
            ->where('user_id', $user->id)
            ->whereDate('created_at', '<=', now()->format('Y-m-d H:i:s'))
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return self::postsPaginatorToArray($paginator);
    }

    public static function searchPosts(?string $term, int $perPage = 10): array
    {
        if (! $term) {
            return [
                'items' => [],
                'meta' => [
                    'currentPage' => 1,
                    'lastPage' => 1,
                    'perPage' => $perPage,
                    'total' => 0,
                    'nextPageUrl' => null,
                    'prevPageUrl' => null,
                    'links' => [],
                ],
            ];
        }

        $paginator = Posts::search($term)
            ->query(function ($query) {
                $query->with(['categories', 'user.social', 'media'])
                    ->where('posts.created_at', '<=', now()->format('Y-m-d H:i:s'));
            })
            ->where('post_type', 'post')
            ->where('language', session('language') ?? app()->getLocale())
            ->where('is_published', 1)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return self::postsPaginatorToArray($paginator);
    }

    public static function postsFromPaginator(LengthAwarePaginator $paginator): array
    {
        return self::postsPaginatorToArray($paginator);
    }

    public static function authorSummary(User $user): array
    {
        $user->loadMissing('social');

        if (! isset($user->posts_count)) {
            $user->loadCount('posts');
        }

        return [
            'id' => $user->id,
            'name' => $user->display_name ?: $user->full_name ?: $user->nickname,
            'nickname' => $user->nickname,
            'slug' => $user->nickname,
            'title' => $user->display_job_title ?: $user->job_title,
            'bio' => $user->display_about ?: $user->about,
            'avatar' => self::mediaUrl($user->getFirstMediaUrl('profile')) ?: replaceCDN($user->profile_image),
            'website' => $user->social?->website,
            'github' => $user->social?->github,
            'twitter' => $user->social?->x,
            'linkedin' => $user->social?->linkedin,
            'email' => $user->email,
            'joinedAt' => optional($user->created_at)->toIso8601String(),
            'postCount' => $user->posts_count,
            'url' => route('user.posts', [
                'language' => session('language') ?? app()->getLocale(),
                'user' => __('routes.user'),
                'users' => $user->nickname,
            ]),
            'socialLinks' => self::authorSocialLinks($user),
        ];
    }

    public static function authorDetail(User $user, Collection|array $posts = []): array
    {
        return [
            'author' => self::authorSummary($user),
            'posts' => self::postsCollection($posts),
        ];
    }

    protected static function authorSocialLinks(User $user): array
    {
        $links = $user->visible_social_links ?? [];

        if (! is_array($links) && ! $links instanceof \Traversable) {
            return [];
        }

        return collect($links)
            ->map(function ($data, $platform) {
                if (! is_array($data)) {
                    return null;
                }

                $url = $data['url'] ?? null;
                if (! $url) {
                    return null;
                }

                return [
                    'type' => $platform,
                    'label' => ucfirst(str_replace(['_', '-'], ' ', $platform)),
                    'url' => $url,
                    'icon' => $data['icon'] ?? null,
                ];
            })
            ->filter()
            ->values()
            ->toArray();
    }

    public static function categorySummary(Categories $category): array
    {
        $category->loadMissing('children');

        $language = session('language') ?? $category->language ?? app()->getLocale();

        return [
            'id' => $category->id,
            'name' => stripslashesNull($category->name),
            'slug' => $category->slug,
            'description' => stripslashesNull($category->description) ?? stripslashesNull($category->meta_description),
            'postCount' => $category->posts_count ?? $category->posts()->count(),
            'image' => self::mediaUrl($category->getFirstMediaUrl('categories', 'thumb')),
            'url' => route('post.categories', [
                'language' => $language,
                'categories' => __('routes.categories'),
                'showCategory' => $category->slug,
            ]),
        ];
    }

    public static function transformCategory(Categories $category, bool $withChildren = false): array
    {
        $data = self::categorySummary($category);

        if ($withChildren) {
            $data['children'] = $category->children
                ->map(fn (Categories $child) => self::transformCategory($child, true))
                ->values()
                ->toArray();
        }

        return $data;
    }

    public static function categoryDetail(Categories $category, int $perPage = 12): array
    {
        return [
            'category' => array_merge(self::categorySummary($category), [
                'meta' => [
                    'description' => stripslashesNull($category->meta_description),
                    'keywords' => $category->meta_keywords,
                    'hrefLang' => $category->href_lang ? json_decode($category->href_lang, true) : null,
                ],
            ]),
            'posts' => self::postsForCategory($category, $perPage),
        ];
    }

    public static function translations(): array
    {
        $language = session('language') ?? app()->getLocale();

        $sections = [
            'navbar',
            'home',
            'archive',
            'search',
            'tags',
            'user',
            'post',
            'footer',
            'authors',
            'categories',
            'contact_page',
            'breadcrumbs',
        ];

        $translations = [];

        foreach ($sections as $section) {
            $value = Lang::get("crypt.$section", [], $language);
            if (is_array($value)) {
                $translations[$section] = $value;
            }
        }

        $contact = Lang::get('contact', [], $language);
        if (is_array($contact)) {
            $translations['contact'] = $contact;
        }

        $routes = Lang::get('routes', [], $language);
        if (is_array($routes)) {
            $translations['routes'] = $routes;
        }

        return $translations;
    }

    public static function ads(): array
    {
        $ads = app()->bound('ad_settings') ? app('ad_settings') : null;

        if (! $ads) {
            return [
                'horizontal' => null,
                'vertical' => null,
                'square' => null,
                'manager' => null,
            ];
        }

        return [
            'horizontal' => $ads->horizontal_display_advertise,
            'vertical' => $ads->vertical_display_advertise,
            'square' => $ads->square_display_advertise,
            'manager' => $ads->google_ad_manager,
        ];
    }

    protected static function decodeSocialList(mixed $value): array
    {
        if (is_array($value)) {
            return self::normalizeSocialList($value);
        }

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return self::normalizeSocialList($decoded);
            }
        }

        return [];
    }

    protected static function normalizeSocialList(array $entries): array
    {
        return array_values(
            array_filter(
                array_map(function ($entry) {
                    if (! is_string($entry)) {
                        return null;
                    }

                    $normalized = self::normalizeSocialKey($entry);

                    return $normalized !== null ? $normalized : null;
                }, $entries)
            )
        );
    }

    protected static function normalizeSocialKey(string $key): ?string
    {
        $trimmed = strtolower(trim($key));

        return match ($trimmed) {
            'twitter' => 'x',
            'bsky', 'bsky.app', 'bskyapp' => 'bluesky',
            'dev.to', 'dev_to', 'dev-to' => 'devto',
            'rss' => 'rss',
            '' => null,
            default => $trimmed,
        };
    }

    protected static function socialNetworksMap(): array
    {
        return [
            'website' => ['label' => 'Website', 'icon' => 'fa-solid fa-globe', 'base' => ''],
            'github' => ['label' => 'GitHub', 'icon' => 'fa-brands fa-github', 'base' => 'https://github.com/'],
            'linkedin' => ['label' => 'LinkedIn', 'icon' => 'fa-brands fa-linkedin-in', 'base' => 'https://www.linkedin.com/in/'],
            'facebook' => ['label' => 'Facebook', 'icon' => 'fa-brands fa-facebook-f', 'base' => 'https://www.facebook.com/'],
            'x' => ['label' => 'X', 'icon' => 'fa-brands fa-x-twitter', 'base' => 'https://twitter.com/'],
            'bluesky' => ['label' => 'Bluesky', 'icon' => 'fa-brands fa-bluesky', 'base' => 'https://bsky.app/profile/'],
            'instagram' => ['label' => 'Instagram', 'icon' => 'fa-brands fa-instagram', 'base' => 'https://www.instagram.com/'],
            'devto' => ['label' => 'Dev.to', 'icon' => 'fa-brands fa-dev', 'base' => 'https://dev.to/'],
            'medium' => ['label' => 'Medium', 'icon' => 'fa-brands fa-medium', 'base' => 'https://medium.com/@'],
            'youtube' => ['label' => 'YouTube', 'icon' => 'fa-brands fa-youtube', 'base' => 'https://www.youtube.com/'],
            'reddit' => ['label' => 'Reddit', 'icon' => 'fa-brands fa-reddit-alien', 'base' => 'https://www.reddit.com/user/'],
            'xbox' => ['label' => 'Xbox', 'icon' => 'fa-brands fa-xbox', 'base' => 'https://account.xbox.com/en-us/profile?gamertag='],
            'deviantart' => ['label' => 'DeviantArt', 'icon' => 'fa-brands fa-deviantart', 'base' => 'https://www.deviantart.com/'],
            'twitch' => ['label' => 'Twitch', 'icon' => 'fa-brands fa-twitch', 'base' => 'https://www.twitch.tv/'],
            'telegram' => ['label' => 'Telegram', 'icon' => 'fa-brands fa-telegram', 'base' => 'https://t.me/'],
            'discord' => ['label' => 'Discord', 'icon' => 'fa-brands fa-discord', 'base' => 'https://discord.gg/'],
        ];
    }

    protected static function buildSocialUrl(string $key, string $value, string $base): string
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            return '';
        }

        if (str_starts_with($trimmed, 'http://') || str_starts_with($trimmed, 'https://')) {
            return $trimmed;
        }

        if (str_starts_with($trimmed, '//')) {
            return 'https:'.$trimmed;
        }

        if ($base === '') {
            return $trimmed;
        }

        $handle = match ($key) {
            'x', 'bluesky' => ltrim($trimmed, '@'),
            default => $trimmed,
        };

        return $base.$handle;
    }

    protected static function postArchiveUrl(Posts $post): ?string
    {
        if (! $post->created_at) {
            return null;
        }

        $language = $post->language ?? session('language') ?? app()->getLocale();
        $slug = self::firstRouteVariant('route_archives', $language, 'archives');

        $params = [
            'language' => $language,
            'archives' => $slug,
            'year' => $post->created_at->format('Y'),
            'month' => $post->created_at->format('m'),
            'day' => $post->created_at->format('d'),
        ];

        return route('post.archives', $params);
    }

    protected static function transformMenuItem(MenuItems $item): array
    {
        $item->loadMissing('children');

        return [
            'id' => $item->id,
            'title' => $item->title,
            'url' => $item->url,
            'target' => $item->target,
            'icon' => $item->icon,
            'menuType' => $item->menu_type,
            'children' => $item->children
                ->sortBy('order')
                ->map(fn (MenuItems $child) => self::transformMenuItem($child))
                ->values()
                ->toArray(),
        ];
    }

    public static function authorsPaginatorToArray(LengthAwarePaginator $paginator): array
    {
        $paginator->getCollection()->loadMissing('social');

        return [
            'items' => $paginator->getCollection()
                ->map(fn (User $user) => self::authorSummary($user))
                ->values()
                ->toArray(),
            'meta' => self::paginationMeta($paginator),
        ];
    }

    public static function contactPage(?ContactPage $contact): array
    {
        if (! $contact) {
            return [];
        }

        $language = session('language') ?? $contact->language ?? app()->getLocale();

        return [
            'title' => self::resolveContactTitle($contact, $language),
            'description' => $contact->description,
            'meta' => [
                'description' => stripslashesNull($contact->meta_description),
                'keywords' => $contact->meta_keywords,
            ],
            'maps' => $contact->maps,
            'email' => app()->bound('general_settings') ? app('general_settings')->contact_email : null,
            'form' => [
                'action' => route('contact.send-ajax', [
                    'language' => $language,
                    'contact' => __('routes.contact'),
                ]),
                'csrfToken' => csrf_token(),
                'turnstileSiteKey' => config('cloudflare.turnstile_site_key'),
            ],
        ];
    }

    protected static function resolveContactTitle(?ContactPage $contact, string $language): string
    {
        $title = is_string($contact?->title) ? trim($contact->title) : '';

        if ($title !== '' && $language !== '' && strcasecmp($title, 'Contact '.$language) !== 0) {
            return $title;
        }

        $fallback = Lang::get('contact.title', [], $language);
        if (! is_string($fallback) || trim($fallback) === '') {
            $fallback = Lang::get('footer.links.contact', [], $language);
        }

        return (is_string($fallback) && trim($fallback) !== '') ? $fallback : 'Contact';
    }

    public static function metaForHome(): array
    {
        $site = self::site();
        $language = session('language') ?? app()->getLocale();
        $url = route('home', ['language' => $language]);

        return self::buildMeta([
            'title' => $site['title'] ?? $site['name'] ?? config('app.name'),
            'description' => $site['description'] ?? null,
            'keywords' => $site['keywords'] ?? null,
            'url' => $url,
            'image' => $site['ogImage'] ?? null,
            'robots' => $site['robots'] ?? null,
            'alternate' => self::languageAlternateLinks('home'),
        ]);
    }

    public static function metaForPost(Posts $post): array
    {
        $post->loadMissing(['categories', 'user.social', 'media']);

        $language = session('language') ?? $post->language ?? app()->getLocale();
        $site = self::site();

        $canonical = route('page', [
            'language' => $language,
            'showPost' => $post->slug,
        ]);

        $description = self::sanitizeMetaText(stripslashesNull($post->meta_description))
            ?? self::sanitizeMetaText(Str::limit(strip_tags((string) $post->excerpt), 160))
            ?? self::sanitizeMetaText(Str::limit(strip_tags((string) $post->content), 160));

        $keywords = $post->meta_keywords ?: ($site['keywords'] ?? null);

        $authorName = $post->user
            ? ($post->user->display_name ?: $post->user->full_name ?: $post->user->nickname)
            : null;

        $userSlug = self::firstRouteVariant('route_user', $language, 'user');
        $authorUrl = $post->user
            ? route('user.posts', [
                'language' => $language,
                'user' => $userSlug,
                'users' => $post->user->nickname,
            ])
            : null;

        $alternates = $post->href_lang ? self::alternateLinksFromJson($post->href_lang) : [];

        return self::buildMeta([
            'title' => stripslashesNull($post->title),
            'description' => $description,
            'keywords' => $keywords,
            'url' => $canonical,
            'image' => self::postImage($post),
            'robots' => $post->is_published ? 'index, follow' : 'noindex, nofollow',
            'type' => 'article',
            'author' => $authorName,
            'authorUrl' => $authorUrl,
            'siteName' => $site['name'] ?? $site['title'] ?? config('app.name'),
            'publishedTime' => optional($post->created_at)->toIso8601String(),
            'modifiedTime' => optional($post->updated_at ?: $post->created_at)->toIso8601String(),
            'section' => $post->categories->first()->name ?? null,
            'tags' => self::explodeKeywords($keywords),
            'alternate' => $alternates,
        ]);
    }

    public static function metaForCategory(Categories $category): array
    {
        $category->loadMissing('children');

        $language = session('language') ?? $category->language ?? app()->getLocale();
        $slug = self::firstRouteVariant('route_categories', $language, 'categories');

        $canonical = route('post.categories', [
            'language' => $language,
            'categories' => $slug,
            'showCategory' => $category->slug,
        ]);

        $description = self::sanitizeMetaText($category->meta_description)
            ?? self::sanitizeMetaText(Str::limit(strip_tags((string) $category->description), 160));

        $keywords = $category->meta_keywords;

        $alternates = $category->href_lang ? self::alternateLinksFromJson($category->href_lang) : [];

        return self::buildMeta([
            'title' => stripslashesNull($category->name),
            'description' => stripslashesNull($description),
            'keywords' => $keywords,
            'url' => $canonical,
            'image' => self::defaultOgImage(),
            'alternate' => $alternates,
        ]);
    }

    public static function metaForTag(?string $tag): array
    {
        $tag = $tag ? trim((string) $tag) : null;
        $language = session('language') ?? app()->getLocale();
        $site = self::site();

        $slug = self::firstRouteVariant('route_tags', $language, 'tags');

        $canonical = $tag
            ? route('post.tags', [
                'language' => $language,
                'tags' => $slug,
                'showTag' => $tag,
            ])
            : route('post.tags', [
                'language' => $language,
                'tags' => $slug,
                'showTag' => '',
            ]);

        $titleTemplate = Lang::get('crypt.tags.title', ['tag' => $tag], $language);
        $title = is_string($titleTemplate) && $titleTemplate !== '' ? $titleTemplate : ($tag ?? 'Tag');

        return self::buildMeta([
            'title' => $title,
            'description' => self::sanitizeMetaText($site['description'] ?? null),
            'keywords' => $site['keywords'] ?? null,
            'url' => $canonical,
            'image' => self::defaultOgImage(),
            'robots' => $site['robots'] ?? null,
        ]);
    }

    public static function metaForSearch(?string $term): array
    {
        $language = session('language') ?? app()->getLocale();
        $site = self::site();
        $slug = self::firstRouteVariant('route_search', $language, 'search');

        $canonical = route('search.result', [
            'language' => $language,
            'search_result' => $slug,
            'search_term' => $term,
        ]);

        $title = Lang::get('post.search_results_for', [], $language);
        if (! is_string($title) || $title === '') {
            $title = 'Search results for';
        }
        if ($term) {
            $title = trim($title).' '.$term;
        }

        return self::buildMeta([
            'title' => $title,
            'description' => self::sanitizeMetaText($site['description'] ?? null),
            'keywords' => $site['keywords'] ?? null,
            'url' => $canonical,
            'image' => self::defaultOgImage(),
            'robots' => $site['robots'] ?? null,
        ]);
    }

    public static function metaForArchive(int $year, ?int $month = null, ?int $day = null): array
    {
        $language = session('language') ?? app()->getLocale();
        $site = self::site();
        $slug = self::firstRouteVariant('route_archives', $language, 'archives');

        $params = [
            'language' => $language,
            'archives' => $slug,
            'year' => $year,
        ];
        if ($month !== null) {
            $params['month'] = sprintf('%02d', $month);
        }
        if ($day !== null) {
            $params['day'] = sprintf('%02d', $day);
        }

        $canonical = route('post.archives', $params);

        $dateParts = array_filter([$year, $month, $day], fn ($part) => $part !== null);
        $dateLabel = implode('-', array_map(fn ($part) => sprintf('%02d', (int) $part), $dateParts));

        $title = Lang::get('crypt.archive.title', [], $language);
        if (! is_string($title) || $title === '') {
            $title = 'Archive';
        }
        $title = $title.' - '.$dateLabel;

        return self::buildMeta([
            'title' => $title,
            'description' => self::sanitizeMetaText($site['description'] ?? null),
            'keywords' => $site['keywords'] ?? null,
            'url' => $canonical,
            'image' => self::defaultOgImage(),
            'robots' => $site['robots'] ?? null,
        ]);
    }

    public static function metaForAuthors(): array
    {
        $language = session('language') ?? app()->getLocale();
        $site = self::site();
        $slug = self::firstRouteVariant('route_authors', $language, 'authors');

        $canonical = route('post.authors', [
            'language' => $language,
            'authors' => $slug,
        ]);

        $title = Lang::get('crypt.authors.title', [], $language);
        if (! is_string($title) || $title === '') {
            $title = 'Authors';
        }

        $description = Lang::get('crypt.authors.description', [], $language);
        if (! is_string($description) || $description === '') {
            $description = $site['description'] ?? null;
        }

        return self::buildMeta([
            'title' => $title,
            'description' => self::sanitizeMetaText($description),
            'keywords' => $site['keywords'] ?? null,
            'url' => $canonical,
            'image' => self::defaultOgImage(),
            'robots' => $site['robots'] ?? null,
        ]);
    }

    public static function metaForUser(User $user): array
    {
        $language = session('language') ?? $user->preferredLocale() ?? app()->getLocale();
        $site = self::site();
        $slug = self::firstRouteVariant('route_user', $language, 'user');

        $canonical = route('user.posts', [
            'language' => $language,
            'user' => $slug,
            'users' => $user->nickname,
        ]);

        $title = $user->display_name ?: $user->full_name ?: $user->nickname;
        $description = $user->display_about ?: $user->about ?: $site['description'] ?? null;

        $profileImage = self::mediaUrl($user->getFirstMediaUrl('profile')) ?: replaceCDN($user->profile_image);

        return self::buildMeta([
            'title' => $title,
            'description' => self::sanitizeMetaText($description),
            'keywords' => $site['keywords'] ?? null,
            'url' => $canonical,
            'image' => $profileImage,
            'robots' => $site['robots'] ?? null,
            'author' => $title,
        ]);
    }

    public static function metaForContact(?ContactPage $contact): array
    {
        $language = session('language') ?? $contact?->language ?? app()->getLocale();
        $site = self::site();
        $slug = self::firstRouteVariant('route_contact', $language, 'contact');

        $canonical = route('contact.front', [
            'language' => $language,
            'contact' => $slug,
        ]);

        $title = self::resolveContactTitle($contact, $language);

        $description = $contact?->meta_description ?: $contact?->description ?: $site['description'] ?? null;

        return self::buildMeta([
            'title' => $title,
            'description' => stripslashesNull(self::sanitizeMetaText($description)),
            'keywords' => $contact?->meta_keywords ?? $site['keywords'] ?? null,
            'url' => $canonical,
            'image' => self::defaultOgImage(),
            'robots' => $site['robots'] ?? null,
        ]);
    }

    public static function metaDefaults(): array
    {
        return self::metaForHome();
    }

    public static function mergeMeta(array $base, array $override = []): array
    {
        $title = $override['title'] ?? $base['title'] ?? null;
        $meta = self::mergeMetaTags($base['meta'] ?? [], $override['meta'] ?? []);
        $links = self::mergeLinkTags($base['links'] ?? [], $override['links'] ?? []);

        return [
            'title' => $title,
            'meta' => $meta,
            'links' => $links,
        ];
    }

    protected static function postTags(Posts $post): array
    {
        $language = session('language') ?? $post->language ?? app()->getLocale();

        return collect(explode(',', (string) $post->meta_keywords))
            ->map(fn ($tag) => trim(stripslashesNull($tag)))
            ->filter()
            ->map(function ($tag) use ($language) {
                return [
                    'name' => $tag,
                    'url' => route('post.tags', [
                        'language' => $language,
                        'tags' => __('routes.tags'),
                        'showTag' => $tag,
                    ]),
                ];
            })
            ->values()
            ->toArray();
    }

    protected static function estimateReadTime(?string $content): string
    {
        $wordCount = str_word_count(strip_tags((string) $content));
        $minutes = max(1, (int) ceil($wordCount / 200));

        $language = session('language') ?? app()->getLocale();
        $template = Lang::get('crypt.post.read_time', ['count' => $minutes], $language);

        if (! is_string($template) || trim($template) === '') {
            return $minutes.' min read';
        }

        return str_replace(':count', (string) $minutes, $template);
    }

    protected static function postImage(Posts $post, string $conversion = 'cover'): string
    {
        $url = $post->getFirstMediaUrl('posts', $conversion);

        if (! $url) {
            return self::placeholderImage();
        }

        return self::mediaUrl($url);
    }

    protected static function faviconVariants($general): array
    {
        if (! $general) {
            return [];
        }

        $icons = [];

        $appleSizes = [
            '57x57' => 'r_57x57',
            '60x60' => 'r_60x60',
            '72x72' => 'r_72x72',
            '76x76' => 'r_76x76',
            '114x114' => 'r_114x114',
            '120x120' => 'r_120x120',
            '144x144' => 'r_144x144',
            '152x152' => 'r_152x152',
            '180x180' => 'r_180x180',
        ];

        foreach ($appleSizes as $size => $conversion) {
            $url = self::mediaUrl($general->getFirstMediaUrl('site_favicon', $conversion));
            if ($url) {
                $icons[] = [
                    'rel' => 'apple-touch-icon',
                    'sizes' => $size,
                    'href' => $url,
                ];
            }
        }

        $pngSizes = [
            '192x192' => 'r_192x192',
            '32x32' => 'r_32x32',
            '96x96' => 'r_96x96',
            '16x16' => 'r_16x16',
        ];

        foreach ($pngSizes as $size => $conversion) {
            $url = self::mediaUrl($general->getFirstMediaUrl('site_favicon', $conversion));
            if ($url) {
                $icons[] = [
                    'rel' => 'icon',
                    'type' => 'image/png',
                    'sizes' => $size,
                    'href' => $url,
                ];
            }
        }

        $defaultIcon = self::mediaUrl($general->getFirstMediaUrl('site_favicon'));
        if ($defaultIcon) {
            $icons[] = [
                'rel' => 'shortcut icon',
                'href' => $defaultIcon,
            ];
        }

        return $icons;
    }

    protected static function dnsPrefetchHosts(): array
    {
        $hosts = [
            config('app.url'),
            config('app.cdn_url'),
            'https://mc.yandex.ru',
            'https://www.google.com',
            'https://fonts.googleapis.com',
            'https://www.googletagmanager.com',
            'https://s7.addthis.com',
            'https://v1.addthisedge.com',
            'https://m.addthis.com',
            'https://z.moatads.com',
            'https://www.google-analytics.com',
        ];

        return collect($hosts)
            ->map(function ($host) {
                if (! $host) {
                    return null;
                }

                $trimmed = trim($host);
                if ($trimmed === '') {
                    return null;
                }

                if (! preg_match('#^https?://#i', $trimmed)) {
                    $trimmed = 'https://'.ltrim($trimmed, '/');
                }

                return rtrim($trimmed, '/');
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected static function mediaUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        return replaceCDN($url);
    }

    protected static function flagAsset(?string $flag): ?string
    {
        if (! $flag) {
            return null;
        }

        $base = config('app.cdn_url') ?: config('app.url');

        return rtrim($base, '/').'/themes/flags/'.$flag.'.webp';
    }

    protected static function firstRouteVariant(string $key, string $language, string $default): string
    {
        $value = Lang::get($key, [], $language);

        if (is_array($value)) {
            $value = $value[0] ?? null;
        }

        if (! is_string($value)) {
            return $default;
        }

        $value = trim($value);

        return $value !== '' ? $value : $default;
    }

    protected static function sanitizeForJsonLd(?string $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $sanitized = strip_tags($value);
        $sanitized = preg_replace('/\s+/u', ' ', $sanitized ?? '');

        if (! is_string($sanitized)) {
            return null;
        }

        $sanitized = trim($sanitized);

        return $sanitized === '' ? null : $sanitized;
    }

    protected static function filterEmpty(array $data): array
    {
        $filtered = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = self::filterEmpty($value);
                if (empty($value)) {
                    continue;
                }
            } elseif ($value === null) {
                continue;
            } elseif (is_string($value) && trim($value) === '') {
                continue;
            }

            $filtered[$key] = $value;
        }

        return $filtered;
    }

    protected static function buildMeta(array $options): array
    {
        $site = self::site();
        $language = self::currentLanguage();

        $title = self::sanitizeMetaText($options['title'] ?? $site['title'] ?? $site['name'] ?? config('app.name'));
        $description = self::sanitizeMetaText($options['description'] ?? $site['description'] ?? null);
        $keywords = self::sanitizeMetaText($options['keywords'] ?? $site['keywords'] ?? null, null, false);
        $url = self::absoluteUrl($options['url'] ?? null);
        if (! $url) {
            $url = route('home', ['language' => $language]);
        }

        $logo = $options['logo'] ?? ($site['logo']['light'] ?? $site['logo']['dark'] ?? null);

        $imageCandidates = [
            $options['image'] ?? null,
            $site['ogImage'] ?? null,
            $site['logo']['light'] ?? null,
            $site['logo']['dark'] ?? null,
        ];
        $image = null;
        foreach ($imageCandidates as $candidate) {
            $absolute = self::absoluteUrl($candidate);
            if ($absolute) {
                $image = $absolute;
                break;
            }
        }

        $robots = $options['robots'] ?? $site['robots'] ?? 'index, follow';
        $author = self::sanitizeMetaText($options['author'] ?? null, null, false);
        $authorUrl = self::absoluteUrl($options['authorUrl'] ?? null);
        $ogType = $options['type'] ?? 'website';
        $siteName = self::sanitizeMetaText($options['siteName'] ?? $site['name'] ?? $site['title'] ?? config('app.name'));
        $twitterHandle = self::sanitizeMetaText($options['twitterHandle'] ?? self::twitterHandle(), null, false);

        $ogLocale = self::sanitizeMetaText($options['ogLocale'] ?? self::toOgLocale($language), null, false);
        $ogLocaleAlternates = $options['ogLocaleAlternates'] ?? self::ogLocaleAlternates($language);

        $publishedTime = $options['publishedTime'] ?? null;
        $modifiedTime = $options['modifiedTime'] ?? null;
        $section = self::sanitizeMetaText($options['section'] ?? null, null, false);
        $tags = $options['tags'] ?? [];

        $alternate = $options['alternate'] ?? [];

        $meta = [];
        self::appendMeta($meta, [
            'name' => 'description',
            'content' => $description,
        ]);
        self::appendMeta($meta, [
            'name' => 'keywords',
            'content' => $keywords,
        ]);
        self::appendMeta($meta, [
            'name' => 'author',
            'content' => $author,
        ]);
        self::appendMeta($meta, [
            'name' => 'robots',
            'content' => $robots,
        ]);

        self::appendMeta($meta, [
            'property' => 'og:type',
            'content' => $ogType,
        ]);
        self::appendMeta($meta, [
            'property' => 'og:title',
            'content' => $title,
        ]);
        self::appendMeta($meta, [
            'property' => 'og:description',
            'content' => $description,
        ]);
        self::appendMeta($meta, [
            'property' => 'og:url',
            'content' => $url,
        ]);
        self::appendMeta($meta, [
            'property' => 'og:image',
            'content' => $image,
        ]);
        self::appendMeta($meta, [
            'property' => 'og:image:secure_url',
            'content' => $image,
        ]);
        self::appendMeta($meta, [
            'property' => 'og:image:alt',
            'content' => $title,
        ]);
        self::appendMeta($meta, [
            'property' => 'og:site_name',
            'content' => $siteName,
        ]);
        self::appendMeta($meta, [
            'property' => 'og:logo',
            'content' => self::absoluteUrl($logo),
        ]);
        self::appendMeta($meta, [
            'property' => 'og:locale',
            'content' => $ogLocale,
        ]);
        foreach ($ogLocaleAlternates as $locale) {
            self::appendMeta($meta, [
                'property' => 'og:locale:alternate',
                'content' => $locale,
            ]);
        }

        $twitterCard = $image ? 'summary_large_image' : 'summary';
        self::appendMeta($meta, [
            'name' => 'twitter:card',
            'content' => $twitterCard,
        ]);
        self::appendMeta($meta, [
            'name' => 'twitter:title',
            'content' => $title,
        ]);
        self::appendMeta($meta, [
            'name' => 'twitter:description',
            'content' => $description,
        ]);
        self::appendMeta($meta, [
            'name' => 'twitter:image',
            'content' => $image,
        ]);
        if ($twitterHandle) {
            $handle = str_starts_with($twitterHandle, '@') ? $twitterHandle : '@'.$twitterHandle;
            self::appendMeta($meta, [
                'name' => 'twitter:site',
                'content' => $handle,
            ]);
            self::appendMeta($meta, [
                'name' => 'twitter:creator',
                'content' => $handle,
            ]);
        }

        if ($ogType === 'article') {
            self::appendMeta($meta, [
                'property' => 'article:published_time',
                'content' => $publishedTime,
            ]);
            self::appendMeta($meta, [
                'property' => 'article:modified_time',
                'content' => $modifiedTime,
            ]);
            self::appendMeta($meta, [
                'property' => 'article:section',
                'content' => $section,
            ]);
            foreach ($tags as $tag) {
                self::appendMeta($meta, [
                    'property' => 'article:tag',
                    'content' => $tag,
                ]);
            }
            self::appendMeta($meta, [
                'property' => 'article:author',
                'content' => $authorUrl,
            ]);
        }

        if (! empty($options['additionalMeta']) && is_array($options['additionalMeta'])) {
            foreach ($options['additionalMeta'] as $metaTag) {
                self::appendMeta($meta, $metaTag);
            }
        }

        $links = [];
        self::appendLink($links, [
            'rel' => 'canonical',
            'href' => $url,
        ]);

        foreach ($alternate as $entry) {
            $href = self::absoluteUrl($entry['url'] ?? null);
            $code = $entry['code'] ?? null;
            if (! $href || ! $code) {
                continue;
            }
            self::appendLink($links, [
                'rel' => 'alternate',
                'hreflang' => $code,
                'href' => $href,
            ]);
        }

        if (! empty($options['additionalLinks']) && is_array($options['additionalLinks'])) {
            foreach ($options['additionalLinks'] as $linkTag) {
                self::appendLink($links, $linkTag);
            }
        }

        return [
            'title' => $title,
            'meta' => array_values($meta),
            'links' => array_values($links),
        ];
    }

    protected static function mergeMetaTags(array $base, array $override): array
    {
        $combined = [];

        foreach ($base as $tag) {
            $key = self::metaTagKey($tag);
            if ($key) {
                $combined[$key] = $tag;
            } else {
                $combined[] = $tag;
            }
        }

        foreach ($override as $tag) {
            $key = self::metaTagKey($tag);
            if ($key) {
                $combined[$key] = $tag;
            } else {
                $combined[] = $tag;
            }
        }

        return array_values($combined);
    }

    protected static function mergeLinkTags(array $base, array $override): array
    {
        $combined = [];

        foreach ($base as $tag) {
            $key = self::linkTagKey($tag);
            if ($key) {
                $combined[$key] = $tag;
            } else {
                $combined[] = $tag;
            }
        }

        foreach ($override as $tag) {
            $key = self::linkTagKey($tag);
            if ($key) {
                $combined[$key] = $tag;
            } else {
                $combined[] = $tag;
            }
        }

        return array_values($combined);
    }

    protected static function metaTagKey(array $tag): ?string
    {
        if (isset($tag['name'])) {
            return 'name:'.strtolower((string) $tag['name']);
        }
        if (isset($tag['property'])) {
            return 'property:'.strtolower((string) $tag['property']);
        }
        if (isset($tag['http-equiv'])) {
            return 'http-equiv:'.strtolower((string) $tag['http-equiv']);
        }

        return null;
    }

    protected static function linkTagKey(array $tag): ?string
    {
        if (! isset($tag['rel'])) {
            return null;
        }

        $key = strtolower((string) $tag['rel']);
        if (isset($tag['hreflang'])) {
            $key .= ':'.strtolower((string) $tag['hreflang']);
        }

        return $key;
    }

    protected static function appendMeta(array &$collection, array $attributes): void
    {
        $content = $attributes['content'] ?? null;

        if ($content === null || (is_string($content) && trim($content) === '')) {
            return;
        }

        $attributes['content'] = is_string($content) ? trim($content) : $content;

        $key = self::metaTagKey($attributes);
        if ($key) {
            $attributes['data-head-key'] = $key;
        }

        $attributes['data-managed-head'] = 'meta';

        $collection[] = $attributes;
    }

    protected static function appendLink(array &$collection, array $attributes): void
    {
        $href = $attributes['href'] ?? null;
        if ($href === null || (is_string($href) && trim($href) === '')) {
            return;
        }

        $attributes['href'] = is_string($href) ? trim($href) : $href;

        $key = self::linkTagKey($attributes);
        if ($key) {
            $attributes['data-head-key'] = $key;
        }

        $attributes['data-managed-head'] = 'link';

        $collection[] = $attributes;
    }

    protected static function languageAlternateLinks(string $routeName): array
    {
        $languages = self::languagesCollection();
        $links = $languages
            ->map(function ($language) use ($routeName) {
                $code = $language->code ?? null;
                if (! $code) {
                    return null;
                }

                $url = match ($routeName) {
                    'home' => route('home', ['language' => $code]),
                    default => null,
                };

                if (! $url) {
                    return null;
                }

                return [
                    'code' => $code,
                    'url' => $url,
                ];
            })
            ->filter()
            ->values();

        $defaultLanguage = $languages->first(fn ($language) => ! empty($language->is_default));
        $defaultCode = $defaultLanguage->code ?? ($languages->first()->code ?? session('language') ?? app()->getLocale());

        if ($defaultCode) {
            $defaultUrl = match ($routeName) {
                'home' => route('home', ['language' => $defaultCode]),
                default => null,
            };

            if ($defaultUrl) {
                $links->prepend([
                    'code' => 'x-default',
                    'url' => $defaultUrl,
                ]);
            }
        }

        return $links->values()->toArray();
    }

    protected static function alternateLinksFromJson(?string $json): array
    {
        if (! $json) {
            return [];
        }

        $decoded = json_decode($json, true);
        if (! is_array($decoded)) {
            return [];
        }

        $links = [];
        foreach ($decoded as $code => $url) {
            if (! $code || ! $url) {
                continue;
            }
            $links[] = [
                'code' => $code,
                'url' => self::absoluteUrl($url),
            ];
        }

        return $links;
    }

    protected static function explodeKeywords(?string $keywords): array
    {
        if (! $keywords) {
            return [];
        }

        return collect(explode(',', $keywords))
            ->map(fn ($value) => self::sanitizeMetaText($value, null, false))
            ->filter()
            ->values()
            ->toArray();
    }

    protected static function defaultOgImage(): ?string
    {
        $site = self::site();

        $candidates = [
            $site['ogImage'] ?? null,
            $site['logo']['light'] ?? null,
            $site['logo']['dark'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            $absolute = self::absoluteUrl($candidate);
            if ($absolute) {
                return $absolute;
            }
        }

        return null;
    }

    protected static function twitterHandle(): ?string
    {
        $social = self::social();
        if (! isset($social['x']) || ! $social['x']) {
            return null;
        }

        $handle = $social['x'];
        return is_string($handle) ? trim($handle) : null;
    }

    protected static function toOgLocale(?string $code): string
    {
        if (! $code) {
            return 'en_US';
        }

        $normalized = str_replace('_', '-', strtolower($code));
        [$primary, $secondary] = array_pad(explode('-', $normalized), 2, null);

        if ($secondary) {
            return $primary.'_'.$secondary;
        }

        return $primary.'_'.strtoupper($primary);
    }

    protected static function ogLocaleAlternates(string $currentCode): array
    {
        $languages = self::languagesCollection();

        return $languages
            ->map(function ($language) use ($currentCode) {
                $code = $language->code ?? null;
                if (! $code || $code === $currentCode) {
                    return null;
                }

                return self::toOgLocale($code);
            })
            ->filter()
            ->values()
            ->toArray();
    }

    protected static function languagesCollection(): Collection
    {
        $languages = app()->bound('languages') ? app('languages') : [];

        return $languages instanceof Collection ? $languages : collect($languages);
    }

    protected static function currentLanguage(): string
    {
        $fromRoute = request()->route('language');
        if (is_string($fromRoute) && trim($fromRoute) !== '') {
            return $fromRoute;
        }

        $fromSession = session('language');
        if (is_string($fromSession) && trim($fromSession) !== '') {
            return $fromSession;
        }

        return app()->getLocale();
    }

    protected static function absoluteUrl(?string $url): ?string
    {
        if (! $url || ! is_string($url)) {
            return null;
        }

        $trimmed = trim($url);
        if ($trimmed === '') {
            return null;
        }

        if (str_starts_with($trimmed, '//')) {
            return 'https:'.$trimmed;
        }

        if (preg_match('#^https?://#i', $trimmed)) {
            return $trimmed;
        }

        $base = config('app.cdn_url') ?: config('app.url');
        if (! $base) {
            return $trimmed;
        }

        return rtrim($base, '/').'/'.ltrim($trimmed, '/');
    }

    protected static function sanitizeMetaText(?string $value, ?int $limit = 160, bool $applyLimit = true): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $sanitized = strip_tags($value);
        $sanitized = preg_replace('/\s+/u', ' ', $sanitized ?? '');
        if (! is_string($sanitized)) {
            return null;
        }

        $sanitized = trim($sanitized);
        if ($sanitized === '') {
            return null;
        }

        if ($applyLimit && $limit !== null) {
            return Str::limit($sanitized, $limit);
        }

        return $sanitized;
    }

    protected static function placeholderImage(): string
    {
        $base = config('app.cdn_url') ?: config('app.url');

        return rtrim($base, '/').'/themes/Default/images/loading.svg';
    }
}
