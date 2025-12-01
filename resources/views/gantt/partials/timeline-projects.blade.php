{{-- Timeline, Projekte und Legende --}}
<div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
    <div style="padding: 20px;">
        @if($projects->count() > 0)
            @php
                $timelineStartIso = $timelineStart->format('Y-m-d');
                $timelineEndIso = $timelineEnd->format('Y-m-d');
                $timelineSpanDays = max(1, $timelineStart->diffInDays($timelineEnd) + 1);
            @endphp

            {{-- Altes Tooltip-System entfernt - nur noch project-bar-tooltip wird verwendet --}}

            <div style="overflow-x: auto; padding-bottom: 10px;">
                <div id="ganttScrollContainer" data-timeline-start="{{ $timelineStartIso }}" data-timeline-end="{{ $timelineEndIso }}" data-timeline-days="{{ $timelineSpanDays }}" style="position: relative; width: 100%; overflow-y: hidden; cursor: grab; user-select: none; border: 1px solid #e5e7eb; border-radius: 8px;" class="gantt-scroll-container">
                    @php
                        $today = \Carbon\Carbon::now()->startOfDay();
                        $todayRatio = null;
                        if ($today->gte($timelineStart) && $today->lte($timelineEnd)) {
                            $todayOffset = max(0, $timelineStart->diffInDays($today));
                            $todayRatio = $todayOffset / $timelineSpanDays;
                        }
                    @endphp

                    {{-- Global HEUTE Marker - Continuous line through all project rows --}}
                    @if($todayRatio !== null)
                        <div style="position: absolute; left: calc(272px + {{ $todayRatio }} * (100% - 284px)); top: 64px; bottom: 12px; width: 3px; background: rgba(239, 68, 68, 0.28); z-index: 5; pointer-events: none; box-shadow: 0 0 12px rgba(239, 68, 68, 0.4);"></div>
                    @endif

                    <div id="ganttContent" style="display: flex; flex-direction: column; gap: 12px; padding: 12px 12px 16px; min-width: 100%;">
                        {{-- Header --}}
                        <div style="display: flex; gap: 12px; align-items: stretch;">
                            <div style="width: 260px; min-width: 260px; border: 1px solid #e5e7eb; background: #f9fafb; border-radius: 8px; display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; font-weight: 600; color: #374151;">
                                <span>Projekt</span>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    {{-- Collapse All Button --}}
                                    <button type="button" 
                                            id="collapseAllBtn"
                                            onclick="event.stopPropagation(); toggleAllProjects();"
                                            title="Alle Projekte ein-/ausklappen"
                                            style="background: #f3f4f6; border: 1px solid #e5e7eb; cursor: pointer; padding: 4px 10px; color: #6b7280; font-size: 14px; line-height: 1; transition: all 0.2s; border-radius: 6px; font-weight: 600;" 
                                            onmouseover="this.style.background='#e5e7eb'; this.style.color='#111827'" 
                                            onmouseout="this.style.background='#f3f4f6'; this.style.color='#6b7280'">
                                        ▼
                                    </button>
                                    {{-- Quick Actions Menu --}}
                                    <div style="position: relative;">
                                        <button type="button" 
                                                class="header-actions-btn"
                                                onclick="event.stopPropagation(); toggleHeaderActionsMenu();"
                                                style="background: #f3f4f6; border: 1px solid #e5e7eb; cursor: pointer; padding: 4px 10px; color: #6b7280; font-size: 16px; line-height: 1; transition: all 0.2s; border-radius: 6px; font-weight: 600; z-index: 1002; pointer-events: auto;" 
                                                onmouseover="this.style.background='#e5e7eb'; this.style.color='#111827'" 
                                                onmouseout="this.style.background='#f3f4f6'; this.style.color='#6b7280'">
                                            ⋮
                                        </button>
                                    <div id="headerActionsMenu" 
                                         style="display: none; position: fixed; background: white; border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); z-index: 10000; min-width: 220px;">
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
                                </div>
                            </div>
                            <div style="flex: 1; position: relative; height: 40px; border: 1px solid #e5e7eb; background: #f9fafb; border-radius: 8px; overflow: visible; z-index: 10;">
                                @foreach($timelineMonths as $index => $monthData)
                                    @php
                                        $periodStart = $monthData['start'];
                                        $periodEnd = $monthData['end'];
                                        $offsetDays = max(0, $timelineStart->diffInDays($periodStart));
                                        $widthDays = max(1, $periodStart->diffInDays($periodEnd) + 1);
                                        $leftPercent = ($offsetDays / $timelineSpanDays) * 100;
                                        $widthPercent = ($widthDays / $timelineSpanDays) * 100;
                                    @endphp
                                    <div data-period-index="{{ $index }}" data-is-current-period="{{ $monthData['is_current'] ? 'true' : 'false' }}" data-period-start="{{ $periodStart->toDateString() }}" data-period-end="{{ $periodEnd->toDateString() }}" style="position: absolute; left: {{ $leftPercent }}%; width: {{ $widthPercent }}%; top: 0; bottom: 0; border-left: 1px solid #e5e7eb; border-right: 1px solid #e5e7eb; background: {{ $monthData['is_current'] ? '#dbeafe' : 'transparent' }}; display: flex; align-items: center; justify-content: center; font-size: {{ $timelineUnit === 'week' ? '11px' : '12px' }}; color: {{ $monthData['is_current'] ? '#1e3a8a' : '#374151' }}; font-weight: 600;">
                                        <span style="background: rgba(255,255,255,0.85); padding: 2px 6px; border-radius: 4px;">{{ $monthData['label'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Projektzeilen --}}
                        @foreach($projects as $project)
                            @php
                                $metrics = $projectMetrics[$project->id] ?? null;
                                if (!$metrics) { continue; }
                                $projectData = $timelineByProject[$project->id] ?? ['summary' => [], 'assignments' => collect(), 'team_names' => collect(), 'has_assignments' => false];
                                $projectAssignments = collect($projectData['assignments'] ?? [])->values();
                                $projectSummary = $projectData['summary'] ?? [];
                                $projectStart = $metrics['startDate'];
                                $projectEnd = $metrics['endDate'];
                                
                                // Check if project extends beyond timeline or is overdue
                                $extendsRight = $projectEnd->gt($timelineEnd);
                                $extendsLeft = $projectStart->lt($timelineStart);
                                $isOverdue = $projectEnd->isPast() && $projectEnd->lt(now());
                                $isDueSoon = !$isOverdue && $projectEnd->isFuture() && $projectEnd->diffInDays(now()) <= 7;
                                
                                // WICHTIG: Überfällige Projekte NICHT an timelineStart clampen!
                                // Sie sollen an ihrem echten end_date enden (auch wenn vor timelineStart)
                                if ($isOverdue && $projectEnd->lt($timelineStart)) {
                                    // Projekt endete VOR Timeline-Start → zeige es NICHT
                                    $shouldDisplayProject = false;
                                } else {
                                    $shouldDisplayProject = true;
                                    $projectClampedStart = $projectStart->lt($timelineStart) ? $timelineStart->copy() : $projectStart;
                                    $projectClampedEnd = $projectEnd->gt($timelineEnd) ? $timelineEnd->copy() : $projectEnd;
                                    $projectOffsetDays = max(0, $timelineStart->diffInDays($projectClampedStart));
                                    $projectDurationDays = max(1, $projectClampedStart->diffInDays($projectClampedEnd) + 1);
                                    $projectLeftPercent = ($projectOffsetDays / $timelineSpanDays) * 100;
                                    $projectWidthPercent = ($projectDurationDays / $timelineSpanDays) * 100;
                                }
                                
                                // Color coding (MS Project style)
                                $statusColor = match (true) {
                                    $isOverdue => '#dc2626', // Red for overdue
                                    $isDueSoon => '#f97316', // Orange for due soon (<7 days)
                                    $metrics['bottleneck'] => '#ef4444', // Lighter red for bottleneck
                                    $metrics['riskScore'] >= 60 => '#f59e0b', // Amber for high risk
                                    $metrics['riskScore'] >= 40 => '#facc15', // Yellow for medium risk
                                    default => '#10b981', // Green for healthy
                                };
                                
                                $progress = round($project->progress ?? 0);
                            @endphp
                            @php
                                // Load fresh assignments directly from database to get accurate count
                                $freshAssignments = \App\Models\Assignment::where('project_id', $project->id)
                                    ->with('employee')
                                    ->orderBy('display_order')
                                    ->get();
                                
                                // Count all assignments (even "Unbekannt" ones)
                                // The "Unbekannt" filtering happens later in the display loop
                                $assignmentCount = $freshAssignments->count();
                                $timelineHeight = 40 + ($assignmentCount > 0 ? $assignmentCount * 32 : 0);
                            @endphp
                                @php
                                    $projectData = $timelineByProject[$project->id] ?? ['summary' => [], 'assignments' => collect(), 'team_names' => collect(), 'has_assignments' => false];
                                    $projectAssignments = collect($projectData['assignments'] ?? [])->values();
                                    $projectSummary = $projectData['summary'] ?? [];
                                @endphp
                                <div class="gantt-project-row" data-project-id="{{ $project->id }}" data-collapsed="false" style="border: 1px solid #e5e7eb; border-radius: 8px; background: white; padding: 16px; display: flex; flex-direction: column; gap: 16px; overflow: visible;">
                                    <div style="display: grid; grid-template-columns: 260px 1fr; gap: 12px; align-items: center;">
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            {{-- Collapse Icon (immer sichtbar für alle Projekte) --}}
                                            <button type="button" 
                                                    class="project-collapse-btn" 
                                                    data-project-id="{{ $project->id }}"
                                                    onclick="event.stopPropagation(); toggleProject({{ $project->id }});"
                                                    title="Mitarbeiter ein-/ausblenden"
                                                    style="background: none; border: none; cursor: pointer; padding: 4px; color: #6b7280; font-size: 14px; line-height: 1; transition: all 0.2s; display: flex; align-items: center; justify-content: center; min-width: 20px;" 
                                                    onmouseover="this.style.color='#111827'" 
                                                    onmouseout="this.style.color='#6b7280'">
                                                <span class="collapse-icon">▼</span>
                                            </button>
                                            {{-- Project Menu (Three Dots) --}}
                                            <div style="position: relative; display: inline-block; z-index: 1001; overflow: visible;">
                                                <button type="button" 
                                                        class="project-menu-btn" 
                                                        data-project-id="{{ $project->id }}"
                                                        onclick="event.stopPropagation(); event.stopImmediatePropagation(); window.toggleProjectMenu({{ $project->id }}); return false;"
                                                        style="background: none; border: none; cursor: pointer; padding: 4px 8px; color: #6b7280; font-size: 18px; line-height: 1; transition: all 0.2s; position: relative; z-index: 1002; pointer-events: auto;" 
                                                        onmouseover="this.style.color='#111827'" 
                                                        onmouseout="this.style.color='#6b7280'">
                                                    ⋮
                                                </button>
                                                <div id="projectMenu{{ $project->id }}" style="display: none; position: fixed; background: white; border: 1px solid #d1d5db; border-radius: 8px; box-shadow: 0 8px 24px rgba(0,0,0,0.12); z-index: 99999; min-width: 220px; max-width: 280px; white-space: nowrap;">
                                                    <div style="padding: 4px 0;">
                                                        <button type="button" onclick="openAddEmployeeModal({{ $project->id }})" style="width: 100%; text-align: left; padding: 10px 16px; background: none; border: none; cursor: pointer; font-size: 14px; color: #374151; display: flex; align-items: center; gap: 10px; transition: all 0.15s;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='white'">
                                                            <span style="font-size: 16px; color: #10b981;">➕</span>
                                                            <span style="font-weight: 500;">Mitarbeiter hinzufügen</span>
                                                        </button>
                                                        <div style="height: 1px; background: #e5e7eb; margin: 4px 0;"></div>
                                                        <button type="button" onclick="window.location.href='{{ route('projects.show', $project->id) }}'" style="width: 100%; text-align: left; padding: 10px 16px; background: none; border: none; cursor: pointer; font-size: 14px; color: #374151; display: flex; align-items: center; gap: 10px; transition: all 0.15s;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='white'">
                                                            <span style="font-size: 16px; color: #3b82f6;">📊</span>
                                                            <span style="font-weight: 500;">Projektdetails</span>
                                                        </button>
                                                        <button type="button" onclick="openRemoveEmployeesModal({{ $project->id }})" style="width: 100%; text-align: left; padding: 10px 16px; background: none; border: none; cursor: pointer; font-size: 14px; color: #374151; display: flex; align-items: center; gap: 10px; transition: all 0.15s;" onmouseover="this.style.background='#fef2f2'; this.querySelector('span:last-child').style.color='#dc2626'" onmouseout="this.style.background='white'; this.querySelector('span:last-child').style.color='#374151'">
                                                            <span style="font-size: 16px; color: #ef4444;">🗑️</span>
                                                            <span style="font-weight: 500; transition: color 0.15s;">Mitarbeiter entfernen</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- Projektname ohne title-Attribut (Tooltip kommt vom Balken-Hover) --}}
                                            <div style="font-weight: 600; color: #111827; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $project->name }}</div>
                                        </div>
                                        <div style="position: relative; height: 24px; border: 1px solid #e5e7eb; border-radius: 12px; background: #f9fafb;">
                                            @php
                                                // Deadline-Status für visuelle Indikatoren
                                                $daysUntilDeadline = now()->startOfDay()->diffInDays($projectEnd, false);
                                                $isOverdue = $daysUntilDeadline < 0 && $project->status !== 'abgeschlossen' && $project->status !== 'completed';
                                                $isUrgent = $daysUntilDeadline >= 0 && $daysUntilDeadline <= 7 && $project->status !== 'abgeschlossen' && $project->status !== 'completed';
                                                
                                                // Überschreibe Status-Farbe bei Deadline-Problemen
                                                if ($isOverdue) {
                                                    $statusColor = '#dc2626'; // Rot für überfällig
                                                } elseif ($isUrgent) {
                                                    $statusColor = '#f97316'; // Orange für dringend
                                                }
                                                
                                                // VERSION 2.0: Zusätzliche Tooltip-Daten
                                                // Verantwortlicher mit Badge für inaktive
                                                $responsibleName = 'Nicht zugewiesen';
                                                if ($project->responsible) {
                                                    $responsibleName = $project->responsible->first_name . ' ' . $project->responsible->last_name;
                                                    if (!$project->responsible->is_active) {
                                                        $responsibleName .= ' (Inaktiv)';
                                                    }
                                                }
                                                
                                                // Team-Größe (eindeutige Mitarbeiter)
                                                $projectAssignments = \App\Models\Assignment::where('project_id', $project->id)->with('employee')->get();
                                                $teamSize = $projectAssignments->unique('employee_id')->count();
                                                
                                                // Gesamtstunden (Wochenstunden × Projektwochen)
                                                $projectDurationWeeks = max(1, $projectStart->diffInWeeks($projectEnd));
                                                $totalWeeklyHours = $projectAssignments->sum('weekly_hours');
                                                $totalPlannedHours = $totalWeeklyHours * $projectDurationWeeks;
                                            @endphp
                                            @if($shouldDisplayProject)
                                            <div class="gantt-bar" data-project-name="{{ $project->name }}" data-start-date="{{ $projectStart->format('d.m.Y') }}" data-end-date="{{ $projectEnd->format('d.m.Y') }}" data-deadline="{{ $projectEnd->format('d.m.Y') }}" data-days-until-deadline="{{ $daysUntilDeadline }}" data-is-overdue="{{ $isOverdue ? 'true' : 'false' }}" data-required-hours="{{ (int)round($metrics['requiredPerWeek']) }}" data-available-hours="{{ (int)round($metrics['availablePerWeek']) }}" data-progress="{{ $progress }}" data-status="{{ ucfirst($project->status) }}" data-capacity-ratio="{{ $metrics['capacityRatio'] ?? '' }}" data-risk-score="{{ round($metrics['riskScore']) }}" data-responsible="{{ $responsibleName }}" data-team-size="{{ $teamSize }}" data-total-hours="{{ $totalPlannedHours }}" data-weekly-hours="{{ $totalWeeklyHours }}" data-duration-weeks="{{ $projectDurationWeeks }}" style="position: absolute; top: 0; height: 100%; border-radius: 12px; background: {{ $statusColor }}; left: {{ $projectLeftPercent }}%; width: {{ $projectWidthPercent }}%; min-width: 1.5%; display: flex; align-items: center; justify-content: space-between; color: white; font-size: 12px; font-weight: 600; box-shadow: 0 2px 6px rgba(0,0,0,0.15); padding: 0 8px; cursor: pointer; {{ $extendsRight ? 'border-right: 3px dashed rgba(255,255,255,0.7);' : '' }}">
                                                <span style="padding: 0 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex: 1;">{{ Str::limit($project->name, 32) }}</span>
                                                <div style="display: flex; align-items: center; gap: 6px; flex-shrink: 0;">
                                                    <span style="font-weight: 600; background: rgba(255,255,255,0.2); padding: 2px 6px; border-radius: 12px;">{{ $progress }}%</span>
                                                    @if($isOverdue)
                                                        <span style="font-size: 14px; filter: drop-shadow(0 0 2px rgba(0,0,0,0.3));" title="Überfällig seit {{ now()->diffInDays($projectEnd) }} Tagen">⚠️</span>
                                                    @elseif($isDueSoon)
                                                        <span style="font-size: 14px; filter: drop-shadow(0 0 2px rgba(0,0,0,0.3));" title="Fällig in {{ $projectEnd->diffInDays(now()) }} Tagen">🕐</span>
                                                    @elseif($extendsLeft)
                                                        <span style="font-size: 14px; filter: drop-shadow(0 0 2px rgba(0,0,0,0.3));" title="Begann vor Timeline">←</span>
                                                    @elseif($extendsRight)
                                                        <span style="font-size: 14px; filter: drop-shadow(0 0 2px rgba(0,0,0,0.3));" title="Läuft über Timeline hinaus">→</span>
                                                    @elseif($isUrgent)
                                                        <span style="font-size: 14px; filter: drop-shadow(0 0 2px rgba(0,0,0,0.3));">⏰</span>
                                                    @else
                                                        <span style="font-size: 14px; filter: drop-shadow(0 0 2px rgba(0,0,0,0.3));">🎯</span>
                                                    @endif
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div style="display: flex; flex-direction: column; gap: 8px;">
                                        @php
                                            // Use already loaded fresh assignments from above (no duplicate query!)
                                            // $freshAssignments already loaded above for accurate count
                                            
                                            // Convert to the format expected by the view
                                            $assignmentsForView = $freshAssignments->map(function($assignment) use ($timelineStart, $timelineEnd) {
                                                // CHANGED: Start-Date Extension für ongoing tasks
                                                $start = $assignment->start_date ? \Carbon\Carbon::parse($assignment->start_date) : now()->subMonths(6);
                                                // CHANGED: End-Date Extension für ongoing tasks
                                                $end = $assignment->end_date ? \Carbon\Carbon::parse($assignment->end_date) : now()->addMonths(6);
                                                
                                                return [
                                                    'employee_id' => $assignment->employee_id,
                                                    'employee_name' => $assignment->employee ? $assignment->employee->first_name . ' ' . $assignment->employee->last_name : 'Unbekannt',
                                                    'task_name' => $assignment->task_name,
                                                    'task_description' => $assignment->task_description,
                                                    'start' => $start,
                                                    'end' => $end,
                                                    'weekly_hours' => $assignment->weekly_hours,
                                                    'assignment_id' => $assignment->id,
                                                    'assignment_ids' => [$assignment->id],
                                                ];
                                            });
                                            
                                            // Group assignments by employee
                                            $assignmentsByEmployee = $assignmentsForView->groupBy('employee_id');
                                        @endphp
                                        {{-- Collapsible Employee Container --}}
                                        <div class="project-employees-container" data-project-id="{{ $project->id }}" style="display: block; transition: all 0.3s ease; overflow: hidden;">
                                        @forelse($assignmentsByEmployee as $employeeId => $employeeAssignments)
                                            @php
                                                $firstAssignment = $employeeAssignments->first();
                                                $employeeName = $firstAssignment['employee_name'] ?? 'Unbekannt';
                                                
                                                // Skip employees with "Unbekannt" name
                                                if ($employeeName === 'Unbekannt') {
                                                    continue;
                                                }
                                            @endphp
                                            {{-- Employee header row with tasks as blue bars on the timeline --}}
                                            <div style="display: grid; grid-template-columns: 260px 1fr; gap: 12px; align-items: center; margin-top: 8px;">
                                                <div style="height: 28px; display: flex; align-items: center; background: #f3f4f6; border: 1px solid #d1d5db; border-radius: 8px; padding: 0 12px; color: #111827; font-size: 13px; font-weight: 600; gap: 8px;">
                                                    {{-- Employee Menu (Three Dots) --}}
                                                    <div style="position: relative; display: inline-block; z-index: 1001;">
                                                        <button type="button" 
                                                                class="employee-menu-btn" 
                                                                data-project-id="{{ $project->id }}"
                                                                data-employee-id="{{ $employeeId }}"
                                                                onclick="event.stopPropagation(); event.stopImmediatePropagation(); window.toggleEmployeeMenu({{ $project->id }}, {{ $employeeId }}); return false;"
                                                                style="background: none; border: none; cursor: pointer; padding: 2px 4px; color: #6b7280; font-size: 16px; line-height: 1; transition: all 0.2s; position: relative; z-index: 1002; pointer-events: auto;" 
                                                                onmouseover="this.style.color='#111827'" 
                                                                onmouseout="this.style.color='#6b7280'">
                                                            ⋮
                                                        </button>
                                                        <div id="employeeMenu{{ $project->id }}_{{ $employeeId }}" style="display: none; position: fixed; background: white; border: 1px solid #d1d5db; border-radius: 8px; box-shadow: 0 8px 24px rgba(0,0,0,0.12); z-index: 99999; min-width: 220px; max-width: 280px; white-space: nowrap;">
                                                            <div style="padding: 4px 0;">
                                                                <button type="button" onclick="openAddTaskModal({{ $project->id }}, {{ $employeeId }})" style="width: 100%; text-align: left; padding: 10px 16px; background: none; border: none; cursor: pointer; font-size: 14px; color: #374151; display: flex; align-items: center; gap: 10px; transition: all 0.15s;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='white'">
                                                                    <span style="font-size: 16px; color: #10b981;">➕</span>
                                                                    <span style="font-weight: 500;">Aufgabe hinzufügen</span>
                                                                </button>
                                                                <div style="height: 1px; background: #e5e7eb; margin: 4px 0;"></div>
                                                                <button type="button" onclick="openManageTasksModal({{ $project->id }}, {{ $employeeId }}, '{{ $employeeName }}')" style="width: 100%; text-align: left; padding: 10px 16px; background: none; border: none; cursor: pointer; font-size: 14px; color: #374151; display: flex; align-items: center; gap: 10px; transition: all 0.15s;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='white'">
                                                                    <span style="font-size: 16px; color: #3b82f6;">📋</span>
                                                                    <span style="font-weight: 500;">Aufgaben verwalten</span>
                                                                </button>
                                                                <button type="button" onclick="openEmployeeUtilizationModal({{ $employeeId }}, '{{ $employeeName }}')" style="width: 100%; text-align: left; padding: 10px 16px; background: none; border: none; cursor: pointer; font-size: 14px; color: #374151; display: flex; align-items: center; gap: 10px; transition: all 0.15s;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='white'">
                                                                    <span style="font-size: 16px; color: #8b5cf6;">📊</span>
                                                                    <span style="font-weight: 500;">Auslastung anzeigen</span>
                                                                </button>
                                                                <div style="height: 1px; background: #e5e7eb; margin: 4px 0;"></div>
                                                                <button type="button" onclick="if(confirm('Möchten Sie {{ $employeeName }} wirklich aus diesem Projekt entfernen?')) { removeEmployeeFromProject({{ $project->id }}, {{ $employeeId }}, '{{ $employeeName }}'); }" style="width: 100%; text-align: left; padding: 10px 16px; background: none; border: none; cursor: pointer; font-size: 14px; color: #374151; display: flex; align-items: center; gap: 10px; transition: all 0.15s;" onmouseover="this.style.background='#fef2f2'; this.querySelector('span:last-child').style.color='#dc2626'" onmouseout="this.style.background='white'; this.querySelector('span:last-child').style.color='#374151'">
                                                                    <span style="font-size: 16px; color: #ef4444;">🗑️</span>
                                                                    <span style="font-weight: 500; transition: color 0.15s;">Aus Projekt entfernen</span>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <span class="employee-name" 
                                                          data-employee-id="{{ $employeeId }}"
                                                          data-employee-name="{{ $employeeName }}"
                                                          data-task-count="{{ count($employeeAssignments) }}"
                                                          data-total-hours="{{ collect($employeeAssignments)->sum('weekly_hours') }}"
                                                          style="cursor: pointer;"
                                                          title="{{ $employeeName }}">{{ $employeeName }}</span>
                                                </div>
                                                {{-- Timeline with all task bars for this employee --}}
                                                <div style="position: relative; height: 28px; border-radius: 8px; background: #f9fafb; border: 1px solid #e5e7eb;">
                                                    @foreach($employeeAssignments as $taskIndex => $assignment)
                                                        @php
                                                            $assignmentStart = $assignment['start'];
                                                            $assignmentEnd = $assignment['end'];
                                                            $clampedStart = $assignmentStart->lt($timelineStart) ? $timelineStart->copy() : $assignmentStart;
                                                            $clampedEnd = $assignmentEnd->gt($timelineEnd) ? $timelineEnd->copy() : $assignmentEnd;
                                                            $offsetDays = max(0, $timelineStart->diffInDays($clampedStart));
                                                            $durationDays = max(1, $clampedStart->diffInDays($clampedEnd) + 1);
                                                            $leftPercent = ($offsetDays / $timelineSpanDays) * 100;
                                                            $widthPercent = ($durationDays / $timelineSpanDays) * 100;
                                                            $hours = $assignment['weekly_hours'];
                                                            $assignmentId = collect($assignment['assignment_ids'] ?? [])->first();
                                                            
                                                            // Try to get task_name from assignment, fallback to primary_activity or generic label
                                                            $taskName = $assignment['task_name'] ?? ($assignment['primary_activity'] ?? 'Aufgabe ' . ($taskIndex + 1));
                                                            
                                                            $barColor = '#0ea5e9'; // Blue for tasks
                                                        @endphp
                                                        <div class="project-task-bar" 
                                                             data-project-id="{{ $project->id }}" 
                                                             data-assignment-id="{{ $assignmentId ?? '' }}" 
                                                             data-employee-id="{{ $employeeId }}"
                                                             data-task-name="{{ $taskName }}"
                                                             data-task-description="{{ $assignment['task_description'] ?? '' }}"
                                                             data-start-date="{{ $assignmentStart->format('d.m.Y') }}"
                                                             data-end-date="{{ $assignmentEnd->format('d.m.Y') }}"
                                                             data-weekly-hours="{{ $hours ?? 20 }}"
                                                             data-duration-days="{{ $assignmentStart->diffInDays($assignmentEnd) + 1 }}"
                                                             style="position: absolute; top: 2px; left: {{ $leftPercent }}%; width: {{ $widthPercent }}%; height: calc(100% - 4px); border-radius: 8px; background: {{ $barColor }}; display: flex; align-items: center; justify-content: center; color: white; font-size: 11px; font-weight: 600; box-shadow: 0 2px 4px rgba(0,0,0,0.15); cursor: pointer; transition: all 0.2s;">
                                                            <span style="padding: 0 8px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ Str::limit($taskName, 32) }}</span>
                                                            @if($hours)
                                                                <span style="margin-left: 6px; font-weight: 600; background: rgba(255,255,255,0.2); padding: 2px 6px; border-radius: 8px; font-size: 10px;">{{ $hours }}h</span>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @empty
                                            <div style="display: grid; grid-template-columns: 260px 1fr; gap: 12px; align-items: center; color: #9ca3af; font-size: 12px; padding: 8px 0;">
                                                <div style="padding-left: 32px;">Keine Mitarbeiter zugewiesen.</div>
                                                <div style="height: 1px; background: #e5e7eb;"></div>
                                            </div>
                                        @endforelse
                                        </div>{{-- End Collapsible Employee Container --}}
                                    </div>
                                </div>
                            @endforeach
                    </div>
                </div>
            </div>

            <div style="margin-top: 20px; padding: 16px; background: #f9fafb; border-radius: 6px;">
                <h4 style="font-size: 14px; font-weight: 600; color: #374151; margin: 0 0 12px 0;">Legende</h4>
                <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 12px; height: 12px; background: #10b981; border-radius: 2px;"></div>
                        <span style="font-size: 12px; color: #374151;">In Bearbeitung</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 12px; height: 12px; background: #3b82f6; border-radius: 2px;"></div>
                        <span style="font-size: 12px; color: #374151;">Geplante Projekte</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 12px; height: 12px; background: #dc2626; border-radius: 2px;"></div>
                        <span style="font-size: 12px; color: #374151;">⚠️ Überfällig / Engpass</span>
                    </div>
                    <div style="display: flex; alignItems: center; gap: 8px;">
                        <div style="width: 12px; height: 12px; background: #f97316; border-radius: 2px;"></div>
                        <span style="font-size: 12px; color: #374151;">⏰ Dringend (≤7 Tage)</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 12px; height: 12px; background: #6b7280; border-radius: 2px;"></div>
                        <span style="font-size: 12px; color: #374151;">Abgeschlossene Projekte</span>
                    </div>
                    <div style="display: flex; alignItems: center; gap: 8px;">
                        <span style="font-size: 14px;">🎯</span>
                        <span style="font-size: 12px; color: #374151; font-weight: 600;">Deadline-Marker</span>
                    </div>
                </div>
            </div>
        @else
            <div style="text-align: center; padding: 60px 20px; color: #6b7280;">
                <h3 style="font-size: 18px; font-weight: 500; color: #111827; margin: 0 0 8px 0;">Keine Projekte vorhanden</h3>
                <p style="margin: 0 0 24px 0;">Erstelle Projekte oder synchronisiere mit MOCO, um die Timeline zu füllen.</p>
            </div>
        @endif
    </div>
