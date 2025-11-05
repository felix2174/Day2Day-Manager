@extends('layouts.app')

@section('title', 'Projektübersicht')

@section('content')
<div style="width: 100%; margin: 0; padding: 0;">
    <div style="background: white; padding: 20px; margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div style="flex: 1; min-width: 260px; display: flex; flex-direction: column; gap: 12px;">
                <h1 style="font-size: 24px; font-weight: bold; color: #111827; margin: 0;">{{ $viewMode === 'employees' ? 'Gantt-Diagramm: Mitarbeiter' : 'Gantt-Diagramm: Projekte' }}</h1>
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
                    <span style="font-size: 12px; color: #6b7280; font-weight: 500; padding: 0 8px;">🔍 Ansicht:</span>
                    @php
                        $zoomOptions = [
                            'month' => ['label' => 'Monate', 'title' => 'Monatsansicht', 'icon' => '📅'],
                            'week' => ['label' => 'Wochen', 'title' => 'Wochenansicht', 'icon' => '📆'],
                            'day' => ['label' => 'Tage', 'title' => 'Tagesansicht', 'icon' => '🗓️'],
                        ];
                    @endphp
                    @foreach($zoomOptions as $zoomKey => $zoomData)
                        <a href="{{ route('gantt.index', array_merge(request()->query(), ['zoom' => $zoomKey])) }}"
                           title="{{ $zoomData['title'] }}"
                           style="padding: 6px 12px; border-radius: 6px; font-size: 13px; font-weight: 600; text-decoration: none; color: {{ $currentZoom === $zoomKey ? '#ffffff' : '#374151' }}; background: {{ $currentZoom === $zoomKey ? '#3b82f6' : 'transparent' }}; transition: all 0.15s ease; display: inline-flex; align-items: center; gap: 4px;"
                           onmouseover="this.style.background = '{{ $currentZoom === $zoomKey ? '#2563eb' : '#f3f4f6' }}'"
                           onmouseout="this.style.background = '{{ $currentZoom === $zoomKey ? '#3b82f6' : 'transparent' }}'">
                            <span>{{ $zoomData['icon'] }}</span>
                            <span>{{ $zoomData['label'] }}</span>
                        </a>
                    @endforeach
                </div>

                {{-- Filter Toggle Button --}}
                @if($viewMode === 'projects')
                    <button id="toggleFiltersBtn" onclick="toggleFilters()" style="background: #ffffff; color: #374151; padding: 10px 16px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer; transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 8px;"
                            onmouseover="this.style.background='#f9fafb'; this.style.borderColor='#d1d5db'"
                            onmouseout="this.style.background='#ffffff'; this.style.borderColor='#e5e7eb'">
                        🔍 Filter
                        <span id="filterIndicator" style="display: {{ count(array_filter(Session::get('gantt_filters', []))) > 0 ? 'inline-flex' : 'none' }}; background: #ef4444; color: white; border-radius: 999px; width: 20px; height: 20px; align-items: center; justify-content: center; font-size: 11px; font-weight: 700;">{{ count(array_filter(Session::get('gantt_filters', []))) }}</span>
                    </button>
                @endif

                {{-- Excel Export --}}
                <a href="{{ route('gantt.export') }}" 
                   onclick="handleExportClick(event, this)"
                   style="background: #ffffff; color: #374151; padding: 10px 20px; border: 1px solid #e5e7eb; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 500; transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 8px;"
                   onmouseover="this.style.background='#f9fafb'; this.style.borderColor='#d1d5db'"
                   onmouseout="this.style.background='#ffffff'; this.style.borderColor='#e5e7eb'">
                    <span class="export-text">📤 Excel Export</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Collapsible Filter Panel (Projects Only) --}}
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
        <div id="filterPanel" style="display: {{ $hasActiveFilters ? 'block' : 'none' }}; background: white; padding: 20px; margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="font-size: 16px; font-weight: 600; color: #111827; margin: 0;">🔍 Filter & Suche</h3>
                <button onclick="clearAllFilters()" style="background: #fef2f2; color: #dc2626; padding: 6px 12px; border: 1px solid #fecaca; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer; transition: all 0.2s ease;"
                        onmouseover="this.style.background='#fee2e2'"
                        onmouseout="this.style.background='#fef2f2'">
                    🗑️ Filter zurücksetzen
                </button>
            </div>
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
// Filter Panel Toggle with State Persistence
function toggleFilters() {
    const panel = document.getElementById('filterPanel');
    const btn = document.getElementById('toggleFiltersBtn');
    if (panel.style.display === 'none' || panel.style.display === '') {
        panel.style.display = 'block';
        btn.style.background = '#3b82f6';
        btn.style.color = '#ffffff';
        btn.style.borderColor = '#3b82f6';
        localStorage.setItem('gantt_filter_panel_open', '1');
        
        // Update Hover für geöffneten Zustand
        btn.onmouseover = function() {
            this.style.background = '#2563eb';
            this.style.borderColor = '#2563eb';
        };
        btn.onmouseout = function() {
            this.style.background = '#3b82f6';
            this.style.borderColor = '#3b82f6';
        };
    } else {
        panel.style.display = 'none';
        btn.style.background = '#ffffff';
        btn.style.color = '#374151';
        btn.style.borderColor = '#e5e7eb';
        localStorage.setItem('gantt_filter_panel_open', '0');
        
        // Update Hover für geschlossenen Zustand
        btn.onmouseover = function() {
            this.style.background = '#f9fafb';
            this.style.borderColor = '#d1d5db';
        };
        btn.onmouseout = function() {
            this.style.background = '#ffffff';
            this.style.borderColor = '#e5e7eb';
        };
    }
}

// Initialize Filter Panel State on Page Load
document.addEventListener('DOMContentLoaded', function() {
    const panel = document.getElementById('filterPanel');
    const btn = document.getElementById('toggleFiltersBtn');
    
    if (panel && btn) {
        // Wenn Panel initial sichtbar ist (wegen aktiven Filtern)
        if (panel.style.display === 'block') {
            btn.style.background = '#3b82f6';
            btn.style.color = '#ffffff';
            btn.style.borderColor = '#3b82f6';
            
            // Update Hover-Events für aktiven Zustand
            btn.onmouseover = function() {
                this.style.background = '#2563eb';
                this.style.borderColor = '#2563eb';
            };
            btn.onmouseout = function() {
                this.style.background = '#3b82f6';
                this.style.borderColor = '#3b82f6';
            };
        }
    }
});

// Auto-Submit Filter with Debounce (für Suchfeld)
let filterTimeout;
function autoSubmitFilter() {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(function() {
        document.getElementById('ganttFilterForm').submit();
    }, 500); // 500ms Verzögerung nach letzter Eingabe
}

// Clear All Filters
function clearAllFilters() {
    const url = new URL(window.location.href);
    url.searchParams.delete('search');
    url.searchParams.delete('status');
    url.searchParams.delete('employee');
    url.searchParams.delete('timeframe');
    url.searchParams.delete('sort');
    url.searchParams.delete('filter_open');
    localStorage.removeItem('gantt_filter_panel_open');
    window.location.href = url.toString();
}

// Handle Excel Export with loading state
function handleExportClick(event, link) {
    event.preventDefault();
    
    // Show loading overlay
    showLoading('Excel wird generiert...');
    
    // Show button loading state
    const textSpan = link.querySelector('.export-text');
    if (textSpan) {
        textSpan.innerHTML = '⏳ Exportiert...';
    }
    link.style.opacity = '0.6';
    link.style.pointerEvents = 'none';
    
    // Trigger download
    window.location.href = link.href;
    
    // Hide loading after 3 seconds (download should have started)
    setTimeout(() => {
        hideLoading();
        if (textSpan) {
            textSpan.innerHTML = '📤 Excel Export';
        }
        link.style.opacity = '1';
        link.style.pointerEvents = '';
    }, 3000);
}
</script>
</script>

@endsection