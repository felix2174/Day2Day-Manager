@extends('layouts.app')

@section('title', 'Gantt-Diagramm')

@section('content')
<div style="width: 100%; margin: 0; padding: 20px;">
    <!-- Page Header -->
    <div style="background: white; padding: 20px; margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="font-size: 24px; font-weight: bold; color: #111827; margin: 0;">Gantt-Diagramm</h1>
                <p style="color: #6b7280; margin: 5px 0 0 0;">Zeitliche Übersicht aller Projekte und deren Fortschritt</p>
                <div style="display: flex; gap: 20px; margin-top: 10px;">
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
            <div>
                <a href="{{ route('gantt.export') }}" style="background: #ffffff; color: #374151; padding: 10px 20px; border-radius: 12px; text-decoration: none; font-size: 14px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 8px;">
                    📊 Excel Export
                </a>
            </div>
        </div>
    </div>

    <!-- Gantt Chart Container -->
    <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
        <!-- Chart Header -->
        <div style="background: #f9fafb; padding: 20px; border-bottom: 1px solid #e5e7eb;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0;">Projekt-Timeline</h3>
                <div style="display: flex; gap: 8px;">
                    <button onclick="zoomIn()" style="background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 4px; padding: 6px 12px; font-size: 12px; cursor: pointer;">🔍+</button>
                    <button onclick="zoomOut()" style="background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 4px; padding: 6px 12px; font-size: 12px; cursor: pointer;">🔍-</button>
                </div>
            </div>
        </div>

        <!-- Chart Content -->
        <div style="padding: 20px; overflow-x: hidden;">
            @if($projects->count() > 0)
                <!-- Timeline Header -->
                <div style="display: grid; grid-template-columns: 260px repeat(12, 1fr); gap: 1px; margin-bottom: 1px;">
                    <div style="background: #f3f4f6; padding: 12px; font-weight: 600; color: #374151; border: 1px solid #e5e7eb;">Projekt</div>
                    @for($i = 0; $i < 12; $i++)
                        @php
                            $month = now()->addMonths($i);
                        @endphp
                        <div style="background: #f3f4f6; padding: 12px; text-align: center; font-weight: 600; color: #374151; border: 1px solid #e5e7eb; font-size: 12px;">
                            {{ $month->format('M Y') }}
                        </div>
                    @endfor
                </div>

                <!-- Project Rows -->
                @foreach($projects as $project)
                    @php
                        $startDate = \Carbon\Carbon::parse($project->start_date);
                        $endDate = \Carbon\Carbon::parse($project->end_date);
                        $startMonth = $startDate->diffInMonths(now());
                        $durationMonths = $startDate->diffInMonths($endDate) + 1;
                    @endphp
                    <div style="display: grid; grid-template-columns: 260px repeat(12, 1fr); gap: 1px; margin-bottom: 1px;">
                        <!-- Project Name -->
                        <div style="background: white; padding: 12px; border: 1px solid #e5e7eb; display: flex; align-items: center;">
                            <div style="width: 8px; height: 8px; background: {{ $project->status == 'active' ? '#10b981' : ($project->status == 'planning' ? '#3b82f6' : '#6b7280') }}; border-radius: 50%; margin-right: 8px;"></div>
                            <div>
                                <div style="font-weight: 500; color: #111827; font-size: 14px;">{{ $project->name }}</div>
                                <div style="color: #6b7280; font-size: 12px;">{{ round($project->progress) }}%</div>
                            </div>
                        </div>

                        <!-- Timeline Cells -->
                        @for($i = 0; $i < 12; $i++)
                            @php
                                $isInRange = $i >= $startMonth && $i < ($startMonth + $durationMonths);
                                $isCurrent = $i == $startMonth;
                            @endphp
                            <div style="background: {{ $isInRange ? ($project->status == 'active' ? '#dcfce7' : ($project->status == 'planning' ? '#dbeafe' : '#f3f4f6')) : 'white' }}; border: 1px solid #e5e7eb; position: relative; min-height: 40px;">
                                @if($isInRange)
                                    <div style="position: absolute; top: 50%; left: 0; right: 0; height: 8px; background: {{ $project->status == 'active' ? '#10b981' : ($project->status == 'planning' ? '#3b82f6' : '#6b7280') }}; transform: translateY(-50%); border-radius: 4px;"></div>
                                    @if($isCurrent)
                                        <div style="position: absolute; top: 50%; left: 0; right: 0; height: 8px; background: #f59e0b; transform: translateY(-50%); border-radius: 4px; width: {{ $project->progress }}%;"></div>
                                    @endif
                                @endif
                            </div>
                        @endfor
                    </div>
                @endforeach

                <!-- Legend -->
                <div style="margin-top: 20px; padding: 16px; background: #f9fafb; border-radius: 6px;">
                    <h4 style="font-size: 14px; font-weight: 600; color: #374151; margin: 0 0 12px 0;">Legende</h4>
                    <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="width: 12px; height: 12px; background: #10b981; border-radius: 2px;"></div>
                            <span style="font-size: 12px; color: #374151;">Aktive Projekte</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="width: 12px; height: 12px; background: #3b82f6; border-radius: 2px;"></div>
                            <span style="font-size: 12px; color: #374151;">Geplante Projekte</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="width: 12px; height: 12px; background: #f59e0b; border-radius: 2px;"></div>
                            <span style="font-size: 12px; color: #374151;">Aktueller Fortschritt</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="width: 12px; height: 12px; background: #6b7280; border-radius: 2px;"></div>
                            <span style="font-size: 12px; color: #374151;">Abgeschlossene Projekte</span>
                        </div>
                    </div>
                </div>
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

<script>
function zoomIn() {
    // Zoom functionality would be implemented here
    console.log('Zoom in');
}

function zoomOut() {
    // Zoom functionality would be implemented here
    console.log('Zoom out');
}
</script>
@endsection