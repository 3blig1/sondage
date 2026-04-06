@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="pagination-shell">
        <div class="pagination-mobile sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="pagination-button pagination-button--disabled">Précédent</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="pagination-button">Précédent</a>
            @endif

            <span class="pagination-mobile-status">
                Page {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
            </span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="pagination-button">Suivant</a>
            @else
                <span class="pagination-button pagination-button--disabled">Suivant</span>
            @endif
        </div>

        <div class="hidden sm:flex sm:items-center sm:justify-between sm:gap-4">
            <div class="pagination-summary">
                @if ($paginator->firstItem())
                    Affichage de <span>{{ $paginator->firstItem() }}</span> à <span>{{ $paginator->lastItem() }}</span> sur <span>{{ $paginator->total() }}</span>
                @else
                    <span>{{ $paginator->count() }}</span> résultat(s)
                @endif
            </div>

            <div class="pagination-links">
                @if ($paginator->onFirstPage())
                    <span class="pagination-icon pagination-icon--disabled" aria-disabled="true">
                        <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="pagination-icon" aria-label="Page précédente">
                        <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </a>
                @endif

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="pagination-ellipsis">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="pagination-page pagination-page--active" aria-current="page">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="pagination-page" aria-label="Aller à la page {{ $page }}">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="pagination-icon" aria-label="Page suivante">
                        <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </a>
                @else
                    <span class="pagination-icon pagination-icon--disabled" aria-disabled="true">
                        <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                @endif
            </div>
        </div>
    </nav>
@endif