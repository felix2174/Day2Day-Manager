@props(['title', 'subtitle' => null, 'actions' => null])

<div class="bg-white shadow-sm border-b border-gray-200 mb-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div class="flex-1 min-w-0">
                <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    {{ $title }}
                </h1>
                @if($subtitle)
                    <p class="mt-1 text-sm text-gray-500">{{ $subtitle }}</p>
                @endif
            </div>
            @if($actions)
                <div class="mt-4 sm:mt-0 sm:ml-4 flex flex-col sm:flex-row gap-3">
                    {{ $actions }}
                </div>
            @endif
        </div>
    </div>
</div>









