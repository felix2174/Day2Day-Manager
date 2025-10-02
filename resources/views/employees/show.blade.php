@extends('layouts.app')

@section('title', 'Mitarbeiter-Details')

@section('content')
    @php
        use GuzzleHttp\Client;
        
        $apiKey = env('MOCO_API_KEY', '911c1d5893d68b59afcc40493c283b40');
        $domain = env('MOCO_DOMAIN', 'enodiasoftware');
        
        $client = new Client([
            'base_uri' => "https://{$domain}.mocoapp.com/api/v1/",
            'headers' => [
                'Authorization' => "Token token={$apiKey}",
                'Content-Type' => 'application/json',
            ],
        ]);
        
        // Lade MOCO-Daten für diesen Mitarbeiter
        $mocoData = [
            'user' => null,
            'schedules' => [],
            'activities' => [],
            'presences' => [],
            'projects_assigned' => []
        ];
        
        if ($employee->moco_id) {
            try {
                // User Data
                $response = $client->get("users/{$employee->moco_id}");
                $mocoData['user'] = json_decode($response->getBody(), true);
                
                // Schedules
                $response = $client->get("schedules", ['query' => ['user_id' => $employee->moco_id]]);
                $mocoData['schedules'] = json_decode($response->getBody(), true);
                
                // Activities
                $response = $client->get("activities", ['query' => ['user_id' => $employee->moco_id, 'limit' => 100]]);
                $mocoData['activities'] = json_decode($response->getBody(), true);
                
                // Presences
                $response = $client->get("users/presences", ['query' => ['user_id' => $employee->moco_id, 'limit' => 100]]);
                $mocoData['presences'] = json_decode($response->getBody(), true);
                
                // Assigned Projects
                $response = $client->get("projects/assigned", ['query' => ['user_id' => $employee->moco_id]]);
                $mocoData['projects_assigned'] = json_decode($response->getBody(), true);
            } catch (Exception $e) {
                // Fehler beim Laden
            }
        }
        
        $user = $mocoData['user'];
        $schedules = $mocoData['schedules'];
        $activities = $mocoData['activities'];
        $presences = $mocoData['presences'];
        $projects = $mocoData['projects_assigned'];
        
        // Filtere Abwesenheiten
        $absences = array_filter($schedules, function($s) {
            return isset($s['assignment']['type']) && $s['assignment']['type'] === 'Absence';
        });
    @endphp

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
                        {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                    </div>
                    <div>
                        <h1 style="margin: 0;">{{ $employee->first_name }} {{ $employee->last_name }}</h1>
                        @if($user)
                        <p style="color: #6c757d; margin: 0.5rem 0;">{{ $user['unit']['name'] ?? 'Keine Einheit' }}</p>
                        @endif
                    </div>
                </div>
                <div>
                    <a href="/employees/{{ $employee->id }}/edit"
                       style="background: #ffffff; color: #374151; padding: 12px 24px; border-radius: 12px;
                          text-decoration: none; margin-right: 10px; border: none;
                          box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); font-size: 14px; font-weight: 500;">
                        Bearbeiten
                    </a>
                    <a href="/employees"
                       style="background: #ffffff; color: #374151; padding: 12px 24px; border-radius: 12px;
                          text-decoration: none; border: none;
                          box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); font-size: 14px; font-weight: 500;">
                        Zurück zur Übersicht
                    </a>
                </div>
            </div>
        </div>

        @if($user)
        <!-- MOCO-Benutzerdaten -->
        <div class="info-card">
            <h2 style="margin-bottom: 1.5rem;">MOCO-Benutzerdaten</h2>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">ID</div>
                    <div class="info-value">#{{ $user['id'] }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Vorname</div>
                    <div class="info-value">{{ $user['firstname'] }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Nachname</div>
                    <div class="info-value">{{ $user['lastname'] }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">E-Mail</div>
                    <div class="info-value">{{ $user['email'] ?: 'Nicht verfügbar' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Aktiv</div>
                    <div class="info-value">
                        @if($user['active'])
                            <span class="status-badge status-active">Ja</span>
                        @else
                            <span class="status-badge status-inactive">Nein</span>
                        @endif
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Extern</div>
                    <div class="info-value">
                        @if($user['extern'])
                            <span class="status-badge" style="background: #fef3c7; color: #92400e;">Ja</span>
                        @else
                            <span class="status-badge" style="background: #f3f4f6; color: #6b7280;">Nein</span>
                        @endif
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Mobiltelefon</div>
                    <div class="info-value">{{ $user['mobile_phone'] ?: 'Nicht verfügbar' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Arbeitstelefon</div>
                    <div class="info-value">{{ $user['work_phone'] ?: 'Nicht verfügbar' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Heimadresse</div>
                    <div class="info-value">{{ $user['home_address'] ?: 'Nicht verfügbar' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Geburtstag</div>
                    <div class="info-value">{{ $user['birthday'] ?: 'Nicht verfügbar' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">IBAN</div>
                    <div class="info-value">{{ $user['iban'] ?: 'Nicht verfügbar' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Rolle</div>
                    <div class="info-value">{{ $user['role'] ?: 'Nicht zugewiesen' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Einheit</div>
                    <div class="info-value">{{ $user['unit']['name'] ?? 'Nicht zugewiesen' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Tags</div>
                    <div class="info-value">{{ empty($user['tags']) ? 'Keine Tags' : implode(', ', $user['tags']) }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Erstellt am</div>
                    <div class="info-value">{{ \Carbon\Carbon::parse($user['created_at'])->format('d.m.Y H:i') }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Aktualisiert am</div>
                    <div class="info-value">{{ \Carbon\Carbon::parse($user['updated_at'])->format('d.m.Y H:i') }}</div>
                </div>
            </div>
        </div>

        <!-- Zugewiesene Projekte -->
        @if(!empty($projects))
        <div class="info-card">
            <h2 style="margin-bottom: 1.5rem;">Zugewiesene Projekte ({{ count($projects) }})</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Projekt</th>
                        <th>Identifier</th>
                        <th>Kunde</th>
                        <th>Abrechenbar</th>
                        <th>Status</th>
                        <th>Tasks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($projects as $project)
                    <tr>
                        <td><strong>{{ $project['name'] }}</strong></td>
                        <td>{{ $project['identifier'] }}</td>
                        <td>{{ $project['customer']['name'] }}</td>
                        <td>
                            @if($project['billable'])
                                <span class="status-badge status-active">Ja</span>
                            @else
                                <span class="status-badge" style="background: #f3f4f6; color: #6b7280;">Nein</span>
                            @endif
                        </td>
                        <td>
                            @if($project['active'])
                                <span class="status-badge status-active">Aktiv</span>
                            @else
                                <span class="status-badge status-inactive">Inaktiv</span>
                            @endif
                        </td>
                        <td>{{ count($project['tasks']) }} Tasks</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- Zeiteinträge (Activities) -->
        @if(!empty($activities))
        <div class="info-card">
            <h2 style="margin-bottom: 1.5rem;">Zeiteinträge ({{ count($activities) }})</h2>
            <div style="margin-bottom: 1rem;">
                <strong>Gesamtstunden:</strong> {{ array_sum(array_column($activities, 'hours')) }}h
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Projekt</th>
                        <th>Task</th>
                        <th>Stunden</th>
                        <th>Beschreibung</th>
                        <th>Abrechenbar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(array_slice($activities, 0, 20) as $activity)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($activity['date'])->format('d.m.Y') }}</td>
                        <td>{{ $activity['project']['name'] }}</td>
                        <td>{{ $activity['task']['name'] }}</td>
                        <td><strong>{{ $activity['hours'] }}h</strong></td>
                        <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            {{ $activity['description'] ?: '-' }}
                        </td>
                        <td>
                            @if($activity['billable'])
                                <span class="status-badge status-active">Ja</span>
                            @else
                                <span class="status-badge" style="background: #f3f4f6; color: #6b7280;">Nein</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @if(count($activities) > 20)
                <div style="margin-top: 1rem; color: #6b7280; font-size: 14px;">
                    Zeige 20 von {{ count($activities) }} Einträgen
                </div>
            @endif
        </div>
        @endif

        <!-- Abwesenheiten -->
        @if(!empty($absences))
        <div class="info-card">
            <h2 style="margin-bottom: 1.5rem;">Abwesenheiten ({{ count($absences) }})</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Typ</th>
                        <th>Ganztags</th>
                        <th>Kommentar</th>
                        <th>Erstellt</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($absences as $absence)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($absence['date'])->format('d.m.Y') }}</td>
                        <td><strong>{{ $absence['assignment']['name'] }}</strong></td>
                        <td>
                            @if($absence['am'] && $absence['pm'])
                                <span class="status-badge" style="background: #fef3c7; color: #92400e;">Ganztags</span>
                            @elseif($absence['am'])
                                <span class="status-badge" style="background: #e0f2fe; color: #0369a1;">Vormittag</span>
                            @else
                                <span class="status-badge" style="background: #e0f2fe; color: #0369a1;">Nachmittag</span>
                            @endif
                        </td>
                        <td>{{ $absence['comment'] ?: '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($absence['created_at'])->format('d.m.Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- Anwesenheiten (Presences) -->
        @if(!empty($presences))
        <div class="info-card">
            <h2 style="margin-bottom: 1.5rem;">Anwesenheiten ({{ count($presences) }})</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Von</th>
                        <th>Bis</th>
                        <th>Stunden</th>
                        <th>Home Office</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(array_slice($presences, 0, 20) as $presence)
                    @php
                        $from = \Carbon\Carbon::parse($presence['from'], 'Europe/Berlin');
                        $to = \Carbon\Carbon::parse($presence['to'], 'Europe/Berlin');
                        $hours = $from->diffInMinutes($to) / 60;
                    @endphp
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($presence['date'])->format('d.m.Y') }}</td>
                        <td>{{ $presence['from'] }}</td>
                        <td>{{ $presence['to'] }}</td>
                        <td><strong>{{ number_format($hours, 2) }}h</strong></td>
                        <td>
                            @if($presence['is_home_office'])
                                <span class="status-badge" style="background: #e0f2fe; color: #0369a1;">Ja</span>
                            @else
                                <span class="status-badge" style="background: #f3f4f6; color: #6b7280;">Nein</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @if(count($presences) > 20)
                <div style="margin-top: 1rem; color: #6b7280; font-size: 14px;">
                    Zeige 20 von {{ count($presences) }} Einträgen
                </div>
            @endif
        </div>
        @endif

        @else
        <div class="info-card">
            <h2>Keine MOCO-Daten verfügbar</h2>
            <p>Dieser Mitarbeiter hat keine MOCO-ID oder die Daten konnten nicht geladen werden.</p>
        </div>
        @endif
    </div>
@endsection