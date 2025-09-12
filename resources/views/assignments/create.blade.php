@extends('layouts.app')

@section('title', 'Neue Zuweisung')

@section('content')
    @if(session('error'))
        <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 20px;">
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <h2>Neue Projekt-Zuweisung</h2>

        <form method="GET" action="/assignments/create" style="margin: 20px 0;">
            <div style="display: flex; gap: 10px; align-items: end;">
                <div style="flex: 1;">
                    <label style="display: block; margin-bottom: 5px;">Projekt auswählen:</label>
                    <select name="project_id" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="">-- Projekt wählen --</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display: block; margin-bottom: 5px;">Benötigte Stunden/Woche:</label>
                    <input type="number" name="weekly_hours" value="{{ request('weekly_hours', 10) }}"
                           min="1" max="60" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>

                <button type="submit" style="padding: 8px 20px; background: #667eea; color: white;
                                            border: none; border-radius: 4px; cursor: pointer;">
                    Verfügbare Mitarbeiter suchen
                </button>
            </div>
        </form>

        @if($selectedProject && $availableEmployees->count() > 0)
            <h3 style="margin-top: 30px;">Verfügbare Mitarbeiter für "{{ $selectedProject->name }}"</h3>
            <p style="color: #6c757d;">Mitarbeiter mit mindestens {{ request('weekly_hours') }}h freier Kapazität:</p>

            <form method="POST" action="/assignments" style="margin-top: 20px;">
                @csrf
                <input type="hidden" name="project_id" value="{{ $selectedProject->id }}">
                <input type="hidden" name="weekly_hours" value="{{ request('weekly_hours') }}">

                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                    <tr style="border-bottom: 2px solid #dee2e6;">
                        <th style="padding: 10px; text-align: left;">Auswahl</th>
                        <th style="padding: 10px; text-align: left;">Mitarbeiter</th>
                        <th style="padding: 10px; text-align: left;">Abteilung</th>
                        <th style="padding: 10px; text-align: center;">Freie Stunden</th>
                        <th style="padding: 10px; text-align: center;">Auslastung nach Zuweisung</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($availableEmployees as $employee)
                        @php
                            $newTotal = ($employee->assignments->sum('weekly_hours') + request('weekly_hours'));
                            $newPercentage = ($newTotal / $employee->weekly_capacity) * 100;
                        @endphp
                        <tr style="border-bottom: 1px solid #dee2e6;">
                            <td style="padding: 10px;">
                                <input type="radio" name="employee_id" value="{{ $employee->id }}" required>
                            </td>
                            <td style="padding: 10px;">
                                {{ $employee->first_name }} {{ $employee->last_name }}
                            </td>
                            <td style="padding: 10px;">{{ $employee->department }}</td>
                            <td style="padding: 10px; text-align: center;">
                                <strong style="color: #28a745;">{{ $employee->free_hours }}h</strong>
                            </td>
                            <td style="padding: 10px; text-align: center;">
                                    <span style="color: {{ $newPercentage > 90 ? '#dc3545' : '#28a745' }};">
                                        {{ round($newPercentage) }}%
                                    </span>
                                ({{ $newTotal }}h / {{ $employee->weekly_capacity }}h)
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                <div style="margin-top: 20px;">
                    <label style="display: block; margin-bottom: 5px;">Priorität:</label>
                    <select name="priority_level" required style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="low">Niedrig</option>
                        <option value="medium" selected>Mittel</option>
                        <option value="high">Hoch</option>
                    </select>
                </div>

                <div style="margin-top: 10px;">
                    <label style="display: block; margin-bottom: 5px;">Startdatum:</label>
                    <input type="date" name="start_date" required value="{{ date('Y-m-d') }}"
                           style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>

                <div style="margin-top: 10px;">
                    <label style="display: block; margin-bottom: 5px;">Enddatum (optional):</label>
                    <input type="date" name="end_date"
                           style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>

                <button type="submit" style="margin-top: 20px; padding: 10px 30px; background: #28a745;
                                            color: white; border: none; border-radius: 4px; cursor: pointer;">
                    Zuweisung erstellen
                </button>
            </form>
        @elseif($selectedProject)
            <div style="margin-top: 30px; padding: 20px; background: #f8d7da; border-radius: 4px;">
                <strong>Keine verfügbaren Mitarbeiter!</strong><br>
                Alle Mitarbeiter haben weniger als {{ request('weekly_hours') }}h freie Kapazität.
            </div>
        @endif
    </div>
@endsection
