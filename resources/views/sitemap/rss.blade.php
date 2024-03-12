<?xml version="1.0" ?>
<rss version="2.0">
    <channel>
        <title>{{$seo_settings->title}}</title>
        <link>{{route('home', ['language' => session('language')])}}</link>
        <description>
            <![CDATA[ {{$seo_settings->description}} ]]>
        </description>
        <language>{{session('language')}}</language>
        <copyright>
            <![CDATA[ {{$seo_settings->site_name}} &copy; {{date('Y')}} ]]>
        </copyright>

        <image>
            <url>{{$general_settings->getFirstMediaUrl('site_logo_light')}}</url>
            <link>{{config('app.url')}}</link>
        </image>

        @foreach($posts as $post)
            <item>
                <title>{{$post->title}}</title>
                <link>{{route('page', ['language' => session('language'), $post])}}</link>
                <guid isPermaLink="true">{{route('page', ['language' => session('language'), $post])}}</guid>
                <description>
                    <![CDATA[ {{substr(strip_tags($post->content), 0, 100)}} ]]>
                </description>
                <dc:creator xmlns:dc="https://purl.org/dc/elements/1.1/">{{$post->user->nickname}}</dc:creator>
                <pubDate>{{dateformat($post->created_at, 'r')}}</pubDate>
                <category>{{$post->categories->last()->name}}</category>
            </item>
        @endforeach

    </channel>
</rss>
