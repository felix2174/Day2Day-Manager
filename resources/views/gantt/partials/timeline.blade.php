{{-- Timeline, Projektzeilen und Legende --}}
<div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
    <div style="padding: 20px;">
        @if($projects->count() > 0)
            <div id="bottleneckOverview" style="margin-bottom: 16px;"></div>
            <div id="ganttTooltip" style="display: none; position: fixed; background: white; border: 2px solid #3b82f6; border-radius: 8px; padding: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 1000; max-width: 300px; pointer-events: none;">
                <div id="tooltipContent"></div>
            </div></div>

            <div style="overflow-x: auto; padding-bottom: 10px;">
            <div id="ganttScrollContainer" style="position: relative; display: inline-block; overflow-y: hidden; cursor: grab; user-select: none; border: 1px solid #e5e7eb; border-radius: 8px; margin-bottom: 20px; max-width: calc(100vw - 420px);" class="gantt-scroll-container">
                <div id="quickActionsDropdown" style="display: none; position: absolute; background: white; border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 1001; min-width: 180px;"></div>

                <div id="ganttContent" style="min-width: fit-content;">
                    <div id="timelineHeader" style="display: grid; grid-template-columns: 260px repeat({{ count($timelineMonths) }}, {{ $columnWidth }}px); gap: 1px; margin-bottom: 1px; position: relative;">
                        <div style="background: #f3f4f6; padding: 12px; font-weight: 600; color: #374151; border: 1px solid #e5e7eb; position: sticky; left: 0; z-index: 10;">Projekt</div>
                        @foreach($timelineMonths as $index => $monthData)
                            <div data-period-index="{{ $index }}" data-is-current-period="{{ $monthData['is_current'] ? 'true' : 'false' }}" style="background: {{ $monthData['is_current'] ? '#dbeafe' : '#f3f4f6' }}; padding: {{ $timelineUnit === 'week' ? '8px 6px' : '12px' }}; text-align: center; font-weight: 600; color: {{ $monthData['is_current'] ? '#1e40af' : '#374151' }}; border: 1px solid {{ $monthData['is_current'] ? '#3b82f6' : '#e5e7eb' }}; font-size: {{ $timelineUnit === 'week' ? '11px' : '12px' }}; position: relative; min-width: {{ $columnWidth }}px;">
                                {{ $monthData['label'] }}
                            </div>
                        @endforeach
                    </div>

                    @foreach($projects as $project)
                        @php
                            $metrics = $projectMetrics[$project->id] ?? null;
                        @endphp
                        @if (!$metrics)
                            @continue
                        @endif

                        <x-gantt.project-row 
                            :project="$project"
                            :metrics="$metrics"
                            :projectTeams="$projectTeams"
                            :projectAbsences="$projectAbsences"
                            :projectAbsenceDetails="$projectAbsenceDetails"
                            :allAssignments="$allAssignments"
                            :timelineMonths="$timelineMonths"
                            :columnWidth="$columnWidth"
                            :timelineUnit="$timelineUnit"
                        />
                    @endforeach
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
                        <div style="width: 12px; height: 12px; background: #ef4444; border-radius: 2px;"></div>
                        <span style="font-size: 12px; color: #374151;">Engpass (Kapazität &lt; Bedarf)</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 12px; height: 12px; background: #f59e0b; border-radius: 2px;"></div>
                        <span style="font-size: 12px; color: #374151;">Aktueller Fortschritt</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 12px; height: 12px; background: #6b7280; border-radius: 2px;"></div>
                        <span style="font-size: 12px; color: #374151;">Abgeschlossene Projekte</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 2px; height: 16px; background: linear-gradient(to bottom, #3b82f6, #60a5fa);"></div>
                        <span style="font-size: 12px; color: #374151; font-weight: 600;">Aktueller Monat</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 6px; height: 6px; background: #a855f7; border-radius: 50%;"></div>
                        <span style="font-size: 12px; color: #374151;">Laufendes Projekt (seit MOCO created_at, ohne Enddatum)</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 12px; height: 12px; background: #eff6ff; border: 1px solid #1e40af; border-radius: 2px;"></div>
                        <span style="font-size: 12px; color: #374151;">Abwesenheiten im Projektzeitraum</span>
                    </div>
                </div>
            </div>
        @else
            <div style="text-align: center; padding: 60px 20px; color: #6b7280;">
                <h3 style="font-size: 18px; font-weight: 500; color: #111827; margin: 0 0 8px 0;">Keine Projekte</h3>
                <p style="margin: 0 0 24px 0;">Erstellen Sie Projekte, um das Gantt-Diagramm zu sehen.</p>
                <a href="{{ route('projects.create') }}" style="background: #2563eb; color: white; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500;">
                    Erstes Projekt erstellen
                </a>
            </div>
        @endif
    </div>
</div>

