@extends('layouts.app')

@section('title', 'Abwesenheit anzeigen')

@section('content')
<div style="width: 100%; margin: 0; padding: 0;">
    <div style="background: white; padding: 20px; margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="font-size: 24px; font-weight: bold; color: #111827; margin: 0;">Abwesenheit</h1>
                <p style="color: #6b7280; margin: 5px 0 0 0;">Details zur Abwesenheit</p>
            </div>
            <a href="{{ route('absences.index') }}" style="background: #ffffff; color: #374151; padding: 10px 20px; border-radius: 12px; text-decoration: none; font-size: 14px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">Zurück</a>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px;">
        <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px;">
            <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: .05em;">Mitarbeiter</div>
            <div style="font-size: 16px; font-weight: 600; color: #111827;">{{ $absence->employee->first_name }} {{ $absence->employee->last_name }}</div>
            <div style="font-size: 13px; color: #6b7280;">{{ $absence->employee->department }}</div>
        </div>
        <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px;">
            <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: .05em;">Typ</div>
            @php
                $typeLabel = $absence->type == 'vacation' ? 'Urlaub' : ($absence->type == 'sick' ? 'Krank' : ($absence->type == 'personal' ? 'Persönlich' : 'Sonstiges'));
                $bg = $absence->type == 'vacation' ? '#dbeafe' : ($absence->type == 'sick' ? '#fee2e2' : ($absence->type == 'personal' ? '#fef3c7' : '#e0e7ff'));
                $fg = $absence->type == 'vacation' ? '#1e40af' : ($absence->type == 'sick' ? '#dc2626' : ($absence->type == 'personal' ? '#d97706' : '#3730a3'));
            @endphp
            <span style="background: {{ $bg }}; color: {{ $fg }}; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500;">{{ $typeLabel }}</span>
        </div>
        <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px;">
            <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: .05em;">Zeitraum</div>
            <div style="font-size: 16px; font-weight: 600; color: #111827;">{{ $start->format('d.m.Y') }} - {{ $end->format('d.m.Y') }}</div>
        </div>
        <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px;">
            <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: .05em;">Dauer</div>
            <div style="font-size: 16px; font-weight: 600; color: #111827;">{{ $durationDays }} Tage</div>
        </div>
    </div>

    <div style="margin-top: 16px; display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px;">
        <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px;">
            <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: .05em;">Status</div>
            <span style="background: {{ $isActive ? '#dcfce7' : ($isUpcoming ? '#dbeafe' : '#f3f4f6') }}; color: {{ $isActive ? '#166534' : ($isUpcoming ? '#1e40af' : '#374151') }}; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                {{ $isActive ? 'Läuft' : ($isUpcoming ? 'Geplant' : 'Abgeschlossen') }}
            </span>
            @if($isActive || $isUpcoming)
                <span style="margin-left: 8px; background:#eef2ff; color:#3730a3; padding: 4px 8px; border-radius: 12px; font-size:12px; font-weight:500;">{{ $remainingDays }} Tage verbleibend</span>
            @endif
        </div>
        <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px;">
            <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: .05em;">Grund</div>
            <div style="font-size: 16px; font-weight: 600; color: #111827;">{{ $absence->reason ?? 'Kein Grund angegeben' }}</div>
        </div>
    </div>
</div>
@endsection



