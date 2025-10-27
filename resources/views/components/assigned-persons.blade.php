@props([
    'persons' => [],
    'maxPersons' => 5,
    'showCount' => true,
    'emptyText' => 'Keine Personen zugewiesen',
    'variant' => 'default' // 'default', 'tooltip', 'detail'
])

@php
    $personsList = is_array($persons) ? $persons : explode(', ', $persons);
    $personsList = array_filter($personsList); // Entferne leere Einträge
    $totalCount = count($personsList);
    $displayPersons = $maxPersons > 0 ? array_slice($personsList, 0, $maxPersons) : $personsList;
    $remainingCount = $totalCount - count($displayPersons);
@endphp

@if($totalCount === 0)
    <span style="color: #6b7280; font-style: italic;">{{ $emptyText }}</span>
@else
    <div style="display: flex; flex-wrap: wrap; gap: 4px; align-items: center;">
        @if($variant === 'detail')
            {{-- Detailansicht mit Avatars --}}
            @foreach($displayPersons as $person)
                @php
                    $initials = collect(explode(' ', $person))->map(fn($name) => substr($name, 0, 1))->implode('');
                    $colors = ['#3b82f6', '#8b5cf6', '#06b6d4', '#10b981', '#f59e0b', '#ef4444'];
                    $color = $colors[array_search($person, $displayPersons) % count($colors)];
                @endphp
                <div style="display: flex; align-items: center; gap: 8px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 10px;">
                    <div style="width: 24px; height: 24px; background: {{ $color }}; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 10px; font-weight: 600;">
                        {{ $initials }}
                    </div>
                    <span style="font-size: 13px; font-weight: 500; color: #374151;">{{ $person }}</span>
                </div>
            @endforeach
        @else
            {{-- Standard/Tooltip-Ansicht --}}
            <span style="color: #111827; font-weight: 600; font-size: {{ $variant === 'tooltip' ? '12px' : '14px' }};">
                {{ implode(', ', $displayPersons) }}
                @if($remainingCount > 0)
                    <span style="color: #6b7280; font-weight: 400;">(+{{ $remainingCount }} weitere)</span>
                @endif
            </span>
        @endif
        
        @if($showCount && $variant !== 'detail')
            <span style="color: #6b7280; font-size: {{ $variant === 'tooltip' ? '11px' : '12px' }}; margin-left: 4px;">
                ({{ $totalCount }})
            </span>
        @endif
    </div>
@endif













