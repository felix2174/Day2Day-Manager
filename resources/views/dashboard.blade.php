@extends('layouts.app')

@section('content')
<div style="width: 100%; margin: 0; padding: 0;">
    <!-- Page Header -->
    <div style="background: white; padding: 20px; margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
        <div>
            <h1 style="font-size: 24px; font-weight: bold; color: #111827; margin: 0;">Dashboard</h1>
            <p style="color: #6b7280; margin: 5px 0 0 0;">Übersicht über Ihr Projektmanagement-System</p>
        </div>
    </div>

    <!-- Metrics Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <!-- Employees Card -->
        <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <p style="color: #6b7280; font-size: 14px; margin: 0 0 8px 0;">Mitarbeiter</p>
                    <p style="font-size: 32px; font-weight: bold; color: #111827; margin: 0;">{{ $employeesCount }}</p>
                    <p style="color: #059669; font-size: 12px; margin: 4px 0 0 0;">Aktiv: {{ $activeEmployeesCount }}</p>
                </div>
                <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #3b82f6, #8b5cf6); color:#ffffff; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="1.75"/>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Projects Card -->
        <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <p style="color: #6b7280; font-size: 14px; margin: 0 0 8px 0;">Projekte</p>
                    <p style="font-size: 32px; font-weight: bold; color: #111827; margin: 0;">{{ $projectsCount }}</p>
                    <p style="color: #059669; font-size: 12px; margin: 4px 0 0 0;">Aktiv: {{ $activeProjectsCount }}</p>
                </div>
                <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #3b82f6, #8b5cf6); color:#ffffff; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 6h5l2 2h11v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6z" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Teams Card -->
        <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <p style="color: #6b7280; font-size: 14px; margin: 0 0 8px 0;">Teams</p>
                    <p style="font-size: 32px; font-weight: bold; color: #111827; margin: 0;">{{ $teamsCount }}</p>
                    <p style="color: #059669; font-size: 12px; margin: 4px 0 0 0;">Aktiv: {{ $teamsCount }}</p>
                </div>
                <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #3b82f6, #8b5cf6); color:#ffffff; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 21V7a2 2 0 0 1 2-2h6v16" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M13 21V3h6a2 2 0 0 1 2 2v16" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M7 10h2M7 14h2M17 7h2M17 11h2M17 15h2" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Assignments Card -->
        <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <p style="color: #6b7280; font-size: 14px; margin: 0 0 8px 0;">Zuweisungen</p>
                    <p style="font-size: 32px; font-weight: bold; color: #111827; margin: 0;">{{ $assignmentsCount }}</p>
                    <p style="color: #059669; font-size: 12px; margin: 4px 0 0 0;">Aktiv: {{ $activeAssignmentsCount }}</p>
                </div>
                <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #3b82f6, #8b5cf6); color:#ffffff; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 13a5 5 0 0 0 7.07 0l2.83-2.83a5 5 0 1 0-7.07-7.07L10 5" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M14 11a5 5 0 0 0-7.07 0L4.1 13.83a5 5 0 1 0 7.07 7.07L14 19" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
        <!-- Recent Projects -->
        <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
            <div style="padding: 20px; border-bottom: 1px solid #e5e7eb;">
                <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0;">Aktuelle Projekte</h3>
                <p style="color: #6b7280; font-size: 14px; margin: 4px 0 0 0;">Die neuesten Projekte im System</p>
            </div>
            <div style="padding: 20px;">
                @if($recentProjects->count() > 0)
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        @foreach($recentProjects->take(5) as $project)
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 8px; height: 8px; background: {{ $project->status == 'active' ? '#10b981' : ($project->status == 'planning' ? '#3b82f6' : '#6b7280') }}; border-radius: 50%;"></div>
                                <div style="flex: 1;">
                                    <div style="font-weight: 500; color: #111827; font-size: 14px;">{{ $project->name }}</div>
                                    <div style="color: #6b7280; font-size: 12px;">{{ round($project->progress) }}% abgeschlossen</div>
                                </div>
                                <div style="background: {{ $project->status == 'active' ? '#dcfce7' : ($project->status == 'planning' ? '#dbeafe' : '#e0e7ff') }}; color: {{ $project->status == 'active' ? '#166534' : ($project->status == 'planning' ? '#1e40af' : '#3730a3') }}; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500; white-space: nowrap;">
                                    {{ ucfirst($project->status) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="text-align: center; padding: 20px; color: #6b7280;">
                        <div style="font-size: 24px; margin-bottom: 8px;">📁</div>
                        <p style="margin: 0;">Keine Projekte vorhanden</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Absences -->
        <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
            <div style="padding: 20px; border-bottom: 1px solid #e5e7eb;">
                <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0;">Aktuelle Abwesenheiten</h3>
                <p style="color: #6b7280; font-size: 14px; margin: 4px 0 0 0;">Mitarbeiter im Urlaub oder krank</p>
            </div>
            <div style="padding: 20px;">
                @if($currentAbsences->count() > 0)
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        @foreach($currentAbsences->take(5) as $absence)
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 8px; height: 8px; background: {{ $absence->type == 'vacation' ? '#3b82f6' : ($absence->type == 'sick' ? '#ef4444' : '#f59e0b') }}; border-radius: 50%;"></div>
                                <div style="flex: 1;">
                                    <div style="font-weight: 500; color: #111827; font-size: 14px;">{{ $absence->first_name }} {{ $absence->last_name }}</div>
                                    <div style="color: #6b7280; font-size: 12px;">{{ \Carbon\Carbon::parse($absence->start_date)->format('d.m.Y') }} - {{ \Carbon\Carbon::parse($absence->end_date)->format('d.m.Y') }}</div>
                                </div>
                                <div style="background: {{ $absence->type == 'vacation' ? '#dbeafe' : ($absence->type == 'sick' ? '#fee2e2' : '#fef3c7') }}; color: {{ $absence->type == 'vacation' ? '#1e40af' : ($absence->type == 'sick' ? '#dc2626' : '#d97706') }}; padding: 2px 6px; border-radius: 8px; font-size: 11px;">
                                    {{ $absence->type == 'vacation' ? 'Urlaub' : ($absence->type == 'sick' ? 'Krank' : 'Persönlich') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="text-align: center; padding: 20px; color: #6b7280;">
                        <div style="font-size: 24px; margin-bottom: 8px;">📅</div>
                        <p style="margin: 0;">Keine aktuellen Abwesenheiten</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px;">
        <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 16px 0;">Schnellaktionen</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">
            <a href="{{ route('employees.create') }}" style="background: #ffffff; border: none; border-radius: 12px; padding: 12px; text-decoration: none; display: flex; align-items: center; gap: 8px; transition: all 0.2s ease; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
                <div style="width: 32px; height: 32px; background: linear-gradient(135deg, #3b82f6, #8b5cf6); color:#ffffff; border-radius: 8px; display:flex; align-items:center; justify-content:center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="1.75"/>
                    </svg>
                </div>
                <span style="color: #374151; font-weight: 500;">Neuer Mitarbeiter</span>
            </a>
            <a href="{{ route('projects.create') }}" style="background: #ffffff; border: none; border-radius: 12px; padding: 12px; text-decoration: none; display: flex; align-items: center; gap: 8px; transition: all 0.2s ease; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
                <div style="width: 32px; height: 32px; background: linear-gradient(135deg, #3b82f6, #8b5cf6); color:#ffffff; border-radius: 8px; display:flex; align-items:center; justify-content:center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 6h5l2 2h11v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6z" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <span style="color: #374151; font-weight: 500;">Neues Projekt</span>
            </a>
            <a href="{{ route('teams.create') }}" style="background: #ffffff; border: none; border-radius: 12px; padding: 12px; text-decoration: none; display: flex; align-items: center; gap: 8px; transition: all 0.2s ease; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
                <div style="width: 32px; height: 32px; background: linear-gradient(135deg, #3b82f6, #8b5cf6); color:#ffffff; border-radius: 8px; display:flex; align-items:center; justify-content:center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 21V7a2 2 0 0 1 2-2h6v16" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M13 21V3h6a2 2 0 0 1 2 2v16" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M7 10h2M7 14h2M17 7h2M17 11h2M17 15h2" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                    </svg>
                </div>
                <span style="color: #374151; font-weight: 500;">Neues Team</span>
            </a>
            <a href="{{ route('assignments.create') }}" style="background: #ffffff; border: none; border-radius: 12px; padding: 12px; text-decoration: none; display: flex; align-items: center; gap: 8px; transition: all 0.2s ease; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
                <div style="width: 32px; height: 32px; background: linear-gradient(135deg, #3b82f6, #8b5cf6); color:#ffffff; border-radius: 8px; display:flex; align-items:center; justify-content:center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 13a5 5 0 0 0 7.07 0l2.83-2.83a5 5 0 1 0-7.07-7.07L10 5" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M14 11a5 5 0 0 0-7.07 0L4.1 13.83a5 5 0 1 0 7.07 7.07L14 19" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <span style="color: #374151; font-weight: 500;">Neue Zuweisung</span>
            </a>
        </div>
    </div>
</div>
@endsection