</div>

{{-- Modal: Add Employee to Project --}}
<div id="addEmployeeModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; padding: 24px; width: 90%; max-width: 500px; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);">
        <h3 style="font-size: 20px; font-weight: 600; color: #111827; margin: 0 0 20px 0;">Mitarbeiter zum Projekt hinzufügen</h3>
        
        <form id="addEmployeeForm" method="POST" action="">
            @csrf
            <input type="hidden" name="project_id" id="modalProjectId" value="">
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 12px;">Mitarbeiter auswählen (Mehrfachauswahl möglich)</label>
                <div style="max-height: 300px; overflow-y: auto; border: 1px solid #d1d5db; border-radius: 8px; padding: 8px; background: white;">
                    @foreach($availableEmployees as $employee)
                        <label style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; cursor: pointer; border-radius: 6px; transition: all 0.15s;" 
                               onmouseover="this.style.background='#f3f4f6'" 
                               onmouseout="this.style.background='white'">
                            <input type="checkbox" name="employee_ids[]" value="{{ $employee->id }}" 
                                   style="width: 18px; height: 18px; cursor: pointer; accent-color: #3b82f6;">
                            <span style="font-size: 14px; color: #111827; flex: 1;">{{ $employee->first_name }} {{ $employee->last_name }}</span>
                            @if($employee->department)
                                <span style="font-size: 12px; color: #6b7280; background: #f3f4f6; padding: 2px 8px; border-radius: 4px;">{{ $employee->department }}</span>
                            @endif
                        </label>
                    @endforeach
                </div>
                <p style="font-size: 12px; color: #6b7280; margin-top: 8px;">✓ Wähle einen oder mehrere Mitarbeiter durch Anklicken der Checkboxen aus.</p>
            </div>
            
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" onclick="closeAddEmployeeModal()" style="padding: 10px 20px; background: white; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; font-weight: 500; color: #374151; cursor: pointer; transition: all 0.2s ease;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='white'">
                    Abbrechen
                </button>
                <button type="submit" style="padding: 10px 20px; background: #3b82f6; border: none; border-radius: 8px; font-size: 14px; font-weight: 500; color: white; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2);" onmouseover="this.style.background='#2563eb'" onmouseout="this.style.background='#3b82f6'">
                    Hinzufügen
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Add Task to Employee --}}
<div id="addTaskModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; padding: 24px; width: 90%; max-width: 550px; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);">
        <h3 style="font-size: 20px; font-weight: 600; color: #111827; margin: 0 0 20px 0;">Aufgabe hinzufügen</h3>
        
        <form id="addTaskForm" method="POST" action="">
            @csrf
            <input type="hidden" name="project_id" id="taskModalProjectId" value="">
            <input type="hidden" name="employee_id" id="taskModalEmployeeId" value="">
            <input type="hidden" name="end_date" id="taskEndDate" value="">
            
            {{-- Task Name --}}
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px;">Aufgabenname *</label>
                <input type="text" name="task_name" required style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; color: #111827;" placeholder="z.B. Frontend-Entwicklung">
            </div>
            
            {{-- Duration Mode Selection --}}
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 12px;">Zeitraum festlegen</label>
                <div style="display: flex; gap: 20px; margin-bottom: 16px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="radio" name="duration_mode" value="fixed" checked onchange="updateDurationMode()" style="cursor: pointer;">
                        <span style="font-size: 14px; color: #374151;">Fester Zeitraum</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="radio" name="duration_mode" value="duration" onchange="updateDurationMode()" style="cursor: pointer;">
                        <span style="font-size: 14px; color: #374151;">Dauer ab Start</span>
                    </label>
                </div>
                
                {{-- Fixed Duration Mode --}}
                <div id="fixedDurationMode">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label style="display: block; font-size: 13px; color: #6b7280; margin-bottom: 6px;">Von</label>
                            <input type="date" name="start_date" id="taskStartDate" required style="width: 100%; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 13px; color: #6b7280; margin-bottom: 6px;">Bis</label>
                            <input type="date" name="end_date_fixed" style="width: 100%; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                        </div>
                    </div>
                </div>
                
                {{-- Duration Mode --}}
                <div id="flexibleDurationMode" style="display: none;">
                    <div style="margin-bottom: 12px;">
                        <label style="display: block; font-size: 13px; color: #6b7280; margin-bottom: 6px;">Startet am</label>
                        <input type="date" id="taskStartDateFlexible" style="width: 100%; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;" onchange="calculateEndDate()">
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                        <div>
                            <label style="display: block; font-size: 13px; color: #6b7280; margin-bottom: 6px;">Dauer</label>
                            <input type="number" id="taskDuration" min="1" value="5" style="width: 100%; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;" oninput="calculateEndDate()">
                        </div>
                        <div>
                            <label style="display: block; font-size: 13px; color: #6b7280; margin-bottom: 6px;">Einheit</label>
                            <select id="taskDurationUnit" style="width: 100%; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; background: white;" onchange="calculateEndDate()">
                                <option value="days">Tage</option>
                                <option value="weeks">Wochen</option>
                            </select>
                        </div>
                    </div>
                    <div style="padding: 12px; background: #f3f4f6; border-radius: 8px; font-size: 13px; color: #374151;">
                        <strong>Endet am:</strong> <span id="calculatedEndDate">-</span>
                    </div>
                </div>
            </div>
            
            {{-- Weekly Hours --}}
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px;">Wochenstunden</label>
                <input type="number" name="weekly_hours" min="1" max="40" value="20" style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; color: #111827;">
            </div>
            
            {{-- Task Description --}}
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px;">Beschreibung (optional)</label>
                <textarea name="task_description" rows="3" style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; color: #111827; resize: vertical;" placeholder="Details zur Aufgabe..."></textarea>
            </div>
            
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" onclick="closeAddTaskModal()" style="padding: 10px 20px; background: white; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; font-weight: 500; color: #374151; cursor: pointer; transition: all 0.2s ease;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='white'">
                    Abbrechen
                </button>
                <button type="submit" style="padding: 10px 20px; background: #10b981; border: none; border-radius: 8px; font-size: 14px; font-weight: 500; color: white; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);" onmouseover="this.style.background='#059669'" onmouseout="this.style.background='#10b981'">
                    Aufgabe erstellen
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Employee Utilization Modal --}}
<div id="employeeUtilizationModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; padding: 20px;">
    <div style="max-width: 900px; margin: 40px auto; background: white; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); max-height: 85vh; overflow-y: auto;">
        {{-- Header --}}
        <div style="padding: 24px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; background: white; z-index: 1; border-radius: 16px 16px 0 0;">
            <div>
                <h3 style="margin: 0; font-size: 20px; font-weight: 600; color: #111827;">Mitarbeiter-Auslastung</h3>
                <p id="utilizationEmployeeName" style="margin: 4px 0 0 0; font-size: 14px; color: #6b7280;"></p>
            </div>
            <button type="button" onclick="closeEmployeeUtilizationModal()" style="background: none; border: none; font-size: 24px; color: #6b7280; cursor: pointer; padding: 0; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 8px; transition: all 0.2s;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='none'">
                ×
            </button>
        </div>
        
        {{-- Content --}}
        <div id="utilizationContent">
            {{-- Content will be loaded dynamically --}}
        </div>
    </div>
