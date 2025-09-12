@extends('layouts.app')

@section('title', 'Zuweisungen')

@section('content')
    @if(session('success'))
        <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 4px; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>Projekt-Zuweisungen</h2>
            <a href="/assignments/create" style="background: #667eea; color: white; padding: 10px 20px;
                                                border-radius: 4px; text-decoration: none;">
                + Neue Zuweisung
            </a>
        </div>

        @if($assignments->count() > 0)
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                <tr style="border-bottom: 2px solid #dee2e6;">
                    <th style="padding: 10px; text-align: left;">Mitarbeiter</th>
                    <th style="padding: 10px; text-align: left;">Projekt</th>
                    <th style="padding: 10px; text-align: center;">Stunden/Woche</th>
                    <th style="padding: 10px; text-align: center;">Priorität</th>
                    <th style="padding: 10px; text-align: center;">Zeitraum</th>
                    <th style="padding: 10px; text-align: center;">Aktionen</th>
                </tr>
                </thead>
                <tbody>
                @foreach($assignments as $assignment)
                    <tr style="border-bottom: 1px solid #dee2e6;">
                        <td style="padding: 10px;">
                            {{ $assignment->employee->first_name }} {{ $assignment->employee->last_name }}
                        </td>
                        <td style="padding: 10px;">{{ $assignment->project->name }}</td>
                        <td style="padding: 10px; text-align: center;">{{ $assignment->weekly_hours }}h</td>
                        <td style="padding: 10px; text-align: center;">
                                <span style="padding: 3px 8px; border-radius: 3px; font-size: 12px;
                                           background: {{ $assignment->priority_level == 'high' ? '#dc3545' :
                                                        ($assignment->priority_level == 'medium' ? '#ffc107' : '#28a745') }};
                                           color: white;">
                                    {{ ucfirst($assignment->priority_level) }}
                                </span>
                        </td>
                        <td style="padding: 10px; text-align: center;">
                            {{ \Carbon\Carbon::parse($assignment->start_date)->format('d.m.Y') }}
                            @if($assignment->end_date)
                                - {{ \Carbon\Carbon::parse($assignment->end_date)->format('d.m.Y') }}
                            @else
                                (unbefristet)
                            @endif
                        </td>
                        <td style="padding: 10px; text-align: center;">
                            <a href="/assignments/{{ $assignment->id }}/edit" style="color: #667eea; margin-right: 10px;">Bearbeiten</a>
                            <form method="POST" action="/assignments/{{ $assignment->id }}" style="display: inline;"
                                  onsubmit="return confirm('Zuweisung wirklich löschen?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="color: #dc3545; border: none; background: none; cursor: pointer;">
                                    Löschen
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <p style="color: #6c757d;">Noch keine Zuweisungen vorhanden.</p>
        @endif
    </div>
@endsection
