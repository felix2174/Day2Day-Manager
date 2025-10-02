@props(['status', 'type' => 'default'])

@php
    $statusConfig = [
        'active' => ['class' => 'bg-green-100 text-green-800', 'text' => 'Aktiv'],
        'inactive' => ['class' => 'bg-gray-100 text-gray-800', 'text' => 'Inaktiv'],
        'planning' => ['class' => 'bg-yellow-100 text-yellow-800', 'text' => 'Planung'],
        'completed' => ['class' => 'bg-blue-100 text-blue-800', 'text' => 'Abgeschlossen'],
        'on_hold' => ['class' => 'bg-red-100 text-red-800', 'text' => 'Pausiert'],
        'geplant' => ['class' => 'bg-blue-100 text-blue-800', 'text' => 'Geplant'],
        'läuft' => ['class' => 'bg-orange-100 text-orange-800', 'text' => 'Läuft'],
        'vergangen' => ['class' => 'bg-gray-100 text-gray-800', 'text' => 'Vergangen'],
        'urlaub' => ['class' => 'bg-yellow-100 text-yellow-800', 'text' => 'Urlaub'],
        'krankheit' => ['class' => 'bg-red-100 text-red-800', 'text' => 'Krankheit'],
        'fortbildung' => ['class' => 'bg-green-100 text-green-800', 'text' => 'Fortbildung'],
        'high' => ['class' => 'bg-red-100 text-red-800', 'text' => 'Hoch'],
        'medium' => ['class' => 'bg-yellow-100 text-yellow-800', 'text' => 'Mittel'],
        'low' => ['class' => 'bg-green-100 text-green-800', 'text' => 'Niedrig'],
    ];
    
    $config = $statusConfig[$status] ?? ['class' => 'bg-gray-100 text-gray-800', 'text' => ucfirst($status)];
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $config['class'] }}">
    {{ $config['text'] }}
</span>


















