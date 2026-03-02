@if(count($breadcrumbs) > 0)
<nav class="flex mb-4" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3">
        <li class="inline-flex items-center">
            <a href="{{ route('dashboard') }}" class="text-base text-gray-700 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                Home
            </a>
        </li>
        @foreach($breadcrumbs as $breadcrumb)
            <li>
                <div class="flex items-center">
                    <svg class="w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    @if(isset($breadcrumb['url']))
                        <a href="{{ $breadcrumb['url'] }}" class="text-base text-gray-700 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                            {{ $breadcrumb['label'] }}
                        </a>
                    @else
                        <span class="text-base text-gray-500 dark:text-gray-400">{{ $breadcrumb['label'] }}</span>
                    @endif
                </div>
            </li>
        @endforeach
    </ol>
</nav>
@endif