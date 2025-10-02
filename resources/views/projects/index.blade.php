@extends('layouts.app')

@section('title', 'Projekte')

@section('content')
<div style="width: 100%; margin: 0; padding: 0;">
    <!-- Page Header -->
    <div style="background: white; padding: 20px; margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="font-size: 24px; font-weight: bold; color: #111827; margin: 0;">Projekt-Verwaltung</h1>
                <p style="color: #6b7280; margin: 5px 0 0 0;">Verwalten Sie Ihre Projekte und deren Fortschritt</p>
                <div style="display: flex; gap: 20px; margin-top: 10px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="color: #6b7280; font-size: 14px;">Gesamt:</span>
                        <span style="font-weight: 600; color: #111827;">{{ $totalCount }}</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="color: #6b7280; font-size: 14px;">Aktiv:</span>
                        <span style="font-weight: 600; color: #059669;">{{ $activeCount }}</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="color: #6b7280; font-size: 14px;">Geplant:</span>
                        <span style="font-weight: 600; color: #3b82f6;">{{ $planningCount }}</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="color: #6b7280; font-size: 14px;">Abgeschlossen:</span>
                        <span style="font-weight: 600; color: #6b7280;">{{ $completedCount }}</span>
                    </div>
                </div>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="{{ route('projects.export') }}" style="background: #ffffff; color: #374151; padding: 10px 20px; border-radius: 12px; text-decoration: none; font-size: 14px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 8px;">
                    📊 Excel Export
                </a>
                <a href="{{ route('projects.import') }}" style="background: #ffffff; color: #374151; padding: 10px 20px; border-radius: 12px; text-decoration: none; font-size: 14px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 8px;">
                    📥 CSV Import
                </a>
                <a href="{{ route('projects.create') }}" style="background: #ffffff; color: #374151; padding: 10px 20px; border-radius: 12px; text-decoration: none; font-size: 14px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 8px;">
                    ➕ Neues Projekt
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

    <!-- Projects Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px;">
        @forelse($projects as $project)
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <!-- Project Header -->
                <div style="padding: 20px; border-bottom: 1px solid #e5e7eb; position: relative;">
                    @php
                        $isCritical = $project->status == 'active' && 
                                     ($project->calculated_progress ?? $project->progress) < 50 && 
                                     \Carbon\Carbon::parse($project->end_date)->diffInDays(now()) < 30;
                        $isOverBudget = isset($project->budget_utilization) && $project->budget_utilization > 100;
                        $isOverTeam = isset($project->team_utilization) && $project->team_utilization > 100;
                    @endphp
                    
                    @if($isCritical || $isOverBudget || $isOverTeam)
                        <div style="position: absolute; top: 8px; right: 8px; display: flex; gap: 4px;">
                            @if($isCritical)
                                <span style="background: #dc2626; color: white; padding: 2px 6px; border-radius: 6px; font-size: 10px; font-weight: 600;">
                                    KRITISCH
                                </span>
                            @endif
                            @if($isOverBudget)
                                <span style="background: #f59e0b; color: white; padding: 2px 6px; border-radius: 6px; font-size: 10px; font-weight: 600;">
                                    ÜBER BUDGET
                                </span>
                            @endif
                            @if($isOverTeam)
                                <span style="background: #8b5cf6; color: white; padding: 2px 6px; border-radius: 6px; font-size: 10px; font-weight: 600;">
                                    ÜBERLASTET
                                </span>
                            @endif
                        </div>
                    @endif
                    
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                        <div style="flex: 1;">
                            <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 8px 0;">{{ $project->name }}</h3>
                            <p style="color: #6b7280; font-size: 14px; line-height: 1.5; margin: 0;">{{ Str::limit($project->description, 100) }}</p>
                            @if($project->responsible)
                                <p style="color: #374151; font-size: 12px; margin: 8px 0 0 0; font-weight: 500;">
                                    👤 Verantwortlich: {{ $project->responsible->first_name }} {{ $project->responsible->last_name }}
                                </p>
                            @endif
                        </div>
                        <span style="background: {{ $project->status == 'active' ? '#dcfce7' : ($project->status == 'planning' ? '#dbeafe' : ($project->status == 'completed' ? '#e0e7ff' : '#fef3c7')) }}; color: {{ $project->status == 'active' ? '#166534' : ($project->status == 'planning' ? '#1e40af' : ($project->status == 'completed' ? '#3730a3' : '#92400e')) }}; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500; white-space: nowrap;">
                            {{ ucfirst($project->status) }}
                        </span>
                    </div>
                </div>

                <!-- Project Content -->
                <div style="padding: 20px;">
                    <!-- Progress Bar -->
                    <div style="margin-bottom: 16px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 14px; font-weight: 500; color: #374151;">Fortschritt</span>
                            <span style="font-size: 14px; font-weight: 600; color: #111827;">{{ round($project->calculated_progress ?? $project->progress) }}%</span>
                        </div>
                        <div style="background: #e5e7eb; height: 8px; border-radius: 4px; overflow: hidden;">
                            <div style="background: {{ ($project->calculated_progress ?? $project->progress) >= 100 ? '#10b981' : (($project->calculated_progress ?? $project->progress) >= 75 ? '#f59e0b' : '#2563eb') }}; height: 100%; width: {{ $project->calculated_progress ?? $project->progress }}%; transition: width 0.3s;"></div>
                        </div>
                    </div>

                    <!-- Project Stats -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                        <div>
                            <div style="font-size: 12px; color: #6b7280; margin-bottom: 4px;">Arbeitsstunden</div>
                            <div style="font-size: 16px; font-weight: 600; color: #111827;">
                                @if(($project->actual_hours ?? 0) > 0)
                                    {{ number_format($project->actual_hours, 1) }}h
                                    @if($project->estimated_hours > 0)
                                        <span style="font-size: 12px; color: #6b7280;">/ {{ $project->estimated_hours }}h</span>
                                    @endif
                                @else
                                    <span style="color: #9ca3af; font-size: 14px;">Keine Zeiterfassung</span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <div style="font-size: 12px; color: #6b7280; margin-bottom: 4px;">Kosten</div>
                            <div style="font-size: 16px; font-weight: 600; color: #111827;">
                                @if(($project->actual_cost ?? 0) > 0)
                                    {{ number_format($project->actual_cost, 0) }}€
                                    @if($project->budget_utilization > 0)
                                        <span style="font-size: 12px; color: {{ $project->budget_utilization > 100 ? '#dc2626' : ($project->budget_utilization > 80 ? '#f59e0b' : '#6b7280') }};">
                                            ({{ round($project->budget_utilization) }}%)
                                        </span>
                                    @endif
                                @else
                                    <span style="color: #9ca3af; font-size: 14px;">Keine Kosten</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Project Dates -->
                    <div style="margin-bottom: 16px;">
                        <div style="font-size: 12px; color: #6b7280; margin-bottom: 4px;">Zeitraum</div>
                        <div style="font-size: 14px; color: #374151;">
                            {{ \Carbon\Carbon::parse($project->start_date)->format('d.m.Y') }} - 
                            {{ \Carbon\Carbon::parse($project->end_date)->format('d.m.Y') }}
                        </div>
                    </div>

                    <!-- Team Members & Budget Info -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                        <div>
                            <div style="font-size: 12px; color: #6b7280; margin-bottom: 4px;">Team</div>
                            <div style="font-size: 14px; color: #374151;">
                                @if(($project->team_members_count ?? $project->assignments->count()) > 0)
                                    {{ $project->team_members_count ?? $project->assignments->count() }} Mitglieder
                                    @if(isset($project->team_utilization) && $project->team_utilization > 0)
                                        <span style="font-size: 12px; color: {{ $project->team_utilization > 100 ? '#dc2626' : ($project->team_utilization > 80 ? '#f59e0b' : '#6b7280') }};">
                                            ({{ round($project->team_utilization) }}% ausgelastet)
                                        </span>
                                    @endif
                                @else
                                    <span style="color: #9ca3af;">Kein Team zugewiesen</span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <div style="font-size: 12px; color: #6b7280; margin-bottom: 4px;">Budget</div>
                            <div style="font-size: 14px; color: #374151;">
                                @if($project->budget > 0)
                                    {{ number_format($project->remaining_budget ?? $project->budget, 0) }}€ verbleibend
                                @else
                                    <span style="color: #9ca3af;">Kein Budget gesetzt</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Team Members List -->
                    @if($project->assignments->count() > 0)
                        <div style="margin-bottom: 16px;">
                            <div style="font-size: 12px; color: #6b7280; margin-bottom: 8px;">Team-Mitglieder</div>
                            <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                @foreach($project->assignments->take(3) as $assignment)
                                    <div style="background: #f3f4f6; color: #374151; padding: 2px 6px; border-radius: 8px; font-size: 11px;">
                                        {{ $assignment->employee->first_name }} {{ $assignment->employee->last_name }}
                                    </div>
                                @endforeach
                                @if($project->assignments->count() > 3)
                                    <div style="background: #e5e7eb; color: #6b7280; padding: 2px 6px; border-radius: 8px; font-size: 11px;">
                                        +{{ $project->assignments->count() - 3 }} weitere
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <!-- Konfigurations-Hinweise für leere Projekte -->
                        <div style="margin-bottom: 16px; padding: 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px;">
                            <div style="font-size: 12px; color: #64748b; margin-bottom: 6px; font-weight: 500;">💡 Konfiguration erforderlich:</div>
                            <div style="font-size: 11px; color: #64748b; line-height: 1.4;">
                                @if($project->budget == 0)
                                    • Budget setzen für Kostenverfolgung<br>
                                @endif
                                @if($project->assignments->count() == 0)
                                    • Team-Mitglieder zuweisen<br>
                                @endif
                                @if(($project->actual_hours ?? 0) == 0)
                                    • Zeiterfassung aktivieren<br>
                                @endif
                                <span style="color: #3b82f6;">→ Projekt bearbeiten</span>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Project Actions -->
                <div style="padding: 16px 20px; background: #f9fafb; border-top: 1px solid #e5e7eb;">
                    <div style="display: flex; gap: 8px;">
                        <a href="{{ route('projects.show', $project) }}" style="background: #ffffff; color: #374151; padding: 6px 12px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 4px;">
                            👁 Anzeigen
                        </a>
                        <a href="{{ route('projects.edit', $project) }}" style="background: #ffffff; color: #374151; padding: 6px 12px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 4px;">
                            ✏️ Bearbeiten
                        </a>
                        <form action="{{ route('projects.destroy', $project) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background: #ffffff; color: #dc2626; padding: 6px 12px; border-radius: 8px; border: none; font-size: 12px; font-weight: 500; cursor: pointer; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 4px;" onclick="return confirm('Sind Sie sicher, dass Sie dieses Projekt löschen möchten?')">
                                🗑️ Löschen
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                <div style="font-size: 48px; margin-bottom: 16px;">📁</div>
                <h3 style="font-size: 18px; font-weight: 500; color: #111827; margin: 0 0 8px 0;">Keine Projekte</h3>
                <p style="color: #6b7280; margin: 0;">Der Bereich Projekt-Verwaltung ist derzeit leer.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection