@extends('layouts.app')

@section('content')
    <style>
        body {
            background: #f5f5f5;
        }
        .dashboard-container {
            padding: 2rem;
        }
        .metric-cards {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .metric-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            flex: 1;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            text-align: center;
        }
        .metric-value {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .metric-label {
            color: #6c757d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .metric-card.total { border-top: 3px solid #007bff; }
        .metric-card.total .metric-value { color: #007bff; }
        .metric-card.allocated { border-top: 3px solid #ffc107; }
        .metric-card.allocated .metric-value { color: #ffc107; }
        .metric-card.available { border-top: 3px solid #28a745; }
        .metric-card.available .metric-value { color: #28a745; }
        .section-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        }
        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #333;
        }
        .capacity-bar {
            background: #e9ecef;
            height: 30px;
            border-radius: 15px;
            overflow: hidden;
            margin: 0.75rem 0;
        }
        .capacity-fill {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.85rem;
            transition: width 0.3s ease;
        }
        .capacity-fill.low { background: #28a745; }
        .capacity-fill.medium { background: #ffc107; }
        .capacity-fill.high { background: #dc3545; }
        .employee-row {
            padding: 1rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        .employee-row:last-child {
            border-bottom: none;
        }
        .project-card {
            background: #f8f9fa;
            border-radius: 6px;
            padding: 1rem;
            margin-bottom: 1rem;
            border-left: 3px solid #007bff;
        }
        .project-progress {
            background: #e9ecef;
            height: 20px;
            border-radius: 10px;
            overflow: hidden;
            margin: 0.5rem 0;
        }
        .project-progress-fill {
            background: #007bff;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .absence-card {
            background: #fff5f5;
            border-left: 3px solid #dc3545;
            padding: 1rem;
            margin-bottom: 0.75rem;
            border-radius: 4px;
        }
        .absence-card.vacation {
            background: #fffbf0;
            border-left-color: #ffc107;
        }
        .absence-card.training {
            background: #f0f9ff;
            border-left-color: #17a2b8;
        }
    </style>

    <div class="dashboard-container">
        <!-- Metrics Overview -->
        <div class="metric-cards">
            <div class="metric-card total">
                <div class="metric-value">{{ $resourceOverview['total_capacity'] }}h</div>
                <div class="metric-label">Gesamtkapazität/Woche</div>
            </div>
            <div class="metric-card allocated">
                <div class="metric-value">{{ $resourceOverview['total_assigned'] }}h</div>
                <div class="metric-label">Bereits verplant</div>
            </div>
            <div class="metric-card available">
                <div class="metric-value">{{ $resourceOverview['total_available'] }}h</div>
                <div class="metric-label">Noch verfügbar</div>
            </div>
        </div>

        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Employee Capacities -->
                <div class="section-card">
                    <h2 class="section-title">Mitarbeiter-Auslastung</h2>

                    @foreach($employeeWorkloads as $workload)
                        <div class="employee-row">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <strong>{{ $workload['employee']->first_name }} {{ $workload['employee']->last_name }}</strong>
                                    <span class="text-muted ms-2">({{ $workload['employee']->department }})</span>
                                </div>
                                <div class="text-end">
                                    <span class="text-success fw-bold">{{ $workload['free_hours'] }}h frei</span>
                                    <span class="text-muted ms-2">von {{ $workload['weekly_capacity'] }}h</span>
                                </div>
                            </div>
                            <div class="capacity-bar">
                                <div class="capacity-fill {{ $workload['utilization'] > 90 ? 'high' : ($workload['utilization'] > 70 ? 'medium' : 'low') }}"
                                     style="width: {{ min(100, $workload['utilization']) }}%;">
                                    {{ $workload['utilization'] }}%
                                </div>
                            </div>
                            @if($workload['assignments']->count() > 0)
                                <small class="text-muted">
                                    Projekte:
                                    @foreach($workload['assignments'] as $assignment)
                                        {{ $assignment->project_name }} ({{ $assignment->weekly_hours }}h){{ !$loop->last ? ', ' : '' }}
                                    @endforeach
                                </small>
                            @else
                                <small class="text-success">✓ Vollständig verfügbar</small>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Current Projects -->
                <div class="section-card">
                    <h2 class="section-title">Aktuelle Projekte</h2>

                    @forelse($projectData as $data)
                        <div class="project-card">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <strong>{{ $data['project']->name }}</strong>
                                    @if($data['weekly_hours'] > 0)
                                        <span class="badge bg-primary ms-2">{{ $data['weekly_hours'] }}h/Woche</span>
                                    @endif
                                </div>
                                <small class="text-muted">
                                    {{ \Carbon\Carbon::parse($data['project']->start_date)->format('d.m.Y') }} -
                                    {{ \Carbon\Carbon::parse($data['project']->end_date)->format('d.m.Y') }}
                                </small>
                            </div>
                            <div class="project-progress">
                                <div class="project-progress-fill" style="width: {{ $data['progress'] }}%;">
                                    {{ $data['progress'] }}%
                                </div>
                            </div>
                            @if($data['project']->description)
                                <small class="text-muted">{{ $data['project']->description }}</small>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted">Keine aktiven Projekte vorhanden</p>
                    @endforelse
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- Absences -->
                <div class="section-card">
                    <h2 class="section-title">Abwesenheiten</h2>
                    <p class="text-muted mb-3">Nächste 30 Tage</p>

                    @forelse($absences as $absence)
                        <div class="absence-card {{ $absence->type }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <strong>{{ $absence->first_name }} {{ $absence->last_name }}</strong>
                                @if($absence->type == 'vacation')
                                    <span class="badge bg-warning">Urlaub</span>
                                @elseif($absence->type == 'sick')
                                    <span class="badge bg-danger">Krankheit</span>
                                @elseif($absence->type == 'training')
                                    <span class="badge bg-info">Fortbildung</span>
                                @endif
                            </div>
                            <small class="text-muted">
                                {{ \Carbon\Carbon::parse($absence->start_date)->format('d.m.Y') }} -
                                {{ \Carbon\Carbon::parse($absence->end_date)->format('d.m.Y') }}
                                ({{ \Carbon\Carbon::parse($absence->start_date)->diffInDays(\Carbon\Carbon::parse($absence->end_date)) + 1 }} Tage)
                            </small>
                            @if($absence->reason)
                                <div class="mt-1">
                                    <small>{{ $absence->reason }}</small>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted">Keine Abwesenheiten in den nächsten 30 Tagen</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
