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
                        <span style="font-weight: 600; color: #111827;">{{ $projects->count() }}</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="color: #6b7280; font-size: 14px;">In Bearbeitung:</span>
                        <span style="font-weight: 600; color: #059669;">{{ $projects->filter(function($p) { 
                            // MOCO-Priorität: finish_date zuerst, dann status als Fallback
                            if ($p->finish_date) {
                                return \Carbon\Carbon::parse($p->finish_date)->isFuture();
                            }
                            return $p->status === 'in_bearbeitung' || $p->status === 'active' || $p->status === 'planning';
                        })->count() }}</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="color: #6b7280; font-size: 14px;">Abgeschlossen:</span>
                        <span style="font-weight: 600; color: #3730a3;">{{ $projects->filter(function($p) { 
                            // MOCO-Priorität: finish_date zuerst, dann status als Fallback
                            if ($p->finish_date) {
                                return \Carbon\Carbon::parse($p->finish_date)->isPast();
                            }
                            return $p->status === 'abgeschlossen' || $p->status === 'completed';
                        })->count() }}</span>
                    </div>
                </div>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="{{ route('projects.export') }}" style="background: #ffffff; color: #374151; padding: 10px 20px; border-radius: 12px; text-decoration: none; font-size: 14px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 8px;">
                    Excel Export
                </a>
                <a href="{{ route('projects.import') }}" style="background: #ffffff; color: #374151; padding: 10px 20px; border-radius: 12px; text-decoration: none; font-size: 14px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 8px;">
                    CSV Import
                </a>
                <a href="{{ route('projects.create') }}" style="background: #ffffff; color: #374151; padding: 10px 20px; border-radius: 12px; text-decoration: none; font-size: 14px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 8px;">
                    Neues Projekt
                </a>
            </div>
        </div>

        <!-- Filter Section -->
        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
            <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                <span style="color: #6b7280; font-size: 14px; font-weight: 500;">Filter:</span>
                
                <!-- Live-Suche -->
                <div style="position: relative; display: inline-block;">
                    <input type="text" 
                           id="searchProject" 
                           oninput="searchProjects()" 
                           placeholder="🔍 Projektname suchen..."
                           style="padding: 8px 32px 8px 12px; width: 240px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; color: #374151; background: white; transition: all 0.2s ease;"
                           onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)'"
                           onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none'">
                    <button id="clearSearchBtn" 
                            onclick="clearSearch()" 
                            style="position: absolute; right: 6px; top: 50%; transform: translateY(-50%); background: transparent; border: none; color: #9ca3af; cursor: pointer; font-size: 18px; padding: 4px 8px; display: none; transition: color 0.2s;"
                            onmouseover="this.style.color='#ef4444'" 
                            onmouseout="this.style.color='#9ca3af'">✕</button>
                </div>
                
                <!-- Status Filter -->
                <select id="filterStatus" onchange="applyFilters()" style="padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; color: #374151; cursor: pointer; background: white;">
                    <option value="">Alle Status</option>
                    <option value="In Bearbeitung">In Bearbeitung</option>
                    <option value="Abgeschlossen">Abgeschlossen</option>
                    <option value="Geplant">Geplant</option>
                </select>

                <!-- Sortierung -->
                <select id="filterSort" onchange="applyFilters()" style="padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; color: #374151; cursor: pointer; background: white;">
                    <option value="">Standard</option>
                    <option value="name-asc">Name (A-Z)</option>
                    <option value="name-desc">Name (Z-A)</option>
                    <option value="date-newest">Neueste zuerst</option>
                    <option value="date-oldest">Älteste zuerst</option>
                    <option value="hours-high">Stunden (Hoch-Niedrig)</option>
                    <option value="hours-low">Stunden (Niedrig-Hoch)</option>
                </select>

                <!-- Verantwortlicher Filter -->
                <select id="filterResponsible" onchange="applyFilters()" style="padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; color: #374151; cursor: pointer; background: white;">
                    <option value="">Alle Verantwortlichen</option>
                    @php
                        $responsibles = $projects->whereNotNull('responsible_id')->pluck('responsible')->unique('id')->sortBy('first_name');
                    @endphp
                    @foreach($responsibles as $responsible)
                        <option value="{{ $responsible->id }}">{{ $responsible->first_name }} {{ $responsible->last_name }}</option>
                    @endforeach
                </select>

                <!-- Zeitraum Filter -->
                <select id="filterTimeframe" onchange="applyFilters()" style="padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; color: #374151; cursor: pointer; background: white;">
                    <option value="">Alle Zeiträume</option>
                    <option value="today">Heute erstellt</option>
                    <option value="week">Diese Woche</option>
                    <option value="month">Dieser Monat</option>
                    <option value="year">Dieses Jahr</option>
                    <option value="older">Älter als 1 Jahr</option>
                </select>

                <!-- Filter zurücksetzen -->
                <button onclick="resetFilters()" style="padding: 8px 16px; background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; cursor: pointer; font-weight: 500; transition: all 0.2s ease;" onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'">
                    Filter zurücksetzen
                </button>

                <!-- Ergebnis-Anzeige -->
                <div id="filterResultContainer" style="margin-left: auto; background: #f0f9ff; border: 1px solid #bae6fd; padding: 8px 16px; border-radius: 8px; display: none;">
                    <span id="filterResult" style="color: #0369a1; font-size: 14px; font-weight: 600;"></span>
                </div>
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
}
</script>