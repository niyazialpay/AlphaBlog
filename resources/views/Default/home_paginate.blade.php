<ul class="actions pagination">
    <!-- Previous Page Link -->
    @if ($paginator->onFirstPage())
        <li class="disabled"><span class="button big previous">Önceki Sayfa</span></li>
    @else
        @if(app('request')->has('s'))
        <li><a href="{{ $paginator->previousPageUrl() }}&amp;s={{app('request')->input('s')}}" class="button big previous" rel="prev">Önceki Sayfa</a></li>
        @else
            <li><a href="{{ $paginator->previousPageUrl() }}" class="button big previous" rel="prev">Önceki Sayfa</a></li>
        @endif
    @endif

    

<!-- Next Page Link -->
    @if ($paginator->hasMorePages())
        @if(app('request')->has('s'))
        <li><a href="{{ $paginator->nextPageUrl() }}&amp;s={{app('request')->input('s')}}" class="button big next" rel="next">Sonraki Sayfa</a></li>
        @else
            <li><a href="{{ $paginator->nextPageUrl() }}" class="button big next" rel="next">Sonraki Sayfa</a></li>
        @endif
    @else
        <li class="disabled"><span class="button big next">Sonraki Sayfa</span></li>
    @endif
</ul>