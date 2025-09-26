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
        .progress-meter {
            background: #e5e7eb;
            height: 32px;
            border-radius: 16px;
            overflow: hidden;
            margin: 16px 0;
        }
        .progress-fill {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 14px;
            transition: width 0.3s ease;
        }
        .team-member {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px;
            background: #f9fafb;
            border-radius: 8px;
            margin-bottom: 8px;
            border: 1px solid #e5e7eb;
        }
        .member-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .member-avatar {
            width: 40px;
            height: 40px;
            background: #667eea;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        .status-badge {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 500;
            display: inline-block;
        }
        .status-active { background: #d4edda; color: #155724; }
        .status-planning { background: #fff3cd; color: #856404; }
        .status-completed { background: #e2e3e5; color: #383d41; }
    </style>

    <div class="detail-container">
        <!-- Header -->
        <div class="info-card">
            <div class="info-header">
                <div>
                    <h1 style="margin: 0;">{{ $project->name }}</h1>
                    @if($project->description)
                        <p style="color: #6c757d; margin: 0.5rem 0;">{{ $project->description }}</p>
                    @endif
                </div>
                <div>
                    <a href="/projects/{{ $project->id }}/edit"
                       style="background: #ffffff; color: #374151; padding: 12px 24px; border-radius: 12px;
                          text-decoration: none; margin-right: 10px; border: none;
                          box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); font-size: 14px; font-weight: 500; transition: all 0.2s ease;
                          onmouseover='this.style.transform=\"translateY(-1px)\"; this.style.boxShadow=\"0 4px 8px rgba(0, 0, 0, 0.15)\"; this.style.background=\"#f9fafb\";'
                          onmouseout='this.style.transform=\"translateY(0)\"; this.style.boxShadow=\"0 2px 4px rgba(0, 0, 0, 0.1)\"; this.style.background=\"#ffffff\";'">
                        Bearbeiten
                    </a>
                    <a href="/projects"
                       style="background: #ffffff; color: #374151; padding: 12px 24px; border-radius: 12px;
                          text-decoration: none; border: none;
                          box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); font-size: 14px; font-weight: 500; transition: all 0.2s ease;
                          onmouseover='this.style.transform=\"translateY(-1px)\"; this.style.boxShadow=\"0 4px 8px rgba(0, 0, 0, 0.15)\"; this.style.background=\"#f9fafb\";'
                          onmouseout='this.style.transform=\"translateY(0)\"; this.style.boxShadow=\"0 2px 4px rgba(0, 0, 0, 0.1)\"; this.style.background=\"#ffffff\";'">
                        Zurück zur Übersicht
                    </a>
                </div>
            </div>

            <!-- Projekt-Informationen -->
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Projekt-ID</div>
                    <div class="info-value">#{{ $project->id }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Status</div>
                    <div class="info-value">
                        @if($project->status == 'active')
                            <span class="status-badge status-active">Aktiv</span>
                        @elseif($project->status == 'planning')
                            <span class="status-badge status-planning">In Planung</span>
                        @else
                            <span class="status-badge status-completed">Abgeschlossen</span>
                        @endif
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Startdatum</div>
                    <div class="info-value">{{ \Carbon\Carbon::parse($project->start_date)->format('d.m.Y') }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Enddatum</div>
                    <div class="info-value">
                        @if($project->end_date)
                            {{ \Carbon\Carbon::parse($project->end_date)->format('d.m.Y') }}
                        @else
                            <span style="color: #6c757d;">Nicht festgelegt</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Projekt-Fortschritt -->
            @if($project->end_date)
                @php
                    $start = \Carbon\Carbon::parse($project->start_date);
                    $end = \Carbon\Carbon::parse($project->end_date);
                    $remaining_days = round(now()->diffInDays($end, false));
                    // Verwende den gespeicherten Fortschritt oder berechne ihn
                    $progress = $project->progress ?? 0;
                @endphp
                <div style="margin-top: 2rem;">
                    <h3 style="margin-bottom: 1rem;">Projekt-Fortschritt</h3>
                    <div class="progress-meter">
                        <div class="progress-fill"
                             style="width: {{ $progress }}%;
                                background: {{ $progress >= 80 ? '#28a745' : ($progress >= 50 ? '#ffc107' : '#dc3545') }};">
                            {{ $progress }}%
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-top: 0.5rem;">
                        <span>Gestartet: {{ $start->format('d.m.Y') }}</span>
                        <span>Geplant bis: {{ $end->format('d.m.Y') }}</span>
                    </div>
                </div>
            @endif
        </div>

        <!-- Team-Mitglieder -->
        <div class="info-card">
            <h2 style="margin-bottom: 1.5rem;">Team-Mitglieder</h2>
            @if($assignments->count() > 0)
                @php
                    $total_weekly_hours = $assignments->sum('weekly_hours');
                @endphp
                <div style="margin-bottom: 1.5rem; padding: 1rem; background: #f8f9fa; border-radius: 6px;">
                    <strong>Gesamt-Wochenstunden: {{ $total_weekly_hours }}h</strong>
                </div>
                @foreach($assignments as $assignment)
                    <div class="team-member">
                        <div class="member-info">
                            <div class="member-avatar">
                                {{ substr($assignment->employee_name, 0, 1) }}
                            </div>
                            <div>
                                <strong>{{ $assignment->employee_name }}</strong>
                                <br>
                                <small style="color: #6c757d;">{{ $assignment->employee_department ?? 'N/A' }}</small>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <strong style="color: #667eea; font-size: 1.25rem;">{{ $assignment->weekly_hours }}h</strong>
                            <br>
                            <small style="color: #6c757d;">pro Woche</small>
                        </div>
                    </div>
                @endforeach
            @else
                <p style="color: #6c757d;">Noch keine Mitarbeiter zugewiesen</p>
            @endif
        </div>
    </div>
@endsection
