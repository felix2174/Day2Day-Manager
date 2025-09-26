@extends('layouts.app')

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
    .section-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 16px;
        color: #111827;
    }
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    .stat-card {
        background: #f9fafb;
        border-radius: 8px;
        padding: 20px;
        border: 1px solid #e5e7eb;
        text-align: center;
    }
    .stat-number {
        font-size: 24px;
        font-weight: bold;
        color: #111827;
        margin-bottom: 4px;
    }
    .stat-label {
        font-size: 12px;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
</style>

<div class="detail-container">
    <!-- Header -->
    <div class="info-card">
        <div class="info-header">
            <div>
                <h1 style="margin: 0; font-size: 24px; font-weight: bold; color: #111827;">{{ $team->name }}</h1>
                <p style="color: #6b7280; margin: 5px 0 0 0;">{{ $team->description }}</p>
            </div>
            <div>
                <a href="{{ route('teams.edit', $team) }}" style="padding: 12px 24px; background: #ffffff; color: #374151;
                       border: none; border-radius: 12px; text-decoration: none; margin-right: 10px;
                       font-size: 14px; font-weight: 500; transition: all 0.2s ease;
                       box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                       onmouseover='this.style.transform=\"translateY(-1px)\"; this.style.boxShadow=\"0 4px 8px rgba(0, 0, 0, 0.15)\"; this.style.background=\"#f9fafb\";'
                       onmouseout='this.style.transform=\"translateY(0)\"; this.style.boxShadow=\"0 2px 4px rgba(0, 0, 0, 0.1)\"; this.style.background=\"#ffffff\";'">
                    Bearbeiten
                </a>
                <a href="{{ route('teams.index') }}" style="padding: 12px 24px; background: #ffffff; color: #374151;
                       border: none; border-radius: 12px; text-decoration: none;
                       font-size: 14px; font-weight: 500; transition: all 0.2s ease;
                       box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                       onmouseover='this.style.transform=\"translateY(-1px)\"; this.style.boxShadow=\"0 4px 8px rgba(0, 0, 0, 0.15)\"; this.style.background=\"#f9fafb\";'
                       onmouseout='this.style.transform=\"translateY(0)\"; this.style.boxShadow=\"0 2px 4px rgba(0, 0, 0, 0.1)\"; this.style.background=\"#ffffff\";'">
                    Zurück zur Übersicht
                </a>
            </div>
        </div>

        <!-- Team Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">{{ $employees->count() }}</div>
                <div class="stat-label">Mitarbeiter</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $team->projects->count() }}</div>
                <div class="stat-label">Projekte</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ round($team->projects->avg('progress') ?? 0) }}%</div>
                <div class="stat-label">Ø Fortschritt</div>
            </div>
        </div>
    </div>

    <!-- Team Members -->
    @if($employees->count() > 0)
    <div class="info-card">
        <h2 class="section-title">Team-Mitglieder</h2>
        <div style="display: grid; gap: 12px;">
            @foreach($employees as $employee)
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px; background: #f9fafb; border-radius: 8px; border: 1px solid #e5e7eb;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 40px; height: 40px; background: #3b82f6; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 16px;">
                        {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                    </div>
                    <div>
                        <div style="font-weight: 500; color: #111827;">{{ $employee->first_name }} {{ $employee->last_name }}</div>
                        <div style="font-size: 14px; color: #6b7280;">{{ $employee->department }}</div>
                    </div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 14px; font-weight: 600; color: #2563eb;">{{ $employee->weekly_capacity }}h</div>
                    <div style="font-size: 12px; color: #6b7280;">pro Woche</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Team Projects -->
    @if($team->projects->count() > 0)
    <div class="info-card">
        <h2 class="section-title">Team-Projekte</h2>
        <div style="display: grid; gap: 12px;">
            @foreach($team->projects as $project)
            <div style="padding: 16px; background: #f9fafb; border-radius: 8px; border: 1px solid #e5e7eb;">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                    <div>
                        <div style="font-weight: 500; color: #111827; margin-bottom: 4px;">{{ $project->name }}</div>
                        <div style="font-size: 14px; color: #6b7280;">{{ $project->description }}</div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 14px; font-weight: 600; color: #2563eb;">{{ round($project->progress) }}%</div>
                        <div style="font-size: 12px; color: #6b7280;">Fortschritt</div>
                    </div>
                </div>
                <div style="background: #e5e7eb; height: 8px; border-radius: 4px; overflow: hidden;">
                    <div style="height: 100%; background: #10b981; width: {{ round($project->progress) }}%; transition: width 0.3s ease;"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection