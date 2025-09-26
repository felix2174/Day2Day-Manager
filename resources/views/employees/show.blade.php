@extends('layouts.app')

@section('title', 'Mitarbeiter-Details')

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
        .capacity-meter {
            background: #e5e7eb;
            height: 32px;
            border-radius: 16px;
            overflow: hidden;
            margin: 16px 0;
        }
        .capacity-fill {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 14px;
            transition: width 0.3s ease;
        }
        .table-section {
            margin-top: 24px;
        }
        .section-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 16px;
            color: #111827;
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }
        .status-active { background: #dcfce7; color: #166534; }
        .status-completed { background: #f3f4f6; color: #374151; }
        .status-planned { background: #fef3c7; color: #92400e; }
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
                       style="background: #ffffff; color: #374151; padding: 12px 24px; border-radius: 12px; text-decoration: none; margin-right: 10px;
                       font-size: 14px; font-weight: 500; transition: all 0.2s ease;
                       box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                       onmouseover='this.style.transform=\"translateY(-1px)\"; this.style.boxShadow=\"0 4px 8px rgba(0, 0, 0, 0.15)\"; this.style.background=\"#f9fafb\";'
                       onmouseout='this.style.transform=\"translateY(0)\"; this.style.boxShadow=\"0 2px 4px rgba(0, 0, 0, 0.1)\"; this.style.background=\"#ffffff\";'">
                        Bearbeiten
                    </a>
                    <a href="{{ route('employees.index') }}"
                       style="background: #ffffff; color: #374151; padding: 12px 24px; border-radius: 12px; text-decoration: none;
                       font-size: 14px; font-weight: 500; transition: all 0.2s ease;
                       box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                       onmouseover='this.style.transform=\"translateY(-1px)\"; this.style.boxShadow=\"0 4px 8px rgba(0, 0, 0, 0.15)\"; this.style.background=\"#f9fafb\";'
                       onmouseout='this.style.transform=\"translateY(0)\"; this.style.boxShadow=\"0 2px 4px rgba(0, 0, 0, 0.1)\"; this.style.background=\"#ffffff\";'">
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
                <table style="width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);">
                    <thead>
                    <tr style="background: #f9fafb; border-bottom: 1px solid #e5e7eb;">
                        <th style="padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Projekt</th>
                        <th style="padding: 12px 16px; text-align: center; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Wochenstunden</th>
                        <th style="padding: 12px 16px; text-align: center; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Zeitraum</th>
                        <th style="padding: 12px 16px; text-align: center; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Verbleibend</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($current_assignments as $assignment)
                        @php
                            $end = \Carbon\Carbon::parse($assignment->end_date);
                            $days_left = round(now()->diffInDays($end));
                        @endphp
                        <tr style="border-bottom: 1px solid #e5e7eb;">
                            <td style="padding: 16px; font-weight: 500; color: #111827;">
                                {{ $assignment->project_name }}
                            </td>
                            <td style="padding: 16px; text-align: center;">
                                <span style="font-size: 14px; font-weight: 600; color: #2563eb;">
                                    {{ $assignment->weekly_hours }}h
                                </span>
                            </td>
                            <td style="padding: 16px; text-align: center; color: #374151; font-size: 14px;">
                                {{ \Carbon\Carbon::parse($assignment->start_date)->format('d.m.Y') }} -
                                {{ $end->format('d.m.Y') }}
                            </td>
                            <td style="padding: 16px; text-align: center;">
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
                <table style="width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);">
                    <thead>
                    <tr style="background: #f9fafb; border-bottom: 1px solid #e5e7eb;">
                        <th style="padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Projekt</th>
                        <th style="padding: 12px 16px; text-align: center; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Zeitraum</th>
                        <th style="padding: 12px 16px; text-align: center; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Wochenstunden</th>
                        <th style="padding: 12px 16px; text-align: center; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Status</th>
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
                        <tr style="border-bottom: 1px solid #e5e7eb;">
                            <td style="padding: 16px; font-weight: 500; color: #111827;">{{ $assignment->project_name }}</td>
                            <td style="padding: 16px; text-align: center; color: #374151; font-size: 14px;">
                                {{ $start->format('d.m.Y') }} - {{ $end->format('d.m.Y') }}
                            </td>
                            <td style="padding: 16px; text-align: center; color: #374151; font-size: 14px;">{{ $assignment->weekly_hours }}h</td>
                            <td style="padding: 16px; text-align: center;">
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
