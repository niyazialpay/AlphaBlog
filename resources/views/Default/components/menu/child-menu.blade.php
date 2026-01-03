<ul class="dropdown-menu">
    @foreach($child_menu_item as $child)
        @if($child->children->count() > 0)
            <li>
                <a href="{{$child->url}}" class="dropdown-toggle" data-toggle="dropdown" role="button"
                   aria-haspopup="true" aria-expanded="false" target="{{$child->target}}">
                {{$child->title}}
                </a>
                    @include('Default.components.menu.child-menu', ['child_menu_item' => $child->children])
            </li>
        @else
            <li><a href="{{$child->url}}" target="{{$child->target}}">{{$child->title}}</a></li>
        @endif
    @endforeach
</ul>
