@props([
    'project',
    'metrics',
    'projectTeams',
    'projectAbsences',
    'projectAbsenceDetails',
    'allAssignments',
    'timelineMonths',
    'columnWidth',
    'timelineUnit',
])

@php
    $startDate = $metrics['startDate'];
    $endDate = $metrics['endDate'];
    $requiredPerWeek = $metrics['requiredPerWeek'];
    $availablePerWeek = $metrics['availablePerWeek'];
    $absenceImpact = $metrics['absenceImpact'];
    $riskScore = $metrics['riskScore'];
    $bottleneck = $metrics['bottleneck'];
    $bottleneckCategory = $metrics['bottleneckCategory'];
    
    $timelineCount = count($timelineMonths);
    $normalizedStart = $timelineUnit === 'month'
        ? $startDate->copy()->startOfMonth()
        : $startDate->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
    $normalizedEnd = $timelineUnit === 'month'
        ? $endDate->copy()->endOfMonth()
        : $endDate->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);

    $startMonth = 0;
    $endMonth = -1;
    foreach ($timelineMonths as $idx => $period) {
        $periodStart = $period['start'];
        $periodEnd = $period['end'];
        if ($normalizedEnd < $periodStart || $normalizedStart > $periodEnd) {
            continue;
        }
        if ($endMonth === -1) {
            $startMonth = $idx;
        }
        $endMonth = $idx;
    }
    $durationMonths = $endMonth >= $startMonth ? ($endMonth - $startMonth + 1) : 0;

    // Status strikt nach MOCO-Regel (nur finish_date/end_date)
    $statusForColors = ($endDate && $endDate->lt(\Carbon\Carbon::now()->startOfDay())) ? 'completed' : 'active';
    $bnColor = $bottleneck ? '#ef4444' : ($statusForColors == 'active' ? '#10b981' : '#6b7280');
