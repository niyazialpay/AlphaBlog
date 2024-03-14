<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach($languages as $language)
        <sitemap>
            <loc>{{route('sitemap.categories', ['language' => $language->code])}}</loc>
        </sitemap>
        <sitemap>
            <loc>{{route('sitemap.posts', ['language' => $language->code])}}</loc>
        </sitemap>
        <sitemap>
            <loc>{{route('sitemap.users', ['language' => $language->code])}}</loc>
        </sitemap>
    @endforeach
</sitemapindex>
