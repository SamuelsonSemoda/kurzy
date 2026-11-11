@if ($paginator->hasPages())
    <nav aria-label="Stránkování">
        <ul>
            @if ($paginator->onFirstPage())
                <li class="is-disabled"><span>Předchozí</span></li>
            @else
                <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev">Předchozí</a></li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="is-disabled"><span>{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="is-current"><span aria-current="page">{{ $page }}</span></li>
                        @else
                            <li><a href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li><a href="{{ $paginator->nextPageUrl() }}" rel="next">Další</a></li>
            @else
                <li class="is-disabled"><span>Další</span></li>
            @endif
        </ul>
    </nav>
@endif
