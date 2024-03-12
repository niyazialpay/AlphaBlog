@if($social_networks->github && in_array('github',$show))
    <li>
        <a href="https://github.com/{{$social_networks->github}}" target="_blank" rel="nofollow">
            <i class="fa-brands fa-github"></i>
        </a>
    </li>
@endif

@if($social_networks->linkedin && in_array('linkedin',$show))
    <li>
        <a href="https://www.linkedin.com/in/{{$social_networks->linkedin}}" target="_blank" rel="nofollow">
            <i class="fa-brands fa-linkedin"></i>
        </a>
    </li>
@endif

@if($social_networks->facebook && in_array('facebook',$show))
    <li>
        <a href="https://facebook.com/{{$social_networks->facebook}}" target="_blank" rel="nofollow">
            <i class="fa-brands fa-facebook"></i>
        </a>
    </li>
@endif

@if($social_networks->x && in_array('x',$show))
    <li>
        <a href="https://twitter.com/{{$social_networks->x}}" target="_blank" rel="nofollow">
            <i class="fa-brands fa-x-twitter"></i>
        </a>
    </li>
@endif

@if($social_networks->devto && in_array('devto',$show))
    <li>
        <a href="https://dev.to/{{$social_networks->devto}}" target="_blank" rel="nofollow">
            <i class="fa-brands fa-dev"></i>
        </a>
    </li>
@endif

@if($social_networks->instagram && in_array('instagram',$show))
    <li>
        <a href="https://instagram.com/{{$social_networks->instagram}}" target="_blank" rel="nofollow">
            <i class="fa-brands fa-instagram"></i>
        </a>
    </li>
@endif

@if($social_networks->medium && in_array('medium',$show))
    <li>
        <a href="https://medium.com/{{'@'.$social_networks->medium}}" target="_blank" rel="nofollow">
            <i class="fa-brands fa-medium"></i>
        </a>
    </li>
@endif

@if($social_networks->deviantart && in_array('deviantart',$show))
    <li>
        <a href="https://eviantart.com/{{$social_networks->deviantart}}" target="_blank" rel="nofollow">
            <i class="fa-brands fa-deviantart"></i>
        </a>
    </li>
@endif

@if($social_networks->youtube && in_array('youtube',$show))
    <li>
        <a href="https://youtube.com/{{$social_networks->youtube}}" target="_blank" rel="nofollow">
            <i class="fa-brands fa-youtube"></i>
        </a>
    </li>
@endif

@if($social_networks->reddit && in_array('reddit',$show))
    <li>
        <a href="https://reddit.com/{{$social_networks->reddit}}" target="_blank" rel="nofollow">
            <i class="fa-brands fa-reddit-alien"></i>
        </a>
    </li>
@endif

@if($social_networks->xbox && in_array('xbox',$show))
    <li>
        <a href="https://account.xbox.com/en-us/profile?gamertag={{$social_networks->xbox}}" target="_blank" rel="nofollow">
            <i class="fa-brands fa-xbox"></i>
        </a>
    </li>
@endif

<li>
    <a href="{{route('rss', ['language' => session('language')])}}" target="_blank">
        <i class="fa fa-rss"></i>
    </a>
</li>