</div>

{{-- Manage Tasks Modal --}}
<div id="manageTasksModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; padding: 20px;">
    <div style="max-width: 800px; margin: 40px auto; background: white; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); max-height: 80vh; overflow-y: auto;">
        {{-- Header --}}
        <div style="padding: 24px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h3 style="margin: 0; font-size: 20px; font-weight: 600; color: #111827;">Aufgaben verwalten</h3>
                <p id="manageTasksEmployeeName" style="margin: 4px 0 0 0; font-size: 14px; color: #6b7280;"></p>
            </div>
            <button type="button" onclick="closeManageTasksModal()" style="background: none; border: none; font-size: 24px; color: #6b7280; cursor: pointer; padding: 0; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 8px; transition: all 0.2s;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='none'">
                ×
            </button>
        </div>
        
        {{-- Tasks List --}}
        <div id="tasksListContainer" style="padding: 24px;">
            {{-- Tasks will be loaded here dynamically --}}
        </div>
    </div>
</div>

<script>
// Close menus when clicking outside
// Note: toggleProjectMenu and toggleEmployeeMenu are defined in gantt/index.blade.php BEFORE this include
document.addEventListener('click', function(e) {
    // Check if click is on a menu button or inside a menu
    const isMenuButton = e.target.closest('.project-menu-btn') || 
                         e.target.closest('.employee-menu-btn');
    const isInsideMenu = e.target.closest('[id^="projectMenu"]') || 
                         e.target.closest('[id^="employeeMenu"]');
    
    if (!isMenuButton && !isInsideMenu) {
        document.querySelectorAll('[id^="projectMenu"], [id^="employeeMenu"]').forEach(menu => {
            menu.style.display = 'none';
        });
    }
});

