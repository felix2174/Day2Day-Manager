@extends('layouts.app')

@section('content')
<div style="width: 100%; margin: 0; padding: 0;">
    <!-- Page Header with Tabs -->
    <div style="background: white; padding: 20px; margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
        <div>
                <h1 style="font-size: 28px; font-weight: bold; color: #111827; margin: 0;">KPI Dashboard</h1>
                <p style="color: #6b7280; margin: 5px 0 0 0;">Echtzeit-Übersicht Ihrer Unternehmenskennzahlen</p>
            </div>
            <div style="display: flex; gap: 8px;">
                <button onclick="switchTab('executive')" id="tab-executive" style="padding: 8px 16px; border: 1px solid #e5e7eb; background: linear-gradient(135deg, #3b82f6, #8b5cf6); color: white; border-radius: 8px; font-weight: 500; cursor: pointer; transition: all 0.2s;">
                    Executive View
                </button>
                <button onclick="switchTab('manager')" id="tab-manager" style="padding: 8px 16px; border: 1px solid #e5e7eb; background: white; color: #374151; border-radius: 8px; font-weight: 500; cursor: pointer; transition: all 0.2s;">
                    Project Manager View
                </button>
            </div>
        </div>
    </div>

    <!-- EXECUTIVE VIEW -->
    <div id="view-executive">
        <!-- Top 4 KPI Cards -->
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 24px;">
            <!-- Revenue Card -->
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div style="flex: 1;">
                        <p style="color: #6b7280; font-size: 13px; font-weight: 500; margin: 0 0 8px 0; text-transform: uppercase; letter-spacing: 0.5px;">Geschätzter Projektumsatz</p>
                        <p style="font-size: 32px; font-weight: bold; color: #111827; margin: 0; line-height: 1;">€{{ number_format($totalEstimatedRevenue, 0, ',', '.') }}</p>
                        <p style="color: #10b981; font-size: 12px; margin: 8px 0 0 0; font-weight: 500;">
                            ▲ €{{ number_format($totalActualRevenue, 0, ',', '.') }} realisiert
                        </p>
                    </div>
                    <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                            <line x1="12" y1="1" x2="12" y2="23"></line>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Project Performance Card -->
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div style="flex: 1;">
                        <p style="color: #6b7280; font-size: 13px; font-weight: 500; margin: 0 0 8px 0; text-transform: uppercase; letter-spacing: 0.5px;">Projekt Performance</p>
                        <p style="font-size: 32px; font-weight: bold; color: #111827; margin: 0; line-height: 1;">{{ $projectPerformanceScore }}%</p>
                        <p style="color: #6b7280; font-size: 12px; margin: 8px 0 0 0; font-weight: 500;">
                            {{ $projectHealth['on_track'] }} von {{ $activeProjects }} on track
                        </p>
                    </div>
                    <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #3b82f6, #2563eb); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    </div>
                </div>
            </div>

            <!-- Team Utilization Card -->
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div style="flex: 1;">
                        <p style="color: #6b7280; font-size: 13px; font-weight: 500; margin: 0 0 8px 0; text-transform: uppercase; letter-spacing: 0.5px;">Team Auslastung</p>
                        <p style="font-size: 32px; font-weight: bold; color: #111827; margin: 0; line-height: 1;">{{ $averageUtilization }}%</p>
                        <p style="color: {{ $averageUtilization >= 70 && $averageUtilization <= 85 ? '#10b981' : ($averageUtilization > 85 ? '#f59e0b' : '#ef4444') }}; font-size: 12px; margin: 8px 0 0 0; font-weight: 500;">
                            {{ $averageUtilization >= 70 && $averageUtilization <= 85 ? 'Optimale Auslastung' : ($averageUtilization > 85 ? 'Hohe Auslastung' : 'Niedrige Auslastung') }}
                        </p>
                    </div>
                    <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #8b5cf6, #7c3aed); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
        </div>
                </div>
            </div>

            <!-- Budget Efficiency Card -->
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div style="flex: 1;">
                        <p style="color: #6b7280; font-size: 13px; font-weight: 500; margin: 0 0 8px 0; text-transform: uppercase; letter-spacing: 0.5px;">Budget Effizienz</p>
                        <p style="font-size: 32px; font-weight: bold; color: #111827; margin: 0; line-height: 1;">{{ $budgetEfficiency }}%</p>
                        <p style="color: {{ $budgetEfficiency >= 95 ? '#10b981' : '#f59e0b' }}; font-size: 12px; margin: 8px 0 0 0; font-weight: 500;">
                            {{ $budgetEfficiency >= 95 ? 'Im Budget' : 'Budget beobachten' }}
                        </p>
                    </div>
                    <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #f59e0b, #d97706); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 24px;">
            <!-- Project Distribution Pie Chart -->
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <div>
                        <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0;">Projektverteilung</h3>
                        <p style="color: #6b7280; font-size: 14px; margin: 4px 0 0 0;">{{ $projectDistribution['total'] }} Projekte gesamt</p>
                    </div>
                </div>
                <div style="position: relative; height: 200px; width: 100%; margin-bottom: 16px;">
                    <canvas id="projectDistributionChart"></canvas>
                </div>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="width: 12px; height: 12px; background: #3b82f6; border-radius: 2px;"></div>
                            <span style="color: #6b7280; font-size: 13px;">Aktiv</span>
                        </div>
                        <div style="text-align: right;">
                            <span style="color: #111827; font-weight: 600; font-size: 14px;">{{ $projectDistribution['active'] }}</span>
                            <span style="color: #6b7280; font-size: 12px; margin-left: 4px;">({{ $projectDistributionPercentages['active'] }}%)</span>
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="width: 12px; height: 12px; background: #10b981; border-radius: 2px;"></div>
                            <span style="color: #6b7280; font-size: 13px;">Abgeschlossen</span>
                        </div>
                        <div style="text-align: right;">
                            <span style="color: #111827; font-weight: 600; font-size: 14px;">{{ $projectDistribution['completed'] }}</span>
                            <span style="color: #6b7280; font-size: 12px; margin-left: 4px;">({{ $projectDistributionPercentages['completed'] }}%)</span>
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="width: 12px; height: 12px; background: #f59e0b; border-radius: 2px;"></div>
                            <span style="color: #6b7280; font-size: 13px;">Geplant</span>
                        </div>
                        <div style="text-align: right;">
                            <span style="color: #111827; font-weight: 600; font-size: 14px;">{{ $projectDistribution['planning'] }}</span>
                            <span style="color: #6b7280; font-size: 12px; margin-left: 4px;">({{ $projectDistributionPercentages['planning'] }}%)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Critical Projects -->
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 8px 0;">Kritische Projekte</h3>
                <p style="color: #6b7280; font-size: 14px; margin: 0 0 16px 0;">Überfällig & gefährdet</p>
                <div style="display: flex; flex-direction: column; gap: 12px; max-height: 280px; overflow-y: auto;">
                    @forelse($criticalProjects as $project)
                        <div style="padding: 12px; border-radius: 8px; border-left: 4px solid {{ $project['type'] === 'overdue' ? '#ef4444' : ($project['type'] === 'delayed' ? '#f59e0b' : '#f97316') }}; background: {{ $project['type'] === 'overdue' ? '#fef2f2' : ($project['type'] === 'delayed' ? '#fef3c7' : '#fff7ed') }};">
                            <div style="display: flex; justify-content: between; align-items: start; margin-bottom: 6px;">
                                <h4 style="color: #111827; font-size: 14px; font-weight: 600; margin: 0; flex: 1;">{{ Str::limit($project['name'], 25) }}</h4>
                                <span style="background: {{ $project['type'] === 'overdue' ? '#ef4444' : ($project['type'] === 'delayed' ? '#f59e0b' : '#f97316') }}; color: white; font-size: 10px; padding: 2px 6px; border-radius: 4px; font-weight: 500; text-transform: uppercase; margin-left: 8px;">
                                    {{ $project['type'] === 'overdue' ? 'Überfällig' : ($project['type'] === 'delayed' ? 'Verzögert' : 'Risiko') }}
                                </span>
                            </div>
                            <div style="display: flex; justify-content: between; align-items: center;">
                                <span style="color: #6b7280; font-size: 12px;">
                                    @if($project['type'] === 'overdue')
                                        {{ $project['days_overdue'] }} Tage überfällig
                                    @else
                                        {{ $project['progress'] }}% (erwartet: {{ $project['expected_progress'] }}%)
                                    @endif
                                </span>
                                <span style="color: #6b7280; font-size: 12px;">
                                    {{ Carbon\Carbon::parse($project['end_date'])->format('d.m.Y') }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div style="text-align: center; padding: 20px; color: #6b7280;">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" style="margin-bottom: 12px; opacity: 0.5;">
                                <path d="M9 12l2 2 4-4"></path>
                                <circle cx="12" cy="12" r="10"></circle>
                            </svg>
                            <p style="margin: 0; font-size: 14px;">Keine kritischen Projekte</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Project Health Donut -->
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 8px 0;">Projekt Status</h3>
                <p style="color: #6b7280; font-size: 14px; margin: 0 0 20px 0;">{{ $activeProjects }} aktive Projekte</p>
                <div style="position: relative; height: 200px; width: 100%;">
                    <canvas id="projectHealthChart"></canvas>
                </div>
                <div style="margin-top: 20px; display: flex; flex-direction: column; gap: 8px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="width: 12px; height: 12px; background: #10b981; border-radius: 2px;"></div>
                            <span style="color: #6b7280; font-size: 13px;">On Track</span>
                        </div>
                        <span style="color: #111827; font-weight: 600; font-size: 14px;">{{ $projectHealth['on_track'] }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="width: 12px; height: 12px; background: #f59e0b; border-radius: 2px;"></div>
                            <span style="color: #6b7280; font-size: 13px;">At Risk</span>
                        </div>
                        <span style="color: #111827; font-weight: 600; font-size: 14px;">{{ $projectHealth['at_risk'] }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="width: 12px; height: 12px; background: #ef4444; border-radius: 2px;"></div>
                            <span style="color: #6b7280; font-size: 13px;">Delayed</span>
                        </div>
                        <span style="color: #111827; font-weight: 600; font-size: 14px;">{{ $projectHealth['delayed'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Trend Chart -->
        <div style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div>
                    <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0;">Umsatzentwicklung</h3>
                    <p style="color: #6b7280; font-size: 14px; margin: 4px 0 0 0;">Letzte 6 Monate</p>
                </div>
            </div>
            <div style="position: relative; height: 250px; width: 100%;">
                <canvas id="revenueTrendChart"></canvas>
            </div>
        </div>

        <!-- Project Pipeline -->
        <div style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div>
                    <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0;">Projekt Pipeline</h3>
                    <p style="color: #6b7280; font-size: 14px; margin: 4px 0 0 0;">Kommende Projekte nach Monat</p>
                </div>
            </div>
            
            @if(count($projectPipeline) > 0)
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                    @foreach($projectPipeline as $month)
                        <div style="background: linear-gradient(135deg, #f9fafb, #ffffff); border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px;">
                            <div style="margin-bottom: 12px;">
                                <h4 style="color: #111827; font-size: 16px; font-weight: 600; margin: 0 0 4px 0;">{{ $month['month'] }}</h4>
                                <p style="color: #6b7280; font-size: 13px; margin: 0;">{{ $month['count'] }} {{ $month['count'] == 1 ? 'Projekt' : 'Projekte' }}</p>
                            </div>
                            <div style="background: white; border-radius: 6px; padding: 12px; margin-bottom: 12px;">
                                <p style="color: #6b7280; font-size: 11px; font-weight: 500; margin: 0 0 4px 0; text-transform: uppercase;">Geschätzter Umsatz</p>
                                <p style="color: #111827; font-size: 20px; font-weight: bold; margin: 0;">€{{ number_format($month['total_revenue'], 0, ',', '.') }}</p>
                            </div>
                            <div style="max-height: 120px; overflow-y: auto;">
                                @foreach($month['projects'] as $project)
                                    <div style="padding: 6px 0; border-top: 1px solid #f3f4f6;">
                                        <p style="color: #374151; font-size: 12px; margin: 0; font-weight: 500;">{{ Str::limit($project['name'], 30) }}</p>
                                        <p style="color: #6b7280; font-size: 11px; margin: 2px 0 0 0;">{{ Carbon\Carbon::parse($project['start_date'])->format('d.m.Y') }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="text-align: center; padding: 40px; color: #6b7280;">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" style="margin-bottom: 16px; opacity: 0.3;">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    <p style="margin: 0; font-size: 14px;">Keine geplanten Projekte in der Pipeline</p>
                </div>
            @endif
        </div>

        <!-- Team Utilization & Alerts -->
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 24px;">
            <!-- Resource Heatmap -->
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 8px 0;">Ressourcen-Auslastung</h3>
                <p style="color: #6b7280; font-size: 14px; margin: 0 0 16px 0;">Top 10 Mitarbeiter nach Auslastung</p>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    @foreach(array_slice($employeeWorkloads, 0, 10) as $workload)
                <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <span style="color: #374151; font-size: 14px; font-weight: 500;">{{ $workload['name'] }}</span>
                            <span style="color: #111827; font-size: 14px; font-weight: 600;">{{ $workload['utilization'] }}%</span>
                        </div>
                        <div style="width: 100%; height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden;">
                            <div style="height: 100%; background: {{ $workload['status'] === 'overloaded' ? 'linear-gradient(90deg, #ef4444, #dc2626)' : ($workload['status'] === 'high' ? 'linear-gradient(90deg, #f59e0b, #d97706)' : ($workload['status'] === 'optimal' ? 'linear-gradient(90deg, #10b981, #059669)' : 'linear-gradient(90deg, #6b7280, #4b5563)')) }}; width: {{ min($workload['utilization'], 100) }}%; transition: width 0.3s ease; border-radius: 4px;"></div>
                        </div>
                </div>
                    @endforeach
                </div>
            </div>

            <!-- Alerts & Warnings -->
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 16px 0;">Alerts & Warnungen</h3>
                
                @if($alerts['overloaded_employees'] > 0)
                <div style="background: #fee2e2; border-left: 4px solid #ef4444; padding: 12px; margin-bottom: 12px; border-radius: 4px;">
                    <div style="display: flex; align-items: start; gap: 8px;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <div>
                            <p style="color: #991b1b; font-weight: 600; font-size: 13px; margin: 0;">{{ $alerts['overloaded_employees'] }} überlastete Mitarbeiter</p>
                            <p style="color: #991b1b; font-size: 12px; margin: 4px 0 0 0;">Auslastung über 100%</p>
        </div>
    </div>
            </div>
                @endif

                @if($alerts['delayed_projects'] > 0)
                <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 12px; margin-bottom: 12px; border-radius: 4px;">
                    <div style="display: flex; align-items: start; gap: 8px;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        <div>
                            <p style="color: #92400e; font-weight: 600; font-size: 13px; margin: 0;">{{ $alerts['delayed_projects'] }} verzögerte Projekte</p>
                            <p style="color: #92400e; font-size: 12px; margin: 4px 0 0 0;">Hinter dem Zeitplan</p>
                                </div>
                                </div>
                            </div>
                @endif

                @if($alerts['low_utilization'] > 0)
                <div style="background: #dbeafe; border-left: 4px solid #3b82f6; padding: 12px; border-radius: 4px;">
                    <div style="display: flex; align-items: start; gap: 8px;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2">
                            <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"></path>
                        </svg>
                        <div>
                            <p style="color: #1e40af; font-weight: 600; font-size: 13px; margin: 0;">{{ $alerts['low_utilization'] }} unterausgelastete Mitarbeiter</p>
                            <p style="color: #1e40af; font-size: 12px; margin: 4px 0 0 0;">Auslastung unter 70%</p>
                        </div>
                    </div>
                </div>
                @endif

                @if($alerts['overloaded_employees'] == 0 && $alerts['delayed_projects'] == 0 && $alerts['low_utilization'] == 0)
                <div style="background: #d1fae5; border-left: 4px solid #10b981; padding: 12px; border-radius: 4px;">
                    <div style="display: flex; align-items: start; gap: 8px;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                        <div>
                            <p style="color: #065f46; font-weight: 600; font-size: 13px; margin: 0;">Alles im grünen Bereich</p>
                            <p style="color: #065f46; font-size: 12px; margin: 4px 0 0 0;">Keine kritischen Alerts</p>
                        </div>
                    </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Top Projects Table -->
        <div style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 16px 0;">Top Projekte nach Umsatz</h3>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid #e5e7eb;">
                            <th style="text-align: left; padding: 12px 8px; color: #6b7280; font-size: 12px; font-weight: 600; text-transform: uppercase;">Projekt</th>
                            <th style="text-align: center; padding: 12px 8px; color: #6b7280; font-size: 12px; font-weight: 600; text-transform: uppercase;">Fortschritt</th>
                            <th style="text-align: center; padding: 12px 8px; color: #6b7280; font-size: 12px; font-weight: 600; text-transform: uppercase;">Status</th>
                            <th style="text-align: right; padding: 12px 8px; color: #6b7280; font-size: 12px; font-weight: 600; text-transform: uppercase;">Umsatz</th>
                            <th style="text-align: center; padding: 12px 8px; color: #6b7280; font-size: 12px; font-weight: 600; text-transform: uppercase;">Deadline</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topProjects as $project)
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 16px 8px;">
                                <a href="{{ route('projects.show', $project['id']) }}" style="color: #111827; font-weight: 500; text-decoration: none;">{{ $project['name'] }}</a>
                            </td>
                            <td style="padding: 16px 8px; text-align: center;">
                                <div style="display: inline-flex; align-items: center; gap: 8px;">
                                    <div style="width: 60px; height: 6px; background: #e5e7eb; border-radius: 3px; overflow: hidden;">
                                        <div style="height: 100%; background: linear-gradient(90deg, #3b82f6, #8b5cf6); width: {{ $project['progress'] }}%;"></div>
            </div>
                                    <span style="color: #111827; font-size: 13px; font-weight: 600;">{{ $project['progress'] }}%</span>
                                </div>
                            </td>
                            <td style="padding: 16px 8px; text-align: center;">
                                <span style="display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; background: {{ $project['status'] === 'on_track' ? '#d1fae5' : ($project['status'] === 'at_risk' ? '#fef3c7' : '#fee2e2') }}; color: {{ $project['status'] === 'on_track' ? '#065f46' : ($project['status'] === 'at_risk' ? '#92400e' : '#991b1b') }};">
                                    {{ $project['status'] === 'on_track' ? 'On Track' : ($project['status'] === 'at_risk' ? 'At Risk' : 'Delayed') }}
                                </span>
                            </td>
                            <td style="padding: 16px 8px; text-align: right; color: #111827; font-weight: 600;">€{{ number_format($project['estimated_revenue'], 0, ',', '.') }}</td>
                            <td style="padding: 16px 8px; text-align: center; color: #6b7280; font-size: 13px;">{{ $project['end_date'] ? \Carbon\Carbon::parse($project['end_date'])->format('d.m.Y') : '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- PROJECT MANAGER VIEW -->
    <div id="view-manager" style="display: none;">
        <div style="display: grid; grid-template-columns: 1fr; gap: 20px;">
            <!-- Project List with Details -->
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 16px 0;">Alle aktiven Projekte</h3>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 2px solid #e5e7eb;">
                                <th style="text-align: left; padding: 12px 8px; color: #6b7280; font-size: 12px; font-weight: 600; text-transform: uppercase;">Projekt</th>
                                <th style="text-align: center; padding: 12px 8px; color: #6b7280; font-size: 12px; font-weight: 600; text-transform: uppercase;">Fortschritt</th>
                                <th style="text-align: center; padding: 12px 8px; color: #6b7280; font-size: 12px; font-weight: 600; text-transform: uppercase;">Erwartet</th>
                                <th style="text-align: center; padding: 12px 8px; color: #6b7280; font-size: 12px; font-weight: 600; text-transform: uppercase;">Status</th>
                                <th style="text-align: right; padding: 12px 8px; color: #6b7280; font-size: 12px; font-weight: 600; text-transform: uppercase;">Umsatz</th>
                                <th style="text-align: center; padding: 12px 8px; color: #6b7280; font-size: 12px; font-weight: 600; text-transform: uppercase;">Deadline</th>
                                <th style="text-align: center; padding: 12px 8px; color: #6b7280; font-size: 12px; font-weight: 600; text-transform: uppercase;">Aktion</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($detailedProjects as $project)
                            <tr style="border-bottom: 1px solid #f3f4f6;">
                                <td style="padding: 16px 8px;">
                                    <span style="color: #111827; font-weight: 500;">{{ $project['name'] }}</span>
                                </td>
                                <td style="padding: 16px 8px; text-align: center;">
                                    <div style="display: inline-flex; align-items: center; gap: 8px;">
                                        <div style="width: 80px; height: 6px; background: #e5e7eb; border-radius: 3px; overflow: hidden;">
                                            <div style="height: 100%; background: linear-gradient(90deg, #3b82f6, #8b5cf6); width: {{ $project['progress'] }}%;"></div>
                                        </div>
                                        <span style="color: #111827; font-size: 13px; font-weight: 600;">{{ $project['progress'] }}%</span>
                                    </div>
                                </td>
                                <td style="padding: 16px 8px; text-align: center; color: #6b7280; font-size: 13px; font-weight: 500;">{{ $project['expected_progress'] }}%</td>
                                <td style="padding: 16px 8px; text-align: center;">
                                    <span style="display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; background: {{ $project['status'] === 'on_track' ? '#d1fae5' : ($project['status'] === 'at_risk' ? '#fef3c7' : '#fee2e2') }}; color: {{ $project['status'] === 'on_track' ? '#065f46' : ($project['status'] === 'at_risk' ? '#92400e' : '#991b1b') }};">
                                        {{ $project['status'] === 'on_track' ? 'On Track' : ($project['status'] === 'at_risk' ? 'At Risk' : 'Delayed') }}
                                    </span>
                                </td>
                                <td style="padding: 16px 8px; text-align: right; color: #111827; font-weight: 600;">€{{ number_format($project['estimated_revenue'], 0, ',', '.') }}</td>
                                <td style="padding: 16px 8px; text-align: center; color: #6b7280; font-size: 13px;">{{ $project['end_date'] ? \Carbon\Carbon::parse($project['end_date'])->format('d.m.Y') : '-' }}</td>
                                <td style="padding: 16px 8px; text-align: center;">
                                    <a href="{{ route('projects.show', $project['id']) }}" style="color: #3b82f6; font-size: 13px; font-weight: 500; text-decoration: none;">Details</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Resource Allocation Details -->
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 16px 0;">Detaillierte Ressourcen-Allocation</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px;">
                    @foreach($employeeWorkloads as $workload)
                    <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <span style="color: #111827; font-weight: 600; font-size: 14px;">{{ $workload['name'] }}</span>
                            <span style="padding: 2px 8px; border-radius: 8px; font-size: 11px; font-weight: 600; background: {{ $workload['status'] === 'overloaded' ? '#fee2e2' : ($workload['status'] === 'high' ? '#fef3c7' : ($workload['status'] === 'optimal' ? '#d1fae5' : '#f3f4f6')) }}; color: {{ $workload['status'] === 'overloaded' ? '#991b1b' : ($workload['status'] === 'high' ? '#92400e' : ($workload['status'] === 'optimal' ? '#065f46' : '#374151')) }};">
                                {{ $workload['utilization'] }}%
                            </span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span style="color: #6b7280; font-size: 12px;">Zugewiesen:</span>
                            <span style="color: #111827; font-size: 12px; font-weight: 500;">{{ $workload['assigned_hours'] }}h</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #6b7280; font-size: 12px;">Kapazität:</span>
                            <span style="color: #111827; font-size: 12px; font-weight: 500;">{{ $workload['capacity'] }}h</span>
                        </div>
                        <div style="width: 100%; height: 6px; background: #e5e7eb; border-radius: 3px; overflow: hidden; margin-top: 12px;">
                            <div style="height: 100%; background: {{ $workload['status'] === 'overloaded' ? 'linear-gradient(90deg, #ef4444, #dc2626)' : ($workload['status'] === 'high' ? 'linear-gradient(90deg, #f59e0b, #d97706)' : ($workload['status'] === 'optimal' ? 'linear-gradient(90deg, #10b981, #059669)' : 'linear-gradient(90deg, #6b7280, #4b5563)')) }}; width: {{ min($workload['utilization'], 100) }}%;"></div>
                        </div>
                </div>
                    @endforeach
                </div>
                </div>
        </div>
    </div>
</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Tab Switching
function switchTab(tab) {
    const executiveView = document.getElementById('view-executive');
    const managerView = document.getElementById('view-manager');
    const executiveBtn = document.getElementById('tab-executive');
    const managerBtn = document.getElementById('tab-manager');
    
    if (tab === 'executive') {
        executiveView.style.display = 'block';
        managerView.style.display = 'none';
        executiveBtn.style.background = 'linear-gradient(135deg, #3b82f6, #8b5cf6)';
        executiveBtn.style.color = 'white';
        managerBtn.style.background = 'white';
        managerBtn.style.color = '#374151';
    } else {
        executiveView.style.display = 'none';
        managerView.style.display = 'block';
        managerBtn.style.background = 'linear-gradient(135deg, #3b82f6, #8b5cf6)';
        managerBtn.style.color = 'white';
        executiveBtn.style.background = 'white';
        executiveBtn.style.color = '#374151';
    }
}

// Revenue Trend Chart
const revenueTrendCtx = document.getElementById('revenueTrendChart').getContext('2d');
const revenueTrendChart = new Chart(revenueTrendCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode(array_column($revenueTrend, 'month')) !!},
        datasets: [{
            label: 'Umsatz (€)',
            data: {!! json_encode(array_column($revenueTrend, 'revenue')) !!},
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            fill: true,
            borderWidth: 3,
            pointRadius: 6,
            pointBackgroundColor: '#3b82f6',
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: '#f3f4f6'
                },
                ticks: {
                    callback: function(value) {
                        return '€' + value.toLocaleString();
                    }
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});

// Project Distribution Pie Chart
const projectDistributionCtx = document.getElementById('projectDistributionChart').getContext('2d');
const projectDistributionChart = new Chart(projectDistributionCtx, {
    type: 'doughnut',
    data: {
        labels: ['Aktiv', 'Abgeschlossen', 'Geplant'],
        datasets: [{
            data: [{{ $projectDistribution['active'] }}, {{ $projectDistribution['completed'] }}, {{ $projectDistribution['planning'] }}],
            backgroundColor: ['#3b82f6', '#10b981', '#f59e0b'],
            borderWidth: 0,
            hoverOffset: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.raw || 0;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                        return `${label}: ${value} (${percentage}%)`;
                    }
                }
            }
        },
        cutout: '65%'
    }
});

// Project Health Donut Chart
const projectHealthCtx = document.getElementById('projectHealthChart').getContext('2d');
const projectHealthChart = new Chart(projectHealthCtx, {
    type: 'doughnut',
    data: {
        labels: ['On Track', 'At Risk', 'Delayed'],
        datasets: [{
            data: [{{ $projectHealth['on_track'] }}, {{ $projectHealth['at_risk'] }}, {{ $projectHealth['delayed'] }}],
            backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
            borderWidth: 0,
            hoverOffset: 10
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        cutout: '70%'
    }
});
</script>
@endsection
