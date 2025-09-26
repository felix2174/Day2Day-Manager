@props(['type' => 'success', 'message'])

@php
    $alertConfig = [
        'success' => ['class' => 'bg-green-50 border-green-200 text-green-800', 'icon' => 'check-circle'],
        'error' => ['class' => 'bg-red-50 border-red-200 text-red-800', 'icon' => 'exclamation-circle'],
        'warning' => ['class' => 'bg-yellow-50 border-yellow-200 text-yellow-800', 'icon' => 'exclamation-triangle'],
        'info' => ['class' => 'bg-blue-50 border-blue-200 text-blue-800', 'icon' => 'information-circle']
    ];
    
    $config = $alertConfig[$type] ?? $alertConfig['success'];
@endphp

<div class="rounded-md {{ $config['class'] }} border p-4 mb-6">
    <div class="flex">
        <div class="flex-shrink-0">
            @if($config['icon'] === 'check-circle')
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
            @elseif($config['icon'] === 'exclamation-circle')
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
            @elseif($config['icon'] === 'exclamation-triangle')
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
            @else
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
            @endif
        </div>
        <div class="ml-3">
            <p class="text-sm font-medium">{{ $message }}</p>
        </div>
    </div>
</div>