// Add Employee Modal
// Note: openAddEmployeeModal and closeAddEmployeeModal are defined in gantt/index.blade.php BEFORE this include

// Add Task Modal
// Note: openAddTaskModal and closeAddTaskModal are defined in gantt/index.blade.php BEFORE this include

// Duration Mode Toggle
function updateDurationMode() {
    const mode = document.querySelector('input[name="duration_mode"]:checked').value;
    const fixedMode = document.getElementById('fixedDurationMode');
    const flexibleMode = document.getElementById('flexibleDurationMode');
    
    if (mode === 'fixed') {
        fixedMode.style.display = 'block';
        flexibleMode.style.display = 'none';
    } else {
        fixedMode.style.display = 'none';
        flexibleMode.style.display = 'block';
        calculateEndDate();
    }
}

// Calculate End Date based on duration
function calculateEndDate() {
    const startDate = document.getElementById('taskStartDate').value;
    const duration = parseInt(document.getElementById('taskDuration').value) || 0;
    const unit = document.getElementById('taskDurationUnit').value;
    
    if (!startDate || duration === 0) {
        document.getElementById('calculatedEndDate').textContent = '-';
        return;
    }
    
    const start = new Date(startDate);
    let end = new Date(start);
    
    if (unit === 'days') {
        end.setDate(end.getDate() + duration);
    } else if (unit === 'weeks') {
        end.setDate(end.getDate() + (duration * 7));
    }
    
    document.getElementById('calculatedEndDate').textContent = end.toLocaleDateString('de-DE');
    document.getElementById('taskEndDate').value = end.toISOString().split('T')[0];
}

// Close modals when clicking outside
document.getElementById('addEmployeeModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        if (typeof window.closeAddEmployeeModal === 'function') {
            window.closeAddEmployeeModal();
        }
    }
});

document.getElementById('addTaskModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        if (typeof window.closeAddTaskModal === 'function') {
            window.closeAddTaskModal();
        }
    }
});

document.getElementById('manageTasksModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        if (typeof window.closeManageTasksModal === 'function') {
            window.closeManageTasksModal();
        }
    }
});

document.getElementById('employeeUtilizationModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        if (typeof window.closeEmployeeUtilizationModal === 'function') {
            window.closeEmployeeUtilizationModal();
        }
    }
});

// Keyboard support - ESC closes modals
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' || e.keyCode === 27) {
        // Close all modals
        if (typeof window.closeAddEmployeeModal === 'function') window.closeAddEmployeeModal();
        if (typeof window.closeAddTaskModal === 'function') window.closeAddTaskModal();
        if (typeof window.closeManageTasksModal === 'function') window.closeManageTasksModal();
        if (typeof window.closeEmployeeUtilizationModal === 'function') window.closeEmployeeUtilizationModal();
        
        // Close all dropdown menus
        const allMenus = document.querySelectorAll('[id^="projectMenu"], [id^="employeeMenu"]');
        allMenus.forEach(menu => menu.style.display = 'none');
        
        // Close project bar tooltip
        if (typeof hideProjectBarTooltip === 'function') {
        hideProjectBarTooltip();
        }
    }
});

// Project Bar Tooltip System (für Deadline-Anzeige)
let projectBarTooltip = null;

function createProjectBarTooltip() {
    if (!projectBarTooltip) {
        projectBarTooltip = document.createElement('div');
        projectBarTooltip.id = 'project-bar-tooltip';
        projectBarTooltip.style.cssText = `
            display: none;
            position: fixed;
            background: white;
            border: 2px solid #3b82f6;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 16px;
            z-index: 10000;
            max-width: 340px;
            pointer-events: none;
        `;
        document.body.appendChild(projectBarTooltip);
    }
    return projectBarTooltip;
}

function showProjectBarTooltip(event) {
    const bar = event.currentTarget;
    const tooltip = createProjectBarTooltip();
    
    const projectName = bar.dataset.projectName || 'Unbekanntes Projekt';
    const startDate = bar.dataset.startDate || '-';
    const deadline = bar.dataset.deadline || bar.dataset.endDate || '-';
    const daysUntilDeadline = parseInt(bar.dataset.daysUntilDeadline) || 0;
    const isOverdue = bar.dataset.isOverdue === 'true';
    const progress = bar.dataset.progress || '0';
    const status = bar.dataset.status || 'Unbekannt';
    
    // VERSION 2.0: Neue Daten
    const responsible = bar.dataset.responsible || 'Nicht zugewiesen';
    const teamSize = parseInt(bar.dataset.teamSize) || 0;
    const totalHours = parseInt(bar.dataset.totalHours) || 0;
    const weeklyHours = parseInt(bar.dataset.weeklyHours) || 0;
    const durationWeeks = parseInt(bar.dataset.durationWeeks) || 0;
    
    // Deadline-Status-Icon und -Farbe
    let deadlineIcon = '🎯';
    let deadlineText = 'Deadline';
    let deadlineColor = '#3b82f6';
    let statusBadge = '';
    
    if (isOverdue) {
        deadlineIcon = '⚠️';
        deadlineText = 'ÜBERFÄLLIG';
        deadlineColor = '#dc2626';
        statusBadge = `<div style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 6px 10px; border-radius: 8px; font-size: 12px; font-weight: 600; text-align: center; margin-top: 10px;">
            ⚠️ ${Math.abs(daysUntilDeadline)} Tage überfällig
        </div>`;
    } else if (daysUntilDeadline >= 0 && daysUntilDeadline <= 7) {
        deadlineIcon = '⏰';
        deadlineText = 'DRINGEND';
        deadlineColor = '#f97316';
        statusBadge = `<div style="background: #fff7ed; border: 1px solid #fed7aa; color: #c2410c; padding: 6px 10px; border-radius: 8px; font-size: 12px; font-weight: 600; text-align: center; margin-top: 10px;">
            ⏰ Noch ${daysUntilDeadline} ${daysUntilDeadline === 1 ? 'Tag' : 'Tage'}
        </div>`;
    }
    
    tooltip.innerHTML = `
        <div style="margin-bottom: 10px;">
            <h4 style="margin: 0 0 2px 0; font-size: 15px; font-weight: 700; color: #111827;">${projectName}</h4>
            <p style="margin: 0; font-size: 11px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px;">${status}</p>
        </div>
        
        <!-- VERSION 2.0: Verantwortlicher + Team-Größe -->
        <div style="display: flex; gap: 8px; margin-bottom: 10px; padding: 8px; background: #f9fafb; border-radius: 8px; border: 1px solid #e5e7eb;">
            <div style="flex: 1;">
                <div style="font-size: 10px; color: #6b7280; margin-bottom: 2px; text-transform: uppercase; letter-spacing: 0.5px;">🎯 Verantwortlich</div>
                <div style="font-size: 12px; color: #111827; font-weight: 600;">${responsible}</div>
            </div>
            <div style="border-right: 1px solid #e5e7eb;"></div>
            <div style="text-align: center; min-width: 60px;">
                <div style="font-size: 10px; color: #6b7280; margin-bottom: 2px; text-transform: uppercase; letter-spacing: 0.5px;">👥 Team</div>
                <div style="font-size: 14px; color: #111827; font-weight: 700;">${teamSize} ${teamSize === 1 ? 'Person' : 'Personen'}</div>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
            <div style="background: #f9fafb; padding: 8px; border-radius: 6px; border: 1px solid #e5e7eb;">
                <div style="font-size: 10px; color: #6b7280; margin-bottom: 3px; text-transform: uppercase; letter-spacing: 0.5px;">📅 Start</div>
                <div style="font-size: 13px; color: #111827; font-weight: 600;">${startDate}</div>
            </div>
            <div style="background: ${isOverdue || (daysUntilDeadline >= 0 && daysUntilDeadline <= 7) ? deadlineColor + '10' : '#f9fafb'}; padding: 8px; border-radius: 6px; border: 1.5px solid ${deadlineColor};">
                <div style="font-size: 10px; color: #6b7280; margin-bottom: 3px; text-transform: uppercase; letter-spacing: 0.5px;">${deadlineIcon} ${deadlineText}</div>
                <div style="font-size: 13px; color: #111827; font-weight: 700;">${deadline}</div>
            </div>
        </div>
        
        <!-- VERSION 2.0: Gesamtstunden -->
        <div style="background: #f0f9ff; border: 1px solid #bae6fd; padding: 8px; border-radius: 8px; margin-bottom: 10px;">
            <div style="font-size: 10px; color: #0369a1; margin-bottom: 2px; text-transform: uppercase; letter-spacing: 0.5px;">⏱️ Geplant</div>
            <div style="font-size: 14px; color: #0c4a6e; font-weight: 700;">${totalHours}h <span style="font-size: 11px; font-weight: 500; color: #0369a1;">(${weeklyHours}h/W × ${durationWeeks}W)</span></div>
        </div>
        
        <div style="margin-bottom: 4px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                <span style="font-size: 11px; color: #6b7280; font-weight: 500;">Fortschritt</span>
                <span style="font-size: 13px; color: #111827; font-weight: 700;">${progress}%</span>
            </div>
            <div style="width: 100%; height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden;">
                <div style="height: 100%; background: ${parseInt(progress) >= 80 ? '#10b981' : parseInt(progress) >= 50 ? '#3b82f6' : '#f59e0b'}; width: ${progress}%; transition: width 0.3s;"></div>
            </div>
        </div>
        ${statusBadge}
    `;
    
    tooltip.style.display = 'block';
    positionTooltip(event, tooltip);
}

function hideProjectBarTooltip() {
    if (projectBarTooltip) {
        projectBarTooltip.style.display = 'none';
    }
}

// Attach Project Bar Tooltip Listeners
document.addEventListener('DOMContentLoaded', function() {
    attachProjectBarTooltipListeners();
    attachTaskTooltipListeners();
});

function attachProjectBarTooltipListeners() {
    const projectBars = document.querySelectorAll('.gantt-bar');
    projectBars.forEach(bar => {
        bar.addEventListener('mouseenter', showProjectBarTooltip);
        bar.addEventListener('mouseleave', hideProjectBarTooltip);
        bar.addEventListener('mousemove', function(e) {
            if (projectBarTooltip && projectBarTooltip.style.display === 'block') {
                positionTooltip(e, projectBarTooltip);
            }
        });
        
        // Hover effect
        bar.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 4px 12px rgba(0,0,0,0.25)';
        });
        bar.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 2px 6px rgba(0,0,0,0.15)';
        });
    });
}

// Task Tooltip System
let taskTooltip = null;

function createTaskTooltip() {
    if (!taskTooltip) {
        taskTooltip = document.createElement('div');
        taskTooltip.id = 'task-tooltip';
        taskTooltip.style.cssText = `
            display: none;
            position: fixed;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 16px;
            z-index: 10000;
            max-width: 320px;
            pointer-events: none;
        `;
        document.body.appendChild(taskTooltip);
    }
    return taskTooltip;
}

function showTaskTooltip(event) {
    const bar = event.currentTarget;
    const tooltip = createTaskTooltip();
    
    const taskName = bar.dataset.taskName || 'Unbekannte Aufgabe';
    const description = bar.dataset.taskDescription || 'Keine Beschreibung';
    const startDate = bar.dataset.startDate || '-';
    const endDate = bar.dataset.endDate || '-';
    const weeklyHours = bar.dataset.weeklyHours || '20';
    const durationDays = bar.dataset.durationDays || '0';
    
    tooltip.innerHTML = `
        <div style="margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #e5e7eb;">
            <h4 style="margin: 0 0 4px 0; font-size: 16px; font-weight: 600; color: #111827;">${taskName}</h4>
            <p style="margin: 0; font-size: 13px; color: #6b7280; line-height: 1.5;">${description}</p>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
            <div>
                <div style="font-size: 11px; color: #6b7280; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Startdatum</div>
                <div style="font-size: 14px; color: #111827; font-weight: 500;">${startDate}</div>
            </div>
            <div>
                <div style="font-size: 11px; color: #6b7280; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Enddatum</div>
                <div style="font-size: 14px; color: #111827; font-weight: 500;">${endDate}</div>
            </div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <div>
                <div style="font-size: 11px; color: #6b7280; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Dauer</div>
                <div style="font-size: 14px; color: #111827; font-weight: 500;">${durationDays} Tage</div>
            </div>
            <div>
                <div style="font-size: 11px; color: #6b7280; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Wochenstunden</div>
                <div style="font-size: 14px; color: #111827; font-weight: 500;">${weeklyHours}h / Woche</div>
            </div>
        </div>
        <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #e5e7eb; font-size: 11px; color: #6b7280; text-align: center;">
            ⚡ Klicken Sie auf ⋮ beim Mitarbeiter für mehr Optionen
        </div>
    `;
    
    tooltip.style.display = 'block';
    positionTooltip(event, tooltip);
}

function hideTaskTooltip() {
    if (taskTooltip) {
        taskTooltip.style.display = 'none';
    }
}

function positionTooltip(event, tooltip) {
    const tooltipRect = tooltip.getBoundingClientRect();
    const margin = 12;
    
    let left = event.clientX + margin;
    let top = event.clientY + margin;
    
    // Keep tooltip in viewport
    if (left + tooltipRect.width > window.innerWidth) {
        left = event.clientX - tooltipRect.width - margin;
    }
    if (top + tooltipRect.height > window.innerHeight) {
        top = event.clientY - tooltipRect.height - margin;
    }
    
    tooltip.style.left = left + 'px';
    tooltip.style.top = top + 'px';
}

// Attach event listeners to all task bars
document.addEventListener('DOMContentLoaded', function() {
    attachTaskTooltipListeners();
});

function attachTaskTooltipListeners() {
    const taskBars = document.querySelectorAll('.project-task-bar');
    taskBars.forEach(bar => {
        bar.addEventListener('mouseenter', showTaskTooltip);
        bar.addEventListener('mouseleave', hideTaskTooltip);
        bar.addEventListener('mousemove', function(e) {
            if (taskTooltip && taskTooltip.style.display === 'block') {
                positionTooltip(e, taskTooltip);
            }
        });
        
        // Hover effect
        bar.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 4px 12px rgba(0,0,0,0.25)';
        });
        bar.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 2px 4px rgba(0,0,0,0.15)';
        });
    });
    
    // Attach employee name tooltips
    attachEmployeeTooltipListeners();
}

