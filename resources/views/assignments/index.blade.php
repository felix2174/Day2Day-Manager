@extends('layouts.app')

@section('title', 'Zuweisungen')

@section('content')
<div style="width: 100%; margin: 0; padding: 0;">
    <!-- Page Header -->
    <div style="background: white; padding: 20px; margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="font-size: 24px; font-weight: bold; color: #111827; margin: 0;">Zuweisungen-Verwaltung</h1>
                <p style="color: #6b7280; margin: 5px 0 0 0;">Verwalten Sie die Zuweisungen von Mitarbeitern zu Projekten</p>
                <div style="display: flex; gap: 20px; margin-top: 10px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="color: #6b7280; font-size: 14px;">Gesamt:</span>
                        <span style="font-weight: 600; color: #111827;">{{ $assignments->count() }}</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="color: #6b7280; font-size: 14px;">Aktiv:</span>
                        <span style="font-weight: 600; color: #059669;">{{ $assignments->where('start_date', '<=', now())->where('end_date', '>=', now())->count() }}</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="color: #6b7280; font-size: 14px;">Geplant:</span>
                        <span style="font-weight: 600; color: #3b82f6;">{{ $assignments->where('start_date', '>', now())->count() }}</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="color: #6b7280; font-size: 14px;">Abgeschlossen:</span>
                        <span style="font-weight: 600; color: #6b7280;">{{ $assignments->where('end_date', '<', now())->count() }}</span>
                    </div>
                </div>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="{{ route('assignments.export') }}" style="background: #ffffff; color: #374151; padding: 10px 20px; border-radius: 12px; text-decoration: none; font-size: 14px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 8px;">
                    📊 Excel Export
                </a>
                <a href="{{ route('assignments.import') }}" style="background: #ffffff; color: #374151; padding: 10px 20px; border-radius: 12px; text-decoration: none; font-size: 14px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 8px;">
                    📥 CSV Import
                </a>
                <a href="{{ route('assignments.create') }}" style="background: #ffffff; color: #374151; padding: 10px 20px; border-radius: 12px; text-decoration: none; font-size: 14px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 8px;">
                    ➕ Neue Zuweisung
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

    <!-- Assignments Table -->
    <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead style="background: #f9fafb;">
                <tr>
                    <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb;">Mitarbeiter</th>
                    <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb;">Projekt</th>
                    <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb;">Wochenstunden</th>
                    <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb;">Zeitraum</th>
                    <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb;">Priorität</th>
                    <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb;">Status</th>
                    <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb;">Aktionen</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignments as $assignment)
                    @php
                        $isActive = $assignment->start_date <= now() && $assignment->end_date >= now();
                        $isUpcoming = $assignment->start_date > now();
                        $isCompleted = $assignment->end_date < now();
                    @endphp
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 12px;">
                            <div style="display: flex; align-items: center;">
                                <div style="width: 32px; height: 32px; background: linear-gradient(135deg, #3b82f6, #8b5cf6); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 12px; margin-right: 12px;">
                                    {{ substr($assignment->employee->first_name, 0, 1) }}{{ substr($assignment->employee->last_name, 0, 1) }}
                                </div>
                                <div>
                                    <div style="font-weight: 500; color: #111827;">{{ $assignment->employee->first_name }} {{ $assignment->employee->last_name }}</div>
                                    <div style="font-size: 12px; color: #6b7280;">{{ $assignment->employee->department }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 12px;">
                            <div>
                                <div style="font-weight: 500; color: #111827;">{{ $assignment->project->name }}</div>
                                <div style="font-size: 12px; color: #6b7280;">{{ Str::limit($assignment->project->description, 50) }}</div>
                            </div>
                        </td>
                        <td style="padding: 12px; text-align: center;">
                            <span style="background: #dbeafe; color: #1e40af; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                                {{ $assignment->weekly_hours }}h/Woche
                            </span>
                        </td>
                        <td style="padding: 12px;">
                            <div style="font-size: 14px; color: #374151;">
                                {{ \Carbon\Carbon::parse($assignment->start_date)->format('d.m.Y') }} - 
                                {{ \Carbon\Carbon::parse($assignment->end_date)->format('d.m.Y') }}
                            </div>
                        </td>
                        <td style="padding: 12px; text-align: center;">
                            <span style="background: {{ $assignment->priority_level == 'high' ? '#fee2e2' : ($assignment->priority_level == 'medium' ? '#fef3c7' : '#dcfce7') }}; color: {{ $assignment->priority_level == 'high' ? '#dc2626' : ($assignment->priority_level == 'medium' ? '#d97706' : '#166534') }}; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                                {{ ucfirst($assignment->priority_level) }}
                            </span>
                        </td>
                        <td style="padding: 12px; text-align: center;">
                            <span style="background: {{ $isActive ? '#dcfce7' : ($isUpcoming ? '#dbeafe' : '#f3f4f6') }}; color: {{ $isActive ? '#166534' : ($isUpcoming ? '#1e40af' : '#374151') }}; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                                {{ $isActive ? 'Aktiv' : ($isUpcoming ? 'Geplant' : 'Abgeschlossen') }}
                            </span>
                        </td>
                        <td style="padding: 12px;">
                            <div style="display: flex; gap: 4px;">
                                <a href="{{ route('assignments.show', $assignment) }}" style="background: #ffffff; color: #374151; padding: 6px 12px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 4px;">
                                    👁 Anzeigen
                                </a>
                                <a href="{{ route('assignments.edit', $assignment) }}" style="background: #ffffff; color: #374151; padding: 6px 12px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 4px;">
                                    ✏️ Bearbeiten
                                </a>
                                <form action="{{ route('assignments.destroy', $assignment) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background: #ffffff; color: #dc2626; padding: 6px 12px; border-radius: 8px; border: none; font-size: 12px; font-weight: 500; cursor: pointer; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 4px;" onclick="return confirm('Sind Sie sicher, dass Sie diese Zuweisung löschen möchten?')">
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
                                <div style="font-size: 48px; margin-bottom: 16px;">🔗</div>
                                <p style="font-size: 18px; font-weight: 500; margin: 0;">Keine Zuweisungen gefunden</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection