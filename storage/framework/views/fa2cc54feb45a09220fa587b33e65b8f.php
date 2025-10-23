<?php $__env->startSection('title', 'Projekte'); ?>

<?php $__env->startSection('content'); ?>
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
                        <span style="font-weight: 600; color: #111827;"><?php echo e($projects->count()); ?></span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="color: #6b7280; font-size: 14px;">In Bearbeitung:</span>
                        <span style="font-weight: 600; color: #059669;"><?php echo e($projects->filter(function($p) { 
                            // MOCO-Priorität: finish_date zuerst, dann status als Fallback
                            if ($p->finish_date) {
                                return \Carbon\Carbon::parse($p->finish_date)->isFuture();
                            }
                            return $p->status === 'in_bearbeitung' || $p->status === 'active' || $p->status === 'planning';
                        })->count()); ?></span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="color: #6b7280; font-size: 14px;">Abgeschlossen:</span>
                        <span style="font-weight: 600; color: #3730a3;"><?php echo e($projects->filter(function($p) { 
                            // MOCO-Priorität: finish_date zuerst, dann status als Fallback
                            if ($p->finish_date) {
                                return \Carbon\Carbon::parse($p->finish_date)->isPast();
                            }
                            return $p->status === 'abgeschlossen' || $p->status === 'completed';
                        })->count()); ?></span>
                    </div>
                </div>
            </div>
            <div style="display: flex; gap: 10px;">
                <button onclick="syncProjectStatuses()" id="syncButton" style="background: #3b82f6; color: white; padding: 10px 20px; border-radius: 12px; border: none; font-size: 14px; font-weight: 500; cursor: pointer; transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 8px;">
                    Status synchronisieren
                </button>
                <a href="<?php echo e(route('projects.export')); ?>" style="background: #ffffff; color: #374151; padding: 10px 20px; border-radius: 12px; text-decoration: none; font-size: 14px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 8px;">
                    Excel Export
                </a>
                <a href="<?php echo e(route('projects.import')); ?>" style="background: #ffffff; color: #374151; padding: 10px 20px; border-radius: 12px; text-decoration: none; font-size: 14px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 8px;">
                    CSV Import
                </a>
                <a href="<?php echo e(route('projects.create')); ?>" style="background: #ffffff; color: #374151; padding: 10px 20px; border-radius: 12px; text-decoration: none; font-size: 14px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 8px;">
                    Neues Projekt
                </a>
            </div>
        </div>

        <!-- Filter Section -->
        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
            <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                <span style="color: #6b7280; font-size: 14px; font-weight: 500;">Filter:</span>
                
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
                    <?php
                        $responsibles = $projects->whereNotNull('responsible_id')->pluck('responsible')->unique('id')->sortBy('first_name');
                    ?>
                    <?php $__currentLoopData = $responsibles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $responsible): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($responsible->id); ?>"><?php echo e($responsible->first_name); ?> <?php echo e($responsible->last_name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                <button onclick="resetFilters()" style="padding: 8px 16px; background: #f3f4f6; color: #6b7280; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; cursor: pointer; font-weight: 500;">
                    Filter zurücksetzen
                </button>

                <!-- Ergebnis-Anzeige -->
                <span id="filterResult" style="color: #6b7280; font-size: 14px; margin-left: auto;"></span>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    <?php if(session('success')): ?>
        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
            ✅ <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
            ❌ <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>

    <!-- Projects Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px; align-items: start;">
        <?php $__empty_1 = true; $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
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
            ?>
            <div class="project-card" 
                 data-project-name="<?php echo e($project->name); ?>"
                 data-start-date="<?php echo e($project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d.m.Y') : 'Nicht festgelegt'); ?>"
                 data-end-date="<?php echo e($project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('d.m.Y') : 'Nicht festgelegt'); ?>"
                 data-estimated-hours="<?php echo e($project->estimated_hours ?? 0); ?>"
                 data-progress="<?php echo e(round($project->progress)); ?>"
                 data-team="<?php echo e($teamMembers ?: 'Keine Personen zugewiesen'); ?>"
                 data-team-members="<?php echo e($teamMembers ?: 'Keine Personen zugewiesen'); ?>"
                 data-revenue="<?php echo e(number_format($estimatedRevenue, 0, ',', '.')); ?>"
                 data-status="<?php echo e($project->calculated_status ?? ucfirst($project->status)); ?>"
                 data-required-hours="<?php echo e($project->assignments->sum('weekly_hours')); ?>"
                 data-available-hours="<?php echo e($project->assignments->sum(function($assignment) { return $assignment->employee ? $assignment->employee->weekly_capacity : 0; })); ?>"
                 data-is-ongoing="<?php echo e(!$project->start_date && !$project->end_date && $project->moco_created_at ? '1' : '0'); ?>"
                 style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); height: fit-content; cursor: pointer;">
                <!-- Project Header -->
                <div style="padding: 20px; border-bottom: 1px solid #e5e7eb;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                        <div style="flex: 1;">
                            <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 8px 0;"><?php echo e($project->name); ?></h3>
                            <p style="color: #6b7280; font-size: 14px; line-height: 1.5; margin: 0;"><?php echo e(Str::limit($project->description, 100)); ?></p>
                            
                            <?php if($project->responsible): ?>
                                <p style="color: #374151; font-size: 12px; margin: 8px 0 0 0; font-weight: 500;">
                                    Verantwortlich: <?php echo e($project->responsible->first_name); ?> <?php echo e($project->responsible->last_name); ?>

                                </p>
                            <?php endif; ?>
                        </div>
                        <?php
                            // Verwende den berechneten Status aus dem Controller
                            $displayStatus = $project->calculated_status ?? $project->status;
                            $statusColor = '#6b7280';
                            $statusBg = '#f3f4f6';
                            
                            // Farbschema basierend auf berechnetem Status
                            switch($displayStatus) {
                                case 'completed':
                                case 'Abgeschlossen':
                                    $statusColor = '#3730a3';
                                    $statusBg = '#e0e7ff';
                                    $displayStatus = 'Abgeschlossen';
                                    break;
                                case 'active':
                                case 'in_bearbeitung':
                                case 'In Bearbeitung':
                                    $statusColor = '#166534';
                                    $statusBg = '#dcfce7';
                                    $displayStatus = 'In Bearbeitung';
                                    break;
                                case 'planning':
                                case 'Geplant':
                                    $statusColor = '#1e40af';
                                    $statusBg = '#dbeafe';
                                    $displayStatus = 'Geplant';
                                    break;
                                default:
                                    $statusColor = '#92400e';
                                    $statusBg = '#fef3c7';
                            }
                        ?>
                        <span style="background: <?php echo e($statusBg); ?>; color: <?php echo e($statusColor); ?>; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500; white-space: nowrap;">
                            <?php echo e($displayStatus); ?>

                        </span>
                    </div>
                </div>

                <!-- Project Content -->
                <div style="padding: 20px;">
                    <!-- Progress Bar -->
                    <div style="margin-bottom: 16px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 14px; font-weight: 500; color: #374151;">Fortschritt</span>
                            <span style="font-size: 14px; font-weight: 600; color: #111827;"><?php echo e(round($project->progress)); ?>%</span>
                        </div>
                        <div style="background: #e5e7eb; height: 8px; border-radius: 4px; overflow: hidden;">
                            <div style="background: #2563eb; height: 100%; width: <?php echo e($project->progress); ?>%; transition: width 0.3s;"></div>
                        </div>
                    </div>

                    <!-- Project Stats -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                        <div>
                            <div style="font-size: 12px; color: #6b7280; margin-bottom: 4px;">Geschätzte Stunden</div>
                            <div style="font-size: 16px; font-weight: 600; color: #111827;"><?php echo e($project->estimated_hours); ?>h</div>
                        </div>
                        <div>
                            <div style="font-size: 12px; color: #6b7280; margin-bottom: 4px;">Stundensatz</div>
                            <div style="font-size: 16px; font-weight: 600; color: #111827;"><?php echo e(number_format($project->hourly_rate, 2)); ?>€</div>
                        </div>
                    </div>

                    <!-- Created Date -->
                    <div style="margin-bottom: 16px;">
                        <div style="font-size: 12px; color: #6b7280; margin-bottom: 4px;">Erstellt am:</div>
                        <div style="font-size: 14px; color: #374151;">
                            <?php if($project->moco_created_at): ?>
                                <?php echo e(\Carbon\Carbon::parse($project->moco_created_at)->format('d.m.Y')); ?>

                            <?php else: ?>
                                <?php echo e(\Carbon\Carbon::parse($project->created_at)->format('d.m.Y')); ?>

                            <?php endif; ?>
                        </div>
                    </div>

                </div>

                <!-- Project Actions -->
                <div style="padding: 16px 20px; background: #f9fafb; border-top: 1px solid #e5e7eb;">
                    <div style="display: flex; gap: 8px;">
                        <a href="<?php echo e(route('projects.show', $project)); ?>" style="background: #ffffff; color: #374151; padding: 6px 12px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 4px;">
                            👁 Anzeigen
                        </a>
                        <a href="<?php echo e(route('projects.edit', $project)); ?>" style="background: #ffffff; color: #374151; padding: 6px 12px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 4px;">
                            ✏️ Bearbeiten
                        </a>
                        <form action="<?php echo e(route('projects.destroy', $project)); ?>" method="POST" style="display: inline;">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" style="background: #ffffff; color: #dc2626; padding: 6px 12px; border-radius: 8px; border: none; font-size: 12px; font-weight: 500; cursor: pointer; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 4px;" onclick="return confirm('Sind Sie sicher, dass Sie dieses Projekt löschen möchten?')">
                                🗑️ Löschen
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                <div style="font-size: 48px; margin-bottom: 16px;">📁</div>
                <h3 style="font-size: 18px; font-weight: 500; color: #111827; margin: 0 0 8px 0;">Keine Projekte</h3>
                <p style="color: #6b7280; margin: 0 0 24px 0;">Beginnen Sie mit der Erstellung Ihres ersten Projekts.</p>
                <a href="<?php echo e(route('projects.create')); ?>" style="background: #ffffff; color: #374151; padding: 12px 24px; border-radius: 12px; text-decoration: none; font-size: 14px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 8px;">
                    ➕ Neues Projekt erstellen
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<script>
function syncProjectStatuses() {
    const button = document.getElementById('syncButton');
    const originalText = button.innerHTML;
    
    // Button während der Synchronisation deaktivieren
    button.disabled = true;
    button.innerHTML = '🔄 Synchronisiere...';
    button.style.background = '#6b7280';
    
    // AJAX-Request an den Server
    fetch('<?php echo e(route("projects.sync-statuses")); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Erfolg - Seite neu laden um aktualisierte Daten anzuzeigen
        if (data.updated_count > 0) {
            alert(`Status-Synchronisation abgeschlossen!\n${data.updated_count} Projekte wurden aktualisiert.`);
        } else {
            alert('Status-Synchronisation abgeschlossen!\nAlle Projekte sind bereits aktuell.');
        }
        
        // Seite neu laden
        window.location.reload();
    })
    .catch(error => {
        console.error('Fehler bei der Synchronisation:', error);
        alert('Fehler bei der Status-Synchronisation. Bitte versuchen Sie es erneut.');
        
        // Button zurücksetzen
        button.disabled = false;
        button.innerHTML = originalText;
        button.style.background = '#3b82f6';
    });
}

