@props(['progress', 'size' => 'sm', 'color' => 'blue'])

@php
    $sizeClasses = [
        'sm' => 'h-2',
        'md' => 'h-3',
        'lg' => 'h-4'
    ];
    
    $colorClasses = [
        'blue' => 'bg-blue-600',
        'green' => 'bg-green-600',
        'yellow' => 'bg-yellow-600',
        'red' => 'bg-red-600',
        'purple' => 'bg-purple-600'
    ];
@endphp

<div class="flex items-center">
    <div class="flex-1 bg-gray-200 rounded-full {{ $sizeClasses[$size] }} mr-3">
        <div class="{{ $colorClasses[$color] }} {{ $sizeClasses[$size] }} rounded-full transition-all duration-300 ease-in-out" 
             style="width: {{ min(100, max(0, $progress)) }}%"></div>
    </div>
    <span class="text-sm font-medium text-gray-700 min-w-[3rem] text-right">
        {{ round($progress) }}%
    </span>
</div>










