@extends('layouts.app')

@section('content')
<div class="container">
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <h1 style="font-size: 28px; font-weight: 700; color: #111827; margin: 0;">Zeiterfassung</h1>
            <p style="color: #6b7280; margin: 4px 0 0 0;">Verfolgen Sie die Arbeitszeit Ihrer Mitarbeiter</p>
        </div>
        <a href="{{ route('time-entries.create') }}" style="background: #2563eb; color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 8px;">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
            </svg>
            Neuer Eintrag
        </a>
    </div>

    <!-- Statistiken -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div style="background: #f9fafb; padding: 20px; border-radius: 12px; border: 1px solid #e5e7eb;">
            <div style="font-size: 24px; font-weight: 700; color: #111827;">{{ number_format($totalHours, 1) }}h</div>
            <div style="color: #6b7280; font-size: 14px;">Gesamtstunden</div>
        </div>
        <div style="background: #f0f9ff; padding: 20px; border-radius: 12px; border: 1px solid #bae6fd;">
            <div style="font-size: 24px; font-weight: 700; color: #0369a1;">{{ number_format($billableHours, 1) }}h</div>
            <div style="color: #6b7280; font-size: 14px;">Abrechenbare Stunden</div>
        </div>
        <div style="background: #fef3c7; padding: 20px; border-radius: 12px; border: 1px solid #fde68a;">
            <div style="font-size: 24px; font-weight: 700; color: #d97706;">{{ number_format($nonBillableHours, 1) }}h</div>
            <div style="color: #6b7280; font-size: 14px;">Nicht abrechenbare Stunden</div>
        </div>
    </div>

    <!-- Filter -->
    <div style="background: #f9fafb; padding: 20px; border-radius: 12px; border: 1px solid #e5e7eb; margin-bottom: 24px;">
        <form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; align-items: end;">
            <div>
                <label style="display: block; font-weight: 500; color: #374151; margin-bottom: 8px;">Mitarbeiter</label>
                <select name="employee_id" style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; background: white;">
                    <option value="">Alle Mitarbeiter</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" {{ $employeeId == $employee->id ? 'selected' : '' }}>
                            {{ $employee->first_name }} {{ $employee->last_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="display: block; font-weight: 500; color: #374151; margin-bottom: 8px;">Projekt</label>
                <select name="project_id" style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; background: white;">
                    <option value="">Alle Projekte</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" {{ $projectId == $project->id ? 'selected' : '' }}>
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="display: block; font-weight: 500; color: #374151; margin-bottom: 8px;">Von</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px;">
            </div>
            <div>
                <label style="display: block; font-weight: 500; color: #374151; margin-bottom: 8px;">Bis</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px;">
            </div>
            <div>
                <button type="submit" style="background: #2563eb; color: white; padding: 12px 24px; border: none; border-radius: 8px; font-weight: 500; cursor: pointer; width: 100%;">
                    Filter anwenden
                </button>
            </div>
        </form>
    </div>

    <!-- Zeiteinträge Tabelle -->
    <div style="background: white; border-radius: 12px; border: 1px solid #e5e7eb; overflow: hidden;">
        <div style="padding: 20px; border-bottom: 1px solid #e5e7eb;">
            <h2 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0;">Zeiteinträge</h2>
        </div>
        
        @if($timeEntries->count() > 0)
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="background: #f9fafb;">
                        <tr>
                            <th style="padding: 16px; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb;">Datum</th>
                            <th style="padding: 16px; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb;">Mitarbeiter</th>
                            <th style="padding: 16px; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb;">Projekt</th>
                            <th style="padding: 16px; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb;">Stunden</th>
                            <th style="padding: 16px; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb;">Beschreibung</th>
                            <th style="padding: 16px; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb;">Abrechenbar</th>
                            <th style="padding: 16px; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb;">Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($timeEntries as $entry)
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 16px; color: #374151;">{{ \Carbon\Carbon::parse($entry->date)->format('d.m.Y') }}</td>
                            <td style="padding: 16px; color: #374151;">{{ $entry->employee->first_name }} {{ $entry->employee->last_name }}</td>
                            <td style="padding: 16px; color: #374151;">{{ $entry->project->name }}</td>
                            <td style="padding: 16px; color: #374151; font-weight: 600;">{{ number_format($entry->hours, 1) }}h</td>
                            <td style="padding: 16px; color: #374151; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                {{ $entry->description ?? '-' }}
                            </td>
                            <td style="padding: 16px;">
                                @if($entry->billable)
                                    <span style="background: #dcfce7; color: #166534; padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: 500;">Ja</span>
                                @else
                                    <span style="background: #fef3c7; color: #d97706; padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: 500;">Nein</span>
                                @endif
                            </td>
                            <td style="padding: 16px;">
                                <div style="display: flex; gap: 8px;">
                                    <a href="{{ route('time-entries.show', $entry) }}" style="color: #2563eb; text-decoration: none; font-size: 14px;">Anzeigen</a>
                                    <a href="{{ route('time-entries.edit', $entry) }}" style="color: #059669; text-decoration: none; font-size: 14px;">Bearbeiten</a>
                                    <form method="POST" action="{{ route('time-entries.destroy', $entry) }}" style="display: inline;" onsubmit="return confirm('Zeiteintrag wirklich löschen?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="color: #dc2626; background: none; border: none; font-size: 14px; cursor: pointer;">Löschen</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="padding: 40px; text-align: center; color: #6b7280;">
                <svg width="48" height="48" fill="currentColor" viewBox="0 0 20 20" style="margin-bottom: 16px; color: #d1d5db;">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                </svg>
                <div style="font-size: 18px; font-weight: 500; margin-bottom: 8px;">Keine Zeiteinträge gefunden</div>
                <div>Erstellen Sie den ersten Zeiteintrag für diesen Zeitraum.</div>
            </div>
        @endif
    </div>
</div>
@endsection









