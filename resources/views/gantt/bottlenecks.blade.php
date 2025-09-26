@extends('layouts.app')

@section('title', 'Engpass- und Risiko-Analyse')

@section('content')
<div style="width: 100%; margin: 0; padding: 20px;">
    <!-- Page Header -->
    <div style="background: white; padding: 20px; margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="font-size: 24px; font-weight: bold; color: #111827; margin: 0;">Abwesenheits-Analyse</h1>
                <p style="color: #6b7280; margin: 5px 0 0 0;">Detaillierte Analyse von Mitarbeiterabwesenheiten während Projektlaufzeiten</p>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="{{ route('gantt.index') }}" style="background: #ffffff; color: #374151; padding: 10px 20px; border-radius: 12px; text-decoration: none; font-size: 14px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">📊 Gantt-Diagramm</a>
                <a href="{{ route('gantt.export') }}" style="background: #ffffff; color: #374151; padding: 10px 20px; border-radius: 12px; text-decoration: none; font-size: 14px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">📊 Excel Export</a>
            </div>
        </div>
    </div>

    <!-- Statistiken -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div style="background: white; padding: 20px; border: 1px solid #e5e7eb; border-radius: 8px; text-align: center;">
            <div style="font-size: 32px; font-weight: bold; color: #111827; margin-bottom: 5px;">{{ $stats['total_projects'] }}</div>
            <div style="color: #6b7280; font-size: 14px;">Gesamt Projekte</div>
        </div>
        <div style="background: white; padding: 20px; border: 1px solid #e5e7eb; border-radius: 8px; text-align: center;">
            <div style="font-size: 32px; font-weight: bold; color: #f59e0b; margin-bottom: 5px;">{{ $stats['projects_with_absences'] }}</div>
            <div style="color: #6b7280; font-size: 14px;">Mit Abwesenheiten</div>
        </div>
        <div style="background: white; padding: 20px; border: 1px solid #e5e7eb; border-radius: 8px; text-align: center;">
            <div style="font-size: 32px; font-weight: bold; color: #3b82f6; margin-bottom: 5px;">{{ $stats['total_affected_employees'] }}</div>
            <div style="color: #6b7280; font-size: 14px;">Betroffene Mitarbeiter</div>
        </div>
        <div style="background: white; padding: 20px; border: 1px solid #e5e7eb; border-radius: 8px; text-align: center;">
            <div style="font-size: 32px; font-weight: bold; color: #dc2626; margin-bottom: 5px;">{{ $stats['total_absence_days'] }}</div>
            <div style="color: #6b7280; font-size: 14px;">Abwesenheitstage</div>
        </div>
    </div>

    @if(count($absenceWarnings) > 0)
        <!-- Abwesenheiten -->
        <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; margin-bottom: 30px; overflow: hidden;">
            <div style="background: #fef3c7; padding: 20px; border-bottom: 1px solid #f59e0b;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 24px;">🏖️</span>
                    <h2 style="font-size: 20px; font-weight: 600; color: #92400e; margin: 0;">Projekte mit Abwesenheiten ({{ count($absenceWarnings) }})</h2>
                </div>
            </div>
            
            <div style="padding: 20px;">
                @foreach($absenceWarnings as $warning)
                    <div style="background: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <div>
                                <h3 style="font-size: 18px; font-weight: 600; color: #92400e; margin: 0 0 5px 0;">{{ $warning['project']->name }}</h3>
                                <p style="color: #6b7280; margin: 0; font-size: 14px;">
                                    {{ $warning['project']->start_date }} - {{ $warning['project']->end_date }} • 
                                    Fortschritt: {{ round($warning['project']->progress) }}% • 
                                    @if($warning['project']->responsible)
                                        Verantwortlich: {{ $warning['project']->responsible->first_name }} {{ $warning['project']->responsible->last_name }}
                                    @else
                                        Kein Verantwortlicher
                                    @endif
                                </p>
                            </div>
                            <div style="display: flex; gap: 10px;">
                                <span style="background: #f59e0b; color: white; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 500;">
                                    {{ $warning['total_affected_employees'] }} Mitarbeiter
                                </span>
                                <a href="{{ route('projects.show', $warning['project']->id) }}" style="background: #3b82f6; color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 500;">Anzeigen</a>
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                            @foreach($warning['absences'] as $absenceInfo)
                                <div style="background: white; padding: 15px; border-radius: 6px; border: 1px solid #e5e7eb;">
                                    <h4 style="font-size: 14px; font-weight: 600; color: #92400e; margin: 0 0 10px 0;">👤 {{ $absenceInfo['employee']->first_name }} {{ $absenceInfo['employee']->last_name }}</h4>
                                    <div style="font-size: 12px; color: #6b7280; margin-bottom: 10px;">
                                        Zuweisung: {{ $absenceInfo['assignment']->weekly_hours }}h/Woche • 
                                        Gesamte Abwesenheitstage: {{ $absenceInfo['total_days'] }}
                                    </div>
                                    @foreach($absenceInfo['absences'] as $absence)
                                        <div style="background: #fef3c7; padding: 10px; border-radius: 4px; margin-bottom: 8px;">
                                            <div style="font-weight: 500; color: #111827;">{{ $absence->type }}</div>
                                            <div style="font-size: 12px; color: #6b7280; margin-top: 2px;">
                                                {{ $absence->start_date }} - {{ $absence->end_date }}
                                            </div>
                                            @if($absence->reason)
                                                <div style="font-size: 12px; color: #6b7280; margin-top: 2px; font-style: italic;">
                                                    Grund: {{ $absence->reason }}
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 60px 20px; text-align: center;">
            <div style="font-size: 48px; margin-bottom: 16px;">✅</div>
            <h3 style="font-size: 18px; font-weight: 500; color: #111827; margin: 0 0 8px 0;">Keine Abwesenheiten während Projektlaufzeiten</h3>
            <p style="margin: 0 0 24px 0; color: #6b7280;">Alle Projekte laufen ohne Mitarbeiterabwesenheiten.</p>
            <a href="{{ route('gantt.index') }}" style="background: #2563eb; color: white; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500;">
                📊 Zum Gantt-Diagramm
            </a>
        </div>
    @endif
</div>
@endsection
