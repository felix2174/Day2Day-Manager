@extends('layouts.app')

@section('title', 'Projektübersicht')

@section('content')
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
    
    {{-- GLOBAL JavaScript für Header-Dropdowns (für beide Ansichten) --}}
    <script>
    // Toggle View Menu (Ansicht Dropdown) - GLOBAL
    window.toggleViewMenu = function() {
        const menu = document.getElementById('viewMenu');
        if (!menu) return;
        
        // Close all other menus
        const allMenus = document.querySelectorAll('[id^="projectMenu"], [id^="employeeMenu"], #moreMenu, #headerActionsMenu');
        allMenus.forEach(m => m.style.display = 'none');
        
        // Toggle menu with dynamic positioning
        const currentDisplay = menu.style.display;
        const button = document.querySelector('.view-menu-btn');
        
        if (currentDisplay === 'block') {
            menu.style.display = 'none';
        } else {
            if (button) {
                const buttonRect = button.getBoundingClientRect();
                menu.style.position = 'fixed';
                menu.style.top = (buttonRect.bottom + 4) + 'px';
                menu.style.left = (buttonRect.left) + 'px';
            }
            menu.style.display = 'block';
        }
    }

    // Toggle More Menu (Mehr Dropdown) - GLOBAL
    window.toggleMoreMenu = function() {
        const menu = document.getElementById('moreMenu');
        if (!menu) return;
        
        // Close all other menus
        const allMenus = document.querySelectorAll('[id^="projectMenu"], [id^="employeeMenu"], #viewMenu, #headerActionsMenu');
        allMenus.forEach(m => m.style.display = 'none');
        
        // Toggle menu with dynamic positioning
        const currentDisplay = menu.style.display;
        const button = document.querySelector('.more-menu-btn');
        
        if (currentDisplay === 'block') {
            menu.style.display = 'none';
        } else {
            if (button) {
                const buttonRect = button.getBoundingClientRect();
                menu.style.position = 'fixed';
                menu.style.top = (buttonRect.bottom + 4) + 'px';
                menu.style.left = (buttonRect.left) + 'px';
            }
            menu.style.display = 'block';
        }
    }
    
    // Close menus when clicking outside - GLOBAL
    document.addEventListener('click', function(e) {
        const viewMenu = document.getElementById('viewMenu');
        const viewBtn = e.target.closest('.view-menu-btn');
        
        const moreMenu = document.getElementById('moreMenu');
        const moreBtn = e.target.closest('.more-menu-btn');
        
        // Close view menu if clicking outside
        if (viewMenu && !viewBtn && !viewMenu.contains(e.target)) {
            viewMenu.style.display = 'none';
        }
        
        // Close more menu if clicking outside
        if (moreMenu && !moreBtn && !moreMenu.contains(e.target)) {
            moreMenu.style.display = 'none';
        }
    });
    </script>

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
        {{-- Define menu functions BEFORE including timeline-projects --}}
        <script>
        // Toggle Header Actions Menu (Quick Actions)
        window.toggleHeaderActionsMenu = function() {
            const menu = document.getElementById('headerActionsMenu');
            if (!menu) return;
            
            // Close all other menus
            const allMenus = document.querySelectorAll('[id^="projectMenu"], [id^="employeeMenu"], #viewMenu, #moreMenu');
            allMenus.forEach(m => m.style.display = 'none');
            
            // Toggle menu with dynamic positioning
            const currentDisplay = menu.style.display;
            const button = document.querySelector('.header-actions-btn');
            
            if (currentDisplay === 'block') {
                menu.style.display = 'none';
            } else {
                if (button) {
                    const buttonRect = button.getBoundingClientRect();
                    menu.style.position = 'fixed';
                    menu.style.top = (buttonRect.bottom + 4) + 'px';
                    menu.style.left = (buttonRect.left) + 'px';
                }
                menu.style.display = 'block';
            }
        }

        // Close all menus when clicking outside
        document.addEventListener('click', function(e) {
            const headerMenu = document.getElementById('headerActionsMenu');
            const headerBtn = e.target.closest('.header-actions-btn');
            
            const viewMenu = document.getElementById('viewMenu');
            const viewBtn = e.target.closest('.view-menu-btn');
            
            const moreMenu = document.getElementById('moreMenu');
            const moreBtn = e.target.closest('.more-menu-btn');
            
            // Close header menu if clicking outside
            if (headerMenu && !headerBtn && !headerMenu.contains(e.target)) {
                headerMenu.style.display = 'none';
            }
            
            // Close view menu if clicking outside
            if (viewMenu && !viewBtn && !viewMenu.contains(e.target)) {
                viewMenu.style.display = 'none';
            }
            
            // Close more menu if clicking outside
            if (moreMenu && !moreBtn && !moreMenu.contains(e.target)) {
                moreMenu.style.display = 'none';
            }
        });

        // MOCO Sync - Shows loading state and refreshes page
        window.syncMocoProjects = function() {
            if (!confirm('Möchten Sie jetzt die Projekt-Daten von MOCO synchronisieren?\n\nDies kann einige Sekunden dauern.')) {
                return;
            }
            
            // Show loading overlay
            const overlay = document.createElement('div');
            overlay.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); z-index: 99999; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(4px);';
            overlay.innerHTML = `
                <div style="background: white; padding: 32px; border-radius: 16px; text-align: center; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);">
                    <div style="font-size: 48px; margin-bottom: 16px;">⏳</div>
                    <div style="font-size: 18px; font-weight: 600; color: #111827; margin-bottom: 8px;">MOCO Synchronisierung läuft...</div>
                    <div style="font-size: 14px; color: #6b7280;">Bitte warten Sie einen Moment.</div>
                </div>
            `;
            document.body.appendChild(overlay);
            
            // Execute sync command via AJAX
            fetch('{{ route('moco.sync') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                overlay.remove();
                if (data.success) {
                    alert('✅ Synchronisierung erfolgreich!\n\n' + (data.message || 'Daten wurden aktualisiert.'));
                    location.reload();
                } else {
                    alert('❌ Fehler bei der Synchronisierung:\n\n' + (data.message || 'Unbekannter Fehler'));
                }
            })
            .catch(error => {
                overlay.remove();
                console.error('Sync error:', error);
                alert('❌ Fehler bei der Synchronisierung:\n\n' + error.message);
            });
        }

        // Toggle Project Menu - Must be defined BEFORE the HTML that uses it
        window.toggleProjectMenu = function(projectId) {
            const menu = document.getElementById('projectMenu' + projectId);
            const button = document.querySelector(`[data-project-id="${projectId}"].project-menu-btn`);
            
            if (!menu || !button) return;
            
            // Close all other menus
            const allMenus = document.querySelectorAll('[id^="projectMenu"], [id^="employeeMenu"], #viewMenu, #moreMenu, #headerActionsMenu');
            allMenus.forEach(m => {
                if (m.id !== 'projectMenu' + projectId) {
                    m.style.display = 'none';
                }
            });
            
            const currentDisplay = menu.style.display;
            
            if (currentDisplay === 'block') {
                menu.style.display = 'none';
            } else {
                // Position the menu relative to the button
                const buttonRect = button.getBoundingClientRect();
                menu.style.position = 'fixed';
                menu.style.top = (buttonRect.bottom + 4) + 'px';
                menu.style.left = (buttonRect.left) + 'px';
                menu.style.display = 'block';
            }
        }

        // Toggle Employee Menu - Must be defined BEFORE the HTML that uses it
        window.toggleEmployeeMenu = function(projectId, employeeId) {
            const menuId = 'employeeMenu' + projectId + '_' + employeeId;
            const menu = document.getElementById(menuId);
            const button = document.querySelector(`[data-project-id="${projectId}"][data-employee-id="${employeeId}"].employee-menu-btn`);
            
            if (!menu || !button) return;
            
            // Close all other menus
            const allMenus = document.querySelectorAll('[id^="projectMenu"], [id^="employeeMenu"], #viewMenu, #moreMenu');
            allMenus.forEach(m => {
                if (m.id !== menuId) {
                    m.style.display = 'none';
                }
            });
            
            const currentDisplay = menu.style.display;
            
            if (currentDisplay === 'block') {
                menu.style.display = 'none';
            } else {
                // Position the menu relative to the button
                const buttonRect = button.getBoundingClientRect();
                menu.style.position = 'fixed';
                menu.style.top = (buttonRect.bottom + 4) + 'px';
                menu.style.left = (buttonRect.left) + 'px';
                menu.style.display = 'block';
            }
        }

        // Modal functions - Must be defined BEFORE the HTML that uses them
        window.openAddTaskModal = function(projectId, employeeId) {
            document.getElementById('taskModalProjectId').value = projectId;
            document.getElementById('taskModalEmployeeId').value = employeeId;
            const baseUrl = '{{ url('/') }}';
            document.getElementById('addTaskForm').action = baseUrl + '/gantt/projects/' + projectId + '/employees/' + employeeId + '/tasks';
            document.getElementById('addTaskModal').style.display = 'flex';
            // Close the dropdown
            const menu = document.getElementById('employeeMenu' + projectId + '_' + employeeId);
            if (menu) menu.style.display = 'none';
            // Set default start date to today
            const startDateInput = document.getElementById('taskStartDate');
            if (startDateInput) {
                startDateInput.value = new Date().toISOString().split('T')[0];
            }
            if (typeof updateDurationMode === 'function') {
                updateDurationMode();
            }
        }

        window.openManageTasksModal = function(projectId, employeeId, employeeName) {
            // Close employee menu
            const employeeMenu = document.getElementById('employeeMenu' + projectId + '_' + employeeId);
            if (employeeMenu) employeeMenu.style.display = 'none';
            
            // Set employee name
            const nameElement = document.getElementById('manageTasksEmployeeName');
            if (nameElement) nameElement.textContent = employeeName;
            
            // Show modal immediately
            const modal = document.getElementById('manageTasksModal');
            if (modal) modal.style.display = 'block';
            
            // Show loading state
            const container = document.getElementById('tasksListContainer');
            if (container) {
                container.innerHTML = '<div style="text-align: center; padding: 40px;"><div style="font-size: 48px; margin-bottom: 16px;">⏳</div><p style="color: #6b7280;">Lade Aufgaben...</p></div>';
            }
            
            // Load tasks via AJAX
            const baseUrl = '{{ url("/") }}';
            const url = `${baseUrl}/gantt/projects/${projectId}/employees/${employeeId}/tasks`;
            
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (typeof window.renderTasksList === 'function') {
                        window.renderTasksList(data.tasks || [], projectId, employeeId);
                    } else {
                        if (container) {
                            container.innerHTML = '<div style="text-align: center; padding: 40px; color: #ef4444;"><div style="font-size: 48px; margin-bottom: 16px;">⚠️</div><p>Fehler: renderTasksList Funktion nicht gefunden.</p></div>';
                        }
                    }
                })
                .catch(error => {
                    if (container) {
                        container.innerHTML = '<div style="text-align: center; padding: 40px; color: #ef4444;"><div style="font-size: 48px; margin-bottom: 16px;">⚠️</div><p>Fehler beim Laden der Aufgaben.</p><p style="font-size: 12px; color: #6b7280; margin-top: 8px;">' + error.message + '</p></div>';
                    }
                });
        }

        window.openEmployeeUtilizationModal = function(employeeId, employeeName) {
            // Show loading state
            const nameElement = document.getElementById('utilizationEmployeeName');
            const contentElement = document.getElementById('utilizationContent');
            const modal = document.getElementById('employeeUtilizationModal');
            
            if (nameElement) nameElement.textContent = employeeName;
            if (contentElement) {
                contentElement.innerHTML = '<div style="text-align: center; padding: 40px;"><div style="font-size: 48px; margin-bottom: 16px;">⏳</div><p style="color: #6b7280;">Lade Auslastungsdaten...</p></div>';
            }
            if (modal) modal.style.display = 'block';
            
            // Load utilization data
            const baseUrl = '{{ url("/") }}';
            const url = `${baseUrl}/gantt/employees/${employeeId}/utilization`;
            
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (typeof window.renderUtilizationView === 'function') {
                        window.renderUtilizationView(data, employeeName);
                    }
                })
                .catch(error => {
                    if (contentElement) {
                        contentElement.innerHTML = '<div style="text-align: center; padding: 40px; color: #ef4444;"><div style="font-size: 48px; margin-bottom: 16px;">⚠️</div><p>Fehler beim Laden der Auslastungsdaten.</p><p style="font-size: 12px; color: #6b7280; margin-top: 8px;">' + error.message + '</p></div>';
                    }
                });
        }

        window.openAddEmployeeModal = function(projectId) {
            const modalProjectId = document.getElementById('modalProjectId');
            const addEmployeeForm = document.getElementById('addEmployeeForm');
            const addEmployeeModal = document.getElementById('addEmployeeModal');
            const projectMenu = document.getElementById('projectMenu' + projectId);
            
            if (modalProjectId) modalProjectId.value = projectId;
            if (addEmployeeForm) {
                const baseUrl = '{{ url('/') }}';
                // Use bulk-assign route for multi-select support
                addEmployeeForm.action = baseUrl + '/gantt/bulk-assign-employees';
            }
            if (addEmployeeModal) addEmployeeModal.style.display = 'flex';
            if (projectMenu) projectMenu.style.display = 'none';
        }

        // Close Modal Functions - Must be defined BEFORE the HTML that uses them
        window.closeAddEmployeeModal = function() {
            const modal = document.getElementById('addEmployeeModal');
            if (modal) modal.style.display = 'none';
        }

        window.closeAddTaskModal = function() {
            const modal = document.getElementById('addTaskModal');
            const form = document.getElementById('addTaskForm');
            if (modal) modal.style.display = 'none';
            if (form) form.reset();
        }

        window.closeManageTasksModal = function() {
            const modal = document.getElementById('manageTasksModal');
            if (modal) modal.style.display = 'none';
        }

        window.closeEmployeeUtilizationModal = function() {
            const modal = document.getElementById('employeeUtilizationModal');
            if (modal) modal.style.display = 'none';
        }

        // =============== PROJECT COLLAPSE/EXPAND FUNCTIONALITY ===============
        
        // Toggle single project
        window.toggleProject = function(projectId) {
            const projectRow = document.querySelector(`.gantt-project-row[data-project-id="${projectId}"]`);
            if (!projectRow) return;
            
            const employeesContainer = projectRow.querySelector('.project-employees-container');
            const collapseBtn = projectRow.querySelector('.project-collapse-btn');
            const collapseIcon = collapseBtn?.querySelector('.collapse-icon');
            const isCollapsed = projectRow.getAttribute('data-collapsed') === 'true';
            
            if (isCollapsed) {
                // Expand
                projectRow.setAttribute('data-collapsed', 'false');
                employeesContainer.style.maxHeight = employeesContainer.scrollHeight + 'px';
                employeesContainer.style.opacity = '1';
                collapseIcon.textContent = '▼';
                
                // Save state
                saveProjectState(projectId, false);
                
                // After animation, set max-height to none
                setTimeout(() => {
                    if (projectRow.getAttribute('data-collapsed') === 'false') {
                        employeesContainer.style.maxHeight = 'none';
                    }
                }, 300);
            } else {
                // Collapse
                employeesContainer.style.maxHeight = employeesContainer.scrollHeight + 'px';
                employeesContainer.offsetHeight; // Force reflow
                employeesContainer.style.maxHeight = '0';
                employeesContainer.style.opacity = '0';
                collapseIcon.textContent = '▶';
                projectRow.setAttribute('data-collapsed', 'true');
                
                // Save state
                saveProjectState(projectId, true);
            }
        }
        
        // Toggle all projects
        window.toggleAllProjects = function() {
            // Wait a bit to ensure DOM is ready
            setTimeout(() => {
                const allProjectRows = document.querySelectorAll('.gantt-project-row');
                const collapseAllBtn = document.getElementById('collapseAllBtn');
                
                // Only toggle projects that HAVE a collapse button
                const projectsWithButton = Array.from(allProjectRows).filter(row => 
                    row.querySelector('.project-collapse-btn') !== null
                );
                
                // Check if at least one is expanded
                const someExpanded = projectsWithButton.some(row => 
                    row.getAttribute('data-collapsed') !== 'true'
                );
                
                if (someExpanded) {
                    // Collapse all (only those with button)
                    projectsWithButton.forEach(row => {
                        const projectId = row.getAttribute('data-project-id');
                        if (row.getAttribute('data-collapsed') !== 'true') {
                            toggleProject(projectId);
                        }
                    });
                    collapseAllBtn.textContent = '▶';
                } else {
                    // Expand all (only those with button)
                    projectsWithButton.forEach(row => {
                        const projectId = row.getAttribute('data-project-id');
                        if (row.getAttribute('data-collapsed') === 'true') {
                            toggleProject(projectId);
                        }
                    });
                    collapseAllBtn.textContent = '▼';
                }
            }, 50);
        }
        
        // Save project collapse state to localStorage
        function saveProjectState(projectId, isCollapsed) {
            let projectStates = JSON.parse(localStorage.getItem('gantt_project_states') || '{}');
            projectStates[projectId] = isCollapsed;
            localStorage.setItem('gantt_project_states', JSON.stringify(projectStates));
        }
        
        // Restore project collapse states from localStorage
        function restoreProjectStates() {
            const projectStates = JSON.parse(localStorage.getItem('gantt_project_states') || '{}');
            
            Object.keys(projectStates).forEach(projectId => {
                const isCollapsed = projectStates[projectId];
                if (isCollapsed) {
                    const projectRow = document.querySelector(`.gantt-project-row[data-project-id="${projectId}"]`);
                    if (projectRow) {
                        const employeesContainer = projectRow.querySelector('.project-employees-container');
                        const collapseBtn = projectRow.querySelector('.project-collapse-btn');
                        const collapseIcon = collapseBtn?.querySelector('.collapse-icon');
                        
                        employeesContainer.style.maxHeight = '0';
                        employeesContainer.style.opacity = '0';
                        employeesContainer.style.transition = 'none';
                        if (collapseIcon) collapseIcon.textContent = '▶';
                        projectRow.setAttribute('data-collapsed', 'true');
                        
                        setTimeout(() => {
                            employeesContainer.style.transition = 'all 0.3s ease';
                        }, 100);
                    }
                }
            });
            
            updateCollapseAllButton();
        }
        
        // Update collapse all button
        function updateCollapseAllButton() {
            const allProjectRows = document.querySelectorAll('.gantt-project-row');
            const collapseAllBtn = document.getElementById('collapseAllBtn');
            
            if (!collapseAllBtn || allProjectRows.length === 0) return;
            
            const allCollapsed = Array.from(allProjectRows).every(row => 
                row.getAttribute('data-collapsed') === 'true'
            );
            
            collapseAllBtn.textContent = allCollapsed ? '▶' : '▼';
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            restoreProjectStates();
        });
        
        // =============== END PROJECT COLLAPSE/EXPAND FUNCTIONALITY ===============
        </script>
        
        @include('gantt.partials.timeline-projects', [
            'timelineStart' => $timelineStart,
            'timelineEnd' => $timelineEnd,
            'totalTimelineDays' => $totalTimelineDays
        ])
    @endif
</div>

<script>
// Filter Modal Toggle (komplett versteckt/angezeigt)
function toggleFilterModal() {
    const modal = document.getElementById('filterModal');
    const backdrop = document.getElementById('filterModalBackdrop');
    
    if (!modal || !backdrop) return;
    
    // Close all other menus
    const allMenus = document.querySelectorAll('[id^="projectMenu"], [id^="employeeMenu"], #viewMenu, #moreMenu, #headerActionsMenu');
    allMenus.forEach(m => m.style.display = 'none');
    
    // Toggle modal
    if (modal.style.display === 'block') {
        // Close modal
        modal.style.display = 'none';
        backdrop.style.display = 'none';
        document.body.style.overflow = ''; // Re-enable scrolling
    } else {
        // Open modal
        modal.style.display = 'block';
        backdrop.style.display = 'block';
        document.body.style.overflow = 'hidden'; // Disable background scrolling
    }
}

// Escape key closes modal
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('filterModal');
        if (modal && modal.style.display === 'block') {
            toggleFilterModal();
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

@endsection