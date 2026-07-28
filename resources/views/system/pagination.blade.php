@if ($paginator->hasPages())
<div class="ts buttons">

            @if ($paginator->onFirstPage())
                <a class="ts disabled icon button"><i class="chevron left icon"></i></a>
            @else
                <a class="ts disabled icon button" href="{{ $paginator->previousPageUrl() }}" rel="prev" ><i class="chevron left icon"></i></a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <a class="ts disabled button">{{ $element }}</a>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <a class="ts active button">{{ $page }}</a>
                        @else
                            <a class="ts button" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a class="ts icon button" href="{{ $paginator->nextPageUrl() }}" rel="next"><i class="chevron right icon"></i></a>
            @else
                <a class="ts disabled icon button" rel="next"><i class="chevron right icon"></i></a>
            @endif

    </div>
@endif
