<urlset xmlns="https://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach($categories as $category)
    <url>
        <loc>{{route('post.categories',
            [
                'language' => session('language'),
                __('routes.categories'),
                $category->slug
            ])}}</loc>
    </url>
    @endforeach
</urlset>
