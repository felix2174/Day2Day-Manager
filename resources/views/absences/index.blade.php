@extends('layouts.app')

@section('title', 'Abwesenheiten')

@section('content')
<div style="width: 100%; margin: 0; padding: 0;">
    <!-- Page Header -->
    <div style="background: white; padding: 20px; margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="font-size: 24px; font-weight: bold; color: #111827; margin: 0;">Abwesenheiten-Verwaltung</h1>
                <p style="color: #6b7280; margin: 5px 0 0 0;">Verwalten Sie Abwesenheiten und Urlaubszeiten Ihrer Mitarbeiter</p>
                <div style="display: flex; gap: 20px; margin-top: 10px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="color: #6b7280; font-size: 14px;">Gesamt:</span>
                        <span style="font-weight: 600; color: #111827;">{{ $absences->count() }}</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="color: #6b7280; font-size: 14px;">Aktiv:</span>
                        <span style="font-weight: 600; color: #059669;">{{ $absences->where('start_date', '<=', now())->where('end_date', '>=', now())->count() }}</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="color: #6b7280; font-size: 14px;">Geplant:</span>
                        <span style="font-weight: 600; color: #3b82f6;">{{ $absences->where('start_date', '>', now())->count() }}</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="color: #6b7280; font-size: 14px;">Beendet:</span>
                        <span style="font-weight: 600; color: #6b7280;">{{ $absences->where('end_date', '<', now())->count() }}</span>
                    </div>
                </div>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="{{ route('absences.export') }}" style="background: #ffffff; color: #374151; padding: 10px 20px; border-radius: 12px; text-decoration: none; font-size: 14px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 8px;">
                    📊 Excel Export
                </a>
                <a href="{{ route('absences.import') }}" style="background: #ffffff; color: #374151; padding: 10px 20px; border-radius: 12px; text-decoration: none; font-size: 14px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 8px;">
                    📥 CSV Import
                </a>
                <a href="{{ route('absences.create') }}" style="background: #ffffff; color: #374151; padding: 10px 20px; border-radius: 12px; text-decoration: none; font-size: 14px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 8px;">
                    ➕ Neue Abwesenheit
                </a>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
            ❌ {{ session('error') }}
        </div>
    @endif

    <!-- Absences Table -->
    <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead style="background: #f9fafb;">
                <tr>
                    <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb;">Mitarbeiter</th>
                    <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb;">Typ</th>
                    <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb;">Zeitraum</th>
                    <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb;">Dauer</th>
                    <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb;">Grund</th>
                    <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb;">Status</th>
                    <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb;">Aktionen</th>
                </tr>
            </thead>
            <tbody>
                @forelse($absences as $absence)
                    @php
                        $startDate = \Carbon\Carbon::parse($absence->start_date);
                        $endDate = \Carbon\Carbon::parse($absence->end_date);
                        $duration = $startDate->diffInDays($endDate) + 1;
                        $isActive = $startDate <= now() && $endDate >= now();
                        $isUpcoming = $startDate > now();
                        $isCompleted = $endDate < now();
                    @endphp
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 12px;">
                            <div style="display: flex; align-items: center;">
                                <div style="width: 32px; height: 32px; background: linear-gradient(135deg, #3b82f6, #8b5cf6); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 12px; margin-right: 12px;">
                                    {{ substr($absence->employee->first_name, 0, 1) }}{{ substr($absence->employee->last_name, 0, 1) }}
                                </div>
                                <div>
                                    <div style="font-weight: 500; color: #111827;">{{ $absence->employee->first_name }} {{ $absence->employee->last_name }}</div>
                                    <div style="font-size: 12px; color: #6b7280;">{{ $absence->employee->department }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 12px;">
                            <span style="background: {{ $absence->type == 'vacation' ? '#dbeafe' : ($absence->type == 'sick' ? '#fee2e2' : ($absence->type == 'personal' ? '#fef3c7' : '#e0e7ff')) }}; color: {{ $absence->type == 'vacation' ? '#1e40af' : ($absence->type == 'sick' ? '#dc2626' : ($absence->type == 'personal' ? '#d97706' : '#3730a3')) }}; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                                {{ $absence->type == 'vacation' ? 'Urlaub' : ($absence->type == 'sick' ? 'Krank' : ($absence->type == 'personal' ? 'Persönlich' : 'Sonstiges')) }}
                            </span>
                        </td>
                        <td style="padding: 12px;">
                            <div style="font-size: 14px; color: #374151;">
                                {{ $startDate->format('d.m.Y') }} - {{ $endDate->format('d.m.Y') }}
                            </div>
                        </td>
                        <td style="padding: 12px; text-align: center;">
                            <span style="background: #f3f4f6; color: #374151; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                                {{ $duration }} Tag{{ $duration !== 1 ? 'e' : '' }}
                            </span>
                        </td>
                        <td style="padding: 12px;">
                            <div style="font-size: 14px; color: #374151;">{{ $absence->reason ?? 'Kein Grund angegeben' }}</div>
                        </td>
                        <td style="padding: 12px; text-align: center;">
                            <span style="background: {{ $isActive ? '#fef3c7' : ($isUpcoming ? '#dbeafe' : '#dcfce7') }}; color: {{ $isActive ? '#d97706' : ($isUpcoming ? '#1e40af' : '#166534') }}; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                                {{ $isActive ? 'Aktiv' : ($isUpcoming ? 'Geplant' : 'Abgeschlossen') }}
                            </span>
                        </td>
                        <td style="padding: 12px;">
                            <div style="display: flex; gap: 4px;">
                                <a href="{{ route('absences.show', $absence) }}" style="background: #ffffff; color: #374151; padding: 6px 12px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 4px;">
                                    👁 Anzeigen
                                </a>
                                <a href="{{ route('absences.edit', $absence) }}" style="background: #ffffff; color: #374151; padding: 6px 12px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 4px;">
                                    ✏️ Bearbeiten
                                </a>
                                <form action="{{ route('absences.destroy', $absence) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background: #ffffff; color: #dc2626; padding: 6px 12px; border-radius: 8px; border: none; font-size: 12px; font-weight: 500; cursor: pointer; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 4px;" onclick="return confirm('Sind Sie sicher, dass Sie diese Abwesenheit löschen möchten?')">
                                        🗑️ Löschen
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="padding: 40px; text-align: center; color: #6b7280;">
                            <div style="display: flex; flex-direction: column; align-items: center;">
                                <div style="font-size: 48px; margin-bottom: 16px;">📅</div>
                                <p style="font-size: 18px; font-weight: 500; margin: 0;">Keine Abwesenheiten gefunden</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection