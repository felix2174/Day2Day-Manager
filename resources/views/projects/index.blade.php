@extends('layouts.app')

@section('title', 'Projekte')

@php
    // Statistiken berechnen
    $totalCount = $projects->count();
    $activeCount = $projects->filter(function($p) {
        if ($p->finish_date) {
            return \Carbon\Carbon::parse($p->finish_date)->isFuture();
        }
        return $p->status === 'in_bearbeitung' || $p->status === 'active' || $p->status === 'planning';
    })->count();
    $completedCount = $projects->filter(function($p) {
        if ($p->finish_date) {
            return \Carbon\Carbon::parse($p->finish_date)->isPast();
        }
        return $p->status === 'abgeschlossen' || $p->status === 'completed';
    })->count();
@endphp

@section('content')
<div style="width: 100%; margin: 0; padding: 0;">
    <!-- Ultra-kompakter Header (eine Zeile) -->
    <div class="card-header" style="padding: 12px 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; gap: 16px;">
            <!-- Links: Statistiken -->
            <div style="display: flex; gap: 20px; align-items: center;">
                <div style="background: #f3f4f6; padding: 6px 12px; border-radius: 8px; display: inline-flex; align-items: center; gap: 10px;">
                    <div style="display: inline-flex; align-items: center; gap: 4px;">
                        <span style="color: #6b7280; font-size: 11px; font-weight: 500; text-transform: uppercase;">Gesamt</span>
                        <span style="color: #111827; font-size: 14px; font-weight: 700;">{{ $totalCount }}</span>
                    </div>
                    <span style="color: #d1d5db;">·</span>
                    <div style="display: inline-flex; align-items: center; gap: 4px;">
                        <span style="color: #10b981; font-size: 11px; font-weight: 500; text-transform: uppercase;">Aktiv</span>
                        <span style="color: #10b981; font-size: 14px; font-weight: 700;">{{ $activeCount }}</span>
                    </div>
                    <span style="color: #d1d5db;">·</span>
                    <div style="display: inline-flex; align-items: center; gap: 4px;">
                        <span style="color: #9ca3af; font-size: 11px; font-weight: 500; text-transform: uppercase;">Fertig</span>
                        <span style="color: #9ca3af; font-size: 14px; font-weight: 700;">{{ $completedCount }}</span>
                    </div>
                </div>

                <!-- Filter Button -->
                <button id="toggleFiltersBtn" onclick="toggleFilterModal()" 
                        style="background: #ffffff; color: #374151; padding: 6px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.15s ease; display: inline-flex; align-items: center; gap: 6px;"
                        onmouseover="this.style.background='#f9fafb'; this.style.borderColor='#d1d5db'"
                        onmouseout="this.style.background='#ffffff'; this.style.borderColor='#e5e7eb'">
                    <span>🔍</span>
                    <span>Filter</span>
                    <span id="activeFilterCount" style="display: none; background: #3b82f6; color: white; border-radius: 10px; padding: 2px 6px; font-size: 11px; font-weight: 700;"></span>
                </button>

                <!-- Ergebnis-Anzeige -->
                <div id="filterResultContainer" style="display: none;">
                    <span id="filterResult" class="badge badge-info"></span>
                </div>
            </div>

            <!-- Rechts: Buttons -->
            <div style="display: flex; gap: 8px;">
                <a href="{{ route('projects.export') }}" class="btn btn-secondary btn-sm">Excel Export</a>
                <a href="{{ route('projects.import') }}" class="btn btn-secondary btn-sm">CSV Import</a>
                <a href="{{ route('projects.create') }}" class="btn btn-primary btn-sm">+ Neues Projekt</a>
            </div>
        </div>
    </div>

    <!-- Filter Modal (versteckt) -->
    <div id="filterModalBackdrop" onclick="toggleFilterModal()" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 99998; backdrop-filter: blur(4px);"></div>
    
    <div id="filterModal" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 90%; max-width: 700px; background: white; padding: 24px; border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); z-index: 99999;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 2px solid #f3f4f6;">
            <h3 style="font-size: 18px; font-weight: 700; color: #111827; margin: 0;">🔍 Filter & Suche</h3>
            <button onclick="toggleFilterModal()" style="background: transparent; border: none; font-size: 24px; cursor: pointer; color: #9ca3af; padding: 4px;"
                    onmouseover="this.style.color='#374151'" onmouseout="this.style.color='#9ca3af'">✕</button>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
            <!-- Suche -->
            <div style="grid-column: span 2;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 6px; text-transform: uppercase;">Projektname</label>
                <div style="position: relative;">
                    <input type="text" 
                           id="searchProject" 
                           oninput="searchProjects()" 
                           placeholder="Suche nach Projektname..."
                           class="form-input"
                           style="width: 100%; padding-right: 32px;">
                    <button id="clearSearchBtn" 
                            onclick="clearSearch()" 
                            style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: transparent; border: none; color: var(--color-text-muted); cursor: pointer; font-size: 18px; display: none;"
                            onmouseover="this.style.color='var(--color-danger)'" 
                            onmouseout="this.style.color='var(--color-text-muted)'">✕</button>
                </div>
            </div>

            <!-- Status -->
            <div>
                <label style="display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 6px; text-transform: uppercase;">Status</label>
                <select id="filterStatus" onchange="applyFilters()" class="form-select" style="width: 100%;">
                    <option value="">Alle Status</option>
                    <option value="In Bearbeitung">In Bearbeitung</option>
                    <option value="Abgeschlossen">Abgeschlossen</option>
                    <option value="Geplant">Geplant</option>
                </select>
            </div>

            <!-- Sortierung -->
            <div>
                <label style="display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 6px; text-transform: uppercase;">Sortierung</label>
                <select id="filterSort" onchange="applyFilters()" class="form-select" style="width: 100%;">
                    <option value="">Standard</option>
                    <option value="name-asc">Name (A-Z)</option>
                    <option value="name-desc">Name (Z-A)</option>
                    <option value="date-newest">Neueste zuerst</option>
                    <option value="date-oldest">Älteste zuerst</option>
                    <option value="hours-high">Stunden (Hoch → Niedrig)</option>
                    <option value="hours-low">Stunden (Niedrig → Hoch)</option>
                </select>
            </div>

            <!-- Verantwortlicher -->
            <div>
                <label style="display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 6px; text-transform: uppercase;">Verantwortlicher</label>
                <select id="filterResponsible" onchange="applyFilters()" class="form-select" style="width: 100%;">
                    <option value="">Alle Verantwortlichen</option>
                    @php
                        $responsibles = $projects->whereNotNull('responsible_id')->pluck('responsible')->unique('id')->sortBy('first_name');
                    @endphp
                    @foreach($responsibles as $responsible)
                        <option value="{{ $responsible->id }}">{{ $responsible->first_name }} {{ $responsible->last_name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Zeitraum -->
            <div>
                <label style="display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 6px; text-transform: uppercase;">Zeitraum</label>
                <select id="filterTimeframe" onchange="applyFilters()" class="form-select" style="width: 100%;">
                    <option value="">Alle Zeiträume</option>
                    <option value="today">Heute erstellt</option>
                    <option value="week">Diese Woche</option>
                    <option value="month">Dieser Monat</option>
                    <option value="year">Dieses Jahr</option>
                    <option value="older">Älter als 1 Jahr</option>
                </select>
            </div>
        </div>

        <!-- Modal Footer -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 24px; padding-top: 16px; border-top: 1px solid #f3f4f6;">
            <button onclick="resetFilters()" class="btn btn-ghost btn-sm">↺ Alle Filter zurücksetzen</button>
            <button onclick="toggleFilterModal()" class="btn btn-primary btn-sm">Schließen</button>
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
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px; align-items: start;">
        @forelse($projects as $project)
            @php
                // Berechne Display-Status (für Filter benötigt)
                $displayStatus = $project->calculated_status ?? $project->status;
                
                // Normalisiere Status-Namen für Filter
                switch($displayStatus) {
                    case 'completed':
                    case 'abgeschlossen':
                        $displayStatus = 'Abgeschlossen';
                        break;
                    case 'active':
                    case 'in_bearbeitung':
                        $displayStatus = 'In Bearbeitung';
                        break;
                    case 'planning':
                        $displayStatus = 'Geplant';
                        break;
                }
                
                // Lade Team-Mitglieder für Tooltip
                $teamMembers = '';
                
                // 1. Versuche MOCO-Daten (vorgefertigt im Controller)
                if ($project->moco_id && isset($projectTeams[$project->moco_id])) {
                    $teamArray = $projectTeams[$project->moco_id];
                    $teamMembers = collect($teamArray)->pluck('name')->filter()->take(5)->implode(', ');
                }
                
                // 2. Fallback auf lokale Daten, wenn keine MOCO-Daten verfügbar
                if (empty($teamMembers)) {
                    $teamMembers = $project->getAssignedPersonsString(null, 5);
                }
                
                $estimatedRevenue = ($project->estimated_hours ?? 0) * ($project->hourly_rate ?? 0);
                
                // === NEUE DATEN FÜR KOMPAKTE KARTE ===
                // Team-Größe zählen
                $projectAssignments = \App\Models\Assignment::where('project_id', $project->id)->with('employee')->get();
                $teamSize = $projectAssignments->unique('employee_id')->count();
                
                // Gesamtstunden berechnen (OHNE Nachkommastellen)
                $projectStart = $project->start_date ? \Carbon\Carbon::parse($project->start_date) : now();
                $projectEnd = $project->end_date ? \Carbon\Carbon::parse($project->end_date) : now()->addWeeks(4);
                $projectDurationWeeks = max(1, round($projectStart->diffInWeeks($projectEnd))); // Runden
                $totalWeeklyHours = round($projectAssignments->sum('weekly_hours')); // Runden
                $totalPlannedHours = $totalWeeklyHours * $projectDurationWeeks;
                $durationDays = round($projectStart->diffInDays($projectEnd)); // Runden
                
                // Arbeitstage berechnen (8h/Tag = Standard)
                $totalPlannedDays = round($totalPlannedHours / 8); // Stunden → Arbeitstage
                
                // Verantwortlicher Name mit Badge für inaktive
                $responsibleName = 'Nicht zugewiesen';
                $responsibleIsInactive = false;
                if ($project->responsible) {
                    $responsibleName = $project->responsible->first_name . ' ' . $project->responsible->last_name;
                    $responsibleIsInactive = !$project->responsible->is_active;
                    if ($responsibleIsInactive) {
                        $responsibleName .= ' (Inaktiv)';
                    }
                }
            @endphp
            <div class="project-card" 
                 data-project-card
                 data-project-name="{{ $project->name }}"
                 data-project-description="{{ Str::limit($project->description, 200) }}"
                 data-project-status="{{ $displayStatus ?? ucfirst($project->status) }}"
                 data-project-responsible="{{ $project->responsible_id ?? '' }}"
                 data-project-created="{{ $project->created_at->format('Y-m-d') }}"
                 data-project-hours="{{ $project->assignments->sum('weekly_hours') }}"
                 data-start-date="{{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d.m.Y') : 'Nicht festgelegt' }}"
                 data-end-date="{{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('d.m.Y') : 'Nicht festgelegt' }}"
                 data-estimated-hours="{{ $project->estimated_hours ?? 0 }}"
                 data-progress="{{ round($project->progress) }}"
                 data-team="{{ $teamMembers ?: 'Keine Personen zugewiesen' }}"
                 data-team-members="{{ $teamMembers ?: 'Keine Personen zugewiesen' }}"
                 data-revenue="{{ number_format($estimatedRevenue, 0, ',', '.') }}"
                 data-status="{{ $project->calculated_status ?? ucfirst($project->status) }}"
                 data-required-hours="{{ $project->assignments->sum('weekly_hours') }}"
                 data-available-hours="{{ $project->assignments->sum(function($assignment) { return $assignment->employee ? $assignment->employee->weekly_capacity : 0; }) }}"
                 data-is-ongoing="{{ !$project->start_date && !$project->end_date && $project->moco_created_at ? '1' : '0' }}"
                 style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); height: fit-content; transition: all 0.2s ease;"
                 onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)'"
                 onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.1)'">
                
                <!-- ========== KOMPAKTE PROJEKT-KARTE ========== -->
                
                <!-- Header: Projektname + Status-Badge -->
                <div style="padding: 20px 20px 16px 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 8px;">
                        <h3 style="font-size: 18px; font-weight: 700; color: #111827; margin: 0; flex: 1; line-height: 1.3;">
                            {{ $project->name }}
                        </h3>
                        @php
                            // Status-Badge Farben
                            $statusColor = '#6b7280';
                            $statusBg = '#f3f4f6';
                            
                            switch($displayStatus) {
                                case 'Abgeschlossen':
                                    $statusColor = '#3730a3';
                                    $statusBg = '#e0e7ff';
                                    break;
                                case 'In Bearbeitung':
                                    $statusColor = '#166534';
                                    $statusBg = '#dcfce7';
                                    break;
                                case 'Geplant':
                                    $statusColor = '#1e40af';
                                    $statusBg = '#dbeafe';
                                    break;
                                default:
                                    $statusColor = '#92400e';
                                    $statusBg = '#fef3c7';
                            }
                        @endphp
                        <span style="background: {{ $statusBg }}; color: {{ $statusColor }}; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; white-space: nowrap; flex-shrink: 0;">
                            {{ $displayStatus }}
                        </span>
                    </div>
                    
                    <!-- Beschreibung (1 Zeile) -->
                    <p style="color: #6b7280; font-size: 13px; line-height: 1.4; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        {{ $project->description ?: 'Keine Beschreibung' }}
                    </p>
                </div>

                <!-- Info-Grid: Verantwortlicher | Team | Zeitraum | Stunden -->
                <div style="padding: 0 20px 16px 20px;">
                    <!-- Zeile 1: Verantwortlicher + Team -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                        <div style="display: flex; align-items: center; gap: 6px; background: #f9fafb; padding: 8px 10px; border-radius: 6px;">
                            <span style="font-size: 14px;">🎯</span>
                            <span style="font-size: 12px; color: {{ $responsibleIsInactive ? '#6b7280' : '#374151' }}; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ Str::limit($responsibleName, 25) }}
                            </span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 6px; background: #f9fafb; padding: 8px 10px; border-radius: 6px;">
                            <span style="font-size: 14px;">👥</span>
                            <span style="font-size: 12px; color: #374151; font-weight: 600;">
                                {{ $teamSize }} {{ $teamSize === 1 ? 'Person' : 'Personen' }}
                            </span>
                        </div>
                    </div>
                    
                    <!-- Zeile 2: Zeitraum (volle Breite) -->
                    <div style="display: flex; align-items: center; gap: 6px; background: #f0f9ff; padding: 8px 10px; border-radius: 6px; margin-bottom: 12px; border: 1px solid #bae6fd;">
                        <span style="font-size: 14px;">📅</span>
                        <span style="font-size: 12px; color: #0369a1; font-weight: 500;">
                            {{ $projectStart->format('d.m.y') }} - {{ $projectEnd->format('d.m.y') }} <span style="color: #0284c7; font-weight: 600;">({{ $durationDays }} Tage)</span>
                        </span>
                    </div>
                    
                    <!-- Zeile 3: Geplante Arbeitstage (volle Breite) -->
                    <div style="display: flex; align-items: center; gap: 6px; background: #fef9e7; padding: 8px 10px; border-radius: 6px; border: 1px solid #fde68a;">
                        <span style="font-size: 14px;">📆</span>
                        <span style="font-size: 12px; color: #92400e; font-weight: 600;">
                            {{ $totalPlannedDays }} Tage geplant 
                        </span>
                        <span style="font-size: 11px; color: #d97706; font-weight: 500;">
                            ({{ $totalWeeklyHours }}h/W × {{ $projectDurationWeeks }} Wochen)
                        </span>
                    </div>
                </div>

                <!-- Fortschrittsbalken -->
                <div style="padding: 0 20px 20px 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <span style="font-size: 12px; font-weight: 600; color: #6b7280;">Fortschritt</span>
                        <span style="font-size: 14px; font-weight: 700; color: #111827;">{{ round($project->progress) }}%</span>
                    </div>
                    <div style="background: #e5e7eb; height: 10px; border-radius: 5px; overflow: hidden; position: relative;">
                        @php
                            $progressColor = '#10b981'; // Grün
                            if ($project->progress >= 80) {
                                $progressColor = '#10b981'; // Grün
                            } elseif ($project->progress >= 50) {
                                $progressColor = '#3b82f6'; // Blau
                            } else {
                                $progressColor = '#f59e0b'; // Orange
                            }
                        @endphp
                        <div style="background: {{ $progressColor }}; height: 100%; width: {{ $project->progress }}%; transition: width 0.3s ease;"></div>
                    </div>
                </div>

                <!-- Actions-Footer -->
                <div style="padding: 12px 20px; background: #f9fafb; border-top: 1px solid #e5e7eb;">
                    <div style="display: flex; gap: 8px; justify-content: flex-start;">
                        <a href="{{ route('projects.show', $project) }}" style="background: #ffffff; color: #374151; padding: 8px 14px; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 600; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 6px; border: 1px solid #e5e7eb;"
                           onmouseover="this.style.background='#f9fafb'; this.style.borderColor='#3b82f6'"
                           onmouseout="this.style.background='#ffffff'; this.style.borderColor='#e5e7eb'">
                            <span style="font-size: 14px;">👁</span> Anzeigen
                        </a>
                        <a href="{{ route('projects.edit', $project) }}" style="background: #ffffff; color: #374151; padding: 8px 14px; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 600; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 6px; border: 1px solid #e5e7eb;"
                           onmouseover="this.style.background='#f9fafb'; this.style.borderColor='#3b82f6'"
                           onmouseout="this.style.background='#ffffff'; this.style.borderColor='#e5e7eb'">
                            <span style="font-size: 14px;">✏️</span> Bearbeiten
                        </a>
                        <form action="{{ route('projects.destroy', $project) }}" method="POST" style="display: inline; margin-left: auto;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background: #ffffff; color: #dc2626; padding: 8px 14px; border-radius: 8px; border: 1px solid #e5e7eb; font-size: 13px; font-weight: 600; cursor: pointer; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 6px;" 
                                    onclick="return confirm('Sind Sie sicher, dass Sie dieses Projekt löschen möchten?')"
                                    onmouseover="this.style.background='#fef2f2'; this.style.borderColor='#dc2626'"
                                    onmouseout="this.style.background='#ffffff'; this.style.borderColor='#e5e7eb'">
                                <span style="font-size: 14px;">🗑️</span> Löschen
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                <div style="font-size: 48px; margin-bottom: 16px;">📁</div>
                <h3 style="font-size: 18px; font-weight: 500; color: #111827; margin: 0 0 8px 0;">Keine Projekte</h3>
                <p style="color: #6b7280; margin: 0 0 24px 0;">Beginnen Sie mit der Erstellung Ihres ersten Projekts.</p>
                <a href="{{ route('projects.create') }}" style="background: #ffffff; color: #374151; padding: 12px 24px; border-radius: 12px; text-decoration: none; font-size: 14px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 8px;">
                    ➕ Neues Projekt erstellen
                </a>
            </div>
        @endforelse
    </div>
