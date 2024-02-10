@if($social_settings->github)
    <li><a href="https://github.com/{{$social_settings->github}}" target="_blank" rel="nofollow"><i
                class="fa-brands fa-github"></i></a></li>
@endif

@if($social_settings->linkedin)
    <li><a href="https://www.linkedin.com/in/{{$social_settings->linkedin}}" target="_blank" rel="nofollow"><i
                class="fa-brands fa-linkedin"></i></a></li>
@endif

@if($social_settings->facebook)
    <li><a href="https://facebook.com/{{$social_settings->facebook}}" target="_blank" rel="nofollow"><i
                class="fa-brands fa-facebook"></i> </a></li>
@endif

@if($social_settings->x)
    <li><a href="https://twitter.com/{{$social_settings->x}}" target="_blank" rel="nofollow"><i
                class="fa-brands fa-twitter"></i></a></li>
@endif

@if($social_settings->devto)
    <li><a href="https://dev.to/{{$social_settings->devto}}" target="_blank" rel="nofollow"><i
                class="fa-brands fa-dev"></i></a></li>
@endif

@if($social_settings->medium)
    <li><a href="https://medium.com/{{'@'.$social_settings->medium}}" target="_blank" rel="nofollow"><i
                class="fa-brands fa-medium"></i></a></li>
@endif

@if($social_settings->deviantart)
    <li><a href="https://eviantart.com/{{$social_settings->deviantart}}" target="_blank" rel="nofollow"><i
                class="fa-brands fa-deviantart"></i></a></li>
@endif

@if($social_settings->instagram)
    <li><a href="https://instagram.com/{{$social_settings->instagram}}" target="_blank" rel="nofollow"><i
                class="fa-brands fa-instagram"></i> </a></li>
@endif

@if($social_settings->youtube)
    <li><a href="https://youtube.com/{{$social_settings->youtube}}" target="_blank" rel="nofollow"><i
                class="fa-brands fa-youtube"></i> </a></li>
@endif

@if($social_settings->reddit)
    <li><a href="https://reddit.com/{{$social_settings->reddit}}" target="_blank" rel="nofollow"><i
                class="fa-brands fa-reddit-alien"></i> </a></li>
@endif

@if($social_settings->xbox)
    <li><a href="https://account.xbox.com/en-us/profile?gamertag={{$social_settings->xbox}}" target="_blank" rel="nofollow"><i
                class="fa-brands fa-xbox"></i> </a></li>
@endif

<li><a href="/rss" target="_blank"><i class="fa fa-rss"></i> </a></li>
