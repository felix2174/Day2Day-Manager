@extends('layouts.app')

@section('title', 'Projektübersicht')

@section('content')
<div style="width: 100%; margin: 0; padding: 20px;">
    <div style="background: white; padding: 20px; margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div style="flex: 1; min-width: 260px; display: flex; flex-direction: column; gap: 12px;">
                {{-- Dropdown Menu Button + Title --}}
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div x-data="{ open: false }" style="position: relative;">
                        <button @click="open = !open" 
                                type="button"
                                style="background: #f9fafb; color: #374151; padding: 8px; border: 1px solid #e5e7eb; border-radius: 6px; cursor: pointer; transition: all 0.2s ease; display: inline-flex; align-items: center; justify-content: center;"
                                onmouseover="this.style.background='#f3f4f6'; this.style.borderColor='#d1d5db'"
                                onmouseout="this.style.background='#f9fafb'; this.style.borderColor='#e5e7eb'">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="5" r="1.5"/>
                                <circle cx="12" cy="12" r="1.5"/>
                                <circle cx="12" cy="19" r="1.5"/>
                            </svg>
                        </button>

                        {{-- Dropdown Menu --}}
                        <div x-show="open" 
                             @click.away="open = false"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             style="position: absolute; left: 0; top: 100%; margin-top: 8px; z-index: 1000; background: white; border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); min-width: 220px; overflow: hidden;">
                            
                            @if($viewMode === 'projects')
                                {{-- Filter & Suche --}}
                                <button type="button" 
                                        @click="toggleFilters(); open = false"
                                        style="width: 100%; text-align: left; padding: 12px 16px; background: transparent; border: none; cursor: pointer; display: flex; align-items: center; gap: 10px; font-size: 14px; color: #374151; transition: background 0.15s ease;"
                                        onmouseover="this.style.background='#f9fafb'"
                                        onmouseout="this.style.background='transparent'">
                                    <svg style="width: 20px; height: 20px; color: #3b82f6;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    <span style="font-weight: 500;">Filter & Suche</span>
                                    <span id="menuFilterIndicator" style="display: {{ count(array_filter(Session::get('gantt_filters', []))) > 0 ? 'inline-flex' : 'none' }}; background: #ef4444; color: white; border-radius: 999px; width: 18px; height: 18px; align-items: center; justify-content: center; font-size: 10px; font-weight: 700; margin-left: auto;">{{ count(array_filter(Session::get('gantt_filters', []))) }}</span>
                                </button>

                                <div style="height: 1px; background: #e5e7eb; margin: 4px 0;"></div>
                            @endif

                            {{-- Exportieren --}}
                            <div style="padding: 4px 0;">
                                <div style="padding: 8px 16px; font-size: 11px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px;">Exportieren</div>
                                <a href="{{ route('gantt.export') }}?view={{ $viewMode }}" 
                                   @click="open = false"
                                   style="width: 100%; text-align: left; padding: 10px 16px; background: transparent; text-decoration: none; display: flex; align-items: center; gap: 10px; font-size: 14px; color: #374151; transition: background 0.15s ease;"
                                   onmouseover="this.style.background='#f9fafb'"
                                   onmouseout="this.style.background='transparent'">
                                    <svg style="width: 20px; height: 20px; color: #10b981;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span style="font-weight: 500;">Excel Export</span>
                                </a>
                                <button type="button" disabled
                                        style="width: 100%; text-align: left; padding: 10px 16px; background: transparent; border: none; cursor: not-allowed; display: flex; align-items: center; gap: 10px; font-size: 14px; color: #9ca3af; opacity: 0.6;"
                                        title="Bald verfügbar">
                                    <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                    <span style="font-weight: 500;">PDF Export</span>
                                    <span style="font-size: 10px; background: #f3f4f6; color: #6b7280; padding: 2px 6px; border-radius: 4px; margin-left: auto;">Bald</span>
                                </button>
                            </div>

                            <div style="height: 1px; background: #e5e7eb; margin: 4px 0;"></div>

                            {{-- Einstellungen --}}
                            <button type="button" disabled
                                    style="width: 100%; text-align: left; padding: 12px 16px; background: transparent; border: none; cursor: not-allowed; display: flex; align-items: center; gap: 10px; font-size: 14px; color: #9ca3af; opacity: 0.6;"
                                    title="Bald verfügbar">
                                <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span style="font-weight: 500;">Einstellungen</span>
                            </button>
                        </div>
                    </div>

                    <h1 style="font-size: 24px; font-weight: bold; color: #111827; margin: 0;">{{ $viewMode === 'employees' ? 'Gantt-Diagramm: Mitarbeiter' : 'Gantt-Diagramm: Projekte' }}</h1>
                </div>
                <div style="display: flex; gap: 16px; margin-top: 10px; align-items: center; flex-wrap: wrap;">
                    @if($viewMode === 'employees')
                        <div style="color: #6b7280; font-size: 14px;">Mitarbeiter:</div>
                        <div style="font-weight: 600; color: #111827;">{{ $timelineByEmployee->count() }}</div>
                    @else
                        <div style="color: #6b7280; font-size: 14px;">Projekte:</div>
                        <div style="font-weight: 600; color: #111827;">{{ $projects->count() }}</div>
                        <div style="height: 16px; width: 1px; background: #e5e7eb;"></div>
                        <div style="display: inline-flex; align-items: center; gap: 8px;">
                            <span style="color: #6b7280; font-size: 14px;">In Bearbeitung:</span>
                            <span style="font-weight: 600; color: #10b981;">{{ $projects->filter(function ($p) {
                                if ($p->finish_date) {
                                    return \Carbon\Carbon::parse($p->finish_date)->isFuture();
                                }
                                return $p->status === 'in_bearbeitung' || $p->status === 'active' || $p->status === 'planning';
                            })->count() }}</span>
                        </div>
                        <div style="display: inline-flex; align-items: center; gap: 8px;">
                            <span style="color: #6b7280; font-size: 14px;">Abgeschlossen:</span>
                            <span style="font-weight: 600; color: #6b7280;">{{ $projects->filter(function ($p) {
                                if ($p->finish_date) {
                                    return \Carbon\Carbon::parse($p->finish_date)->isPast();
                                }
                                return $p->status === 'abgeschlossen' || $p->status === 'completed';
                            })->count() }}</span>
                        </div>
                    @endif
                </div>
                @if($viewMode === 'employees')
                    <div style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
                        <button id="ganttEmployeeUndo" type="button" disabled style="padding: 10px 16px; background: #ffffff; color: #374151; border: 1px solid #d1d5db; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; opacity: 0.5; transition: all 0.2s ease;">Änderung rückgängig</button>
                        <span style="font-size: 12px; color: #6b7280;">Snapping & Undo aktiv – Änderungen werden übernommen, sobald du loslässt.</span>
                    </div>
                @endif
            </div>
            <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                {{-- View Mode Toggle --}}
                <div style="background: #f3f4f6; border-radius: 999px; padding: 4px; display: inline-flex;">
                    <a href="{{ route('gantt.index', ['view' => 'projects']) }}"
                       style="padding: 8px 16px; border-radius: 999px; font-size: 14px; font-weight: 500; text-decoration: none; color: {{ $viewMode === 'projects' ? '#ffffff' : '#374151' }}; background: {{ $viewMode === 'projects' ? '#111827' : 'transparent' }}; transition: all 0.2s ease;">
                        Projekte
                    </a>
                    <a href="{{ route('gantt.index', ['view' => 'employees']) }}"
                       style="padding: 8px 16px; border-radius: 999px; font-size: 14px; font-weight: 500; text-decoration: none; color: {{ $viewMode === 'employees' ? '#ffffff' : '#374151' }}; background: {{ $viewMode === 'employees' ? '#111827' : 'transparent' }}; transition: all 0.2s ease;">
                        Mitarbeiter
                    </a>
                </div>

                {{-- Zoom Controls --}}
                <div style="display: flex; gap: 6px; align-items: center; background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 4px;">
                    <span style="font-size: 12px; color: #6b7280; font-weight: 500; padding: 0 8px;">🔍 Zoom:</span>
                    @php
                        $zoomOptions = [
                            '12m' => ['label' => '12M', 'title' => '12 Monate'],
                            '6m' => ['label' => '6M', 'title' => '6 Monate'],
                            '3m' => ['label' => '3M', 'title' => '3 Monate'],
                            '12w' => ['label' => '12W', 'title' => '12 Wochen'],
                            '6w' => ['label' => '6W', 'title' => '6 Wochen'],
                            '3w' => ['label' => '3W', 'title' => '3 Wochen'],
                        ];
                    @endphp
                    @foreach($zoomOptions as $zoomKey => $zoomData)
                        <a href="{{ route('gantt.index', array_merge(request()->query(), ['zoom' => $zoomKey])) }}"
                           title="{{ $zoomData['title'] }}"
                           style="padding: 6px 12px; border-radius: 6px; font-size: 13px; font-weight: 600; text-decoration: none; color: {{ $currentZoom === $zoomKey ? '#ffffff' : '#374151' }}; background: {{ $currentZoom === $zoomKey ? '#3b82f6' : 'transparent' }}; transition: all 0.15s ease;"
                           onmouseover="this.style.background = '{{ $currentZoom === $zoomKey ? '#2563eb' : '#f3f4f6' }}'"
                           onmouseout="this.style.background = '{{ $currentZoom === $zoomKey ? '#3b82f6' : 'transparent' }}'">
                            {{ $zoomData['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Collapsible Filter Panel (Projects Only) --}}
    @if($viewMode === 'projects')
        <div id="filterPanel" style="display: none; background: white; padding: 20px; margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="font-size: 16px; font-weight: 600; color: #111827; margin: 0;">🔍 Filter & Suche</h3>
                <button onclick="clearAllFilters()" style="background: #fef2f2; color: #dc2626; padding: 6px 12px; border: 1px solid #fecaca; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer; transition: all 0.2s ease;"
                        onmouseover="this.style.background='#fee2e2'"
                        onmouseout="this.style.background='#fef2f2'">
                    🗑️ Filter zurücksetzen
                </button>
            </div>
            <form method="GET" action="{{ route('gantt.index') }}" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                <input type="hidden" name="view" value="projects">
                
                {{-- Search --}}
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">Suche</label>
                    <input type="text" name="search" value="{{ Session::get('gantt_filters.search', '') }}" placeholder="Projektname..." style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
                </div>

                {{-- Status --}}
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">Status</label>
                    <select name="status" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; background: white;">
                        <option value="">Alle Status</option>
                        <option value="in_bearbeitung" {{ Session::get('gantt_filters.status') === 'in_bearbeitung' ? 'selected' : '' }}>In Bearbeitung</option>
                        <option value="abgeschlossen" {{ Session::get('gantt_filters.status') === 'abgeschlossen' ? 'selected' : '' }}>Abgeschlossen</option>
                    </select>
                </div>

                {{-- Employee --}}
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">Mitarbeiter</label>
                    <select name="employee" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; background: white;">
                        <option value="">Alle Mitarbeiter</option>
                        @foreach($availableEmployees ?? [] as $emp)
                            <option value="{{ $emp->id }}" {{ Session::get('gantt_filters.employee') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->first_name }} {{ $emp->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Timeframe --}}
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">Zeitraum</label>
                    <select name="timeframe" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; background: white;">
                        <option value="">Alle Zeiträume</option>
                        <option value="current" {{ Session::get('gantt_filters.timeframe') === 'current' ? 'selected' : '' }}>Aktuelle Projekte</option>
                        <option value="future" {{ Session::get('gantt_filters.timeframe') === 'future' ? 'selected' : '' }}>Zukünftig</option>
                        <option value="past" {{ Session::get('gantt_filters.timeframe') === 'past' ? 'selected' : '' }}>Abgeschlossen</option>
                        <option value="this-month" {{ Session::get('gantt_filters.timeframe') === 'this-month' ? 'selected' : '' }}>Dieser Monat</option>
                        <option value="this-quarter" {{ Session::get('gantt_filters.timeframe') === 'this-quarter' ? 'selected' : '' }}>Dieses Quartal</option>
                    </select>
                </div>

                {{-- Sort --}}
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">Sortierung</label>
                    <select name="sort" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; background: white;">
                        <option value="">Standard</option>
                        <option value="name-asc" {{ Session::get('gantt_filters.sort') === 'name-asc' ? 'selected' : '' }}>Name (A-Z)</option>
                        <option value="name-desc" {{ Session::get('gantt_filters.sort') === 'name-desc' ? 'selected' : '' }}>Name (Z-A)</option>
                        <option value="date-start-asc" {{ Session::get('gantt_filters.sort') === 'date-start-asc' ? 'selected' : '' }}>Startdatum (aufsteigend)</option>
                        <option value="date-start-desc" {{ Session::get('gantt_filters.sort') === 'date-start-desc' ? 'selected' : '' }}>Startdatum (absteigend)</option>
                    </select>
                </div>

                {{-- Submit Button --}}
                <div style="display: flex; align-items: flex-end;">
                    <button type="submit" style="width: 100%; background: #3b82f6; color: white; padding: 10px 16px; border: none; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s ease;"
                            onmouseover="this.style.background='#2563eb'"
                            onmouseout="this.style.background='#3b82f6'">
                        Filter anwenden
                    </button>
                </div>
            </form>
        </div>
    @endif

    @if($viewMode === 'employees')
        @include('gantt.partials.timeline-employees', [
            'timelineStart' => $timelineStart,
            'timelineEnd' => $timelineEnd,
            'totalTimelineDays' => $totalTimelineDays
        ])
    @else
        @include('gantt.partials.timeline-projects', [
            'timelineStart' => $timelineStart,
            'timelineEnd' => $timelineEnd,
            'totalTimelineDays' => $totalTimelineDays
        ])
    @endif
</div>

<script>
// Filter Panel Toggle
function toggleFilters() {
    const panel = document.getElementById('filterPanel');
    if (panel.style.display === 'none' || panel.style.display === '') {
        panel.style.display = 'block';
    } else {
        panel.style.display = 'none';
    }
    
    // Update filter indicators
    updateFilterIndicators();
}

function updateFilterIndicators() {
    const filterCount = {{ count(array_filter(Session::get('gantt_filters', []))) }};
    const menuIndicator = document.getElementById('menuFilterIndicator');
    
    if (menuIndicator) {
        menuIndicator.style.display = filterCount > 0 ? 'inline-flex' : 'none';
        menuIndicator.textContent = filterCount;
    }
}

// Initialize Alpine.js functions
document.addEventListener('alpine:init', () => {
    Alpine.data('dropdownMenu', () => ({
        open: false,
        toggleFilters() {
            toggleFilters();
            this.open = false;
        }
    }));
});
        btn.style.color = '#374151';
        btn.style.borderColor = '#e5e7eb';
    }
}

// Clear All Filters
function clearAllFilters() {
    const url = new URL(window.location.href);
    url.searchParams.delete('search');
    url.searchParams.delete('status');
    url.searchParams.delete('employee');
    url.searchParams.delete('timeframe');
    url.searchParams.delete('sort');
    window.location.href = url.toString();
}
</script>

@endsection