// Filter-Funktionen
function applyFilters() {
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
    
    // Ergebnis anzeigen
    const resultSpan = document.getElementById('filterResult');
    if (visibleCount === projectCards.length) {
        resultSpan.textContent = '';
    } else {
        resultSpan.textContent = `${visibleCount} von ${projectCards.length} Projekten angezeigt`;
    }
}

function resetFilters() {
    document.getElementById('filterStatus').value = '';
    document.getElementById('filterSort').value = '';
    document.getElementById('filterResponsible').value = '';
    document.getElementById('filterTimeframe').value = '';
    applyFilters();
}

// ==================== PROJECT TOOLTIP SYSTEM ====================
document.addEventListener('DOMContentLoaded', function() {
    initProjectTooltips();
});

function initProjectTooltips() {
    const projectCards = document.querySelectorAll('.project-card');
    let tooltip = null;

    projectCards.forEach(card => {
        card.addEventListener('mouseenter', function(e) {
            if (tooltip) {
                tooltip.remove();
            }

            // Extrahiere Daten aus data-Attributen
            const data = {
                name: this.dataset.projectName,
                startDate: this.dataset.startDate,
                endDate: this.dataset.endDate,
                estimatedHours: this.dataset.estimatedHours,
                progress: this.dataset.progress,
                teamMembers: this.dataset.teamMembers || this.dataset.team,
                revenue: this.dataset.revenue,
                status: this.dataset.status,
                requiredHours: parseInt(this.dataset.requiredHours) || 0,
                availableHours: parseInt(this.dataset.availableHours) || 0,
                isOngoing: this.dataset.isOngoing === '1',
            };

            // Erstelle Tooltip
            tooltip = document.createElement('div');
            tooltip.className = 'project-tooltip';
            tooltip.style.cssText = `
                position: absolute;
                background: white;
                border: 1px solid #e5e7eb;
                border-radius: 12px;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
                padding: 16px;
                max-width: 320px;
                z-index: 1000;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                font-size: 13px;
                line-height: 1.4;
                pointer-events: none;
                opacity: 0;
                transform: translateY(10px);
                transition: all 0.2s ease;
            `;

            // Berechne Auslastung
            const utilization = data.requiredHours > 0 ? Math.round((data.requiredHours / data.availableHours) * 100) : 0;
            const utilizationColor = utilization > 100 ? '#ef4444' : utilization > 80 ? '#f59e0b' : '#10b981';
            const utilizationPercent = Math.min(utilization, 100);

            // Tooltip-Inhalt
            tooltip.innerHTML = `
                <!-- Header -->
                <div style="margin-bottom: 12px;">
                    <div style="font-weight: 700; color: #111827; font-size: 15px; margin-bottom: 8px;">${data.name}</div>
                    <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
                        <div style="display: inline-block; padding: 3px 8px; background: #dbeafe; color: #1e40af; border-radius: 4px; font-size: 11px; font-weight: 600;">
                            ${data.status}
                        </div>
                        ${data.isOngoing ? '<div style="display: inline-block; padding: 3px 8px; background: #f3e8ff; color: #6b21a8; border-radius: 4px; font-size: 11px; font-weight: 600;">∞ Laufend</div>' : ''}
                    </div>
                </div>
                
                <!-- Wichtige Metriken -->
                <div style="display: grid; gap: 8px; margin-bottom: 12px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: #6b7280; font-size: 12px;">Zeitraum:</span>
                        <span style="color: #111827; font-weight: 600; font-size: 12px;">${data.startDate} - ${data.endDate}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: #6b7280; font-size: 12px;">Fortschritt:</span>
                        <span style="color: #111827; font-weight: 600; font-size: 12px;">${data.progress}%</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: #6b7280; font-size: 12px;">Geschätzte Stunden:</span>
                        <span style="color: #111827; font-weight: 600; font-size: 12px;">${data.estimatedHours || '0'}h</span>
                    </div>
                    ${data.revenue ? `
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: #6b7280; font-size: 12px;">Geschätzter Umsatz:</span>
                        <span style="color: #111827; font-weight: 600; font-size: 12px;">€${data.revenue}</span>
                    </div>
                    ` : ''}
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: #6b7280; font-size: 12px;">Zugewiesene Personen ${data.teamMembers && data.teamMembers !== 'Keine Personen zugewiesen' ? `(${data.teamMembers.split(',').length})` : ''}:</span>
                        <span style="color: #111827; font-weight: 600; font-size: 12px;">${data.teamMembers || 'Keine Personen zugewiesen'}</span>
                    </div>
                </div>
                
                <!-- Wöchentliche Ressourcen -->
                <div style="border-top: 1px solid #e5e7eb; padding-top: 10px;">
                    <div style="display: grid; gap: 6px; font-size: 12px;">
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #6b7280;">Benötigt/Woche:</span>
                            <span style="color: #111827; font-weight: 600;">${data.requiredHours}h</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #6b7280;">Verfügbar/Woche:</span>
                            <span style="color: #111827; font-weight: 600;">${data.availableHours}h</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: #6b7280;">Auslastung:</span>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <div style="background: #e5e7eb; width: 40px; height: 4px; border-radius: 2px; overflow: hidden;">
                                    <div style="background: ${utilizationColor}; height: 100%; width: ${utilizationPercent}%; transition: width 0.3s;"></div>
                                </div>
                                <span style="color: ${utilizationColor}; font-weight: 600; font-size: 11px;">${utilizationPercent}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Positioniere Tooltip
            tooltip.style.display = 'block';
            document.body.appendChild(tooltip);
            
            // Position berechnen
            const rect = this.getBoundingClientRect();
            const tooltipRect = tooltip.getBoundingClientRect();
            
            let left = rect.left + (rect.width / 2) - (tooltipRect.width / 2);
            let top = rect.bottom + 10;
            
            // Anpassung für Bildschirmrand
            if (left < 10) left = 10;
            if (left + tooltipRect.width > window.innerWidth - 10) {
                left = window.innerWidth - tooltipRect.width - 10;
            }
            if (top + tooltipRect.height > window.innerHeight - 10) {
                top = rect.top - tooltipRect.height - 10;
            }
            
            tooltip.style.left = left + 'px';
            tooltip.style.top = top + 'px';
            
            // Animation
            setTimeout(() => {
                tooltip.style.opacity = '1';
                tooltip.style.transform = 'translateY(0)';
            }, 10);
        });

        card.addEventListener('mouseleave', function() {
            if (tooltip) {
                tooltip.style.opacity = '0';
                tooltip.style.transform = 'translateY(10px)';
                setTimeout(() => {
                    if (tooltip) {
                        tooltip.remove();
                        tooltip = null;
                    }
                }, 200);
            }
        });
    });
}
</script>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\mein-projekt\resources\views/projects/index.blade.php ENDPATH**/ ?>