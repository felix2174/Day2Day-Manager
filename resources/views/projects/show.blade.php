@extends('layouts.app')

@section('title', 'Projekt-Details')

@section('content')
    <style>
        .detail-container {
            padding: 0;
            width: 100%;
            margin: 0;
        }
        .info-card {
            background: white;
            border-radius: 8px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
        }
        .info-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e5e7eb;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .info-item {
            padding: 16px;
            background: #f9fafb;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }
        .info-label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 8px;
            font-weight: 500;
        }
        .info-value {
            font-size: 16px;
            font-weight: 600;
            color: #111827;
        }
        .status-badge {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 500;
            display: inline-block;
        }
        .status-active { background: #d4edda; color: #155724; }
        .status-inactive { background: #f8d7da; color: #721c24; }
        .status-planning { background: #cce5ff; color: #1e40af; }
        .status-completed { background: #f3f4f6; color: #6b7280; }
        .avatar {
            width: 80px;
            height: 80px;
            background: #667eea;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 24px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table th {
            background: #f3f4f6;
            padding: 12px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
        }
        .data-table td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 14px;
            color: #111827;
        }
        .data-table tr:hover {
            background: #f9fafb;
        }
    </style>

    <div class="detail-container">
        <!-- Header -->
        <div class="info-card">
            <div class="info-header">
                <div style="display: flex; align-items: center; gap: 20px;">
                    <div class="avatar">
                        {{ substr($project->name, 0, 2) }}
        </div>
        <div>
                        <h1 style="margin: 0;">{{ $project->name }}</h1>
                        <p style="color: #6c757d; margin: 0.5rem 0;">{{ $project->description ?: 'Keine Beschreibung' }}</p>
                    </div>
        </div>
        <div>
                    <a href="{{ route('projects.edit', $project) }}"
                       style="background: #ffffff; color: #374151; padding: 12px 24px; border-radius: 12px;
                          text-decoration: none; margin-right: 10px; border: none;
                          box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); font-size: 14px; font-weight: 500;">
                        Bearbeiten
                    </a>
                    <a href="{{ route('projects.index') }}"
                       style="background: #ffffff; color: #374151; padding: 12px 24px; border-radius: 12px;
                          text-decoration: none; border: none;
                          box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); font-size: 14px; font-weight: 500;">
                        Zurück zur Übersicht
                    </a>
                </div>
            </div>
        </div>

        <!-- Projekt-Grunddaten -->
        <div class="info-card">
            <h2 style="margin-bottom: 1.5rem;">Projekt-Grunddaten</h2>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Identifier</div>
                    <div class="info-value">{{ $project->identifier ?: 'Nicht verfügbar' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Status</div>
                    <div class="info-value">
                        @if($project->status === 'active')
                            <span class="status-badge status-active">Aktiv</span>
                        @elseif($project->status === 'planning')
                            <span class="status-badge status-planning">Geplant</span>
                        @elseif($project->status === 'completed')
                            <span class="status-badge status-completed">Abgeschlossen</span>
                        @else
                            <span class="status-badge status-inactive">{{ ucfirst($project->status ?: 'Unbekannt') }}</span>
                        @endif
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Abrechenbar</div>
                    <div class="info-value">
                        @if($project->billable)
                            <span class="status-badge status-active">Ja</span>
                        @else
                            <span class="status-badge" style="background: #f3f4f6; color: #6b7280;">Nein</span>
                        @endif
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Startdatum</div>
                    <div class="info-value">{{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d.m.Y') : 'Nicht verfügbar' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Enddatum</div>
                    <div class="info-value">{{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('d.m.Y') : 'Nicht verfügbar' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Verantwortlich</div>
                    <div class="info-value">{{ $project->responsible ? $project->responsible->first_name . ' ' . $project->responsible->last_name : 'Nicht zugewiesen' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">MOCO ID</div>
                    <div class="info-value">{{ $project->moco_id ?: 'Nicht verfügbar' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Erstellt am</div>
                    <div class="info-value">{{ $project->created_at ? \Carbon\Carbon::parse($project->created_at)->format('d.m.Y H:i') : 'Nicht verfügbar' }}</div>
        </div>
                <div class="info-item">
                    <div class="info-label">Aktualisiert am</div>
                    <div class="info-value">{{ $project->updated_at ? \Carbon\Carbon::parse($project->updated_at)->format('d.m.Y H:i') : 'Nicht verfügbar' }}</div>
        </div>
        </div>
    </div>

    @if(isset($progress))
        <!-- Fortschritt -->
        <div class="info-card">
            <h2 style="margin-bottom: 1.5rem;">Fortschritt</h2>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Automatisch</div>
                    <div class="info-value">{{ $progress['automatic'] }}%</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Nach Stunden</div>
                    <div class="info-value">{{ $progress['hours'] }}%</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Nach Zeit</div>
                    <div class="info-value">{{ $progress['time'] }}%</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Manuell</div>
                    <div class="info-value">{{ $progress['manual'] }}%</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Gearbeitete Stunden</div>
                    <div class="info-value">{{ $progress['total_hours_worked'] }}h</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Geschätzte Stunden</div>
                    <div class="info-value">{{ $progress['estimated_hours'] }}h</div>
                </div>
        </div>
    </div>
    @endif

    @if(isset($budget))
        <!-- Budget -->
        <div class="info-card">
            <h2 style="margin-bottom: 1.5rem;">Budget</h2>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Gesamtbudget</div>
                    <div class="info-value">{{ number_format($budget['total_budget'], 2, ',', '.') }} €</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Stundensatz</div>
                    <div class="info-value">{{ number_format($budget['hourly_rate'], 2, ',', '.') }} €</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Geschätzte Stunden</div>
                    <div class="info-value">{{ $budget['estimated_hours'] }}h</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Tatsächliche Stunden</div>
                    <div class="info-value">{{ $budget['actual_hours'] }}h</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Tatsächliche Kosten</div>
                    <div class="info-value">{{ number_format($budget['actual_cost'], 2, ',', '.') }} €</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Verbleibendes Budget</div>
                    <div class="info-value">{{ number_format($budget['remaining_budget'], 2, ',', '.') }} €</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Auslastung</div>
                    <div class="info-value">{{ round($budget['budget_utilization'], 1) }}%</div>
                </div>
        </div>
    </div>
    @endif

    @if(isset($team))
        <!-- Team -->
        <div class="info-card">
            <h2 style="margin-bottom: 1.5rem;">Team</h2>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Team-Auslastung</div>
                    <div class="info-value">{{ round($team['team_utilization'], 1) }}%</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Gesamt zugewiesen</div>
                    <div class="info-value">{{ $team['total_assigned'] }}h/Woche</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Gesamtkapazität</div>
                    <div class="info-value">{{ $team['total_capacity'] }}h/Woche</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Überlastete Mitglieder</div>
                    <div class="info-value">{{ count($team['overloaded_members']) }}</div>
                </div>
        </div>

        @if(count($team['overloaded_members']) > 0)
                <div style="margin-top: 1rem;">
                    <h3 style="font-size: 14px; color: #6b7280; margin-bottom: 8px;">Überlastete Teammitglieder:</h3>
                    <ul style="list-style: disc; margin-left: 20px; font-size: 14px;">
                        @foreach($team['overloaded_members'] as $member)
                            <li style="margin-bottom: 4px;">{{ $member['name'] }} ({{ round($member['utilization']) }}%)</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endif

@if(isset($timeline))
        <!-- Timeline -->
        <div class="info-card">
            <h2 style="margin-bottom: 1.5rem;">Timeline</h2>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Phase</div>
                    <div class="info-value">{{ ucfirst($timeline['phase']) }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Zeitfortschritt</div>
                    <div class="info-value">{{ $timeline['time_progress'] }}%</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Verbleibende Tage</div>
                    <div class="info-value">
                @if($timeline['phase'] === 'planned')
                            Startet in {{ $timeline['days_to_start'] }} Tagen
                @elseif($timeline['phase'] === 'running')
                            {{ $timeline['days_remaining'] }} Tage
                @else
                    —
                @endif
            </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Überfällig</div>
                    <div class="info-value">
                        @if($timeline['overdue_days'] > 0)
                            <span class="status-badge status-inactive">{{ $timeline['overdue_days'] }} Tage</span>
                        @else
                            <span class="status-badge status-active">Nein</span>
                        @endif
                    </div>
                </div>
        </div>
    </div>
@endif

@if(isset($bottlenecks) && $bottlenecks['total_count'] > 0)
        <!-- Bottlenecks -->
        <div class="info-card">
            <h2 style="margin-bottom: 1.5rem;">Engpässe</h2>
            <div style="margin-bottom: 1rem; font-size: 14px; color: #6b7280;">
                Kritisch: {{ $bottlenecks['critical_count'] }} · Warnungen: {{ $bottlenecks['warning_count'] }}
        </div>
            <ul style="list-style: disc; margin-left: 20px; font-size: 14px;">
                @foreach($bottlenecks['bottlenecks'] as $bottleneck)
                    <li style="margin-bottom: 8px;">
                        <span style="color: {{ $bottleneck['severity'] === 'critical' ? '#dc2626' : '#d97706' }}; font-weight: 600;">
                            [{{ strtoupper($bottleneck['severity']) }}]
                    </span>
                        {{ $bottleneck['message'] }}
                </li>
            @endforeach
        </ul>
    </div>
@endif

        @if(isset($mocoData) && $mocoData && !isset($mocoData['error']))
        <!-- MOCO-Projektdaten -->
        <div class="info-card">
            <h2 style="margin-bottom: 1.5rem;">MOCO-Projektdaten</h2>
            @if($mocoData['project'])
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">MOCO ID</div>
                        <div class="info-value">#{{ $mocoData['project']['id'] ?? 'Nicht verfügbar' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Kunde</div>
                        <div class="info-value">{{ $mocoData['project']['customer']['name'] ?? 'Nicht zugewiesen' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Projektleiter</div>
                        <div class="info-value">{{ $mocoData['project']['leader']['firstname'] ?? '' }} {{ $mocoData['project']['leader']['lastname'] ?? '' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Co-Leiter</div>
                        <div class="info-value">{{ $mocoData['project']['co_leader']['firstname'] ?? '' }} {{ $mocoData['project']['co_leader']['lastname'] ?? '' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Währung</div>
                        <div class="info-value">{{ $mocoData['project']['currency'] ?? 'EUR' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Abrechnungsvariante</div>
                        <div class="info-value">{{ $mocoData['project']['billing_variant'] ?? 'Nicht verfügbar' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Feste Preise</div>
                        <div class="info-value">
                            @if(isset($mocoData['project']['fixed_price']) && $mocoData['project']['fixed_price'])
                                <span class="status-badge status-active">Ja</span>
                            @else
                                <span class="status-badge" style="background: #f3f4f6; color: #6b7280;">Nein</span>
                            @endif
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Retainer</div>
                        <div class="info-value">
                            @if(isset($mocoData['project']['retainer']) && $mocoData['project']['retainer'])
                                <span class="status-badge status-active">Ja</span>
                            @else
                                <span class="status-badge" style="background: #f3f4f6; color: #6b7280;">Nein</span>
                            @endif
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Erstellt in MOCO</div>
                        <div class="info-value">{{ isset($mocoData['project']['created_at']) ? \Carbon\Carbon::parse($mocoData['project']['created_at'])->format('d.m.Y H:i') : 'Nicht verfügbar' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Aktualisiert in MOCO</div>
                        <div class="info-value">{{ isset($mocoData['project']['updated_at']) ? \Carbon\Carbon::parse($mocoData['project']['updated_at'])->format('d.m.Y H:i') : 'Nicht verfügbar' }}</div>
                    </div>
                </div>
            @endif
        </div>

        <!-- MOCO-Zusammenfassung -->
        @if($mocoData['summary'])
        <div class="info-card">
            <h2 style="margin-bottom: 1.5rem;">MOCO-Zusammenfassung</h2>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Gesamtstunden</div>
                    <div class="info-value">{{ $mocoData['summary']['total_hours'] ?? 0 }}h</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Gesamtkosten</div>
                    <div class="info-value">{{ number_format($mocoData['summary']['total_cost'] ?? 0, 2, ',', '.') }} €</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Abrechenbare Stunden</div>
                    <div class="info-value">{{ $mocoData['summary']['billable_hours'] ?? 0 }}h</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Nicht-abrechenbare Stunden</div>
                    <div class="info-value">{{ $mocoData['summary']['non_billable_hours'] ?? 0 }}h</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Abgeschlossene Tasks</div>
                    <div class="info-value">{{ $mocoData['summary']['completed_tasks'] ?? 0 }}/{{ $mocoData['summary']['total_tasks'] ?? 0 }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Task-Fortschritt</div>
                    <div class="info-value">{{ round($mocoData['summary']['task_completion_rate'] ?? 0, 1) }}%</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Teammitglieder</div>
                    <div class="info-value">{{ $mocoData['summary']['team_members'] ?? 0 }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Aktive Zuweisungen</div>
                    <div class="info-value">{{ $mocoData['summary']['active_assignments'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        @endif

        <!-- MOCO-Aktivitäten -->
        @if(!empty($mocoData['activities']))
        <div class="info-card">
            <h2 style="margin-bottom: 1.5rem;">MOCO-Aktivitäten ({{ count($mocoData['activities']) }})</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Mitarbeiter</th>
                        <th>Task</th>
                        <th>Stunden</th>
                        <th>Kosten</th>
                        <th>Abrechenbar</th>
                        <th>Beschreibung</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(array_slice($mocoData['activities'], 0, 20) as $activity)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($activity['date'])->format('d.m.Y') }}</td>
                        <td>{{ $activity['user']['firstname'] ?? '' }} {{ $activity['user']['lastname'] ?? '' }}</td>
                        <td>{{ $activity['task']['name'] ?? '-' }}</td>
                        <td><strong>{{ $activity['hours'] ?? 0 }}h</strong></td>
                        <td>{{ number_format($activity['cost'] ?? 0, 2, ',', '.') }} €</td>
                        <td>
                            @if(isset($activity['billable']) && $activity['billable'])
                                <span class="status-badge status-active">Ja</span>
                            @else
                                <span class="status-badge" style="background: #f3f4f6; color: #6b7280;">Nein</span>
                            @endif
                        </td>
                        <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            {{ $activity['description'] ?? '-' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @if(count($mocoData['activities']) > 20)
                <div style="margin-top: 1rem; color: #6b7280; font-size: 14px;">
                    Zeige 20 von {{ count($mocoData['activities']) }} Aktivitäten
                </div>
            @endif
        </div>
        @endif

        <!-- MOCO-Tasks -->
        @if(!empty($mocoData['tasks']))
        <div class="info-card">
            <h2 style="margin-bottom: 1.5rem;">MOCO-Tasks ({{ count($mocoData['tasks']) }})</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Task</th>
                        <th>Zugewiesen an</th>
                        <th>Stunden</th>
                        <th>Abrechenbare Stunden</th>
                        <th>Letzte Aktivität</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($mocoData['tasks'] as $task)
                    <tr>
                        <td><strong>{{ $task['name'] ?? 'Unbekannter Task' }}</strong></td>
                        <td>{{ $task['user']['firstname'] ?? '' }} {{ $task['user']['lastname'] ?? '' }}</td>
                        <td><strong>{{ $task['total_hours'] ?? 0 }}h</strong></td>
                        <td>{{ $task['billable_hours'] ?? 0 }}h</td>
                        <td>{{ isset($task['last_activity']) ? \Carbon\Carbon::parse($task['last_activity'])->format('d.m.Y') : '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- MOCO-Team-Mitglieder -->
        @if(!empty($mocoData['assignments']))
        <div class="info-card">
            <h2 style="margin-bottom: 1.5rem;">MOCO-Team-Mitglieder ({{ count($mocoData['assignments']) }})</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Mitarbeiter</th>
                        <th>Gesamtstunden</th>
                        <th>Abrechenbare Stunden</th>
                        <th>Letzte Aktivität</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($mocoData['assignments'] as $assignment)
                    <tr>
                        <td><strong>{{ $assignment['user']['firstname'] ?? '' }} {{ $assignment['user']['lastname'] ?? '' }}</strong></td>
                        <td><strong>{{ $assignment['total_hours'] ?? 0 }}h</strong></td>
                        <td>{{ $assignment['billable_hours'] ?? 0 }}h</td>
                        <td>{{ isset($assignment['last_activity']) ? \Carbon\Carbon::parse($assignment['last_activity'])->format('d.m.Y') : '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @elseif(isset($mocoData) && isset($mocoData['error']))
        <!-- MOCO-Fehler -->
        <div class="info-card">
            <h2 style="margin-bottom: 1.5rem;">MOCO-Daten</h2>
            <div style="padding: 16px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; color: #dc2626;">
                <strong>Fehler beim Laden der MOCO-Daten:</strong><br>
                {{ $mocoData['error'] }}
            </div>
        </div>
        @else
        <!-- Keine MOCO-Daten -->
        <div class="info-card">
            <h2 style="margin-bottom: 1.5rem;">MOCO-Daten</h2>
            <div style="padding: 16px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; color: #6b7280;">
                <strong>Keine MOCO-Daten verfügbar</strong><br>
                Dieses Projekt hat keine MOCO-ID oder die Daten konnten nicht geladen werden.
            </div>
    </div>
        @endif
</div>
@endsection
