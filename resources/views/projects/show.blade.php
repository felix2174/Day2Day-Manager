@extends('layouts.app')

@section('title', 'Projekt-Details')

@section('content')
<div style="width: 100%; margin: 0; padding: 0;">
    <!-- Page Header -->
    <div style="background: white; padding: 20px; margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="font-size: 24px; font-weight: bold; color: #111827; margin: 0;">{{ $project->name }}</h1>
                @if($project->description)
                    <p style="color: #6b7280; margin: 5px 0 0 0;">{{ $project->description }}</p>
                @endif
                <div style="display: flex; gap: 20px; margin-top: 10px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="color: #6b7280; font-size: 14px;">Status:</span>
                        <span style="font-weight: 600; color: {{ $project->status == 'active' ? '#059669' : ($project->status == 'planning' ? '#3b82f6' : '#6b7280') }};">
                            {{ ucfirst($project->status) }}
                        </span>
                    </div>
                    @if($project->responsible)
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="color: #6b7280; font-size: 14px;">Verantwortlich:</span>
                        <span style="font-weight: 600; color: {{ $project->responsible->is_active ? '#111827' : '#6b7280' }};">
                            {{ $project->responsible->first_name }} {{ $project->responsible->last_name }}
                            @if(!$project->responsible->is_active)
                                <span style="background: #e5e7eb; color: #6b7280; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 500; margin-left: 6px;">⚪ Inaktiv</span>
                            @endif
                        </span>
                    </div>
                    @endif
                    @if($project->end_date)
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="color: #6b7280; font-size: 14px;">Ende:</span>
                        <span style="font-weight: 600; color: #111827;">{{ \Carbon\Carbon::parse($project->end_date)->format('d.m.Y') }}</span>
                    </div>
                    @endif
                </div>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="{{ route('projects.edit', $project) }}" style="background: #ffffff; color: #374151; padding: 10px 20px; border-radius: 12px; text-decoration: none; font-size: 14px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 8px;">
                    Bearbeiten
                </a>
                <a href="{{ route('projects.index') }}" style="background: #ffffff; color: #374151; padding: 10px 20px; border-radius: 12px; text-decoration: none; font-size: 14px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 8px;">
                    ← Zurück zur Übersicht
                </a>
            </div>
        </div>
    </div>

    <!-- Projekt-Statistiken -->
    <div style="background: white; padding: 20px; margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
        <h2 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 20px 0;">Projekt-Statistiken</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; text-align: center;">
                <div style="font-size: 2rem; font-weight: bold; color: #111827; margin-bottom: 4px;">{{ $projectStats['time_entries_count'] }}</div>
                <div style="font-size: 14px; color: #6b7280;">Zeiteinträge</div>
            </div>
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; text-align: center;">
                <div style="font-size: 2rem; font-weight: bold; color: #111827; margin-bottom: 4px;">{{ number_format($projectStats['total_time_logged'], 1) }}h</div>
                <div style="font-size: 14px; color: #6b7280;">Gesamtstunden</div>
            </div>
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; text-align: center;">
                <div style="font-size: 2rem; font-weight: bold; color: #111827; margin-bottom: 4px;">{{ number_format($projectStats['total_billable_hours'], 1) }}h</div>
                <div style="font-size: 14px; color: #6b7280;">Abrechenbar</div>
            </div>
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; text-align: center;">
                <div style="font-size: 2rem; font-weight: bold; color: #111827; margin-bottom: 4px;">{{ number_format($projectStats['total_cost'], 0) }}€</div>
                <div style="font-size: 14px; color: #6b7280;">Kosten</div>
            </div>
        </div>
    </div>

    <!-- Projekt-Details -->
    <div style="background: white; padding: 20px; margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
        <h2 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 20px 0;">Projekt-Details</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
            
            <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px;">
                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; font-weight: 500;">Status</div>
                <div style="font-size: 16px; font-weight: 600; color: #111827;">
                    @php
                        // Status basierend ausschließlich auf finish_date aus MOCO berechnen
                        $status = 'Unbekannt';
                        $statusColor = '#6b7280';
                        $statusBg = '#f3f4f6';
                        
                        if ($mocoData && isset($mocoData['finish_date']) && $mocoData['finish_date'] !== null) {
                            // Nur finish_date aus MOCO berücksichtigen - keine Eigeninterpretation
                            $finishDate = \Carbon\Carbon::parse($mocoData['finish_date']);
                            if ($finishDate->isPast()) {
                                $status = 'Abgeschlossen';
                                $statusColor = '#3730a3';
                                $statusBg = '#e0e7ff';
                            } else {
                                $status = 'In Bearbeitung';
                                $statusColor = '#166534';
                                $statusBg = '#dcfce7';
                            }
                        } elseif ($project->end_date) {
                            // Fallback für lokale Projekte ohne MOCO finish_date
                            $finishDate = \Carbon\Carbon::parse($project->end_date);
                            if ($finishDate->isPast()) {
                                $status = 'Abgeschlossen';
                                $statusColor = '#3730a3';
                                $statusBg = '#e0e7ff';
                            } else {
                                $status = 'In Bearbeitung';
                                $statusColor = '#166534';
                                $statusBg = '#dcfce7';
                            }
                        } else {
                            // Kein Enddatum verfügbar - lokalen Status verwenden
                            $status = $project->status === 'completed' ? 'Abgeschlossen' : 
                                     ($project->status === 'active' ? 'In Bearbeitung' : 'Unbekannt');
                            
                            if ($status === 'Abgeschlossen') {
                                $statusColor = '#3730a3';
                                $statusBg = '#e0e7ff';
                            } elseif ($status === 'In Bearbeitung') {
                                $statusColor = '#166534';
                                $statusBg = '#dcfce7';
                            }
                        }
                    @endphp
                    <span style="background: {{ $statusBg }}; color: {{ $statusColor }}; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                        {{ $status }}
                    </span>
                    @php
                        // Zeige an, welches Datum für die Status-Berechnung verwendet wurde
                        $statusInfo = '';
                        if ($mocoData && isset($mocoData['finish_date']) && $mocoData['finish_date'] !== null) {
                            $statusInfo = 'Basiert auf MOCO finish_date: ' . \Carbon\Carbon::parse($mocoData['finish_date'])->format('d.m.Y');
                        } elseif ($project->end_date) {
                            $statusInfo = 'Basiert auf lokalem end_date: ' . \Carbon\Carbon::parse($project->end_date)->format('d.m.Y');
                        } else {
                            $statusInfo = 'Basiert auf lokalem Status';
                        }
                    @endphp
                    @if($statusInfo)
                    <div style="font-size: 10px; color: #9ca3af; margin-top: 4px;">
                        {{ $statusInfo }}
                    </div>
                    @endif
                </div>
            </div>
            <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px;">
                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; font-weight: 500;">Startdatum</div>
                <div style="font-size: 16px; font-weight: 600; color: #111827;">{{ \Carbon\Carbon::parse($project->start_date)->format('d.m.Y') }}</div>
            </div>
            <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px;">
                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; font-weight: 500;">Enddatum</div>
                <div style="font-size: 16px; font-weight: 600; color: #111827;">
                    @if($project->end_date)
                        {{ \Carbon\Carbon::parse($project->end_date)->format('d.m.Y') }}
                    @else
                        <span style="color: #6b7280;">Nicht festgelegt</span>
                    @endif
                </div>
            </div>
            @if($project->estimated_hours)
            <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px;">
                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; font-weight: 500;">Geschätzte Stunden</div>
                <div style="font-size: 16px; font-weight: 600; color: #111827;">{{ $project->estimated_hours }}h</div>
            </div>
            @endif
            @if($project->hourly_rate)
            <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px;">
                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; font-weight: 500;">Stundensatz</div>
                <div style="font-size: 16px; font-weight: 600; color: #111827;">{{ number_format($project->hourly_rate, 2) }}€</div>
            </div>
            @endif
            @if($mocoData && $mocoData['budget'])
            <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px;">
                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; font-weight: 500;">MOCO Budget</div>
                <div style="font-size: 16px; font-weight: 600; color: #111827;">{{ number_format($mocoData['budget'], 2) }} {{ $mocoData['currency'] ?? 'EUR' }}</div>
            </div>
            @endif
            @if($mocoData && $mocoData['billing_variant'])
            <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px;">
                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; font-weight: 500;">Abrechnungsart</div>
                <div style="font-size: 16px; font-weight: 600; color: #111827;">{{ ucfirst($mocoData['billing_variant']) }}</div>
            </div>
            @endif
        </div>
    </div>

    @if($mocoData)
    <!-- Kompletter MOCO JSON -->
    <div style="background: white; padding: 20px; margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
        <h2 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 12px 0;">MOCO Projektdaten (JSON)</h2>
        <pre style="margin: 0; font-size: 12px; color: #111827; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 12px; overflow: auto; max-height: 420px;">{{ json_encode($mocoData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
    @endif

    <!-- Projekt-Fortschritt -->
    @if($project->end_date)
        @php
            $start = \Carbon\Carbon::parse($project->start_date);
            $end = \Carbon\Carbon::parse($project->end_date);
            $remaining_days = round(now()->diffInDays($end, false));
            $progress = $project->progress ?? 0;
        @endphp
        <div style="background: white; padding: 20px; margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
            <h2 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 20px 0;">Projekt-Fortschritt</h2>
            <div style="margin-bottom: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="font-size: 14px; font-weight: 500; color: #374151;">Fortschritt</span>
                    <span style="font-size: 14px; font-weight: 600; color: #111827;">{{ $progress }}%</span>
                </div>
                <div style="background: #e5e7eb; height: 8px; border-radius: 4px; overflow: hidden;">
                    <div style="background: {{ $progress >= 80 ? '#28a745' : ($progress >= 50 ? '#ffc107' : '#dc3545') }}; height: 100%; width: {{ $progress }}%; transition: width 0.3s;"></div>
                </div>
            </div>
            <div style="display: flex; justify-content: space-between; color: #6b7280; font-size: 14px;">
                <span>Gestartet: {{ $start->format('d.m.Y') }}</span>
                <span>Geplant bis: {{ $end->format('d.m.Y') }}</span>
            </div>
        </div>
    @endif


    <!-- MOCO Kunde-Informationen -->
    @if($mocoCustomer)
    <div style="background: white; padding: 20px; margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
        <h2 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 20px 0;">Kunde (MOCO)</h2>
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px;">
            <h3 style="font-size: 16px; font-weight: 600; color: #111827; margin: 0 0 8px 0;">{{ $mocoCustomer['name'] }}</h3>
            <div style="color: #6b7280; font-size: 14px;">Kunden-ID: {{ $mocoCustomer['id'] }}</div>
        </div>
        @if($mocoData && $mocoData['billing_address'])
        <div style="margin-top: 16px;">
            <h4 style="font-size: 14px; font-weight: 600; color: #374151; margin: 0 0 8px 0;">Rechnungsadresse</h4>
            <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 12px; font-size: 14px; color: #6b7280; white-space: pre-line;">{{ $mocoData['billing_address'] }}</div>
        </div>
        @endif
    </div>
    @endif

    <!-- MOCO Tasks/Leistungen -->
    @if(count($mocoTasks) > 0)
    <div style="background: white; padding: 20px; margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
        <h2 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 20px 0;">MOCO Tasks/Leistungen</h2>
        @foreach($mocoTasks as $task)
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-bottom: 12px;">
                <div style="display: flex; justify-content: between; align-items: flex-start; margin-bottom: 8px;">
                    <h3 style="font-size: 16px; font-weight: 600; color: #111827; margin: 0; flex: 1;">{{ $task['name'] }}</h3>
                    <div style="display: flex; gap: 8px; margin-left: 16px;">
                        @if($task['budget'])
                        <span style="background: #d1fae5; color: #065f46; padding: 2px 8px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                            {{ number_format($task['budget'], 2) }}€
                        </span>
                        @endif
                        @if($task['hourly_rate'] > 0)
                        <span style="background: #dbeafe; color: #1e40af; padding: 2px 8px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                            {{ $task['hourly_rate'] }}€/h
                        </span>
                        @endif
                        @if($task['billable'])
                        <span style="background: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                            Abrechenbar
                        </span>
                        @endif
                        @if($task['active'])
                        <span style="background: #fef3c7; color: #92400e; padding: 2px 8px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                            Aktiv
                        </span>
                        @endif
                    </div>
                </div>
                @if($task['description'])
                <div style="color: #6b7280; font-size: 14px; line-height: 1.5; margin-top: 8px;">
                    {!! strip_tags($task['description'], '<br><ul><li><strong>') !!}
                </div>
                @endif
            </div>
        @endforeach
    </div>
    @endif

    <!-- Zugewiesene Personen (Vereinheitlicht) -->
    <div style="background: white; padding: 20px; margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
        <h2 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 20px 0;">Zugewiesene Personen</h2>
        
        @php
            $mocoTeamData = isset($projectTeams[$project->moco_id]) ? $projectTeams[$project->moco_id] : null;
            $assignedPersons = $project->getAssignedPersonsList($mocoTeamData);
        @endphp
        
        @if(empty($assignedPersons))
            <div style="background: #fef3c7; border: 1px solid #f59e0b; border-radius: 6px; padding: 16px; text-align: center;">
                <div style="color: #92400e; font-weight: 500;">⚠️ Keine Personen zugewiesen</div>
                <div style="color: #92400e; font-size: 14px; margin-top: 4px;">Dieses Projekt hat derzeit keine zugewiesenen Mitarbeiter.</div>
            </div>
        @else
            <x-assigned-persons 
                :persons="$assignedPersons" 
                :maxPersons="0" 
                :showCount="true" 
                variant="detail" 
            />
            <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid #e5e7eb;">
                <div style="color: #6b7280; font-size: 14px;">
                    <strong>Datenquelle:</strong> 
                    @if($project->moco_id && isset($projectTeams[$project->moco_id]))
                        MOCO ({{ count($assignedPersons) }} Personen)
                    @else
                        Lokale Zuweisungen ({{ count($assignedPersons) }} Personen)
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- Personen & Teams für die Zeiterfassung (MOCO Contracts) -->
    @if(count($mocoContracts) > 0)
    <div style="background: white; padding: 20px; margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
        <h2 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 20px 0;">Personen & Teams für die Zeiterfassung</h2>
        <div style="background: #f8f9fa; border: 1px solid #e5e7eb; border-radius: 6px; padding: 16px; margin-bottom: 20px;">
            <strong style="color: #374151;">Zugewiesene Personen aus MOCO:</strong>
        </div>
        <div style="display: flex; flex-direction: column; gap: 12px;">
            @foreach($mocoContracts as $contract)
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; display: flex; align-items: center; gap: 16px;">
                    <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #3b82f6, #8b5cf6); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px;">
                        {{ substr($contract['firstname'], 0, 1) }}{{ substr($contract['lastname'], 0, 1) }}
                    </div>
                    <div style="flex: 1;">
                        <h3 style="font-size: 16px; font-weight: 600; color: #111827; margin: 0 0 4px 0;">{{ $contract['firstname'] }} {{ $contract['lastname'] }}</h3>
                        <div style="color: #6b7280; font-size: 14px;">User ID: {{ $contract['user_id'] }} | Contract ID: {{ $contract['id'] }}</div>
                    </div>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        @if($contract['hourly_rate'] > 0)
                        <span style="background: #dbeafe; color: #1e40af; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                            {{ $contract['hourly_rate'] }}€/h
                        </span>
                        @endif
                        @if($contract['billable'])
                        <span style="background: #dcfce7; color: #166534; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                            Abrechenbar
                        </span>
                        @endif
                        @if($contract['active'])
                        <span style="background: #fef3c7; color: #92400e; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                            Aktiv
                        </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Zusätzliche MOCO-Informationen -->
    @if($mocoData)
    <div style="background: white; padding: 20px; margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
        <h2 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 20px 0;">Zusätzliche MOCO-Informationen</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
            @if($mocoData['fixed_price'])
            <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px;">
                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; font-weight: 500;">Festpreis</div>
                <div style="font-size: 16px; font-weight: 600; color: #111827;">{{ number_format($mocoData['fixed_price'], 2) }} {{ $mocoData['currency'] ?? 'EUR' }}</div>
            </div>
            @endif
            @if($mocoData['retainer'])
            <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px;">
                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; font-weight: 500;">Retainer</div>
                <div style="font-size: 16px; font-weight: 600; color: #111827;">{{ number_format($mocoData['retainer'], 2) }} {{ $mocoData['currency'] ?? 'EUR' }}</div>
            </div>
            @endif
            @if($mocoData['budget_monthly'])
            <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px;">
                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; font-weight: 500;">Monatsbudget</div>
                <div style="font-size: 16px; font-weight: 600; color: #111827;">{{ number_format($mocoData['budget_monthly'], 2) }} {{ $mocoData['currency'] ?? 'EUR' }}</div>
            </div>
            @endif
            @if($mocoData['budget_expenses'])
            <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px;">
                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; font-weight: 500;">Ausgabenbudget</div>
                <div style="font-size: 16px; font-weight: 600; color: #111827;">{{ number_format($mocoData['budget_expenses'], 2) }} {{ $mocoData['currency'] ?? 'EUR' }}</div>
            </div>
            @endif
            @if($mocoData['color'])
            <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px;">
                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; font-weight: 500;">Projektfarbe</div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 20px; height: 20px; background: {{ $mocoData['color'] }}; border-radius: 4px; border: 1px solid #e5e7eb;"></div>
                    <span style="font-size: 16px; font-weight: 600; color: #111827;">{{ $mocoData['color'] }}</span>
                </div>
            </div>
            @endif
            @if($mocoData['created_at'])
            <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px;">
                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; font-weight: 500;">Erstellt in MOCO</div>
                <div style="font-size: 16px; font-weight: 600; color: #111827;">{{ \Carbon\Carbon::parse($mocoData['created_at'])->format('d.m.Y H:i') }}</div>
            </div>
            @endif
            @if($mocoData['updated_at'])
            <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px;">
                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; font-weight: 500;">Zuletzt aktualisiert</div>
                <div style="font-size: 16px; font-weight: 600; color: #111827;">{{ \Carbon\Carbon::parse($mocoData['updated_at'])->format('d.m.Y H:i') }}</div>
            </div>
            @endif
        </div>
        @if($mocoData['billing_notes'])
        <div style="margin-top: 20px;">
            <h4 style="font-size: 14px; font-weight: 600; color: #374151; margin: 0 0 8px 0;">Rechnungsnotizen</h4>
            <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 12px; font-size: 14px; color: #6b7280; white-space: pre-line;">{{ $mocoData['billing_notes'] }}</div>
        </div>
        @endif
    </div>
    @endif


    <!-- Zeiterfassung -->
    <div style="background: white; padding: 20px; margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
        <h2 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 20px 0;">Zeiterfassung</h2>
        <div style="text-align: center; padding: 40px; color: #6b7280;">
            <p style="margin: 0; font-size: 16px;">Zeiterfassungen werden über MOCO verwaltet</p>
            <p style="margin: 8px 0 0 0; font-size: 14px;">Für detaillierte Zeiterfassungen besuchen Sie die MOCO-Plattform.</p>
        </div>
    </div>
</div>
@endsection