// Employee Tooltip System
let employeeTooltip = null;

function createEmployeeTooltip() {
    if (!employeeTooltip) {
        employeeTooltip = document.createElement('div');
        employeeTooltip.id = 'employee-tooltip';
        employeeTooltip.style.cssText = `
            display: none;
            position: fixed;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 16px;
            z-index: 10000;
            max-width: 300px;
            pointer-events: none;
        `;
        document.body.appendChild(employeeTooltip);
    }
    return employeeTooltip;
}

function showEmployeeTooltip(event) {
    const nameElement = event.currentTarget;
    const tooltip = createEmployeeTooltip();
    
    const employeeName = nameElement.dataset.employeeName || 'Unbekannt';
    const taskCount = nameElement.dataset.taskCount || '0';
    const totalHours = nameElement.dataset.totalHours || '0';
    
    tooltip.innerHTML = `
        <div style="margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #e5e7eb;">
            <h4 style="margin: 0 0 4px 0; font-size: 16px; font-weight: 600; color: #111827;">👤 ${employeeName}</h4>
            <p style="margin: 0; font-size: 12px; color: #6b7280;">Mitarbeiter in diesem Projekt</p>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <div style="background: #f3f4f6; padding: 12px; border-radius: 8px;">
                <div style="font-size: 11px; color: #6b7280; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Aufgaben</div>
                <div style="font-size: 20px; color: #111827; font-weight: 700;">${taskCount}</div>
            </div>
            <div style="background: #f3f4f6; padding: 12px; border-radius: 8px;">
                <div style="font-size: 11px; color: #6b7280; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Stunden/Woche</div>
                <div style="font-size: 20px; color: #111827; font-weight: 700;">${totalHours}h</div>
            </div>
        </div>
        <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #e5e7eb; font-size: 11px; color: #6b7280; text-align: center;">
            ⚡ Klicken Sie auf ⋮ für Optionen
        </div>
    `;
    
    tooltip.style.display = 'block';
    positionEmployeeTooltip(event, tooltip);
}

function hideEmployeeTooltip() {
    if (employeeTooltip) {
        employeeTooltip.style.display = 'none';
    }
}

function positionEmployeeTooltip(event, tooltip) {
    const tooltipRect = tooltip.getBoundingClientRect();
    const margin = 12;
    
    let left = event.clientX + margin;
    let top = event.clientY + margin;
    
    // Keep tooltip in viewport
    if (left + tooltipRect.width > window.innerWidth) {
        left = event.clientX - tooltipRect.width - margin;
    }
    if (top + tooltipRect.height > window.innerHeight) {
        top = event.clientY - tooltipRect.height - margin;
    }
    
    tooltip.style.left = left + 'px';
    tooltip.style.top = top + 'px';
}

function attachEmployeeTooltipListeners() {
    const employeeNames = document.querySelectorAll('.employee-name');
    employeeNames.forEach(nameElement => {
        nameElement.addEventListener('mouseenter', showEmployeeTooltip);
        nameElement.addEventListener('mouseleave', hideEmployeeTooltip);
        nameElement.addEventListener('mousemove', function(e) {
            if (employeeTooltip && employeeTooltip.style.display === 'block') {
                positionEmployeeTooltip(e, employeeTooltip);
            }
        });
    });
}

// Remove Employees Modal Functions
window.openRemoveEmployeesModal = function(projectId) {
    // Close project menu
    const projectMenu = document.getElementById('projectMenu' + projectId);
    if (projectMenu) projectMenu.style.display = 'none';
    
    // Show modal with employee list (to be implemented)
    alert('Mitarbeiter entfernen - Funktion wird implementiert.\nHier können Sie Mitarbeiter aus dem Projekt entfernen.');
}

// Manage Tasks Modal Functions
// Note: openManageTasksModal and closeManageTasksModal are defined in gantt/index.blade.php BEFORE this include
// renderTasksList must be in window scope to be accessible
window.renderTasksList = function(tasks, projectId, employeeId) {
    const container = document.getElementById('tasksListContainer');
    
    if (tasks.length === 0) {
        container.innerHTML = `
            <div style="text-align: center; padding: 40px; color: #6b7280;">
                <div style="font-size: 48px; margin-bottom: 16px;">📋</div>
                <p style="font-size: 16px; margin: 0;">Keine Aufgaben vorhanden</p>
            </div>
        `;
        return;
    }
    
    let html = '<div style="display: flex; flex-direction: column; gap: 12px;">';
    
    tasks.forEach((task, index) => {
        const startDate = new Date(task.start_date).toLocaleDateString('de-DE');
        const endDate = new Date(task.end_date).toLocaleDateString('de-DE');
        const duration = Math.ceil((new Date(task.end_date) - new Date(task.start_date)) / (1000 * 60 * 60 * 24));
        
        html += `
            <div id="task-${task.id}" style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px; transition: all 0.2s;">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
                    <div style="flex: 1;">
                        <h4 style="margin: 0 0 4px 0; font-size: 16px; font-weight: 600; color: #111827;">${task.task_name}</h4>
                        <p style="margin: 0; font-size: 13px; color: #6b7280;">${task.task_description || 'Keine Beschreibung'}</p>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <button onclick="openTransferModal(${task.id}, ${projectId}, ${employeeId}, '${task.task_name}')" style="padding: 6px 12px; background: #8b5cf6; border: none; border-radius: 6px; color: white; font-size: 13px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#7c3aed'" onmouseout="this.style.background='#8b5cf6'">
                            🔄 Übertragen
                        </button>
                        <button onclick="editTask(${task.id}, ${projectId}, ${employeeId})" style="padding: 6px 12px; background: #3b82f6; border: none; border-radius: 6px; color: white; font-size: 13px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#2563eb'" onmouseout="this.style.background='#3b82f6'">
                            ✏️ Bearbeiten
                        </button>
                        <button onclick="deleteTask(${task.id}, ${projectId}, ${employeeId}, '${task.task_name}')" style="padding: 6px 12px; background: #ef4444; border: none; border-radius: 6px; color: white; font-size: 13px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='#ef4444'">
                            🗑️ Löschen
                        </button>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; padding: 12px; background: white; border-radius: 8px;">
                    <div>
                        <div style="font-size: 11px; color: #6b7280; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Zeitraum</div>
                        <div style="font-size: 14px; color: #111827; font-weight: 500;">${startDate} - ${endDate}</div>
                        <div style="font-size: 12px; color: #6b7280; margin-top: 2px;">${duration} Tage</div>
                    </div>
                    <div>
                        <div style="font-size: 11px; color: #6b7280; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Wochenstunden</div>
                        <div style="font-size: 14px; color: #111827; font-weight: 500;">${task.weekly_hours || 20}h / Woche</div>
                    </div>
                    <div>
                        <div style="font-size: 11px; color: #6b7280; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Reihenfolge</div>
                        <div style="font-size: 14px; color: #111827; font-weight: 500;">#${task.display_order || index + 1}</div>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

window.editTask = function(taskId, projectId, employeeId) {
    const taskElement = document.getElementById('task-' + taskId);
    if (!taskElement) return;
    
    // Get current task data
    const baseUrl = '{{ url('/') }}';
    fetch(`${baseUrl}/gantt/tasks/${taskId}`)
        .then(response => response.json())
        .then(data => {
            const task = data.task;
            
            // Replace task display with inline edit form
            taskElement.innerHTML = `
                <form onsubmit="saveTaskEdit(event, ${taskId}, ${projectId}, ${employeeId})" style="background: white; border: 2px solid #3b82f6; border-radius: 12px; padding: 16px;">
                    <div style="margin-bottom: 12px;">
                        <label style="display: block; font-size: 13px; color: #6b7280; margin-bottom: 4px; font-weight: 500;">Aufgabenname</label>
                        <input type="text" name="task_name" value="${task.task_name}" required style="width: 100%; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                    </div>
                    <div style="margin-bottom: 12px;">
                        <label style="display: block; font-size: 13px; color: #6b7280; margin-bottom: 4px; font-weight: 500;">Beschreibung</label>
                        <textarea name="task_description" rows="2" style="width: 100%; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">${task.task_description || ''}</textarea>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                        <div>
                            <label style="display: block; font-size: 13px; color: #6b7280; margin-bottom: 4px; font-weight: 500;">Startdatum</label>
                            <input type="date" name="start_date" value="${task.start_date}" required style="width: 100%; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 13px; color: #6b7280; margin-bottom: 4px; font-weight: 500;">Enddatum</label>
                            <input type="date" name="end_date" value="${task.end_date}" required style="width: 100%; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                        </div>
                    </div>
                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 13px; color: #6b7280; margin-bottom: 4px; font-weight: 500;">Wochenstunden</label>
                        <input type="number" name="weekly_hours" value="${task.weekly_hours || 20}" min="1" max="40" style="width: 100%; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                    </div>
                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
                        <button type="button" onclick="cancelTaskEdit(${taskId}, ${projectId}, ${employeeId})" style="padding: 8px 16px; background: #f3f4f6; border: none; border-radius: 6px; color: #374151; font-size: 13px; cursor: pointer; font-weight: 500;">
                            Abbrechen
                        </button>
                        <button type="submit" style="padding: 8px 16px; background: #10b981; border: none; border-radius: 6px; color: white; font-size: 13px; cursor: pointer; font-weight: 500;">
                            💾 Speichern
                        </button>
                    </div>
                </form>
            `;
        })
        .catch(error => {
            console.error('Error loading task:', error);
            alert('Fehler beim Laden der Aufgabe.');
        });
}

window.cancelTaskEdit = function(taskId, projectId, employeeId) {
    // Reload the tasks list to restore original view
    if (typeof window.openManageTasksModal === 'function') {
        const nameElement = document.getElementById('manageTasksEmployeeName');
        if (nameElement) {
            window.openManageTasksModal(projectId, employeeId, nameElement.textContent);
        }
    }
}

window.saveTaskEdit = function(event, taskId, projectId, employeeId) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    const data = {
        task_name: formData.get('task_name'),
        task_description: formData.get('task_description'),
        start_date: formData.get('start_date'),
        end_date: formData.get('end_date'),
        weekly_hours: formData.get('weekly_hours'),
    };
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const baseUrl = '{{ url('/') }}';
    fetch(`${baseUrl}/gantt/tasks/${taskId}`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload tasks list to show updated task
            if (typeof window.openManageTasksModal === 'function') {
                const nameElement = document.getElementById('manageTasksEmployeeName');
                if (nameElement) {
                    window.openManageTasksModal(projectId, employeeId, nameElement.textContent);
                }
            }
            
            // Also reload the gantt page to reflect changes
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            alert('Fehler beim Speichern: ' + (data.message || 'Unbekannter Fehler'));
        }
    })
    .catch(error => {
        console.error('Error saving task:', error);
        alert('Fehler beim Speichern der Aufgabe.');
    });
}

window.deleteTask = function(taskId, projectId, employeeId, taskName) {
    if (!confirm(`Möchten Sie die Aufgabe "${taskName}" wirklich löschen?`)) {
        return;
    }
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const baseUrl = '{{ url('/') }}';
    fetch(`${baseUrl}/gantt/tasks/${taskId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove task from UI with animation
            const taskElement = document.getElementById('task-' + taskId);
            if (taskElement) {
                taskElement.style.opacity = '0';
                taskElement.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    taskElement.remove();
                    // Close modal and reload page to update gantt timeline
                    if (typeof window.closeManageTasksModal === 'function') {
                        window.closeManageTasksModal();
                    }
                    window.location.reload();
                }, 300);
            }
        } else {
            alert('Fehler beim Löschen: ' + (data.message || 'Unbekannter Fehler'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Fehler beim Löschen der Aufgabe.');
    });
}

/**
 * Open transfer modal to reassign task to another employee
 * 
 * ENTSCHEIDUNG: Separate Modal (nicht inline) für klare UX
 * GRUND: User braucht Übersicht über alle Mitarbeiter
 */
window.openTransferModal = function(taskId, projectId, employeeId, taskName) {
    document.getElementById('transferTaskId').value = taskId;
    document.getElementById('transferProjectId').value = projectId;
    document.getElementById('transferOldEmployeeId').value = employeeId;
    document.getElementById('transferTaskName').textContent = `"${taskName}"`;
    
    // Reset form
    document.getElementById('transferReason').value = '';
    document.getElementById('reasonCharCount').textContent = '0';
    
    // Update dropdown: disable/mark current employee
    const dropdown = document.getElementById('transferNewEmployeeId');
    const options = dropdown.querySelectorAll('option');
    
    options.forEach(option => {
        if (option.value == employeeId) {
            option.disabled = true;
            option.style.color = '#9ca3af';
            const originalText = option.textContent.replace(' (Aktuell zugewiesen)', '');
            option.textContent = originalText + ' (Aktuell zugewiesen)';
        } else if (option.value !== '') {
            option.disabled = false;
            option.style.color = '';
            option.textContent = option.textContent.replace(' (Aktuell zugewiesen)', '');
        }
    });
    
    // Reset selection
    dropdown.value = '';
    
    // Show modal
    document.getElementById('transferTaskModal').style.display = 'block';
}

window.closeTransferModal = function() {
    const modal = document.getElementById('transferTaskModal');
    if (modal) modal.style.display = 'none';
}

/**
 * Submit task transfer (update employee_id via API)
 * 
 * FALLBACK: Bei Fehler bleibt Modal offen mit Fehlermeldung
 */
window.submitTransfer = function(event) {
    event.preventDefault();
    
    const taskId = document.getElementById('transferTaskId').value;
    const projectId = document.getElementById('transferProjectId').value;
    const oldEmployeeId = document.getElementById('transferOldEmployeeId').value;
    const newEmployeeId = document.getElementById('transferNewEmployeeId').value;
    const reason = document.getElementById('transferReason').value;
    
    // Validation
    if (!newEmployeeId) {
        alert('Bitte wählen Sie einen Mitarbeiter aus.');
        return;
    }
    
    if (newEmployeeId == oldEmployeeId) {
        alert('Die Aufgabe ist bereits diesem Mitarbeiter zugewiesen.');
        return;
    }
    
    // Disable submit button during request
    const submitBtn = document.getElementById('transferSubmitBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-small" style="margin-right: 8px;"></span>Wird übertragen...';
    submitBtn.style.opacity = '0.7';
    
    // Send transfer request
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const baseUrl = '{{ url('/') }}';
    fetch(`${baseUrl}/gantt/tasks/${taskId}/transfer`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            new_employee_id: newEmployeeId,
            reason: reason
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Success: Show message and reload
            alert(data.message);
            if (typeof window.closeTransferModal === 'function') {
                window.closeTransferModal();
            }
            if (typeof window.closeManageTasksModal === 'function') {
                window.closeManageTasksModal();
            }
            window.location.reload(); // Reload to update Gantt timeline
        } else {
            // Error: Show message but keep modal open
            alert('Fehler: ' + (data.message || 'Übertragung fehlgeschlagen'));
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            submitBtn.style.opacity = '1';
        }
    })
    .catch(error => {
        console.error('Transfer error:', error);
        alert('Fehler beim Übertragen der Aufgabe. Bitte versuchen Sie es erneut.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        submitBtn.style.opacity = '1';
    });
}

// Character counter for reason field
document.addEventListener('DOMContentLoaded', function() {
    const reasonField = document.getElementById('transferReason');
    if (reasonField) {
        reasonField.addEventListener('input', function() {
            document.getElementById('reasonCharCount').textContent = this.value.length;
        });
    }
});

// Employee Utilization Modal Functions
// Note: openEmployeeUtilizationModal and closeEmployeeUtilizationModal are defined in gantt/index.blade.php BEFORE this include
// renderUtilizationView is defined here as it's only used internally

window.renderUtilizationView = function(data, employeeName) {
    const container = document.getElementById('utilizationContent');
    const tasks = data.tasks || [];
    const peakHours = data.peak_weekly_hours || 0;
    const averageHours = data.average_weekly_hours || 0;
    const totalHours = data.total_weekly_hours || 0;
    const projectCount = data.project_count || 0;
    const hasOverlaps = data.has_overlaps || false;
    const overlapWeeks = data.overlap_weeks || 0;
    const maxCapacity = 40;
    
    // Use PEAK hours for status calculation (most important metric)
    const peakPercent = Math.round((peakHours / maxCapacity) * 100);
    const avgPercent = Math.round((averageHours / maxCapacity) * 100);
    
    let statusColor = '#10b981'; // Green
    let statusText = 'Gut verfügbar';
    let statusIcon = '✅';
    if (peakPercent >= 100) {
        statusColor = '#ef4444'; // Red
        statusText = 'Überlastet!';
        statusIcon = '🔴';
    } else if (peakPercent >= 80) {
        statusColor = '#f59e0b'; // Orange
        statusText = 'Hoch ausgelastet';
        statusIcon = '⚠️';
    } else if (peakPercent >= 60) {
        statusColor = '#3b82f6'; // Blue
        statusText = 'Normal ausgelastet';
        statusIcon = '✓';
    }
    
    let html = `
        <div style="padding: 24px;">
            <div style="margin-bottom: 24px;">
                <h4 style="margin: 0 0 16px 0; font-size: 18px; font-weight: 600; color: #111827;">Intelligente Auslastungs-Analyse</h4>
                
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 20px;">
                    <div style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); padding: 20px; border-radius: 12px; color: white;">
                        <div style="font-size: 12px; opacity: 0.9; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">🔝 Peak-Auslastung</div>
                        <div style="font-size: 32px; font-weight: 700; margin-bottom: 4px;">${peakHours}h</div>
                        <div style="font-size: 13px; opacity: 0.9;">Höchste Woche (${peakPercent}%)</div>
                    </div>
                    <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 20px; border-radius: 12px; color: white;">
                        <div style="font-size: 12px; opacity: 0.9; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">📊 Durchschnitt</div>
                        <div style="font-size: 32px; font-weight: 700; margin-bottom: 4px;">${averageHours}h</div>
                        <div style="font-size: 13px; opacity: 0.9;">Ø pro aktive Woche (${avgPercent}%)</div>
                    </div>
                    <div style="background: linear-gradient(135deg, ${statusColor} 0%, ${statusColor}dd 100%); padding: 20px; border-radius: 12px; color: white;">
                        <div style="font-size: 12px; opacity: 0.9; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Status</div>
                        <div style="font-size: 32px; font-weight: 700; margin-bottom: 4px;">${statusIcon}</div>
                        <div style="font-size: 13px; opacity: 0.9;">${statusText}</div>
                    </div>
                </div>
                
                ${hasOverlaps ? `
                <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; padding: 16px; margin-bottom: 20px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="font-size: 24px;">⚠️</div>
                        <div style="flex: 1;">
                            <div style="font-size: 14px; font-weight: 600; color: #dc2626; margin-bottom: 4px;">Überlappungen erkannt!</div>
                            <div style="font-size: 13px; color: #991b1b;">${overlapWeeks} Woche(n) mit über 40h/Woche - Bitte Aufgaben neu verteilen!</div>
                        </div>
                    </div>
                </div>
                ` : `
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 16px; margin-bottom: 20px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="font-size: 24px;">✅</div>
                        <div style="flex: 1;">
                            <div style="font-size: 14px; font-weight: 600; color: #16a34a; margin-bottom: 4px;">Keine kritischen Überlappungen</div>
                            <div style="font-size: 13px; color: #15803d;">Alle Aufgaben zeitlich gut verteilt</div>
                        </div>
                    </div>
                </div>
                `}
                
                <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px; margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="font-size: 13px; color: #6b7280; font-weight: 500;">Peak-Kapazität (wichtigste Metrik)</span>
                        <span style="font-size: 14px; color: #111827; font-weight: 600;">${peakHours}h / ${maxCapacity}h</span>
                    </div>
                    <div style="width: 100%; height: 12px; background: #e5e7eb; border-radius: 6px; overflow: hidden;">
                        <div style="height: 100%; background: ${statusColor}; width: ${Math.min(peakPercent, 100)}%; transition: width 0.3s;"></div>
                    </div>
                </div>
                
                <div style="background: #f3f4f6; border-radius: 12px; padding: 16px; margin-bottom: 20px;">
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; text-align: center;">
                        <div>
                            <div style="font-size: 11px; color: #6b7280; margin-bottom: 4px;">Projekte</div>
                            <div style="font-size: 18px; font-weight: 700; color: #111827;">${projectCount}</div>
                        </div>
                        <div>
                            <div style="font-size: 11px; color: #6b7280; margin-bottom: 4px;">Aufgaben</div>
                            <div style="font-size: 18px; font-weight: 700; color: #111827;">${tasks.length}</div>
                        </div>
                        <div>
                            <div style="font-size: 11px; color: #6b7280; margin-bottom: 4px;">Gesamt h/Wo.</div>
                            <div style="font-size: 18px; font-weight: 700; color: #111827;">${totalHours}h</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <h5 style="margin: 0 0 16px 0; font-size: 16px; font-weight: 600; color: #111827;">Aufgaben über alle Projekte</h5>
    `;
    
    if (tasks.length === 0) {
        html += `
            <div style="text-align: center; padding: 40px; color: #6b7280;">
                <div style="font-size: 48px; margin-bottom: 16px;">📋</div>
                <p>Keine Aufgaben gefunden</p>
            </div>
        `;
    } else {
        html += '<div style="display: flex; flex-direction: column; gap: 12px;">';
        
        tasks.forEach(task => {
            const startDate = new Date(task.start_date).toLocaleDateString('de-DE');
            const endDate = new Date(task.end_date).toLocaleDateString('de-DE');
            const duration = Math.ceil((new Date(task.end_date) - new Date(task.start_date)) / (1000 * 60 * 60 * 24));
            
            html += `
                <div style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
                        <div style="flex: 1;">
                            <div style="font-size: 14px; font-weight: 600; color: #111827; margin-bottom: 2px;">${task.task_name}</div>
                            <div style="font-size: 13px; color: #6b7280; margin-bottom: 6px;">${task.project_name}</div>
                            <div style="font-size: 12px; color: #9ca3af;">${task.task_description || 'Keine Beschreibung'}</div>
                        </div>
                        <div style="background: #0ea5e9; color: white; padding: 4px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; white-space: nowrap;">
                            ${task.weekly_hours}h/Woche
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; padding: 12px; background: #f9fafb; border-radius: 8px;">
                        <div>
                            <div style="font-size: 11px; color: #6b7280; margin-bottom: 4px;">Zeitraum</div>
                            <div style="font-size: 13px; color: #111827; font-weight: 500;">${startDate} - ${endDate}</div>
                        </div>
                        <div>
                            <div style="font-size: 11px; color: #6b7280; margin-bottom: 4px;">Dauer</div>
                            <div style="font-size: 13px; color: #111827; font-weight: 500;">${duration} Tage</div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
    }
    
    html += '</div>';
    container.innerHTML = html;
}

// Remove Employee from Project
window.removeEmployeeFromProject = function(projectId, employeeId, employeeName) {
    // Close employee menu
    const employeeMenu = document.getElementById('employeeMenu' + projectId + '_' + employeeId);
    if (employeeMenu) employeeMenu.style.display = 'none';
    
    // Submit deletion via form
    const form = document.createElement('form');
    form.method = 'POST';
    const baseUrl = '{{ url('/') }}';
    form.action = baseUrl + '/gantt/projects/' + projectId + '/employees/' + employeeId + '/remove';
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken;
    
    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'DELETE';
    
    form.appendChild(csrfInput);
    form.appendChild(methodInput);
    document.body.appendChild(form);
    form.submit();
}

// Bulk Assign Modal
window.openBulkAssignModal = function(projectId, projectName) {
    const modal = document.getElementById('bulkAssignModal');
    if (!modal) return;
    
    document.getElementById('bulkAssignProjectId').value = projectId;
    document.getElementById('bulkAssignProjectName').textContent = projectName;
    
    // Uncheck all checkboxes
    document.querySelectorAll('#bulkAssignForm input[type="checkbox"]').forEach(cb => cb.checked = false);
    
    modal.style.display = 'flex';
}

window.closeBulkAssignModal = function() {
    const modal = document.getElementById('bulkAssignModal');
    if (modal) modal.style.display = 'none';
}

window.submitBulkAssign = function(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const employeeIds = [];
    
    formData.getAll('employee_ids[]').forEach(id => {
        if (id) employeeIds.push(id);
    });
    
    if (employeeIds.length === 0) {
        alert('Bitte wählen Sie mindestens einen Mitarbeiter aus.');
        return;
    }
    
    const projectId = formData.get('project_id');
    
    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Wird zugewiesen...';
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const baseUrl = '{{ url('/') }}';
    fetch(baseUrl + '/gantt/bulk-assign-employees', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            project_id: projectId,
            employee_ids: employeeIds
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            alert(data.message);
            // Reload page to show new assignments
            window.location.reload();
        } else {
            alert('Fehler: ' + (data.message || 'Unbekannter Fehler'));
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Fehler bei der Zuweisung: ' + error.message);
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
}

// Close modal on outside click
document.addEventListener('click', function(event) {
    const modal = document.getElementById('bulkAssignModal');
    if (modal && event.target === modal) {
        closeBulkAssignModal();
    }
});
</script>

{{-- Bulk Assign Modal --}}
<div id="bulkAssignModal" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; max-width: 500px; width: 90%; max-height: 80vh; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);">
        {{-- Header --}}
        <div style="padding: 20px; border-bottom: 1px solid #e5e7eb;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0;">
                    Mitarbeiter zuweisen
                </h3>
                <button onclick="closeBulkAssignModal()" style="background: none; border: none; font-size: 24px; color: #6b7280; cursor: pointer; line-height: 1; padding: 0; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; border-radius: 4px; transition: all 0.15s;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='none'">
                    ×
                </button>
            </div>
            <p style="margin: 8px 0 0 0; font-size: 14px; color: #6b7280;">
                Projekt: <span id="bulkAssignProjectName" style="font-weight: 600; color: #111827;"></span>
            </p>
        </div>
        
        {{-- Body --}}
        <form id="bulkAssignForm" onsubmit="submitBulkAssign(event)">
            <input type="hidden" name="project_id" id="bulkAssignProjectId">
            
            <div style="padding: 20px; max-height: 50vh; overflow-y: auto;">
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 12px;">
                        Verfügbare Mitarbeiter:
                    </label>
                    
                    @if($availableEmployees->isEmpty())
                        <p style="color: #9ca3af; font-size: 14px; text-align: center; padding: 20px;">
                            Keine Mitarbeiter verfügbar
                        </p>
                    @else
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            @foreach($availableEmployees as $employee)
                                <label style="display: flex; align-items: center; padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px; cursor: pointer; transition: all 0.15s;" onmouseover="this.style.background='#f9fafb'; this.style.borderColor='#3b82f6'" onmouseout="this.style.background='white'; this.style.borderColor='#e5e7eb'">
                                    <input 
                                        type="checkbox" 
                                        name="employee_ids[]" 
                                        value="{{ $employee->id }}"
                                        style="margin-right: 12px; width: 16px; height: 16px; cursor: pointer;">
                                    <div style="flex: 1;">
                                        <div style="font-weight: 500; color: #111827;">
                                            {{ $employee->first_name }} {{ $employee->last_name }}
                                        </div>
                                        @if($employee->email)
                                            <div style="font-size: 12px; color: #6b7280; margin-top: 2px;">
                                                {{ $employee->email }}
                                            </div>
                                        @endif
                                    </div>
                                    @if($employee->weekly_capacity)
                                        <div style="font-size: 12px; color: #6b7280; background: #f3f4f6; padding: 4px 8px; border-radius: 4px;">
                                            {{ $employee->weekly_capacity }}h/Woche
                                        </div>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            
            {{-- Footer --}}
            <div style="padding: 16px 20px; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; gap: 12px; background: #f9fafb;">
                <button 
                    type="button" 
                    onclick="closeBulkAssignModal()" 
                    style="padding: 10px 20px; background: white; border: 1px solid #d1d5db; border-radius: 8px; color: #374151; font-size: 14px; font-weight: 500; cursor: pointer; transition: all 0.15s;"
                    onmouseover="this.style.background='#f9fafb'"
                    onmouseout="this.style.background='white'">
                    Abbrechen
                </button>
                <button 
                    type="submit" 
                    style="padding: 10px 20px; background: #3b82f6; border: none; border-radius: 8px; color: white; font-size: 14px; font-weight: 500; cursor: pointer; transition: all 0.15s;"
                    onmouseover="this.style.background='#2563eb'"
                    onmouseout="this.style.background='#3b82f6'">
                    Mitarbeiter zuweisen
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Transfer Task Modal --}}
<div id="transferTaskModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 10000; backdrop-filter: blur(4px);">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; border-radius: 16px; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3); width: 90%; max-width: 500px; max-height: 80vh; overflow: auto;">
        <div style="padding: 24px 24px 20px 24px; border-bottom: 1px solid #e5e7eb;">
            <h3 style="margin: 0; font-size: 20px; font-weight: 600; color: #111827;">🔄 Aufgabe übertragen</h3>
            <p id="transferTaskName" style="margin: 8px 0 0 0; font-size: 14px; color: #6b7280;">Aufgabe wird übertragen...</p>
        </div>
        
        <form id="transferTaskForm" onsubmit="submitTransfer(event)" style="padding: 24px;">
            <input type="hidden" id="transferTaskId" name="task_id">
            <input type="hidden" id="transferProjectId" name="project_id">
            <input type="hidden" id="transferOldEmployeeId" name="old_employee_id">
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 13px; color: #374151; margin-bottom: 8px; font-weight: 600;">
                    Neuer Mitarbeiter
                </label>
                <select 
                    id="transferNewEmployeeId" 
                    name="new_employee_id" 
                    required 
                    style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; background: white; cursor: pointer;"
                    onchange="this.style.borderColor='#3b82f6'">
                    <option value="">-- Mitarbeiter wählen --</option>
                    @if(isset($availableEmployees) && $availableEmployees->isNotEmpty())
                        @foreach($availableEmployees->sortBy('last_name') as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->first_name }} {{ $employee->last_name }}</option>
                        @endforeach
                    @else
                        <option value="" disabled>Keine Mitarbeiter verfügbar</option>
                    @endif
                </select>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 13px; color: #374151; margin-bottom: 8px; font-weight: 600;">
                    Begründung (optional)
                </label>
                <textarea 
                    id="transferReason" 
                    name="reason" 
                    rows="3" 
                    placeholder="z.B. Urlaub, Prioritätswechsel, Skillset-Match..."
                    style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; resize: vertical;"
                    maxlength="500"></textarea>
                <div style="font-size: 12px; color: #6b7280; margin-top: 4px;">
                    <span id="reasonCharCount">0</span>/500 Zeichen
                </div>
            </div>
            
            <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 12px; margin-bottom: 20px;">
                <div style="font-size: 12px; color: #1e40af; font-weight: 500; margin-bottom: 4px;">ℹ️ Hinweis:</div>
                <div style="font-size: 12px; color: #1e3a8a;">
                    Zeitraum, Wochenstunden und Beschreibung bleiben unverändert.
                </div>
            </div>
            
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button 
                    type="button" 
                    onclick="closeTransferModal()" 
                    style="padding: 10px 20px; background: white; border: 1px solid #d1d5db; border-radius: 8px; color: #374151; font-size: 14px; font-weight: 500; cursor: pointer; transition: all 0.15s;"
                    onmouseover="this.style.background='#f9fafb'"
                    onmouseout="this.style.background='white'">
                    Abbrechen
                </button>
                <button 
                    type="submit" 
                    id="transferSubmitBtn"
                    style="padding: 10px 20px; background: #8b5cf6; border: none; border-radius: 8px; color: white; font-size: 14px; font-weight: 500; cursor: pointer; transition: all 0.15s;"
                    onmouseover="this.style.background='#7c3aed'"
                    onmouseout="this.style.background='#8b5cf6'">
                    🔄 Übertragen
                </button>
            </div>
        </form>
    </div>
</div>
