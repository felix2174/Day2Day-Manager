@extends('layouts.app')

@section('title', 'Projektübersicht')

@section('content')
<div style="width: 100%; margin: 0; padding: 0;">
    <div style="background: white; padding: 20px; margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div style="flex: 1; min-width: 260px; display: flex; flex-direction: column; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <h1 style="font-size: 24px; font-weight: bold; color: #111827; margin: 0;">{{ $viewMode === 'employees' ? 'Gantt-Diagramm: Mitarbeiter' : 'Gantt-Diagramm: Projekte' }}</h1>
                    
                    @if($viewMode !== 'employees')
                        {{-- Quick Actions Menu --}}
                        <div style="position: relative;">
                            <button type="button" 
                                    class="header-actions-btn"
                                    onclick="event.stopPropagation(); toggleHeaderActionsMenu();"
                                    style="background: #f3f4f6; border: 1px solid #e5e7eb; cursor: pointer; padding: 6px 12px; color: #6b7280; font-size: 18px; line-height: 1; transition: all 0.2s; border-radius: 8px; font-weight: 600; z-index: 1002; pointer-events: auto;" 
                                    onmouseover="this.style.background='#e5e7eb'; this.style.color='#111827'" 
                                    onmouseout="this.style.background='#f3f4f6'; this.style.color='#6b7280'">
                                ⋮
                            </button>
                            <div id="headerActionsMenu" 
                                 style="display: none; position: absolute; top: 100%; left: 0; background: white; border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); z-index: 10000; min-width: 220px; margin-top: 4px;">
                                <a href="{{ route('projects.create') }}" 
                                   style="display: block; padding: 12px 16px; color: #111827; text-decoration: none; font-size: 14px; border-bottom: 1px solid #f3f4f6; transition: all 0.15s;"
                                   onmouseover="this.style.background='#f9fafb'"
                                   onmouseout="this.style.background='white'">
                                    ➕ Neues Projekt anlegen
                                </a>
                                <button type="button" 
                                        onclick="syncMocoProjects()"
                                        style="display: block; width: 100%; text-align: left; padding: 12px 16px; background: none; border: none; color: #111827; font-size: 14px; cursor: pointer; border-bottom: 1px solid #f3f4f6; transition: all 0.15s;"
                                        onmouseover="this.style.background='#f9fafb'"
                                        onmouseout="this.style.background='white'">
                                    🔄 MOCO synchronisieren
                                </button>
                                <a href="{{ route('projects.index') }}" 
                                   style="display: block; padding: 12px 16px; color: #111827; text-decoration: none; font-size: 14px; transition: all 0.15s;"
                                   onmouseover="this.style.background='#f9fafb'"
                                   onmouseout="this.style.background='white'">
                                    📊 Zur Projektverwaltung
                                </a>
                            </div>
                        </div>
                    @endif
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
        {{-- Define menu functions BEFORE including timeline-projects --}}
        <script>
        // Toggle Header Actions Menu (Quick Actions)
        window.toggleHeaderActionsMenu = function() {
            const menu = document.getElementById('headerActionsMenu');
            if (!menu) return;
            
            // Close all other menus
            const allMenus = document.querySelectorAll('[id^="projectMenu"], [id^="employeeMenu"]');
            allMenus.forEach(m => m.style.display = 'none');
            
            // Toggle this menu
            const currentDisplay = menu.style.display;
            menu.style.display = currentDisplay === 'block' ? 'none' : 'block';
        }

        // Close all menus when clicking outside
        document.addEventListener('click', function(e) {
            const headerMenu = document.getElementById('headerActionsMenu');
            const headerBtn = e.target.closest('.header-actions-btn');
            
            // Close header menu if clicking outside
            if (headerMenu && !headerBtn && !headerMenu.contains(e.target)) {
                headerMenu.style.display = 'none';
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
            console.log('toggleProjectMenu called with:', projectId);
            const menu = document.getElementById('projectMenu' + projectId);
            const button = document.querySelector(`[data-project-id="${projectId}"].project-menu-btn`);
            
            if (!menu || !button) {
                console.error('Menu or button not found for project:', projectId);
                return;
            }
            
            // Close all other menus
            const allMenus = document.querySelectorAll('[id^="projectMenu"], [id^="employeeMenu"]');
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
            
            console.log('Menu display changed to:', menu.style.display);
        }

        // Toggle Employee Menu - Must be defined BEFORE the HTML that uses it
        window.toggleEmployeeMenu = function(projectId, employeeId) {
            console.log('toggleEmployeeMenu called with:', projectId, employeeId);
            const menuId = 'employeeMenu' + projectId + '_' + employeeId;
            const menu = document.getElementById(menuId);
            const button = document.querySelector(`[data-project-id="${projectId}"][data-employee-id="${employeeId}"].employee-menu-btn`);
            
            if (!menu || !button) {
                console.error('Menu or button not found for employee:', projectId, employeeId);
                return;
            }
            
            // Close all other menus
            const allMenus = document.querySelectorAll('[id^="projectMenu"], [id^="employeeMenu"]');
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
            
            console.log('Menu display changed to:', menu.style.display);
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
            console.log('openManageTasksModal called:', { projectId, employeeId, employeeName });
            
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
            console.log('Full URL:', url);
            console.log('Base URL:', baseUrl);
            
            fetch(url)
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Tasks loaded:', data);
                    if (typeof window.renderTasksList === 'function') {
                        window.renderTasksList(data.tasks || [], projectId, employeeId);
                    } else {
                        console.error('renderTasksList function not found');
                        if (container) {
                            container.innerHTML = '<div style="text-align: center; padding: 40px; color: #ef4444;"><div style="font-size: 48px; margin-bottom: 16px;">⚠️</div><p>Fehler: renderTasksList Funktion nicht gefunden.</p></div>';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error loading tasks:', error);
                    if (container) {
                        container.innerHTML = '<div style="text-align: center; padding: 40px; color: #ef4444;"><div style="font-size: 48px; margin-bottom: 16px;">⚠️</div><p>Fehler beim Laden der Aufgaben.</p><p style="font-size: 12px; color: #6b7280; margin-top: 8px;">URL: ' + url + '<br>' + error.message + '</p></div>';
                    }
                });
        }

        window.openEmployeeUtilizationModal = function(employeeId, employeeName) {
            console.log('openEmployeeUtilizationModal called:', { employeeId, employeeName });
            
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
            console.log('Full URL:', url);
            console.log('Base URL:', baseUrl);
            
            fetch(url)
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Utilization loaded:', data);
                    if (typeof window.renderUtilizationView === 'function') {
                        window.renderUtilizationView(data, employeeName);
                    }
                })
                .catch(error => {
                    console.error('Error loading utilization:', error);
                    if (contentElement) {
                        contentElement.innerHTML = '<div style="text-align: center; padding: 40px; color: #ef4444;"><div style="font-size: 48px; margin-bottom: 16px;">⚠️</div><p>Fehler beim Laden der Auslastungsdaten.</p><p style="font-size: 12px; color: #6b7280; margin-top: 8px;">URL: ' + url + '<br>' + error.message + '</p></div>';
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
        </script>
        
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