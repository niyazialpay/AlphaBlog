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
        $language = session('language') ?? app()->getLocale();

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
        $language = session('language') ?? app()->getLocale();
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
        return [
            'name' => app()->bound('theme') ? app('theme')->name : null,
            'renderer' => config('theme.renderer'),
        ];
    }

    public static function languages(): array
    {
        $current = session('language') ?? app()->getLocale();
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

        return [
            'linkedin' => $social->linkedin,
            'facebook' => $social->facebook,
            'x' => $social->x,
            'instagram' => $social->instagram,
            'github' => $social->github,
            'youtube' => $social->youtube,
            'twitch' => $social->twitch,
            'telegram' => $social->telegram,
            'discord' => $social->discord,
            'website' => $social->website,
        ];
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
        $language = session('language') ?? app()->getLocale();
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
        $language = session('language') ?? app()->getLocale();
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

    public static function featuredPosts(int $limit = 5): array
    {
        $language = session('language') ?? app()->getLocale();
        $cacheKey = config('cache.prefix').'featured_posts_vue_'.$language.'_'.$limit;

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
        $language = session('language') ?? app()->getLocale();
        $cacheKey = config('cache.prefix').'recent_posts_vue_'.$language.'_'.$limit.'_skip_'.$skip;

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
        $language = session('language') ?? app()->getLocale();
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
            'excerpt' => Str::limit(strip_tags(stripslashesNull($post->content)), 220),
            'image' => self::postImage($post),
            'category' => $primaryCategory ? self::categorySummary($primaryCategory) : null,
            'categories' => $post->categories->map(fn (Categories $category) => self::categorySummary($category))->toArray(),
            'author' => $post->user ? self::authorSummary($post->user) : null,
            'publishedAt' => optional($post->created_at)->toIso8601String(),
            'readTime' => self::estimateReadTime($post->content),
            'views' => (int) $post->views,
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
                'description' => $post->meta_description,
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
        ];
    }

    public static function authorDetail(User $user, Collection|array $posts = []): array
    {
        return [
            'author' => self::authorSummary($user),
            'posts' => self::postsCollection($posts),
        ];
    }

    public static function categorySummary(Categories $category): array
    {
        $category->loadMissing('children');

        $language = session('language') ?? $category->language ?? app()->getLocale();

        return [
            'id' => $category->id,
            'name' => stripslashesNull($category->name),
            'slug' => $category->slug,
            'description' => $category->description ?? $category->meta_description,
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

    public static function categoryDetail(Categories $category, int $perPage = 10): array
    {
        return [
            'category' => array_merge(self::categorySummary($category), [
                'meta' => [
                    'description' => $category->meta_description,
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
            'title' => $contact->title,
            'description' => $contact->description,
            'meta' => [
                'description' => $contact->meta_description,
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

    protected static function placeholderImage(): string
    {
        $base = config('app.cdn_url') ?: config('app.url');

        return rtrim($base, '/').'/themes/Default/images/loading.svg';
    }
}
