@props(['name', 'size' => 'md', 'class' => ''])

@php
    $sizeClasses = [
        'sm' => 'h-8 w-8 text-xs',
        'md' => 'h-10 w-10 text-sm',
        'lg' => 'h-12 w-12 text-base',
        'xl' => 'h-16 w-16 text-lg'
    ];
    
    $initials = '';
    if ($name) {
        $parts = explode(' ', $name);
        $initials = strtoupper(substr($parts[0], 0, 1));
        if (count($parts) > 1) {
            $initials .= strtoupper(substr($parts[1], 0, 1));
        }
    }
@endphp

<div class="inline-flex items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-purple-600 text-white font-semibold {{ $sizeClasses[$size] }} {{ $class }}">
    {{ $initials }}
</div>


















