<ul class="actions pagination">
    <!-- Previous Page Link -->
    @if ($paginator->onFirstPage())
        <li class="disabled"><span class="button big previous">@lang('general.previous')</span></li>
    @else
        @if(app('request')->has('search'))
        <li><a href="{{ $paginator->previousPageUrl() }}&amp;search={{app('request')->input('search')}}" class="button big previous" rel="prev">@lang('general.previous')</a></li>
        @else
            <li><a href="{{ $paginator->previousPageUrl() }}" class="button big previous" rel="prev">@lang('general.previous')</a></li>
        @endif
    @endif



<!-- Next Page Link -->
    @if ($paginator->hasMorePages())
        @if(app('request')->has('search'))
        <li><a href="{{ $paginator->nextPageUrl() }}&amp;search={{app('request')->input('search')}}" class="button big next" rel="next">@lang('general.next')</a></li>
        @else
            <li><a href="{{ $paginator->nextPageUrl() }}" class="button big next" rel="next">@lang('general.next')</a></li>
        @endif
    @else
        <li class="disabled"><span class="button big next">@lang('general.next')</span></li>
    @endif
</ul>
