<urlset xmlns="https://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach($users as $user)
        <url>
            <loc>{{route('user.posts',
            [
                'language' => session('language'),
                __('routes.user'),
                $user->nickname
            ])}}</loc>
        </url>
    @endforeach
</urlset>
