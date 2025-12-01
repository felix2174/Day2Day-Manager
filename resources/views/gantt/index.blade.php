@extends('layouts.app')

@section('title', 'Projektübersicht')

@section('content')
{{-- Include Gantt JavaScript --}}
<script src="{{ asset('js/gantt.js') }}"></script>

{{-- Pass Laravel config to JavaScript --}}
<script>
const ganttConfig = {
    baseUrl: '{{ url('/') }}',
    csrfToken: '{{ csrf_token() }}',
    mocoSyncUrl: '{{ route('moco.sync') }}'
};
</script>

<div style="width: 100%; margin: 0; padding: 0;">
    {{-- ULTRA-KOMPAKTER HEADER (eine Zeile) --}}
    <div style="background: white; padding: 12px 20px; margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                {{-- Statistik-Badge mit Labels (links) --}}
                @if($viewMode === 'employees')
                    <span style="background: #f3f4f6; color: #111827; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                        <span style="color: #6b7280; font-weight: 500;">Mitarbeiter:</span>
                        <span style="font-weight: 700;">{{ $timelineByEmployee->count() }}</span>
                    </span>
                @else
                    @php
                        $totalCount = $projects->count();
                        $activeCount = $projects->filter(function ($p) {
                            if ($p->finish_date) {
                                return \Carbon\Carbon::parse($p->finish_date)->isFuture();
                            }
                            return $p->status === 'in_bearbeitung' || $p->status === 'active' || $p->status === 'planning';
                        })->count();
                        $completedCount = $projects->filter(function ($p) {
                            if ($p->finish_date) {
                                return \Carbon\Carbon::parse($p->finish_date)->isPast();
                            }
                            return $p->status === 'abgeschlossen' || $p->status === 'completed';
                        })->count();
                    @endphp
                    <div style="background: #f3f4f6; padding: 6px 12px; border-radius: 8px; display: inline-flex; align-items: center; gap: 10px;">
                        {{-- Gesamt --}}
                        <div style="display: inline-flex; align-items: center; gap: 4px;">
                            <span style="color: #6b7280; font-size: 11px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px;">Gesamt</span>
                            <span style="color: #111827; font-size: 14px; font-weight: 700;">{{ $totalCount }}</span>
                        </div>
                        <span style="color: #d1d5db;">·</span>
                        {{-- Aktiv --}}
                        <div style="display: inline-flex; align-items: center; gap: 4px;">
                            <span style="color: #10b981; font-size: 11px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px;">Aktiv</span>
                            <span style="color: #10b981; font-size: 14px; font-weight: 700;">{{ $activeCount }}</span>
                        </div>
                        <span style="color: #d1d5db;">·</span>
                        {{-- Fertig --}}
                        <div style="display: inline-flex; align-items: center; gap: 4px;">
                            <span style="color: #9ca3af; font-size: 11px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px;">Fertig</span>
                            <span style="color: #9ca3af; font-size: 14px; font-weight: 700;">{{ $completedCount }}</span>
                        </div>
                    </div>
                @endif

                {{-- View Mode Tabs (kompakt) --}}
                <div style="background: #f3f4f6; border-radius: 8px; padding: 3px; display: inline-flex;">
                    <a href="{{ route('gantt.index', ['view' => 'projects']) }}"
                       style="padding: 6px 14px; border-radius: 6px; font-size: 13px; font-weight: 600; text-decoration: none; color: {{ $viewMode === 'projects' ? '#ffffff' : '#374151' }}; background: {{ $viewMode === 'projects' ? '#111827' : 'transparent' }}; transition: all 0.15s ease;">
                        Projekte
                    </a>
                    <a href="{{ route('gantt.index', ['view' => 'employees']) }}"
                       style="padding: 6px 14px; border-radius: 6px; font-size: 13px; font-weight: 600; text-decoration: none; color: {{ $viewMode === 'employees' ? '#ffffff' : '#374151' }}; background: {{ $viewMode === 'employees' ? '#111827' : 'transparent' }}; transition: all 0.15s ease;">
                        Mitarbeiter
                    </a>
                </div>

                {{-- Ansicht Dropdown --}}
                <div style="position: relative; display: inline-block;">
                    <button type="button" 
                            class="view-menu-btn"
                            onclick="event.stopPropagation(); toggleViewMenu();"
                            style="background: white; border: 1px solid #e5e7eb; cursor: pointer; padding: 6px 12px; color: #374151; font-size: 13px; font-weight: 600; transition: all 0.15s; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px;"
                            onmouseover="this.style.background='#f9fafb'; this.style.borderColor='#d1d5db'"
                            onmouseout="this.style.background='white'; this.style.borderColor='#e5e7eb'">
                        @php
                            $currentZoomIcon = ['month' => '📅', 'week' => '📆', 'day' => '🗓️'][$currentZoom] ?? '📅';
                        @endphp
                        <span>{{ $currentZoomIcon }}</span>
                        <span>Ansicht</span>
                        <span style="font-size: 10px;">▼</span>
                    </button>
                    <div id="viewMenu" style="display: none; position: fixed; background: white; border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); z-index: 10000; min-width: 160px;">
                        <a href="{{ route('gantt.index', array_merge(request()->query(), ['zoom' => 'month'])) }}"
                           style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; color: {{ $currentZoom === 'month' ? '#3b82f6' : '#374151' }}; text-decoration: none; font-size: 13px; font-weight: {{ $currentZoom === 'month' ? '600' : '500' }}; border-bottom: 1px solid #f3f4f6; transition: all 0.15s;"
                           onmouseover="this.style.background='#f9fafb'"
                           onmouseout="this.style.background='white'">
                            <span style="font-size: 16px;">📅</span>
                            <span>Monate</span>
                            @if($currentZoom === 'month')<span style="margin-left: auto; color: #3b82f6; font-size: 14px;">✓</span>@endif
                        </a>
                        <a href="{{ route('gantt.index', array_merge(request()->query(), ['zoom' => 'week'])) }}"
                           style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; color: {{ $currentZoom === 'week' ? '#3b82f6' : '#374151' }}; text-decoration: none; font-size: 13px; font-weight: {{ $currentZoom === 'week' ? '600' : '500' }}; border-bottom: 1px solid #f3f4f6; transition: all 0.15s;"
                           onmouseover="this.style.background='#f9fafb'"
                           onmouseout="this.style.background='white'">
                            <span style="font-size: 16px;">📆</span>
                            <span>Wochen</span>
                            @if($currentZoom === 'week')<span style="margin-left: auto; color: #3b82f6; font-size: 14px;">✓</span>@endif
                        </a>
                        <a href="{{ route('gantt.index', array_merge(request()->query(), ['zoom' => 'day'])) }}"
                           style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; color: {{ $currentZoom === 'day' ? '#3b82f6' : '#374151' }}; text-decoration: none; font-size: 13px; font-weight: {{ $currentZoom === 'day' ? '600' : '500' }}; transition: all 0.15s;"
                           onmouseover="this.style.background='#f9fafb'"
                           onmouseout="this.style.background='white'">
                            <span style="font-size: 16px;">🗓️</span>
                            <span>Tage</span>
                            @if($currentZoom === 'day')<span style="margin-left: auto; color: #3b82f6; font-size: 14px;">✓</span>@endif
                        </a>
                    </div>
                </div>

                {{-- Mehr Dropdown --}}
                <div style="position: relative; display: inline-block;">
                    <button type="button" 
                            class="more-menu-btn"
                            onclick="event.stopPropagation(); toggleMoreMenu();"
                            style="background: white; border: 1px solid #e5e7eb; cursor: pointer; padding: 6px 12px; color: #374151; font-size: 13px; font-weight: 600; transition: all 0.15s; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px;"
                            onmouseover="this.style.background='#f9fafb'; this.style.borderColor='#d1d5db'"
                            onmouseout="this.style.background='white'; this.style.borderColor='#e5e7eb'">
                        <span style="font-size: 16px; line-height: 1;">⋮</span>
                        <span>Mehr</span>
                    </button>
                    <div id="moreMenu" style="display: none; position: fixed; background: white; border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); z-index: 10000; min-width: 200px;">
                        <a href="{{ route('gantt.export') }}" 
                           onclick="handleExportClick(event, this)"
                           style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; color: #374151; text-decoration: none; font-size: 13px; font-weight: 500; border-bottom: 1px solid #f3f4f6; transition: all 0.15s;"
                           onmouseover="this.style.background='#f9fafb'"
                           onmouseout="this.style.background='white'">
                            <span style="font-size: 16px;">📤</span>
                            <span>Excel Export</span>
                        </a>
                        <a href="{{ route('projects.index') }}" 
                           style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; color: #374151; text-decoration: none; font-size: 13px; font-weight: 500; transition: all 0.15s;"
                           onmouseover="this.style.background='#f9fafb'"
                           onmouseout="this.style.background='white'">
                            <span style="font-size: 16px;">📊</span>
                            <span>Projektverwaltung</span>
                        </a>
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: 8px; align-items: center;">
                {{-- Filter Button --}}
                @if($viewMode === 'projects')
                    @php
                        $hasActiveFilters = count(array_filter(Session::get('gantt_filters', []))) > 0;
                    @endphp
                    <button id="toggleFiltersBtn" onclick="toggleFilterModal()" 
                            style="background: {{ $hasActiveFilters ? '#3b82f6' : '#ffffff' }}; color: {{ $hasActiveFilters ? '#ffffff' : '#374151' }}; padding: 6px 12px; border: 1px solid {{ $hasActiveFilters ? '#3b82f6' : '#e5e7eb' }}; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.15s ease; display: inline-flex; align-items: center; gap: 6px;"
                            onmouseover="this.style.background='{{ $hasActiveFilters ? '#2563eb' : '#f9fafb' }}'; this.style.borderColor='{{ $hasActiveFilters ? '#2563eb' : '#d1d5db' }}'"
                            onmouseout="this.style.background='{{ $hasActiveFilters ? '#3b82f6' : '#ffffff' }}'; this.style.borderColor='{{ $hasActiveFilters ? '#3b82f6' : '#e5e7eb' }}'">
                        <span>🔍</span>
                        <span>Filter</span>
                        @if($hasActiveFilters)
                            <span style="background: rgba(255,255,255,0.3); color: white; border-radius: 10px; padding: 2px 6px; font-size: 11px; font-weight: 700;">
                                {{ count(array_filter(Session::get('gantt_filters', []))) }}
                            </span>
                        @endif
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Filter Modal (Projects Only) - Komplett versteckt bis Button-Klick --}}
    @if($viewMode === 'projects')
        @php
            // Prüfe ob Filter aktiv sind (nicht-leere Werte)
            $ganttFilters = Session::get('gantt_filters', []);
            $hasActiveFilters = !empty($ganttFilters['search']) || 
                               !empty($ganttFilters['status']) || 
                               !empty($ganttFilters['employee']) || 
                               !empty($ganttFilters['timeframe']) || 
                               !empty($ganttFilters['sort']);
        @endphp
        
        {{-- Modal Backdrop --}}
        <div id="filterModalBackdrop" onclick="toggleFilterModal()" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 99998; backdrop-filter: blur(4px); transition: all 0.3s ease;"></div>
        
        {{-- Modal Content --}}
        <div id="filterModal" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 90%; max-width: 800px; max-height: 80vh; overflow-y: auto; background: white; padding: 24px; border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); z-index: 99999; transition: all 0.3s ease;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 2px solid #f3f4f6;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <h3 style="font-size: 18px; font-weight: 700; color: #111827; margin: 0;">🔍 Filter & Suche</h3>
                    @if($hasActiveFilters)
                        <span style="background: #3b82f6; color: white; border-radius: 999px; padding: 4px 10px; font-size: 12px; font-weight: 700;">
                            {{ count(array_filter([$ganttFilters['search'] ?? '', $ganttFilters['status'] ?? '', $ganttFilters['employee'] ?? '', $ganttFilters['timeframe'] ?? '', $ganttFilters['sort'] ?? ''])) }} aktiv
                        </span>
                    @endif
                </div>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <button onclick="clearAllFilters()" style="background: #fef2f2; color: #dc2626; padding: 6px 12px; border: 1px solid #fecaca; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s ease;"
                            onmouseover="this.style.background='#fee2e2'"
                            onmouseout="this.style.background='#fef2f2'">
                        🗑️ Zurücksetzen
                    </button>
                    <button onclick="toggleFilterModal()" style="background: #f3f4f6; color: #6b7280; padding: 6px 10px; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 16px; font-weight: 700; cursor: pointer; transition: all 0.2s ease; line-height: 1;"
                            onmouseover="this.style.background='#e5e7eb'; this.style.color='#111827'"
                            onmouseout="this.style.background='#f3f4f6'; this.style.color='#6b7280'"
                            title="Schließen">
                        ✕
                    </button>
                </div>
            </div>
            
            {{-- Filter Form Content --}}
            <div id="filterContent">
            <form method="GET" action="{{ route('gantt.index') }}" id="ganttFilterForm" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                <input type="hidden" name="view" value="projects">
                
                {{-- Search --}}
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">Suche</label>
                    <input type="text" 
                           name="search" 
                           value="{{ Session::get('gantt_filters.search', '') }}" 
                           placeholder="Projektname..." 
                           style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;"
                           oninput="autoSubmitFilter()">
                </div>

                {{-- Status --}}
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">Status</label>
                    <select name="status" 
                            style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; background: white;"
                            onchange="document.getElementById('ganttFilterForm').submit()">
                        <option value="">Alle Status</option>
                        <option value="in_bearbeitung" {{ Session::get('gantt_filters.status') === 'in_bearbeitung' ? 'selected' : '' }}>In Bearbeitung</option>
                        <option value="abgeschlossen" {{ Session::get('gantt_filters.status') === 'abgeschlossen' ? 'selected' : '' }}>Abgeschlossen</option>
                    </select>
                </div>

                {{-- Employee --}}
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">Mitarbeiter</label>
                    <select name="employee" 
                            style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; background: white;"
                            onchange="document.getElementById('ganttFilterForm').submit()">
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
                    <select name="timeframe" 
                            style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; background: white;"
                            onchange="document.getElementById('ganttFilterForm').submit()">
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
                    <select name="sort" 
                            style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; background: white;"
                            onchange="document.getElementById('ganttFilterForm').submit()">
                        <option value="">Standard</option>
                        <option value="name-asc" {{ Session::get('gantt_filters.sort') === 'name-asc' ? 'selected' : '' }}>Name (A-Z)</option>
                        <option value="name-desc" {{ Session::get('gantt_filters.sort') === 'name-desc' ? 'selected' : '' }}>Name (Z-A)</option>
                        <option value="date-start-asc" {{ Session::get('gantt_filters.sort') === 'date-start-asc' ? 'selected' : '' }}>Startdatum (aufsteigend)</option>
                        <option value="date-start-desc" {{ Session::get('gantt_filters.sort') === 'date-start-desc' ? 'selected' : '' }}>Startdatum (absteigend)</option>
                    </select>
                </div>
            </form>
            </div>{{-- End Filter Content --}}
        </div>{{-- End Filter Modal --}}
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

@endsection