</div>
@endsection

<script>
// ==================== FILTER MODAL ====================
function toggleFilterModal() {
    const modal = document.getElementById('filterModal');
    const backdrop = document.getElementById('filterModalBackdrop');
    const isVisible = modal.style.display === 'block';
    
    modal.style.display = isVisible ? 'none' : 'block';
    backdrop.style.display = isVisible ? 'none' : 'block';
    
    // Body scroll verhindern wenn Modal offen
    document.body.style.overflow = isVisible ? '' : 'hidden';
}

// Escape-Taste schließt Modal
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('filterModal');
        if (modal && modal.style.display === 'block') {
            toggleFilterModal();
        }
    }
});

// Update Filter-Button wenn Filter aktiv
function updateFilterButton() {
    const searchTerm = document.getElementById('searchProject')?.value || '';
    const statusFilter = document.getElementById('filterStatus')?.value || '';
    const sortFilter = document.getElementById('filterSort')?.value || '';
    const responsibleFilter = document.getElementById('filterResponsible')?.value || '';
    const timeframeFilter = document.getElementById('filterTimeframe')?.value || '';
    
    const activeFilters = [searchTerm, statusFilter, sortFilter, responsibleFilter, timeframeFilter].filter(f => f).length;
    
    const btn = document.getElementById('toggleFiltersBtn');
    const countBadge = document.getElementById('activeFilterCount');
    
    if (activeFilters > 0) {
        btn.style.background = '#3b82f6';
        btn.style.color = '#ffffff';
        btn.style.borderColor = '#3b82f6';
        countBadge.textContent = activeFilters;
        countBadge.style.display = 'inline';
    } else {
        btn.style.background = '#ffffff';
        btn.style.color = '#374151';
        btn.style.borderColor = '#e5e7eb';
        countBadge.style.display = 'none';
    }
}