@endphp
<div class="project-row" 
     data-status="{{ $project->status }}"
     data-project-name="{{ $project->name }}"
     data-start-date="{{ $project->start_date ?: $project->moco_created_at }}"
     data-end-date="{{ $project->end_date ?: ($project->moco_created_at ? now()->addMonths(23)->format('Y-m-d') : null) }}"
     data-responsibles="{{ $allAssignments->get($project->id, collect())->pluck('employee_id')->implode(',') }}"
     data-is-bottleneck="{{ $bottleneck ? '1' : '0' }}"
     data-risk-score="{{ round($riskScore) }}"
     data-bottleneck-category="{{ $bottleneckCategory }}"
     data-required="{{ (int)round($requiredPerWeek) }}"
     data-available="{{ (int)round($availablePerWeek) }}"
     data-deficit="{{ max(0, (int)round($requiredPerWeek - $availablePerWeek)) }}"
     data-absence-impact="{{ $absenceImpact ? '1' : '0' }}"
     style="display: grid; grid-template-columns: 260px repeat({{ count($timelineMonths) }}, {{ $columnWidth }}px); gap: 1px; margin-bottom: 1px;">
    <!-- Project Name -->
    @php
        // Lade Team-Mitglieder für Projektnamen-Container
        $projectTeamMembers = '';
        
        // 1. Versuche MOCO-Daten (vorgefertigt im Controller)
        if ($project->moco_id && isset($projectTeams[$project->moco_id])) {
            $projectTeamMembers = $projectTeams[$project->moco_id];
        }
        
        // 2. Fallback auf lokale Daten, wenn keine MOCO-Daten verfügbar
        if (empty($projectTeamMembers)) {
            $projectTeamMembers = $project->getAssignedPersonsString(null, 5);
        }
        
        $projectEstimatedRevenue = ($project->estimated_hours ?? 0) * ($project->hourly_rate ?? 0);
    @endphp
    <div class="project-name-container" 
         data-project-name="{{ $project->name }}"
         data-start-date="{{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d.m.Y') : ($project->moco_created_at ? \Carbon\Carbon::parse($project->moco_created_at)->format('d.m.Y') : 'Nicht festgelegt') }}"
         data-end-date="{{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('d.m.Y') : 'Nicht festgelegt' }}"
         data-estimated-hours="{{ $project->estimated_hours ?? 0 }}"
         data-progress="{{ round($project->progress) }}"
         data-team="{{ $projectTeamMembers ?: 'Keine Personen zugewiesen' }}"
         data-team-members="{{ $projectTeamMembers ?: 'Keine Personen zugewiesen' }}"
         data-revenue="{{ number_format($projectEstimatedRevenue, 0, ',', '.') }}"
         data-status="{{ ucfirst($project->status) }}"
         data-required-hours="{{ (int)round($requiredPerWeek) }}"
         data-available-hours="{{ (int)round($availablePerWeek) }}"
         data-is-ongoing="{{ !$project->start_date && !$project->end_date && $project->moco_created_at ? '1' : '0' }}"
         style="background: white; padding: 12px; border: 1px solid #e5e7eb; display: flex; align-items: center; position: relative; position: sticky; left: 0; z-index: 10; cursor: pointer;" 
         onmouseover="this.style.background='#f9fafb'" 
         onmouseout="this.style.background='white'">
        <a href="{{ route('projects.show', $project) }}" style="display: flex; align-items: center; text-decoration: none; flex: 1; min-width: 0;">
            <div style="flex: 1; min-width: 0;">
            <div style="font-weight: 500; color: #111827; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                {{ $project->name }}
            </div>
            
            @if(!$project->start_date && !$project->end_date && $project->moco_created_at)
            <div style="color: #8b5cf6; font-size: 11px; margin-top: 2px; font-weight: 600; display: flex; align-items: center; gap: 4px;">
                <span style="width: 6px; height: 6px; background: #a855f7; border-radius: 50%; display: inline-block;"></span>
                Laufendes Projekt (seit {{ \Carbon\Carbon::parse($project->moco_created_at)->format('d.m.Y') }})
            </div>
            @endif
            
            
            
            @if($bottleneck)
            <div style="color:#b91c1c; font-size: 12px; display:flex; align-items:center; gap:6px; margin-top: 4px;">
                @php
                    $deficit = max(0, $requiredPerWeek - $availablePerWeek);
                    $categoryColor = match($bottleneckCategory) {
                        'kritisch' => '#dc2626',
                        'hoch' => '#ea580c',
                        'mittel' => '#d97706',
                        'niedrig' => '#65a30d',
                        default => '#059669'
                    };
                    $categoryIcon = match($bottleneckCategory) {
                        'kritisch' => '🔴',
                        'hoch' => '🟠',
                        'mittel' => '🟡',
                        'niedrig' => '🟢',
                        default => '🔵'
                    };
                @endphp
                {{ $categoryIcon }} {{ ucfirst($bottleneckCategory) }} ({{ round($riskScore) }}%)
                @if($deficit > 0)
                    | Defizit: {{ (int)$deficit }}h/W
                @endif
                @if($absenceImpact)
                    | Abwesenheit wirkt sich aus
                @endif
            </div>
            @endif
            
            {{-- Hinweis auf konkrete Abwesenheiten im Projektnamen entfernt, um die Zeile schlanker zu halten --}}
        </div>
    </a>
        
        <!-- Schnellaktionen-Menü -->
        <div style="position: relative; margin-left: 8px;">
            <button onclick="toggleQuickActions(event, {{ $project->id }})" style="width: 28px; height: 28px; background: white; border: 1px solid #e5e7eb; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s ease; color: #6b7280;" onmouseover="this.style.background='#f3f4f6'; this.style.borderColor='#3b82f6'; this.style.color='#3b82f6'" onmouseout="this.style.background='white'; this.style.borderColor='#e5e7eb'; this.style.color='#6b7280'">
                ⋮
            </button>
            
            <!-- Dropdown-Menü wird jetzt außerhalb des Scroll-Containers gerendert -->
        </div>
    </div>

    <!-- Timeline Cells -->
    @for($i = 0; $i < $timelineCount; $i++)
        @php
            $period = $timelineMonths[$i];
            $isInRange = $durationMonths > 0 && $i >= $startMonth && $i < ($startMonth + $durationMonths);
            $isCurrent = $isInRange && $i === $startMonth;
            $isCurrentPeriod = $period['is_current'] ?? false;
        @endphp
        @php
            $cellBg = $isInRange
                ? ($bottleneck
                    ? '#fee2e2'
                    : (($endDate && $endDate->lt(\Carbon\Carbon::now()->startOfDay())) ? '#f3f4f6' : ($timelineUnit === 'week' ? '#e0f2fe' : '#dcfce7')))
                : 'white';
        @endphp
        <div style="background: {{ $cellBg }}; border: 1px solid #e5e7eb; position: relative; min-height: 40px;">
            @if($isInRange)
                @php
                    // Verwende vorgefertigte Team-Daten aus dem Controller
                    $teamMembers = '';
                    
                    // 1. Versuche MOCO-Daten (vorgefertigt im Controller)
                    if ($project->moco_id && isset($projectTeams[$project->moco_id])) {
                        $teamMembers = $projectTeams[$project->moco_id];
                    }
                    
                    // 2. Fallback auf lokale Daten, wenn keine MOCO-Daten verfügbar
                    if (empty($teamMembers)) {
                        $teamMembers = $project->getAssignedPersonsString(null, 5);
                    }
                    
                    $estimatedRevenue = ($project->estimated_hours ?? 0) * ($project->hourly_rate ?? 0);
                @endphp
                
                {{-- Debug: Zeige Assignment-Count (temporär aktiviert) --}}
                @if($allAssignments->get($project->id, collect())->count() > 0)
                    {{-- Debug: {{ $project->name }} hat {{ $allAssignments->get($project->id, collect())->count() }} Assignments --}}
                @endif
                <div class="gantt-bar" 
                     data-project-name="{{ $project->name }}"
                     data-start-date="{{ $startDate->format('d.m.Y') }}"
                     data-end-date="{{ !$project->start_date && !$project->end_date && $project->moco_created_at ? 'laufend' : $endDate->format('d.m.Y') }}"
                     data-estimated-hours="{{ $project->estimated_hours ?? 0 }}"
                     data-progress="{{ round($project->progress) }}"
                     data-team="{{ $teamMembers ?: 'Keine Personen zugewiesen' }}"
                     data-team-members="{{ $teamMembers ?: 'Keine Personen zugewiesen' }}"
                     data-revenue="{{ number_format($estimatedRevenue, 0, ',', '.') }}"
                     data-status="{{ ucfirst($project->status) }}"
                     data-required-hours="{{ (int)round($requiredPerWeek) }}"
                     data-available-hours="{{ (int)round($availablePerWeek) }}"
                     data-is-ongoing="{{ !$project->start_date && !$project->end_date && $project->moco_created_at ? '1' : '0' }}"
                     style="position: absolute; top: 50%; left: 0; right: 0; height: 8px; background: {{ $bnColor }}; transform: translateY(-50%); border-radius: 4px; cursor: pointer; transition: height 0.2s ease;"
                     onmouseover="this.style.height='12px'"
                     onmouseout="this.style.height='8px'">
                </div>
                @if($isCurrent)
                    <div style="position: absolute; top: 50%; left: 0; right: 0; height: 8px; background: #f59e0b; transform: translateY(-50%); border-radius: 4px; width: {{ $project->progress }}%; pointer-events: none;"></div>
                @endif
            @endif
            @if($isCurrentPeriod)
                <!-- Heute-Marker (vertikale Linie) - nur in Timeline-Zellen -->
                <div style="position: absolute; left: 50%; top: 0; bottom: 0; width: 2px; background: linear-gradient(to bottom, #3b82f6, #60a5fa); transform: translateX(-50%); z-index: 5; opacity: 0.6; pointer-events: none;"></div>
            @endif
        </div>
    @endfor
</div>




