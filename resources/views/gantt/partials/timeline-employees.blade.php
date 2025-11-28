{{-- Timeline, Mitarbeiterzeilen und Legende --}}
<div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
    <div style="padding: 20px;">
        @if($timelineByEmployee->count() > 0)
            <div id="employeeGanttTooltip" style="display: none; position: fixed; background: white; border: 2px solid #1f2937; border-radius: 8px; padding: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 1000; max-width: 320px; pointer-events: none;">
                <div id="employeeTooltipContent"></div>
            </div>

            <div style="overflow-x: auto; padding-bottom: 10px;">
                <div id="employeeGanttScroll" data-timeline-start="{{ $timelineStart->toDateString() }}" data-timeline-end="{{ $timelineEnd->toDateString() }}" data-timeline-days="{{ $totalTimelineDays }}" style="position: relative; width: 100%; overflow-y: hidden; cursor: grab; user-select: none; border: 1px solid #e5e7eb; border-radius: 8px; margin-bottom: 20px;" class="gantt-scroll-container">
                    @php
                        $today = \Carbon\Carbon::now()->startOfDay();
                        $todayLeftPercent = null;
                        if ($today->gte($timelineStart) && $today->lte($timelineEnd)) {
                            $todayOffset = max(0, $timelineStart->diffInDays($today));
                            $todayLeftPercent = ($todayOffset / $totalTimelineDays) * 100;
                        }
                    @endphp

                    <div style="display: flex; flex-direction: column; gap: 12px; padding: 12px 12px 16px; min-width: 100%;">
                        {{-- Header --}}
                        <div style="display: flex; gap: 12px; align-items: stretch;">
                            <div style="width: 260px; min-width: 260px; border: 1px solid #e5e7eb; background: #f9fafb; border-radius: 8px; display: flex; align-items: center; padding: 12px 16px; font-weight: 600; color: #374151;">
                                Mitarbeiter
                            </div>
                            <div style="flex: 1; position: relative; height: 40px; border: 1px solid #e5e7eb; background: #f9fafb; border-radius: 8px; overflow: hidden;">
                                @if($todayLeftPercent !== null)
                                    <div style="position: absolute; left: {{ $todayLeftPercent }}%; top: 0; bottom: 0; width: 2px; background: rgba(37,99,235,0.8); pointer-events: none;"></div>
                                @endif
                                @foreach($timelineMonths as $index => $monthData)
                                    @php
                                        $periodStart = $monthData['start'];
                                        $periodEnd = $monthData['end'];
                                        $offsetDays = max(0, $timelineStart->diffInDays($periodStart));
                                        $widthDays = max(1, $periodStart->diffInDays($periodEnd) + 1);
                                        $leftPercent = ($offsetDays / $totalTimelineDays) * 100;
                                        $widthPercent = ($widthDays / $totalTimelineDays) * 100;
                                    @endphp
                                    <div data-period-index="{{ $index }}" data-is-current-period="{{ $monthData['is_current'] ? 'true' : 'false' }}" data-period-start="{{ $periodStart->toDateString() }}" data-period-end="{{ $periodEnd->toDateString() }}" style="position: absolute; left: {{ $leftPercent }}%; width: {{ $widthPercent }}%; top: 0; bottom: 0; border-left: 1px solid #e5e7eb; border-right: 1px solid #e5e7eb; background: {{ $monthData['is_current'] ? '#dbeafe' : 'transparent' }}; display: flex; align-items: center; justify-content: center; font-size: {{ $timelineUnit === 'week' ? '11px' : '12px' }}; color: {{ $monthData['is_current'] ? '#1e3a8a' : '#374151' }}; font-weight: 600;">
                                        <span style="background: rgba(255,255,255,0.85); padding: 2px 6px; border-radius: 4px;">{{ $monthData['label'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Rows --}}
                        <div id="employeeRowsContainer" style="display: flex; flex-direction: column; gap: 12px;">
                            @foreach($timelineByEmployee as $entry)
                                @php
                                    $employee = $entry['employee'];
                                    $projects = $entry['projects'];
                                    $capacity = $entry['summary']['capacity'];
                                    $totalWeeklyLoad = $entry['summary']['total_weekly_load'];
                                    $overloadRatio = $entry['summary']['overload_ratio'];
                                    $employeeSpan = $entry['span'];
                                    $rowProjectCount = max(1, $projects->count());
                                    $timelineHeight = max(28, 28 + ($rowProjectCount - 1) * 32);
                                    $peakUtilization = $entry['summary']['peak_utilization_percent'] ?? 0;
                                    $avgUtilization = $entry['summary']['average_utilization_percent'] ?? 0;
                                    $hasAbsences = $entry['summary']['has_absences'] ?? false;
                                    
                                    // Use PEAK utilization for status (most critical metric)
                                    $statusBadge = null;
                                    if ($peakUtilization >= 999) {
                                        $statusBadge = ['label' => '🌴 Urlaub', 'color' => '#9ca3af', 'bg' => 'rgba(156, 163, 175, 0.1)'];
                                    } elseif ($peakUtilization >= 100) {
                                        $statusBadge = ['label' => '🔴 Überlast ' . round($peakUtilization) . '%', 'color' => '#dc2626', 'bg' => 'rgba(220, 38, 38, 0.1)'];
                                    } elseif ($peakUtilization >= 80) {
                                        $statusBadge = ['label' => '⚠️ Hoch ' . round($peakUtilization) . '%', 'color' => '#f59e0b', 'bg' => 'rgba(245, 158, 11, 0.1)'];
                                    } elseif ($peakUtilization >= 60) {
                                        $statusBadge = ['label' => '✓ Normal ' . round($peakUtilization) . '%', 'color' => '#3b82f6', 'bg' => 'rgba(59, 130, 246, 0.1)'];
                                    } elseif ($peakUtilization > 0) {
                                        $statusBadge = ['label' => '✅ Verfügbar ' . round($peakUtilization) . '%', 'color' => '#10b981', 'bg' => 'rgba(16, 185, 129, 0.1)'];
                                    }

                                    $employeeStart = $employeeSpan['start'];
                                    $employeeEnd = $employeeSpan['end'];
                                    $offsetDays = max(0, $timelineStart->diffInDays($employeeStart));
                                    $durationDays = max(1, $employeeStart->diffInDays($employeeEnd) + 1);
                                    $employeeLeftPercent = ($offsetDays / $totalTimelineDays) * 100;
                                    $employeeWidthPercent = ($durationDays / $totalTimelineDays) * 100;
                                @endphp
                                <div class="employee-row" data-employee-id="{{ $employee->id }}" data-row-key="employee-{{ $employee->id }}" style="display: flex; gap: 12px; align-items: stretch;">
                                    <div class="employee-row-card" style="position: sticky; left: 0; width: 260px; min-width: 260px; border: 1px solid #e5e7eb; border-radius: 8px; background: white; padding: 12px 16px; display: flex; flex-direction: column; gap: 4px; cursor: grab; z-index: 3;">
                                        @php
                                            $bookedHours = $entry['summary']['booked_hours_30d'] ?? 0;
                                            $bookedColor = $bookedHours > 120 ? '#10b981' : ($bookedHours > 60 ? '#3b82f6' : ($bookedHours > 0 ? '#3b82f6' : '#9ca3af'));
                                            $projectCount = $entry['summary']['project_count'] ?? 0;
                                        @endphp
                                        <div style="font-weight: 600; color: #111827; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $employee->first_name }} {{ $employee->last_name }}">{{ $employee->first_name }} {{ $employee->last_name }}</div>
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <span style="color: #6b7280; font-size: 12px;">Gebucht (30T):</span>
                                            <span style="color: {{ $bookedColor }}; font-size: 13px; font-weight: 700;">{{ $bookedHours }}h</span>
                                        </div>
                                        <div style="color: #6b7280; font-size: 11px;">{{ $projectCount }} Projekte · {{ $capacity }}h/W Kapazität</div>
                                        @if(!empty($employee->department))
                                            <div style="color: #6b7280; font-size: 12px;">{{ $employee->department }}</div>
                                        @endif
                                        @if($statusBadge)
                                            <span style="align-self: flex-start; margin-top: 4px; font-size: 11px; font-weight: 600; color: {{ $statusBadge['color'] }}; background: {{ $statusBadge['bg'] }}; border: 1px solid {{ $statusBadge['color'] }}; border-radius: 12px; padding: 4px 10px;">{{ $statusBadge['label'] }}</span>
                                        @endif
                                        @if($avgUtilization > 0 && $avgUtilization != $peakUtilization)
                                            <span style="align-self: flex-start; font-size: 11px; font-weight: 600; color: #6b7280; background: rgba(107, 114, 128, 0.1); border: 1px solid #9ca3af; border-radius: 12px; padding: 4px 10px;">Ø {{ round($avgUtilization) }}%</span>
                                        @endif
                                        @if($hasAbsences)
                                            <span style="align-self: flex-start; font-size: 11px; font-weight: 600; color: #92400e; background: #fef3c7; border: 1px solid #fcd34d; border-radius: 12px; padding: 4px 10px;">📅 Abwesenheit</span>
                                        @endif
                                    </div>
                                    <div class="employee-buckets" style="flex: 1; position: relative; border: 1px solid #e5e7eb; border-radius: 8px; background: white; padding: 16px; min-height: {{ $timelineHeight + 32 }}px;">
                                        @if($projects->isEmpty())
                                            <div style="color: #9ca3af; font-size: 12px;">Keine Zuweisungen vorhanden.</div>
                                        @else
                                            <div style="position: relative; height: {{ $timelineHeight }}px;">
                                                <div style="position: absolute; left: {{ $employeeLeftPercent }}%; width: {{ $employeeWidthPercent }}%; top: 0; bottom: 0; border-radius: 12px; background: rgba(59,130,246,0.12); border: 1px dashed rgba(59,130,246,0.3); z-index: 1;"></div>
                                                @if($todayLeftPercent !== null)
                                                    {{-- Enhanced Current Timeline Marker --}}
                                                    <div style="position: absolute; left: {{ $todayLeftPercent }}%; top: -8px; bottom: -8px; width: 3px; background: linear-gradient(to bottom, #ef4444, #dc2626); z-index: 10; box-shadow: 0 0 8px rgba(239, 68, 68, 0.5); border-radius: 2px;"></div>
                                                    <div style="position: absolute; left: calc({{ $todayLeftPercent }}% - 40px); top: -28px; background: #ef4444; color: white; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3); z-index: 11; white-space: nowrap;">
                                                        📍 HEUTE
                                                    </div>
                                                @endif
                                                
                                                {{-- Render Absence Blocks --}}
                                                @php
                                                    $employeeAbsencesList = $employeeAbsences->get($employee->id, collect());
                                                @endphp
                                                @foreach($employeeAbsencesList as $absence)
                                                    @php
                                                        $absenceStart = \Carbon\Carbon::parse($absence->start_date)->startOfDay();
                                                        $absenceEnd = \Carbon\Carbon::parse($absence->end_date)->endOfDay();
                                                        
                                                        // Clamp to timeline bounds
                                                        $clampedAbsenceStart = $absenceStart->lt($timelineStart) ? $timelineStart->copy() : $absenceStart;
                                                        $clampedAbsenceEnd = $absenceEnd->gt($timelineEnd) ? $timelineEnd->copy() : $absenceEnd;
                                                        
                                                        // Calculate position
                                                        $absenceOffsetDays = max(0, $timelineStart->diffInDays($clampedAbsenceStart));
                                                        $absenceDurationDays = max(1, $clampedAbsenceStart->diffInDays($clampedAbsenceEnd) + 1);
                                                        $absenceLeftPercent = ($absenceOffsetDays / $totalTimelineDays) * 100;
                                                        $absenceWidthPercent = ($absenceDurationDays / $totalTimelineDays) * 100;
                                                        
                                                        // Determine absence type and color
                                                        $absenceType = $absence->type ?? 'vacation';
                                                        $absenceIcon = match($absenceType) {
                                                            'vacation' => '🌴',
                                                            'sick' => '🏥',
                                                            'holiday' => '🎉',
                                                            default => '📅'
                                                        };
                                                        $absenceLabel = match($absenceType) {
                                                            'vacation' => 'Urlaub',
                                                            'sick' => 'Krank',
                                                            'holiday' => 'Feiertag',
                                                            default => 'Abwesend'
                                                        };
                                                    @endphp
                                                    <div class="absence-bar" 
                                                         data-absence-type="{{ $absenceType }}"
                                                         data-absence-label="{{ $absenceLabel }}"
                                                         data-start-date="{{ $absenceStart->format('d.m.Y') }}"
                                                         data-end-date="{{ $absenceEnd->format('d.m.Y') }}"
                                                         style="position: absolute; 
                                                                left: {{ $absenceLeftPercent }}%; 
                                                                width: {{ $absenceWidthPercent }}%; 
                                                                top: 0; 
                                                                height: 100%; 
                                                                background: repeating-linear-gradient(45deg, #d1d5db, #d1d5db 10px, #e5e7eb 10px, #e5e7eb 20px); 
                                                                border: 2px solid #9ca3af; 
                                                                border-radius: 8px; 
                                                                z-index: 2; 
                                                                opacity: 0.6; 
                                                                cursor: pointer; 
                                                                display: flex; 
                                                                align-items: center; 
                                                                justify-content: center; 
                                                                font-size: 14px; 
                                                                font-weight: 700; 
                                                                color: #374151; 
                                                                box-shadow: 0 2px 8px rgba(0,0,0,0.15);"
                                                         onmouseenter="showAbsenceTooltip(event, '{{ $absenceIcon }}', '{{ $absenceLabel }}', '{{ $absenceStart->format('d.m.Y') }}', '{{ $absenceEnd->format('d.m.Y') }}')"
                                                         onmouseleave="hideAbsenceTooltip()">
                                                        <span style="text-shadow: 0 1px 2px rgba(255,255,255,0.8);">{{ $absenceIcon }}</span>
                                                    </div>
                                                @endforeach
                                                
                                                @foreach($projects as $projectIndex => $project)
                                                    @php
                                                        $pStart = $project['start'];
                                                        $pEnd = $project['end'];
                                                        
                                                        // Check for overdue and extension indicators
                                                        $isOverdue = $pEnd->isPast() && $pEnd->lt(now());
                                                        $extendsRight = $pEnd->gt($timelineEnd);
                                                        $extendsLeft = $pStart->lt($timelineStart);
                                                        
                                                        // WICHTIG: Überfällige Projekte NICHT an timelineStart clampen!
                                                        // Sie sollen an ihrem echten end_date enden (auch wenn vor timelineStart)
                                                        if ($isOverdue && $pEnd->lt($timelineStart)) {
                                                            // Projekt endete VOR Timeline-Start → zeige es NICHT
                                                            // (Diese Projekte sind zu alt für aktuelle Timeline)
                                                            $shouldDisplay = false;
                                                        } else {
                                                            $shouldDisplay = true;
                                                            $clampedStart = $pStart->lt($timelineStart) ? $timelineStart->copy() : $pStart;
                                                            $clampedEnd = $pEnd->gt($timelineEnd) ? $timelineEnd->copy() : $pEnd;
                                                            $offsetDays = max(0, $timelineStart->diffInDays($clampedStart));
                                                            $durationDays = max(1, $clampedStart->diffInDays($clampedEnd) + 1);
                                                            $leftPercent = ($offsetDays / $totalTimelineDays) * 100;
                                                            $widthPercent = ($durationDays / $totalTimelineDays) * 100;
                                                        }
                                                        
                                                        $hours = $project['weekly_hours'];
                                                        $utilizationRatio = $project['utilization_ratio'];
                                                        $isOverCapacity = $project['is_over_capacity'];
                                                        
                                                        // Color coding with overdue priority
                                                        $bgColor = '#0ea5e9';
                                                        $borderStyle = 'none';
                                                        
                                                        if ($isOverdue) {
                                                            $bgColor = '#ef4444'; // Red for overdue
                                                            $borderStyle = 'none';
                                                        } elseif ($isOverCapacity) {
                                                            $borderStyle = '2px solid #dc2626';
                                                            $bgColor = '#fb7185';
                                                        } elseif ($utilizationRatio !== null && $utilizationRatio > 0.9) {
                                                            $borderStyle = '2px dashed #f59e0b';
                                                            $bgColor = '#fcd34d';
                                                        } elseif ($project['sources']->contains('moco')) {
                                                            $bgColor = '#06b6d4';
                                                        }
                                                        
                                                        // Dashed border for extending projects
                                                        if ($extendsRight) {
                                                            $borderStyle = '2px dashed rgba(255,255,255,0.7)';
                                                        }
                                                    @endphp
                                                    
                                                    @if($shouldDisplay)
                                                    <div class="employee-project-row" data-assignment-id="{{ $project['assignment_ids']->first() }}" data-employee-id="{{ $employee->id }}" data-start-date="{{ $project['start']->toDateString() }}" data-end-date="{{ $project['end']->toDateString() }}" style="position: absolute; left: {{ $leftPercent }}%; top: {{ $projectIndex * 32 }}px; width: {{ $widthPercent }}%; height: 24px; z-index: 3; cursor: grab;">
                                                        <div class="employee-project-bar" style="width: 100%; height: 100%; border-radius: 12px; background: {{ $bgColor }}; border: {{ $borderStyle }}; display: flex; align-items: center; justify-content: center; color: {{ $isOverdue ? '#ffffff' : '#0f172a' }}; font-size: 12px; font-weight: 600; box-shadow: 0 2px 6px rgba(0,0,0,0.15);">
                                                            <span style="padding: 0 10px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $project['project_name'] }}{{ $isOverdue ? ' (Überfällig)' : '' }}">{{ Str::limit($project['project_name'], 32) }}</span>
                                                            @if($hours)
                                                                <span style="margin-left: 8px; font-weight: 600; background: rgba({{ $isOverdue ? '255,255,255' : '15,23,42' }},0.1); padding: 2px 6px; border-radius: 12px;">{{ $hours }}h</span>
                                                            @endif
                                                            @if($isOverdue)
                                                                <span style="margin-left: 4px; font-size: 12px;" title="Überfällig">⚠️</span>
                                                            @elseif($extendsLeft)
                                                                <span style="margin-left: 4px; font-size: 12px;" title="Begann vor Timeline">←</span>
                                                            @elseif($extendsRight)
                                                                <span style="margin-left: 4px; font-size: 12px;" title="Läuft über Timeline hinaus">→</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div style="margin-top: 20px; padding: 16px; background: #f9fafb; border-radius: 6px;">
                <h4 style="font-size: 14px; font-weight: 600; color: #374151; margin: 0 0 12px 0;">Legende</h4>
                <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 12px; height: 12px; background: #06b6d4; border-radius: 2px;"></div>
                        <span style="font-size: 12px; color: #374151;">Projekt-Zuweisung</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 12px; height: 12px; background: repeating-linear-gradient(45deg, #d1d5db, #d1d5db 3px, #e5e7eb 3px, #e5e7eb 6px); border: 1px solid #9ca3af; border-radius: 2px;"></div>
                        <span style="font-size: 12px; color: #374151;">🌴 Abwesenheit (Urlaub/Krank)</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 12px; height: 12px; background: #fb7185; border-radius: 2px;"></div>
                        <span style="font-size: 12px; color: #374151;">🔴 Überlast (&gt;100% Auslastung)</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 12px; height: 12px; background: #fcd34d; border-radius: 2px;"></div>
                        <span style="font-size: 12px; color: #374151;">⚠️ Hoch (80-100% Auslastung)</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 12px; height: 12px; background: #3b82f6; border-radius: 2px;"></div>
                        <span style="font-size: 12px; color: #374151;">✓ Normal (60-80% Auslastung)</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 12px; height: 12px; background: #10b981; border-radius: 2px;"></div>
                        <span style="font-size: 12px; color: #374151;">✅ Verfügbar (&lt;60% Auslastung)</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 2px; height: 16px; background: linear-gradient(to bottom, #2563eb, #60a5fa);"></div>
                        <span style="font-size: 12px; color: #374151; font-weight: 600;">Aktuelle KW / Monat</span>
                    </div>
                </div>
            </div>
        @else
            <div style="text-align: center; padding: 60px 20px; color: #6b7280;">
                <h3 style="font-size: 18px; font-weight: 500; color: #111827; margin: 0 0 8px 0;">Keine Mitarbeiter-Zuweisungen</h3>
                <p style="margin: 0 0 24px 0;">Sobald Mitarbeiter Projekten zugewiesen sind, erscheint hier die Wochenübersicht.</p>
            </div>
        @endif
    </div>
</div>

<script>
// Absence Tooltip System
let absenceTooltip = null;

function createAbsenceTooltip() {
    if (!absenceTooltip) {
        absenceTooltip = document.createElement('div');
        absenceTooltip.id = 'absence-tooltip';
        absenceTooltip.style.cssText = `
            display: none;
            position: fixed;
            background: white;
            border: 2px solid #9ca3af;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.25);
            padding: 16px;
            z-index: 10000;
            max-width: 280px;
            pointer-events: none;
        `;
        document.body.appendChild(absenceTooltip);
    }
    return absenceTooltip;
}

function showAbsenceTooltip(event, icon, label, startDate, endDate) {
    const tooltip = createAbsenceTooltip();
    
    tooltip.innerHTML = `
        <div style="margin-bottom: 12px; padding-bottom: 12px; border-bottom: 2px solid #e5e7eb;">
            <h4 style="margin: 0 0 4px 0; font-size: 18px; font-weight: 700; color: #111827;">${icon} ${label}</h4>
            <p style="margin: 0; font-size: 13px; color: #6b7280;">Mitarbeiter nicht verfügbar</p>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <div>
                <div style="font-size: 11px; color: #6b7280; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Von</div>
                <div style="font-size: 14px; color: #111827; font-weight: 600;">${startDate}</div>
            </div>
            <div>
                <div style="font-size: 11px; color: #6b7280; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Bis</div>
                <div style="font-size: 14px; color: #111827; font-weight: 600;">${endDate}</div>
            </div>
        </div>
        <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #e5e7eb; font-size: 11px; color: #6b7280; text-align: center; font-style: italic;">
            ⚠️ Kapazität in diesem Zeitraum = 0 Stunden
        </div>
    `;
    
    tooltip.style.display = 'block';
    positionAbsenceTooltip(event, tooltip);
}

function hideAbsenceTooltip() {
    if (absenceTooltip) {
        absenceTooltip.style.display = 'none';
    }
}

function positionAbsenceTooltip(event, tooltip) {
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

// Update tooltip position on mouse move
document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('mousemove', function(e) {
        if (absenceTooltip && absenceTooltip.style.display === 'block') {
            const target = e.target.closest('.absence-bar');
            if (target) {
                positionAbsenceTooltip(e, absenceTooltip);
            }
        }
    });
});
</script>