// ==================== LIVE-SUCHE ====================
function searchProjects() {
    const searchInput = document.getElementById('searchProject');
    const searchTerm = searchInput.value.toLowerCase().trim();
    const clearBtn = document.getElementById('clearSearchBtn');
    
    // Clear-Button anzeigen/verstecken
    clearBtn.style.display = searchTerm ? 'block' : 'none';
    
    // Rufe die normale Filter-Funktion auf (integriert Suche automatisch)
    applyFilters();
}

function clearSearch() {
    const searchInput = document.getElementById('searchProject');
    const clearBtn = document.getElementById('clearSearchBtn');
    
    searchInput.value = '';
    clearBtn.style.display = 'none';
    
    // Filter neu anwenden ohne Suchbegriff
    applyFilters();
}

// ==================== FILTER-FUNKTIONEN ====================
function applyFilters() {
    const searchTerm = document.getElementById('searchProject').value.toLowerCase().trim();
    const statusFilter = document.getElementById('filterStatus').value;
    const sortFilter = document.getElementById('filterSort').value;
    const responsibleFilter = document.getElementById('filterResponsible').value;
    const timeframeFilter = document.getElementById('filterTimeframe').value;
    
    const projectCards = document.querySelectorAll('[data-project-card]');
    let visibleCount = 0;
    
    // Sammle alle sichtbaren Projekte für Sortierung
    let visibleProjects = [];
    
    projectCards.forEach(card => {
        let isVisible = true;
        
        // ==================== HYBRID LIVE-SUCHE ====================
        // Smart-Logik: Kurze Suchen (1-2 Zeichen) = Prefix, Lange Suchen (3+) = Contains
        if (searchTerm) {
            const projectName = (card.dataset.projectName || '').toLowerCase();
            const projectDesc = (card.dataset.projectDescription || '').toLowerCase();
            
            // Kurze Eingaben (1-2 Zeichen): Nur Projekte, die MIT diesem Buchstaben BEGINNEN
            if (searchTerm.length <= 2) {
                // Prefix-Match: "a" findet nur "Anbindung...", nicht "Marketing"
                if (!projectName.startsWith(searchTerm) && !projectDesc.startsWith(searchTerm)) {
                    isVisible = false;
                }
            }
            // Längere Eingaben (3+ Zeichen): Contains-Match (überall im Text)
            else {
                // "drucker" findet "Anbindung Drucker..." (auch wenn nicht am Anfang)
                if (!projectName.includes(searchTerm) && !projectDesc.includes(searchTerm)) {
                    isVisible = false;
                }
            }
        }
        
        // Status-Filter
        if (statusFilter && card.dataset.projectStatus !== statusFilter) {
            isVisible = false;
        }
        
        // Verantwortlicher-Filter
        if (responsibleFilter && card.dataset.projectResponsible !== responsibleFilter) {
            isVisible = false;
        }
        
        // Zeitraum-Filter
        if (timeframeFilter) {
            const createdAt = new Date(card.dataset.projectCreated);
            const now = new Date();
            const dayInMs = 24 * 60 * 60 * 1000;
            
            switch(timeframeFilter) {
                case 'today':
                    if (Math.floor((now - createdAt) / dayInMs) !== 0) isVisible = false;
                    break;
                case 'week':
                    if ((now - createdAt) / dayInMs > 7) isVisible = false;
                    break;
                case 'month':
                    if ((now - createdAt) / dayInMs > 30) isVisible = false;
                    break;
                case 'year':
                    if ((now - createdAt) / dayInMs > 365) isVisible = false;
                    break;
                case 'older':
                    if ((now - createdAt) / dayInMs <= 365) isVisible = false;
                    break;
            }
        }
        
        if (isVisible) {
            visibleProjects.push(card);
            visibleCount++;
        }
        
        card.style.display = isVisible ? 'block' : 'none';
    });
    
    // Sortierung anwenden
    if (sortFilter) {
        visibleProjects.sort((a, b) => {
            switch(sortFilter) {
                case 'name-asc':
                    return a.dataset.projectName.localeCompare(b.dataset.projectName);
                case 'name-desc':
                    return b.dataset.projectName.localeCompare(a.dataset.projectName);
                case 'date-newest':
                    return new Date(b.dataset.projectCreated) - new Date(a.dataset.projectCreated);
                case 'date-oldest':
                    return new Date(a.dataset.projectCreated) - new Date(b.dataset.projectCreated);
                case 'hours-high':
                    return parseInt(b.dataset.projectHours || 0) - parseInt(a.dataset.projectHours || 0);
                case 'hours-low':
                    return parseInt(a.dataset.projectHours || 0) - parseInt(b.dataset.projectHours || 0);
            }
            return 0;
        });
        
        // Neu sortierte Elemente ins DOM einfügen
        const grid = projectCards[0]?.parentElement;
        if (grid) {
            visibleProjects.forEach(card => {
                grid.appendChild(card);
            });
        }
    }
    
    // Ergebnis anzeigen mit verbesserter Visualisierung
    const resultSpan = document.getElementById('filterResult');
    const resultContainer = document.getElementById('filterResultContainer');
    
    if (visibleCount === projectCards.length) {
        resultContainer.style.display = 'none';
        resultSpan.textContent = '';
    } else {
        resultContainer.style.display = 'block';
        resultSpan.textContent = `${visibleCount} von ${projectCards.length} Projekten`;
        
        // Färbe Container basierend auf Ergebnis-Anzahl
        if (visibleCount === 0) {
            resultContainer.style.background = '#fef2f2';
            resultContainer.style.borderColor = '#fecaca';
            resultSpan.style.color = '#dc2626';
        } else if (visibleCount < 5) {
            resultContainer.style.background = '#fff7ed';
            resultContainer.style.borderColor = '#fed7aa';
            resultSpan.style.color = '#c2410c';
        } else {
            resultContainer.style.background = '#f0f9ff';
            resultContainer.style.borderColor = '#bae6fd';
            resultSpan.style.color = '#0369a1';
        }
    }
    
    // Filter-Button aktualisieren
    updateFilterButton();
}

function resetFilters() {
    // Alle Filter-Dropdowns zurücksetzen
    document.getElementById('filterStatus').value = '';
    document.getElementById('filterSort').value = '';
    document.getElementById('filterResponsible').value = '';
    document.getElementById('filterTimeframe').value = '';
    
    // Suchfeld leeren
    document.getElementById('searchProject').value = '';
    document.getElementById('clearSearchBtn').style.display = 'none';
    
    // Filter neu anwenden (zeigt alle Projekte)
    applyFilters();
    
    // Filter-Button aktualisieren
    updateFilterButton();
}
</script>