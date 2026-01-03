<urlset xmlns="https://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{route('home')}}</loc>
    </url>
    <url>
        <loc>{{route('home',[
                'language' => session('language'),
            ])}}</loc>
    </url>
    @foreach($posts as $post)
        <url>
            <loc>{{route('page',
            [
                'language' => session('language'),
                $post->slug
            ])}}</loc>
        </url>
    @endforeach
        <url>
            <loc>{{route('contact.front',[
                'language' => session('language'),
                __('routes.contact')
            ])}}</loc>
        </url>
</urlset>
