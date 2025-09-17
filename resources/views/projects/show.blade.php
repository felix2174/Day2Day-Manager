@extends('layouts.app')

@section('title', 'Projekt-Details')

@section('content')
    <style>
        .detail-container {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        .info-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        }
        .info-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e9ecef;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .info-item {
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 6px;
        }
        .info-label {
            font-size: 0.875rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }
        .info-value {
            font-size: 1.25rem;
            font-weight: 600;
            color: #212529;
        }
        .progress-meter {
            background: #e9ecef;
            height: 30px;
            border-radius: 15px;
            overflow: hidden;
            margin: 1rem 0;
        }
        .progress-fill {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            transition: width 0.3s ease;
        }
        .team-member {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 6px;
            margin-bottom: 0.5rem;
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
                       style="background: #667eea; color: white; padding: 10px 20px; border-radius: 4px;
                          text-decoration: none; margin-right: 10px; border: 1px solid #5a67d8;
                          box-shadow: 0 2px 4px rgba(0,0,0,0.15);">
                        Bearbeiten
                    </a>
                    <a href="/projects"
                       style="background: #6c757d; color: white; padding: 10px 20px; border-radius: 4px;
                          text-decoration: none; border: 1px solid #5a6268;
                          box-shadow: 0 2px 4px rgba(0,0,0,0.15);">
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
                    $total_days = $start->diffInDays($end);
                    $elapsed_days = $start->diffInDays(now());
                    $progress = min(100, round(($elapsed_days / max(1, $total_days)) * 100));
                    $remaining_days = max(0, now()->diffInDays($end, false));
                @endphp
                <div style="margin-top: 2rem;">
                    <h3 style="margin-bottom: 1rem;">Projekt-Fortschritt</h3>
                    <div class="progress-meter">
                        <div class="progress-fill"
                             style="width: {{ $progress }}%;
                                background: {{ $progress >= 90 ? '#dc3545' : ($progress >= 70 ? '#ffc107' : '#28a745') }};">
                            {{ $progress }}%
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-top: 0.5rem;">
                        <span>Gestartet: {{ $start->format('d.m.Y') }}</span>
                        @if($remaining_days > 0)
                            <span style="color: #28a745; font-weight: bold;">{{ $remaining_days }} Tage verbleibend</span>
                        @else
                            <span style="color: #dc3545; font-weight: bold;">Überfällig</span>
                        @endif
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
