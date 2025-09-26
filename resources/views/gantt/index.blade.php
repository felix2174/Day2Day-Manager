@extends('layouts.app')

@section('title', 'Gantt-Diagramm')

@section('content')
<div style="width: 100%; margin: 0; padding: 20px;">


    <!-- Gantt Chart Container -->
    <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
        <!-- Chart Header -->
        <div style="background: #f9fafb; padding: 20px; border-bottom: 1px solid #e5e7eb;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 20px;">
                <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0;">Projekt-Timeline</h3>
                    <!-- Statistiken -->
                    <div style="display: flex; gap: 20px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="color: #6b7280; font-size: 14px;">Projekte:</span>
                            <span style="font-weight: 600; color: #111827;">{{ $projects->count() }}</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="color: #6b7280; font-size: 14px;">Aktiv:</span>
                            <span style="font-weight: 600; color: #059669;">{{ $projects->where('status', 'active')->count() }}</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="color: #6b7280; font-size: 14px;">Geplant:</span>
                            <span style="font-weight: 600; color: #3b82f6;">{{ $projects->where('status', 'planning')->count() }}</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="color: #6b7280; font-size: 14px;">Abgeschlossen:</span>
                            <span style="font-weight: 600; color: #6b7280;">{{ $projects->where('status', 'completed')->count() }}</span>
                        </div>
                    </div>
                </div>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <!-- Date Range Input -->
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <label style="font-size: 12px; color: #374151; font-weight: 500;">Zeitraum:</label>
                        <input type="date" id="dateFrom" value="{{ request('date_from', now()->format('Y-m-d')) }}" 
                               style="border: 1px solid #e5e7eb; border-radius: 4px; padding: 6px 8px; font-size: 12px; width: 120px;">
                        <span style="color: #6b7280; font-size: 12px;">bis</span>
                        <input type="date" id="dateTo" value="{{ request('date_to', now()->addMonths(6)->format('Y-m-d')) }}" 
                               style="border: 1px solid #e5e7eb; border-radius: 4px; padding: 6px 8px; font-size: 12px; width: 120px;">
                        <button onclick="applyDateRange()" style="background: #3b82f6; color: white; border: none; border-radius: 4px; padding: 6px 12px; font-size: 12px; cursor: pointer; font-weight: 500;">
                            Anwenden
                        </button>
                    </div>
                    <!-- Timeline View Selector -->
                    <div style="display: flex; gap: 4px; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 6px; padding: 2px;">
                        <button onclick="changeTimelineView('day')" 
                                style="background: {{ $timelineView == 'day' ? '#3b82f6' : 'transparent' }}; color: {{ $timelineView == 'day' ? 'white' : '#374151' }}; border: none; border-radius: 4px; padding: 6px 12px; font-size: 12px; cursor: pointer; font-weight: 500;">
                            Tag
                        </button>
                        <button onclick="changeTimelineView('week')" 
                                style="background: {{ $timelineView == 'week' ? '#3b82f6' : 'transparent' }}; color: {{ $timelineView == 'week' ? 'white' : '#374151' }}; border: none; border-radius: 4px; padding: 6px 12px; font-size: 12px; cursor: pointer; font-weight: 500;">
                            Woche
                        </button>
                        <button onclick="changeTimelineView('month')" 
                                style="background: {{ $timelineView == 'month' ? '#3b82f6' : 'transparent' }}; color: {{ $timelineView == 'month' ? 'white' : '#374151' }}; border: none; border-radius: 4px; padding: 6px 12px; font-size: 12px; cursor: pointer; font-weight: 500;">
                            Monat
                        </button>
                    </div>
                <div style="display: flex; gap: 8px;">
                    <button onclick="zoomIn()" style="background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 4px; padding: 6px 12px; font-size: 12px; cursor: pointer;">🔍+</button>
                    <button onclick="zoomOut()" style="background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 4px; padding: 6px 12px; font-size: 12px; cursor: pointer;">🔍-</button>
                    </div>
                    <!-- Hamburger Menu -->
                    <div style="position: relative;">
                        <button onclick="toggleMenu()" style="background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 4px; padding: 6px 12px; font-size: 12px; cursor: pointer;">☰</button>
                        <div id="hamburgerMenu" style="display: none; position: absolute; top: 100%; right: 0; background: white; border: 1px solid #e5e7eb; border-radius: 6px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); z-index: 1000; min-width: 200px; margin-top: 4px;">
                            <a href="{{ route('gantt.export') }}" style="display: block; padding: 12px 16px; color: #374151; text-decoration: none; font-size: 14px; border-bottom: 1px solid #f3f4f6;">
                                📊 Excel Export
                            </a>
                            <a href="{{ route('gantt.bottlenecks') }}" style="display: block; padding: 12px 16px; color: #374151; text-decoration: none; font-size: 14px;">
                                ⚠️ Engpass-Analyse
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Content -->
        <div id="ganttScroll" style="padding: 20px; overflow: auto; max-height: 60vh;">
            <div id="ganttContent" style="transform-origin: 0 0; transform: scale(1); display: inline-block;">
            @if($projects->count() > 0)
                <!-- Timeline Header -->
                <div style="display: grid; grid-template-columns: 250px repeat({{ count($timelineData) }}, 1fr); gap: 1px; margin-bottom: 1px;">
                    <div style="background: #f3f4f6; padding: 12px; font-weight: 600; color: #374151; border: 1px solid #e5e7eb;">Projekte</div>
                    @foreach($timelineData as $timelineItem)
                        <div style="background: {{ isset($timelineItem['is_today']) && $timelineItem['is_today'] ? '#dbeafe' : (isset($timelineItem['is_current_week']) && $timelineItem['is_current_week'] ? '#dbeafe' : (isset($timelineItem['is_current_month']) && $timelineItem['is_current_month'] ? '#dbeafe' : '#f3f4f6')) }}; padding: 8px 4px; text-align: center; font-weight: 600; color: #374151; border: 1px solid #e5e7eb; font-size: 11px; {{ isset($timelineItem['is_weekend']) && $timelineItem['is_weekend'] ? 'background: #fef3c7;' : '' }}">
                            <div style="font-size: 11px; font-weight: 600;">{{ $timelineItem['label'] }}</div>
                            @if(isset($timelineItem['weekday']))
                                <div style="font-size: 9px; color: #6b7280; margin-top: 1px;">{{ $timelineItem['weekday'] }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Project Rows - Sortiert nach Priorität -->
                @php
                    $sortedProjects = $projects->sortBy(function($project) {
                        // Priorität: 1. Überfällig, 2. Aktiv, 3. Geplant, 4. Abgeschlossen
                        $priority = 0;
                        if($project->status == 'active' && $project->progress < 50 && \Carbon\Carbon::parse($project->end_date)->diffInDays(now()) < 30) {
                            $priority = 1; // Kritisch
                        } elseif($project->status == 'active') {
                            $priority = 2; // Aktiv
                        } elseif($project->status == 'planning') {
                            $priority = 3; // Geplant
                        } else {
                            $priority = 4; // Abgeschlossen
                        }
                        return $priority;
                    });
                @endphp
                @foreach($sortedProjects as $project)
                    @php
                        $startDate = \Carbon\Carbon::parse($project->start_date);
                        $endDate = \Carbon\Carbon::parse($project->end_date);
                        $hasAbsenceWarning = collect($absenceWarnings)->contains('project.id', $project->id);
                        $absenceData = collect($absenceWarnings)->firstWhere('project.id', $project->id);
                        
                        // Berechne Projekt-Position basierend auf Timeline-Ansicht
                        $projectPosition = [];
                        foreach ($timelineData as $index => $timelineItem) {
                            $isInRange = false;
                            
                            switch ($timelineView) {
                                case 'day':
                                    $timelineDate = \Carbon\Carbon::parse($timelineItem['date']);
                                    $isInRange = $timelineDate->between($startDate, $endDate);
                                    break;
                                    
                                case 'week':
                                    $weekStart = \Carbon\Carbon::parse($timelineItem['start_date']);
                                    $weekEnd = \Carbon\Carbon::parse($timelineItem['end_date']);
                                    $isInRange = $startDate->lte($weekEnd) && $endDate->gte($weekStart);
                                    break;
                                    
                                case 'month':
                                default:
                                    $monthStart = \Carbon\Carbon::parse($timelineItem['month'] . '-01')->startOfMonth();
                                    $monthEnd = $monthStart->copy()->endOfMonth();
                                    $isInRange = $startDate->lte($monthEnd) && $endDate->gte($monthStart);
                                    break;
                            }
                            
                            $projectPosition[] = [
                                'index' => $index,
                                'is_in_range' => $isInRange,
                                'is_current' => $isInRange && (isset($timelineItem['is_today']) && $timelineItem['is_today'] || isset($timelineItem['is_current_week']) && $timelineItem['is_current_week'] || isset($timelineItem['is_current_month']) && $timelineItem['is_current_month']),
                                'is_overdue' => $isInRange && $project->status == 'active' && $project->progress < 100
                            ];
                        }
                    @endphp
                    <div style="display: grid; grid-template-columns: 250px repeat({{ count($timelineData) }}, 1fr); gap: 1px; margin-bottom: 1px; {{ $hasAbsenceWarning ? 'background: #fef3c7;' : '' }}">
                        <!-- Project Details - Verbesserte Hierarchie -->
                        @php
                            $isCritical = $project->status == 'active' && $project->progress < 50 && \Carbon\Carbon::parse($project->end_date)->diffInDays(now()) < 30;
                            $priorityClass = $isCritical ? 'border-l-4 border-red-500' : ($project->status == 'active' ? 'border-l-4 border-green-500' : ($project->status == 'planning' ? 'border-l-4 border-blue-500' : 'border-l-4 border-gray-500'));
                        @endphp
                        <div style="background: {{ $isCritical ? '#fef2f2' : 'white' }}; padding: 10px; border: 1px solid #e5e7eb; position: relative; min-height: 70px; {{ $priorityClass }};" 
                             onmouseover="showProjectTooltip(this, '{{ $project->name }}', '{{ $project->description ? Str::limit($project->description, 100) : 'Keine Ziele definiert' }}', '{{ $project->responsible ? $project->responsible->first_name . ' ' . $project->responsible->last_name : 'Nicht zugewiesen' }}', {{ $project->assignments->count() }}, {{ round($project->progress) }}, '{{ \Carbon\Carbon::parse($project->start_date)->format('d.m.Y') }}', '{{ \Carbon\Carbon::parse($project->end_date)->format('d.m.Y') }}')" 
                             onmouseout="hideProjectTooltip()">
                            @if($hasAbsenceWarning)
                                @php
                                    $tooltipContent = 'Abwesenheiten:';
                                    foreach ($absenceData['absences'] as $absenceInfo) {
                                        $tooltipContent .= ' ' . $absenceInfo['employee']->first_name . ' ' . $absenceInfo['employee']->last_name . ' (' . $absenceInfo['total_days'] . ' Tage)';
                                        if (!$loop->last) $tooltipContent .= ',';
                                    }
                                @endphp
                                <div style="position: absolute; top: 6px; right: 6px; background: #f59e0b; color: white; padding: 3px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; cursor: help; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" 
                                     title="{{ $tooltipContent }}">
                                    {{ $absenceData['total_affected_employees'] }}
                                </div>
                            @endif
                            
                            @if($isCritical)
                                <div style="position: absolute; top: 6px; left: 6px; background: #dc2626; color: white; padding: 2px 6px; border-radius: 8px; font-size: 9px; font-weight: 600;">
                                    KRITISCH
                                </div>
                            @endif
                            
                            <!-- Projekt-Header mit Status -->
                            <div style="display: flex; align-items: center; margin-bottom: 8px;">
                                <div style="width: 12px; height: 12px; background: {{ $project->status == 'active' ? '#10b981' : ($project->status == 'planning' ? '#3b82f6' : '#6b7280') }}; border-radius: 50%; margin-right: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"></div>
                                <div style="font-weight: 700; color: #111827; font-size: 14px; flex: 1;">{{ $project->name }}</div>
                                <div style="background: {{ $project->status == 'active' ? '#dcfce7' : ($project->status == 'planning' ? '#dbeafe' : '#f3f4f6') }}; color: {{ $project->status == 'active' ? '#166534' : ($project->status == 'planning' ? '#1e40af' : '#4b5563') }}; padding: 2px 8px; border-radius: 12px; font-size: 10px; font-weight: 600;">
                                    {{ $project->status == 'active' ? 'AKTIV' : ($project->status == 'planning' ? 'GEPLANT' : 'ABGESCHLOSSEN') }}
                                </div>
                            </div>
                            
                            <!-- Fortschritt mit besserer Visualisierung -->
                            <div style="margin-bottom: 8px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                    <span style="color: #6b7280; font-size: 11px; font-weight: 500;">Fortschritt</span>
                                    <span style="color: #111827; font-size: 12px; font-weight: 700;">{{ round($project->progress) }}%</span>
                                </div>
                                <div style="width: 100%; height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden; box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);">
                                    <div style="width: {{ $project->progress }}%; height: 100%; background: {{ $project->progress >= 100 ? '#10b981' : ($project->progress >= 75 ? '#f59e0b' : '#3b82f6') }}; transition: width 0.3s ease; border-radius: 4px;"></div>
                                </div>
                            </div>
                            
                            <!-- Team & Zeit mit besserer Struktur -->
                            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 11px; color: #6b7280;">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <span style="font-size: 12px;">👥</span>
                                    <span style="font-weight: 500;">{{ $project->responsible ? $project->responsible->first_name : 'N/A' }}</span>
                                    @if($project->assignments->count() > 0)
                                        <span style="background: #f3f4f6; padding: 1px 6px; border-radius: 8px; font-size: 10px;">+{{ $project->assignments->count() }}</span>
                                    @endif
                                </div>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <span style="font-size: 12px;">📅</span>
                                    <span style="font-weight: 500;">{{ \Carbon\Carbon::parse($project->start_date)->format('d.m') }} - {{ \Carbon\Carbon::parse($project->end_date)->format('d.m') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Timeline Cells - Durchgehende Balken -->
                        @php
                            $projectStartIndex = null;
                            $projectEndIndex = null;
                            $projectSpan = 0;
                            
                            // Finde Start- und End-Index des Projekts
                            foreach($projectPosition as $index => $position) {
                                if($position['is_in_range']) {
                                    if($projectStartIndex === null) {
                                        $projectStartIndex = $index;
                                    }
                                    $projectEndIndex = $index;
                                    $projectSpan++;
                                }
                            }
                        @endphp
                        
                        @foreach($projectPosition as $index => $position)
                            <div style="background: white; border: 1px solid #e5e7eb; position: relative; min-height: 60px;">
                                @if($position['is_in_range'])
                                    @if($index == $projectStartIndex)
                                        <!-- Projekt-Balken Start -->
                                        <div style="position: absolute; top: 50%; left: 0; right: 0; height: 20px; background: {{ $project->status == 'active' ? '#10b981' : ($project->status == 'planning' ? '#3b82f6' : '#6b7280') }}; transform: translateY(-50%); border-radius: 10px; box-shadow: 0 3px 6px rgba(0,0,0,0.15); display: flex; align-items: center; justify-content: space-between; padding: 0 8px;">
                                            <!-- Fortschritts-Balken -->
                                            <div style="position: absolute; top: 0; left: 0; height: 100%; background: {{ $project->progress >= 100 ? '#059669' : ($project->progress >= 75 ? '#f59e0b' : '#3b82f6') }}; border-radius: 10px; width: {{ $project->progress }}%; transition: width 0.3s ease;"></div>
                                            
                                            <!-- Projekt-Name auf Balken -->
                                            <div style="position: relative; z-index: 2; color: white; font-size: 11px; font-weight: 600; text-shadow: 0 1px 2px rgba(0,0,0,0.3);">
                                                {{ $project->name }}
                                            </div>
                                            
                                            <!-- Fortschritt-Text -->
                                            <div style="position: relative; z-index: 2; color: white; font-size: 10px; font-weight: 500; text-shadow: 0 1px 2px rgba(0,0,0,0.3);">
                                                {{ round($project->progress) }}%
                                            </div>
                                        </div>
                                        
                                        <!-- Meilensteine -->
                                        @php
                                            $milestones = [
                                                25 => ['color' => '#8b5cf6', 'label' => '25%'],
                                                50 => ['color' => '#06b6d4', 'label' => '50%'],
                                                75 => ['color' => '#f59e0b', 'label' => '75%'],
                                                100 => ['color' => '#10b981', 'label' => '100%']
                                            ];
                            @endphp
                                        
                                        @foreach($milestones as $milestone => $config)
                                            @if($project->progress >= $milestone)
                                                <div style="position: absolute; top: 20%; right: 8px; width: 12px; height: 12px; background: {{ $config['color'] }}; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 0 2px {{ $config['color'] }}, 0 3px 6px rgba(0,0,0,0.2);" 
                                                     title="Meilenstein {{ $config['label'] }} erreicht"></div>
                                            @endif
                                        @endforeach
                                        
                                        <!-- Kritischer Pfad Indikator -->
                                        @if($project->status == 'active' && $project->progress < 50 && \Carbon\Carbon::parse($project->end_date)->diffInDays(now()) < 30)
                                            <div style="position: absolute; top: 5%; left: 8px; width: 10px; height: 10px; background: #dc2626; border-radius: 50%; box-shadow: 0 0 0 3px white, 0 3px 6px rgba(220,38,38,0.4);" 
                                                 title="Kritischer Pfad - Dringend"></div>
                                        @endif
                                    @elseif($index > $projectStartIndex && $index < $projectEndIndex)
                                        <!-- Projekt-Balken Fortsetzung -->
                                        <div style="position: absolute; top: 50%; left: 0; right: 0; height: 20px; background: {{ $project->status == 'active' ? '#10b981' : ($project->status == 'planning' ? '#3b82f6' : '#6b7280') }}; transform: translateY(-50%); border-radius: 0; box-shadow: 0 3px 6px rgba(0,0,0,0.15);">
                                            <div style="position: absolute; top: 0; left: 0; height: 100%; background: {{ $project->progress >= 100 ? '#059669' : ($project->progress >= 75 ? '#f59e0b' : '#3b82f6') }}; width: {{ $project->progress }}%; transition: width 0.3s ease;"></div>
                                        </div>
                                    @elseif($index == $projectEndIndex)
                                        <!-- Projekt-Balken Ende -->
                                        <div style="position: absolute; top: 50%; left: 0; right: 0; height: 20px; background: {{ $project->status == 'active' ? '#10b981' : ($project->status == 'planning' ? '#3b82f6' : '#6b7280') }}; transform: translateY(-50%); border-radius: 10px; box-shadow: 0 3px 6px rgba(0,0,0,0.15);">
                                            <div style="position: absolute; top: 0; left: 0; height: 100%; background: {{ $project->progress >= 100 ? '#059669' : ($project->progress >= 75 ? '#f59e0b' : '#3b82f6') }}; width: {{ $project->progress }}%; border-radius: 10px; transition: width 0.3s ease;"></div>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endforeach

            @else
                <div style="text-align: center; padding: 60px 20px; color: #6b7280;">
                    <div style="font-size: 48px; margin-bottom: 16px;">📊</div>
                    <h3 style="font-size: 18px; font-weight: 500; color: #111827; margin: 0 0 8px 0;">Keine Projekte</h3>
                    <p style="margin: 0 0 24px 0;">Erstellen Sie Projekte, um das Gantt-Diagramm zu sehen.</p>
                    <a href="{{ route('projects.create') }}" style="background: #2563eb; color: white; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500;">
                        ➕ Erstes Projekt erstellen
                    </a>
                </div>
            @endif
            </div>
        </div>
    </div>

    <!-- Legend under chart inside main container -->
    <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px 20px; margin-top: 20px;">
        <div>
            <h4 style="font-size: 14px; font-weight: 600; color: #374151; margin: 0 0 12px 0;">Legende</h4>
            <div style="display: flex; gap: 24px; flex-wrap: wrap; margin-bottom: 12px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 14px; height: 14px; background: #059669; border-radius: 3px;"></div>
                    <span style="font-size: 12px; color: #374151; font-weight: 500;">Aktiv</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 14px; height: 14px; background: #2563eb; border-radius: 3px;"></div>
                    <span style="font-size: 12px; color: #374151; font-weight: 500;">Geplant</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 14px; height: 14px; background: #d97706; border-radius: 3px;"></div>
                    <span style="font-size: 12px; color: #374151; font-weight: 500;">Fortschritt</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 14px; height: 14px; background: #dc2626; border-radius: 3px;"></div>
                    <span style="font-size: 12px; color: #374151; font-weight: 500;">Überfällig</span>
                </div>
            </div>
            <div style="display: flex; gap: 24px; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 10px; height: 10px; background: #8b5cf6; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 0 1px #8b5cf6;"></div>
                    <span style="font-size: 12px; color: #374151;">Meilensteine</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 8px; height: 8px; background: #dc2626; border-radius: 50%; box-shadow: 0 0 0 2px white;"></div>
                    <span style="font-size: 12px; color: #374151;">Kritisch</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 16px; height: 16px; background: #f59e0b; color: white; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 500;">2</div>
                    <span style="font-size: 12px; color: #374151;">Abwesenheiten</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let ganttScale = 1;
const MIN_SCALE = 0.6;
const MAX_SCALE = 2.0;
const SCALE_STEP = 0.1;

function applyZoom() {
    const content = document.getElementById('ganttContent');
    if (!content) return;
    content.style.transform = `scale(${ganttScale})`;
}

function zoomIn() {
    ganttScale = Math.min(MAX_SCALE, +(ganttScale + SCALE_STEP).toFixed(2));
    applyZoom();
}

function zoomOut() {
    ganttScale = Math.max(MIN_SCALE, +(ganttScale - SCALE_STEP).toFixed(2));
    applyZoom();
}

// Timeline View Change
function changeTimelineView(view) {
    const currentUrl = new URL(window.location);
    currentUrl.searchParams.set('view', view);
    window.location.href = currentUrl.toString();
}

// Date Range Application
function applyDateRange() {
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    
    if (!dateFrom || !dateTo) {
        alert('Bitte wählen Sie sowohl ein Start- als auch ein Enddatum aus.');
        return;
    }
    
    if (new Date(dateFrom) > new Date(dateTo)) {
        alert('Das Startdatum darf nicht nach dem Enddatum liegen.');
        return;
    }
    
    const currentUrl = new URL(window.location);
    currentUrl.searchParams.set('date_from', dateFrom);
    currentUrl.searchParams.set('date_to', dateTo);
    window.location.href = currentUrl.toString();
}

// Hamburger Menu Toggle
function toggleMenu() {
    const menu = document.getElementById('hamburgerMenu');
    if (menu.style.display === 'none' || menu.style.display === '') {
        menu.style.display = 'block';
    } else {
        menu.style.display = 'none';
    }
}

// Close menu when clicking outside
document.addEventListener('click', function(event) {
    const menu = document.getElementById('hamburgerMenu');
    const button = event.target.closest('button[onclick="toggleMenu()"]');
    if (!button && !menu.contains(event.target)) {
        menu.style.display = 'none';
    }
});

// Project Tooltip Functions
function showProjectTooltip(element, name, description, responsible, teamSize, progress, startDate, endDate) {
    const tooltip = document.createElement('div');
    tooltip.id = 'projectTooltip';
    tooltip.style.cssText = `
        position: fixed;
        background: #1f2937;
        color: white;
        padding: 12px 16px;
        border-radius: 8px;
        font-size: 12px;
        z-index: 10000;
        max-width: 300px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        pointer-events: none;
    `;
    
    tooltip.innerHTML = `
        <div style="font-weight: 600; margin-bottom: 8px; color: #f3f4f6;">${name}</div>
        <div style="margin-bottom: 6px; color: #d1d5db;">${description}</div>
        <div style="margin-bottom: 4px;"><strong>Verantwortlich:</strong> ${responsible}</div>
        <div style="margin-bottom: 4px;"><strong>Team:</strong> ${teamSize} Mitarbeiter</div>
        <div style="margin-bottom: 4px;"><strong>Fortschritt:</strong> ${progress}%</div>
        <div><strong>Zeitraum:</strong> ${startDate} - ${endDate}</div>
    `;
    
    document.body.appendChild(tooltip);
    
    const rect = element.getBoundingClientRect();
    tooltip.style.left = (rect.right + 10) + 'px';
    tooltip.style.top = (rect.top + window.scrollY) + 'px';
}

function hideProjectTooltip() {
    const tooltip = document.getElementById('projectTooltip');
    if (tooltip) {
        tooltip.remove();
    }
}


</script>
@endsection