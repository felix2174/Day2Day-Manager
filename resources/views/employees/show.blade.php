@extends('layouts.app')

@section('title', 'Mitarbeiter-Details')

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
        .capacity-meter {
            background: #e9ecef;
            height: 40px;
            border-radius: 20px;
            overflow: hidden;
            margin: 1rem 0;
        }
        .capacity-fill {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            transition: width 0.3s ease;
        }
        .table-section {
            margin-top: 2rem;
        }
        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #212529;
        }
        .status-badge {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 500;
            display: inline-block;
        }
        .status-active { background: #d4edda; color: #155724; }
        .status-completed { background: #e2e3e5; color: #383d41; }
        .status-planned { background: #fff3cd; color: #856404; }
    </style>

    <div class="detail-container">
        <!-- Header -->
        <div class="info-card">
            <div class="info-header">
                <div>
                    <h1 style="margin: 0;">{{ $employee->first_name }} {{ $employee->last_name }}</h1>
                    <p style="color: #6c757d; margin: 0.5rem 0;">{{ $employee->department }}</p>
                </div>
                <div>
                    <a href="{{ route('employees.edit', $employee->id) }}"
                       style="background: #667eea; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none; margin-right: 10px;">
                        Bearbeiten
                    </a>
                    <a href="{{ route('employees.index') }}"
                       style="background: #6c757d; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none;">
                        Zurück zur Übersicht
                    </a>
                </div>
            </div>

            <!-- Stammdaten -->
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Mitarbeiter-ID</div>
                    <div class="info-value">#{{ $employee->id }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Abteilung</div>
                    <div class="info-value">{{ $employee->department }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Wochenkapazität</div>
                    <div class="info-value">{{ $employee->weekly_capacity }} Stunden</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Status</div>
                    <div class="info-value">
                        @if($employee->is_active)
                            <span class="status-badge status-active">Aktiv</span>
                        @else
                            <span class="status-badge" style="background: #f8d7da; color: #721c24;">Inaktiv</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Aktuelle Auslastung -->
            @php
                $current_assignments = collect($assignments)->filter(function($a) {
                    $start = \Carbon\Carbon::parse($a->start_date);
                    $end = \Carbon\Carbon::parse($a->end_date);
                    return now()->between($start, $end);
                });
                $total_hours = $current_assignments->sum('weekly_hours');
                $utilization = $employee->weekly_capacity > 0 ? round(($total_hours / $employee->weekly_capacity) * 100) : 0;
                $free_hours = $employee->weekly_capacity - $total_hours;
            @endphp

            <div style="margin-top: 2rem;">
                <h3 style="margin-bottom: 1rem;">Aktuelle Auslastung</h3>
                <div class="capacity-meter">
                    <div class="capacity-fill"
                         style="width: {{ min(100, $utilization) }}%;
                            background: {{ $utilization > 90 ? '#dc3545' : ($utilization > 70 ? '#ffc107' : '#28a745') }};">
                        {{ $utilization }}%
                    </div>
                </div>
                <div style="display: flex; justify-content: space-between; margin-top: 0.5rem;">
                    <span>{{ $total_hours }}h / {{ $employee->weekly_capacity }}h belegt</span>
                    <span style="color: #28a745; font-weight: bold;">{{ $free_hours }}h verfügbar</span>
                </div>
            </div>
        </div>

        <!-- Aktuelle Projekte -->
        <div class="info-card">
            <h2 class="section-title">Aktuelle Projekte</h2>
            @if($current_assignments->count() > 0)
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                    <tr style="border-bottom: 2px solid #dee2e6;">
                        <th style="padding: 10px; text-align: left;">Projekt</th>
                        <th style="padding: 10px; text-align: center;">Wochenstunden</th>
                        <th style="padding: 10px; text-align: center;">Zeitraum</th>
                        <th style="padding: 10px; text-align: center;">Verbleibend</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($current_assignments as $assignment)
                        @php
                            $end = \Carbon\Carbon::parse($assignment->end_date);
                            $days_left = now()->diffInDays($end);
                        @endphp
                        <tr style="border-bottom: 1px solid #dee2e6;">
                            <td style="padding: 10px;">
                                <strong>{{ $assignment->project_name }}</strong>
                            </td>
                            <td style="padding: 10px; text-align: center;">
                                <span style="font-size: 1.1rem; font-weight: bold; color: #007bff;">
                                    {{ $assignment->weekly_hours }}h
                                </span>
                            </td>
                            <td style="padding: 10px; text-align: center;">
                                {{ \Carbon\Carbon::parse($assignment->start_date)->format('d.m.Y') }} -
                                {{ $end->format('d.m.Y') }}
                            </td>
                            <td style="padding: 10px; text-align: center;">
                                <span class="status-badge status-active">
                                    {{ $days_left }} Tage
                                </span>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <p style="color: #6c757d;">Derzeit keine aktiven Projekte</p>
            @endif
        </div>

        <!-- Projekt-Historie -->
        <div class="info-card">
            <h2 class="section-title">Projekt-Historie</h2>
            @php
                $all_assignments = collect($assignments)->sortByDesc('end_date');
            @endphp

            @if($all_assignments->count() > 0)
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                    <tr style="border-bottom: 2px solid #dee2e6;">
                        <th style="padding: 10px; text-align: left;">Projekt</th>
                        <th style="padding: 10px; text-align: center;">Zeitraum</th>
                        <th style="padding: 10px; text-align: center;">Wochenstunden</th>
                        <th style="padding: 10px; text-align: center;">Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($all_assignments as $assignment)
                        @php
                            $start = \Carbon\Carbon::parse($assignment->start_date);
                            $end = \Carbon\Carbon::parse($assignment->end_date);
                            $is_current = now()->between($start, $end);
                            $is_past = now()->gt($end);
                            $is_future = now()->lt($start);
                        @endphp
                        <tr style="border-bottom: 1px solid #dee2e6;">
                            <td style="padding: 10px;">{{ $assignment->project_name }}</td>
                            <td style="padding: 10px; text-align: center;">
                                {{ $start->format('d.m.Y') }} - {{ $end->format('d.m.Y') }}
                            </td>
                            <td style="padding: 10px; text-align: center;">{{ $assignment->weekly_hours }}h</td>
                            <td style="padding: 10px; text-align: center;">
                                @if($is_current)
                                    <span class="status-badge status-active">Läuft</span>
                                @elseif($is_past)
                                    <span class="status-badge status-completed">Abgeschlossen</span>
                                @else
                                    <span class="status-badge status-planned">Geplant</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <p style="color: #6c757d;">Noch keine Projektzuweisungen vorhanden</p>
            @endif
        </div>
    </div>
@endsection
