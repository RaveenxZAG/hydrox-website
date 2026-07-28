@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
            Showing
            <span class="font-bold text-slate-800 dark:text-slate-100">{{ $paginator->firstItem() }}</span>
            to
            <span class="font-bold text-slate-800 dark:text-slate-100">{{ $paginator->lastItem() }}</span>
            of
            <span class="font-bold text-slate-800 dark:text-slate-100">{{ $paginator->total() }}</span>
            results
        </p>

        <div class="inline-flex w-full items-center gap-1 rounded-xl border border-slate-200 bg-white p-1 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:w-auto">
            @if ($paginator->onFirstPage())
                <span class="inline-flex h-9 min-w-9 cursor-not-allowed items-center justify-center rounded-lg px-3 text-sm font-semibold text-slate-300 dark:text-slate-600" aria-disabled="true">
                    Previous
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg px-3 text-sm font-semibold text-slate-600 transition hover:bg-[#0082c9]/10 hover:text-[#0082c9] dark:text-slate-300 dark:hover:bg-slate-800">
                    Previous
                </a>
            @endif

            <div class="hidden items-center gap-1 sm:flex">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg px-3 text-sm font-semibold text-slate-400">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg bg-[#0082c9] px-3 text-sm font-black text-white shadow-sm">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}" class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg px-3 text-sm font-semibold text-slate-600 transition hover:bg-[#0082c9]/10 hover:text-[#0082c9] dark:text-slate-300 dark:hover:bg-slate-800">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            <span class="inline-flex h-9 flex-1 items-center justify-center rounded-lg bg-slate-50 px-3 text-sm font-bold text-slate-600 dark:bg-slate-950 dark:text-slate-300 sm:hidden">
                Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}
            </span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg px-3 text-sm font-semibold text-slate-600 transition hover:bg-[#0082c9]/10 hover:text-[#0082c9] dark:text-slate-300 dark:hover:bg-slate-800">
                    Next
                </a>
            @else
                <span class="inline-flex h-9 min-w-9 cursor-not-allowed items-center justify-center rounded-lg px-3 text-sm font-semibold text-slate-300 dark:text-slate-600" aria-disabled="true">
                    Next
                </span>
            @endif
        </div>
    </nav>
@endif
