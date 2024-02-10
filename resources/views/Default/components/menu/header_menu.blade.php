<ul class="nav navbar-nav navbar-left">
    @foreach($menu_items as $menu_item)
        @if($menu_item->children->count() > 0)
            <li>
                <a href="{{$menu_item->url}}" class="dropdown-toggle" data-toggle="dropdown" role="button"
                   aria-haspopup="true" aria-expanded="false" target="{{$menu_item->target}}">
                    {{$menu_item->title}}
                </a>
                @include('Default.components.menu.child-menu', ['child_menu_item' => $menu_item->children])

            </li>
        @else
            <li>
                <a href="{{$menu_item->url}}" target="{{$menu_item->target}}">{{$menu_item->title}}</a>
            </li>
        @endif

    @endforeach
    <li class="hidden-lg hidden-md"><a href="javascript:void(0);" class="searchbutton" title="Ara"><i
            class="fa fa-search searchbutton" title="Ara"></i></a></li>
</ul>
