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
                @php
                    // Engpass-Übersicht vorbereiten (Top 5 nach größtem Defizit)
                    $bnList = [];
                    foreach ($projects as $p) {
                        $pStart = \Carbon\Carbon::parse($p->start_date);
                        $pEnd = \Carbon\Carbon::parse($p->end_date);
                        $req = \DB::table('assignments')->where('project_id', $p->id)->sum('weekly_hours');
                        $empIds = \DB::table('assignments')->where('project_id', $p->id)->pluck('employee_id');
                        $avail = 0; $absHit = false;
                        foreach ($empIds as $eid) {
                            $emp = \DB::table('employees')->where('id', $eid)->first(); if (!$emp) continue;
                            $cap = (float)($emp->weekly_capacity ?? 40);
                            $abs = \DB::table('absences')->where('employee_id', $eid)
                                ->where('start_date', '<=', $pEnd->format('Y-m-d'))
                                ->where('end_date', '>=', $pStart->format('Y-m-d'))
                                ->first();
                            if ($abs) { $cap *= 0.5; $absHit = true; }
                            $avail += $cap;
                        }
                        $def = max(0, (int)round($req - $avail));
                        if ($def > 0) {
                            $bnList[] = [
                                'project' => $p,
                                'deficit' => $def,
                                'required' => (int)round($req),
                                'available' => (int)round($avail),
                                'hasAbsence' => $absHit,
                            ];
                        }
                    }
                    usort($bnList, fn($a,$b) => $b['deficit'] <=> $a['deficit']);
                    $bnTop = array_slice($bnList, 0, 5);
                @endphp

                @if(count($bnTop) > 0)
                <div style="border:1px solid #fecaca; background:#fef2f2; padding:12px 16px; border-radius:8px; margin-bottom:16px;">
                    <div style="display:flex; align-items:center; gap:8px; color:#991b1b; font-weight:600; margin-bottom:6px;">
                        ⚠ Engpass-Übersicht (Top {{ count($bnTop) }})
                    </div>
                    <ul style="list-style:none; padding:0; margin:0; display:grid; gap:6px;">
                        @foreach($bnTop as $bn)
                        <li style="display:flex; justify-content:space-between; align-items:center; background:#fff; border:1px dashed #fecaca; border-radius:6px; padding:8px 10px;">
                            <div style="display:flex; flex-wrap:wrap; gap:8px; align-items:center;">
                                <span style="font-weight:600; color:#111827;">{{ $bn['project']->name }}</span>
                                <span style="font-size:12px; color:#6b7280;">({{ \Carbon\Carbon::parse($bn['project']->start_date)->format('d.m.') }}–{{ \Carbon\Carbon::parse($bn['project']->end_date)->format('d.m.') }})</span>
                                @if($bn['hasAbsence'])
                                    <span style="font-size:12px; color:#b91c1c;">Abwesenheit wirkt sich aus</span>
                                @endif
                            </div>
                            <div style="font-size:12px; color:#991b1b;">
                                Defizit: <strong>{{ $bn['deficit'] }}h/W</strong>
                                <span style="color:#6b7280;">(Bedarf {{ $bn['required'] }} • Verfügbar {{ $bn['available'] }})</span>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif
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

                        // Bottleneck-Heuristik: Summe der wöchentlichen benötigten Stunden im Projekt
                        $requiredPerWeek = \DB::table('assignments')
                            ->where('project_id', $project->id)
                            ->sum('weekly_hours');

                        // Verfügbare Kapazität der zugewiesenen Mitarbeiter (unter Abzug von Abwesenheiten in Projektzeitraum)
                        $employeeIds = \DB::table('assignments')->where('project_id', $project->id)->pluck('employee_id');
                        $availablePerWeek = 0;
                        foreach ($employeeIds as $eid) {
                            $emp = \DB::table('employees')->where('id', $eid)->first();
                            if (!$emp) continue;
                            $capacity = (float)($emp->weekly_capacity ?? 40);
                            // Wenn Mitarbeiter innerhalb des Projektzeitraums abwesend ist, reduziere grob um 50% (vereinfachte Annahme)
                            $absence = \DB::table('absences')
                                ->where('employee_id', $eid)
                                ->where('start_date', '<=', $endDate->format('Y-m-d'))
                                ->where('end_date', '>=', $startDate->format('Y-m-d'))
                                ->first();
                            if ($absence) {
                                $capacity *= 0.5;
                            }
                            $availablePerWeek += $capacity;
                        }

                        $bottleneck = $requiredPerWeek > $availablePerWeek && $availablePerWeek > 0;
                        $bnColor = $bottleneck ? '#ef4444' : ($project->status == 'active' ? '#10b981' : ($project->status == 'planning' ? '#3b82f6' : '#6b7280'));
                    @endphp
                    <div style="display: grid; grid-template-columns: 260px repeat(12, 1fr); gap: 1px; margin-bottom: 1px;">
                        <!-- Project Name -->
                        <div style="background: white; padding: 12px; border: 1px solid #e5e7eb; display: flex; align-items: center;">
                            <div style="width: 8px; height: 8px; background: {{ $project->status == 'active' ? '#10b981' : ($project->status == 'planning' ? '#3b82f6' : '#6b7280') }}; border-radius: 50%; margin-right: 8px;"></div>
                            <div>
                                <div style="font-weight: 500; color: #111827; font-size: 14px;">{{ $project->name }}</div>
                                <div style="color: #6b7280; font-size: 12px;">{{ round($project->progress) }}%</div>
                                @if($bottleneck)
                                <div style="color:#b91c1c; font-size: 12px; display:flex; align-items:center; gap:6px;">
                                    ⚠ Engpass: Bedarf {{ (int)$requiredPerWeek }}h/W  • Verfügbar {{ (int)$availablePerWeek }}h/W
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Timeline Cells -->
                        @for($i = 0; $i < 12; $i++)
                            @php
                                $isInRange = $i >= $startMonth && $i < ($startMonth + $durationMonths);
                                $isCurrent = $i == $startMonth;
                            @endphp
                            <div style="background: {{ $isInRange ? ($bottleneck ? '#fee2e2' : ($project->status == 'active' ? '#dcfce7' : ($project->status == 'planning' ? '#dbeafe' : '#f3f4f6'))) : 'white' }}; border: 1px solid #e5e7eb; position: relative; min-height: 40px;">
                                @if($isInRange)
                                    <div style="position: absolute; top: 50%; left: 0; right: 0; height: 8px; background: {{ $bnColor }}; transform: translateY(-50%); border-radius: 4px;"></div>
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
                            <div style="width: 12px; height: 12px; background: #ef4444; border-radius: 2px;"></div>
                            <span style="font-size: 12px; color: #374151;">Engpass (Kapazität < Bedarf)</span>
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