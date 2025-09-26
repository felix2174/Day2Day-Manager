@extends('layouts.app')

@section('title', 'Zuweisung anzeigen')

@section('content')
    <div style="width: 100%; margin: 0; padding: 0;">
        <div style="background: white; padding: 20px; margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1 style="font-size: 24px; font-weight: bold; color: #111827; margin: 0;">Zuweisung</h1>
                    <p style="color: #6b7280; margin: 5px 0 0 0;">Details zur Zuweisung</p>
                </div>
                <a href="{{ route('assignments.index') }}" style="background: #ffffff; color: #374151; padding: 10px 20px; border-radius: 12px; text-decoration: none; font-size: 14px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">Zurück</a>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px;">
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px;">
                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: .05em;">Mitarbeiter</div>
                <div style="font-size: 16px; font-weight: 600; color: #111827;">{{ $assignment->employee->first_name }} {{ $assignment->employee->last_name }}</div>
                <div style="font-size: 13px; color: #6b7280;">{{ $assignment->employee->department }}</div>
            </div>
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px;">
                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: .05em;">Projekt</div>
                <div style="font-size: 16px; font-weight: 600; color: #111827;">{{ $assignment->project->name }}</div>
                <div style="font-size: 13px; color: #6b7280;">{{ $assignment->project->description }}</div>
            </div>
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px;">
                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: .05em;">Wochenstunden</div>
                <div style="font-size: 16px; font-weight: 600; color: #111827;">{{ $assignment->weekly_hours }}h</div>
            </div>
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px;">
                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: .05em;">Zeitraum</div>
                <div style="font-size: 16px; font-weight: 600; color: #111827;">{{ $start->format('d.m.Y') }} - {{ $end->format('d.m.Y') }}</div>
            </div>
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px;">
                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: .05em;">Priorität</div>
                <div style="font-size: 16px; font-weight: 600; color: #111827; text-transform: capitalize;">{{ $assignment->priority_level }}</div>
            </div>
        </div>

        <div style="margin-top: 16px; display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px;">
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px;">
                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: .05em;">Status</div>
                <div>
                    <span style="background: {{ $isActive ? '#dcfce7' : ($isUpcoming ? '#dbeafe' : '#f3f4f6') }}; color: {{ $isActive ? '#166534' : ($isUpcoming ? '#1e40af' : '#374151') }}; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                        {{ $isActive ? 'Aktiv' : ($isUpcoming ? 'Geplant' : 'Abgeschlossen') }}
                    </span>
                    @if($isActive || $isUpcoming)
                        <span style="margin-left: 8px; background:#eef2ff; color:#3730a3; padding: 4px 8px; border-radius: 12px; font-size:12px; font-weight:500;">{{ $remainingDays }} Tage verbleibend</span>
                    @endif
                </div>
            </div>
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px;">
                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: .05em;">Kapazität Mitarbeiter</div>
                <div style="font-size: 16px; font-weight: 600; color: #111827;">{{ $usedHours }}h belegt / {{ $weeklyCapacity }}h • <span style="color: {{ $freeHours >= 0 ? '#166534' : '#dc2626' }}">{{ $freeHours }}h frei</span></div>
            </div>
        </div>

        @if($overlaps->count() > 0)
            <div style="margin-top: 16px; background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px;">
                <div style="font-size: 14px; font-weight: 600; color:#111827; margin-bottom: 8px;">Zeitliche Überschneidungen</div>
                <div style="display: grid; gap: 8px;">
                    @foreach($overlaps as $o)
                        <div style="display:flex; justify-content: space-between; align-items:center; padding: 8px 12px; background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px;">
                            <div style="font-weight:500; color:#111827;">{{ $o->project->name }}</div>
                            <div style="color:#6b7280;">{{ \Carbon\Carbon::parse($o->start_date)->format('d.m.Y') }} - {{ \Carbon\Carbon::parse($o->end_date)->format('d.m.Y') }} • {{ $o->weekly_hours }}h/Woche</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection


