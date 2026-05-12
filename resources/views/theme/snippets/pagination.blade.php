@if ($paginator->hasPages())
    <nav class="flex flex-col sm:flex-row items-center justify-between gap-3 mt-10 pt-8 border-t border-gray-100" aria-label="Pagination">
        <p class="text-sm text-gray-500 order-2 sm:order-1">
            Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}
            of {{ $paginator->total() }} {{ Str::plural('product', $paginator->total()) }}
        </p>

        <div class="flex items-center gap-1 order-1 sm:order-2">
            {{-- Prev --}}
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center px-3 py-2 text-sm text-gray-300 border border-gray-200 rounded-lg cursor-not-allowed select-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
                    </svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}"
                   class="inline-flex items-center px-3 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
                    </svg>
                </a>
            @endif

            {{-- Page numbers --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-3 py-2 text-sm text-gray-400 select-none">{{ $element }}</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="px-3 py-2 text-sm font-semibold theme-variant-selected border rounded-lg select-none">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}"
                               class="px-3 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}"
                   class="inline-flex items-center px-3 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                    </svg>
                </a>
            @else
                <span class="inline-flex items-center px-3 py-2 text-sm text-gray-300 border border-gray-200 rounded-lg cursor-not-allowed select-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                    </svg>
                </span>
            @endif
        </div>
    </nav>
@endif
