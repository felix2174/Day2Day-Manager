@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="card">
        <h2>Mitarbeiter-Kapazitäten</h2>
        <p style="color: #6c757d;">Wochenübersicht - KW {{ \Carbon\Carbon::now()->weekOfYear }}</p>

        <div style="margin-top: 20px;">
            @foreach($employees as $employee)
                @php
                    $totalHours = $employee->assignments->sum('weekly_hours');
                    $freeHours = $employee->weekly_capacity - $totalHours;
                    $percentage = ($totalHours / $employee->weekly_capacity) * 100;
                @endphp

                <div style="margin-bottom: 25px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h3 style="margin: 0;">
                            {{ $employee->first_name }} {{ $employee->last_name }}
                            <small style="color: #6c757d;">({{ $employee->department }})</small>
                        </h3>
                        <div style="text-align: right;">
                            <span style="font-size: 24px; font-weight: bold; color: {{ $freeHours > 0 ? '#28a745' : '#dc3545' }};">
                                {{ $freeHours }}h frei
                            </span>
                            <br>
                            <small style="color: #6c757d;">
                                {{ $totalHours }}h von {{ $employee->weekly_capacity }}h verplant
                            </small>
                        </div>
                    </div>

                    <div style="margin: 15px 0;">
                        <div style="background: #e9ecef; border-radius: 4px; height: 30px; position: relative;">
                            <div style="background: {{ $percentage > 90 ? '#dc3545' : ($percentage > 70 ? '#ffc107' : '#28a745') }};
                                        width: {{ min($percentage, 100) }}%;
                                        height: 100%;
                                        border-radius: 4px;
                                        display: flex;
                                        align-items: center;
                                        justify-content: center;
                                        color: white;
                                        font-weight: bold;">
                                {{ round($percentage) }}%
                            </div>
                        </div>
                    </div>

                    @if($employee->assignments->count() > 0)
                        <div style="border-top: 1px solid #dee2e6; padding-top: 10px; margin-top: 10px;">
                            <small style="color: #6c757d;">Aktuelle Projekte:</small>
                            <div style="margin-top: 5px;">
                                @foreach($employee->assignments as $assignment)
                                    <span style="display: inline-block; margin: 3px 5px 3px 0; padding: 5px 10px;
                                               background: white; border: 1px solid #dee2e6; border-radius: 4px; font-size: 14px;">
                                        {{ $assignment->project->name }}
                                        <strong>({{ $assignment->weekly_hours }}h/Woche)</strong>
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div style="color: #6c757d; font-style: italic; margin-top: 10px;">
                            ✓ Vollständig verfügbar für neue Projekte
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <div class="card">
        <h2>Ressourcen-Übersicht</h2>
        <div style="display: flex; gap: 20px; margin-top: 20px;">
            <div style="flex: 1; padding: 20px; background: #e7f3ff; border-radius: 8px; text-align: center;">
                <div style="font-size: 48px; font-weight: bold; color: #667eea;">
                    {{ $employees->sum('weekly_capacity') }}h
                </div>
                <div>Gesamtkapazität/Woche</div>
            </div>
            <div style="flex: 1; padding: 20px; background: #fff3cd; border-radius: 8px; text-align: center;">
                <div style="font-size: 48px; font-weight: bold; color: #ffc107;">
                    {{ $employees->sum(function($e) { return $e->assignments->sum('weekly_hours'); }) }}h
                </div>
                <div>Bereits verplant</div>
            </div>
            <div style="flex: 1; padding: 20px; background: #d4edda; border-radius: 8px; text-align: center;">
                <div style="font-size: 48px; font-weight: bold; color: #28a745;">
                    {{ $employees->sum('weekly_capacity') - $employees->sum(function($e) { return $e->assignments->sum('weekly_hours'); }) }}h
                </div>
                <div>Noch verfügbar</div>
            </div>
        </div>
    </div>

    <div class="card">
        <h2>🚨 Abwesenheiten (Nächste 30 Tage)</h2>
        @if($upcomingAbsences->count() > 0)
            <div style="margin-top: 20px;">
                @foreach($upcomingAbsences as $absence)
                    @php
                        $startDate = \Carbon\Carbon::parse($absence->start_date);
                        $endDate = \Carbon\Carbon::parse($absence->end_date);
                        $days = $startDate->diffInDays($endDate) + 1;
                        $typeColor = match($absence->type) {
                            'urlaub' => '#17a2b8',
                            'krankheit' => '#dc3545',
                            'fortbildung' => '#28a745',
                            default => '#6c757d'
                        };
                    @endphp
                    <div style="margin-bottom: 10px; padding: 10px; background: #fff3cd; border-left: 4px solid {{ $typeColor }}; border-radius: 4px;">
                        <strong>{{ $absence->employee->first_name }} {{ $absence->employee->last_name }}</strong>
                        - {{ ucfirst($absence->type) }}
                        <br>
                        <small>
                            📅 {{ $startDate->format('d.m.Y') }} bis {{ $endDate->format('d.m.Y') }}
                            ({{ $days }} {{ $days == 1 ? 'Tag' : 'Tage' }})
                            @if($absence->reason)
                                - {{ $absence->reason }}
                            @endif
                        </small>
                    </div>
                @endforeach
            </div>
        @else
            <p style="color: #6c757d;">Keine Abwesenheiten in den nächsten 30 Tagen</p>
        @endif
    </div>
@endsection
