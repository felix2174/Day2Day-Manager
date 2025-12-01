<?php $__env->startSection('title', 'Projektübersicht'); ?>

<?php $__env->startSection('content'); ?>

<script src="<?php echo e(asset('js/gantt.js')); ?>"></script>


<script>
const ganttConfig = {
    baseUrl: '<?php echo e(url('/')); ?>',
    csrfToken: '<?php echo e(csrf_token()); ?>',
    mocoSyncUrl: '<?php echo e(route('moco.sync')); ?>'
};
</script>

<div style="width: 100%; margin: 0; padding: 0;">
    
    <div style="background: white; padding: 12px 20px; margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                
                <?php if($viewMode === 'employees'): ?>
                    <span style="background: #f3f4f6; color: #111827; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                        <span style="color: #6b7280; font-weight: 500;">Mitarbeiter:</span>
                        <span style="font-weight: 700;"><?php echo e($timelineByEmployee->count()); ?></span>
                    </span>
                <?php else: ?>
                    <?php
                        $totalCount = $projects->count();
                        $activeCount = $projects->filter(function ($p) {
                            if ($p->finish_date) {
                                return \Carbon\Carbon::parse($p->finish_date)->isFuture();
                            }
                            return $p->status === 'in_bearbeitung' || $p->status === 'active' || $p->status === 'planning';
                        })->count();
                        $completedCount = $projects->filter(function ($p) {
                            if ($p->finish_date) {
                                return \Carbon\Carbon::parse($p->finish_date)->isPast();
                            }
                            return $p->status === 'abgeschlossen' || $p->status === 'completed';
                        })->count();
                    ?>
                    <div style="background: #f3f4f6; padding: 6px 12px; border-radius: 8px; display: inline-flex; align-items: center; gap: 10px;">
                        
                        <div style="display: inline-flex; align-items: center; gap: 4px;">
                            <span style="color: #6b7280; font-size: 11px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px;">Gesamt</span>
                            <span style="color: #111827; font-size: 14px; font-weight: 700;"><?php echo e($totalCount); ?></span>
                        </div>
                        <span style="color: #d1d5db;">·</span>
                        
                        <div style="display: inline-flex; align-items: center; gap: 4px;">
                            <span style="color: #10b981; font-size: 11px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px;">Aktiv</span>
                            <span style="color: #10b981; font-size: 14px; font-weight: 700;"><?php echo e($activeCount); ?></span>
                        </div>
                        <span style="color: #d1d5db;">·</span>
                        
                        <div style="display: inline-flex; align-items: center; gap: 4px;">
                            <span style="color: #9ca3af; font-size: 11px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px;">Fertig</span>
                            <span style="color: #9ca3af; font-size: 14px; font-weight: 700;"><?php echo e($completedCount); ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                
                <div style="background: #f3f4f6; border-radius: 8px; padding: 3px; display: inline-flex;">
                    <a href="<?php echo e(route('gantt.index', ['view' => 'projects'])); ?>"
                       style="padding: 6px 14px; border-radius: 6px; font-size: 13px; font-weight: 600; text-decoration: none; color: <?php echo e($viewMode === 'projects' ? '#ffffff' : '#374151'); ?>; background: <?php echo e($viewMode === 'projects' ? '#111827' : 'transparent'); ?>; transition: all 0.15s ease;">
                        Projekte
                    </a>
                    <a href="<?php echo e(route('gantt.index', ['view' => 'employees'])); ?>"
                       style="padding: 6px 14px; border-radius: 6px; font-size: 13px; font-weight: 600; text-decoration: none; color: <?php echo e($viewMode === 'employees' ? '#ffffff' : '#374151'); ?>; background: <?php echo e($viewMode === 'employees' ? '#111827' : 'transparent'); ?>; transition: all 0.15s ease;">
                        Mitarbeiter
                    </a>
                </div>

                
                <div style="position: relative; display: inline-block;">
                    <button type="button" 
                            class="view-menu-btn"
                            onclick="event.stopPropagation(); toggleViewMenu();"
                            style="background: white; border: 1px solid #e5e7eb; cursor: pointer; padding: 6px 12px; color: #374151; font-size: 13px; font-weight: 600; transition: all 0.15s; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px;"
                            onmouseover="this.style.background='#f9fafb'; this.style.borderColor='#d1d5db'"
                            onmouseout="this.style.background='white'; this.style.borderColor='#e5e7eb'">
                        <?php
                            $currentZoomIcon = ['month' => '📅', 'week' => '📆', 'day' => '🗓️'][$currentZoom] ?? '📅';
                        ?>
                        <span><?php echo e($currentZoomIcon); ?></span>
                        <span>Ansicht</span>
                        <span style="font-size: 10px;">▼</span>
                    </button>
                    <div id="viewMenu" style="display: none; position: fixed; background: white; border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); z-index: 10000; min-width: 160px;">
                        <a href="<?php echo e(route('gantt.index', array_merge(request()->query(), ['zoom' => 'month']))); ?>"
                           style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; color: <?php echo e($currentZoom === 'month' ? '#3b82f6' : '#374151'); ?>; text-decoration: none; font-size: 13px; font-weight: <?php echo e($currentZoom === 'month' ? '600' : '500'); ?>; border-bottom: 1px solid #f3f4f6; transition: all 0.15s;"
                           onmouseover="this.style.background='#f9fafb'"
                           onmouseout="this.style.background='white'">
                            <span style="font-size: 16px;">📅</span>
                            <span>Monate</span>
                            <?php if($currentZoom === 'month'): ?><span style="margin-left: auto; color: #3b82f6; font-size: 14px;">✓</span><?php endif; ?>
                        </a>
                        <a href="<?php echo e(route('gantt.index', array_merge(request()->query(), ['zoom' => 'week']))); ?>"
                           style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; color: <?php echo e($currentZoom === 'week' ? '#3b82f6' : '#374151'); ?>; text-decoration: none; font-size: 13px; font-weight: <?php echo e($currentZoom === 'week' ? '600' : '500'); ?>; border-bottom: 1px solid #f3f4f6; transition: all 0.15s;"
                           onmouseover="this.style.background='#f9fafb'"
                           onmouseout="this.style.background='white'">
                            <span style="font-size: 16px;">📆</span>
                            <span>Wochen</span>
                            <?php if($currentZoom === 'week'): ?><span style="margin-left: auto; color: #3b82f6; font-size: 14px;">✓</span><?php endif; ?>
                        </a>
                        <a href="<?php echo e(route('gantt.index', array_merge(request()->query(), ['zoom' => 'day']))); ?>"
                           style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; color: <?php echo e($currentZoom === 'day' ? '#3b82f6' : '#374151'); ?>; text-decoration: none; font-size: 13px; font-weight: <?php echo e($currentZoom === 'day' ? '600' : '500'); ?>; transition: all 0.15s;"
                           onmouseover="this.style.background='#f9fafb'"
                           onmouseout="this.style.background='white'">
                            <span style="font-size: 16px;">🗓️</span>
                            <span>Tage</span>
                            <?php if($currentZoom === 'day'): ?><span style="margin-left: auto; color: #3b82f6; font-size: 14px;">✓</span><?php endif; ?>
                        </a>
                    </div>
                </div>

                
                <div style="position: relative; display: inline-block;">
                    <button type="button" 
                            class="more-menu-btn"
                            onclick="event.stopPropagation(); toggleMoreMenu();"
                            style="background: white; border: 1px solid #e5e7eb; cursor: pointer; padding: 6px 12px; color: #374151; font-size: 13px; font-weight: 600; transition: all 0.15s; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px;"
                            onmouseover="this.style.background='#f9fafb'; this.style.borderColor='#d1d5db'"
                            onmouseout="this.style.background='white'; this.style.borderColor='#e5e7eb'">
                        <span style="font-size: 16px; line-height: 1;">⋮</span>
                        <span>Mehr</span>
                    </button>
                    <div id="moreMenu" style="display: none; position: fixed; background: white; border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); z-index: 10000; min-width: 200px;">
                        <a href="<?php echo e(route('gantt.export')); ?>" 
                           onclick="handleExportClick(event, this)"
                           style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; color: #374151; text-decoration: none; font-size: 13px; font-weight: 500; border-bottom: 1px solid #f3f4f6; transition: all 0.15s;"
                           onmouseover="this.style.background='#f9fafb'"
                           onmouseout="this.style.background='white'">
                            <span style="font-size: 16px;">📤</span>
                            <span>Excel Export</span>
                        </a>
                        <a href="<?php echo e(route('projects.index')); ?>" 
                           style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; color: #374151; text-decoration: none; font-size: 13px; font-weight: 500; transition: all 0.15s;"
                           onmouseover="this.style.background='#f9fafb'"
                           onmouseout="this.style.background='white'">
                            <span style="font-size: 16px;">📊</span>
                            <span>Projektverwaltung</span>
                        </a>
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: 8px; align-items: center;">
                
                <?php if($viewMode === 'projects'): ?>
                    <?php
                        $hasActiveFilters = count(array_filter(Session::get('gantt_filters', []))) > 0;
                    ?>
                    <button id="toggleFiltersBtn" onclick="toggleFilterModal()" 
                            style="background: <?php echo e($hasActiveFilters ? '#3b82f6' : '#ffffff'); ?>; color: <?php echo e($hasActiveFilters ? '#ffffff' : '#374151'); ?>; padding: 6px 12px; border: 1px solid <?php echo e($hasActiveFilters ? '#3b82f6' : '#e5e7eb'); ?>; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.15s ease; display: inline-flex; align-items: center; gap: 6px;"
                            onmouseover="this.style.background='<?php echo e($hasActiveFilters ? '#2563eb' : '#f9fafb'); ?>'; this.style.borderColor='<?php echo e($hasActiveFilters ? '#2563eb' : '#d1d5db'); ?>'"
                            onmouseout="this.style.background='<?php echo e($hasActiveFilters ? '#3b82f6' : '#ffffff'); ?>'; this.style.borderColor='<?php echo e($hasActiveFilters ? '#3b82f6' : '#e5e7eb'); ?>'">
                        <span>🔍</span>
                        <span>Filter</span>
                        <?php if($hasActiveFilters): ?>
                            <span style="background: rgba(255,255,255,0.3); color: white; border-radius: 10px; padding: 2px 6px; font-size: 11px; font-weight: 700;">
                                <?php echo e(count(array_filter(Session::get('gantt_filters', [])))); ?>

                            </span>
                        <?php endif; ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    
    <?php if($viewMode === 'projects'): ?>
        <?php
            // Prüfe ob Filter aktiv sind (nicht-leere Werte)
            $ganttFilters = Session::get('gantt_filters', []);
            $hasActiveFilters = !empty($ganttFilters['search']) || 
                               !empty($ganttFilters['status']) || 
                               !empty($ganttFilters['employee']) || 
                               !empty($ganttFilters['timeframe']) || 
                               !empty($ganttFilters['sort']);
        ?>
        
        
        <div id="filterModalBackdrop" onclick="toggleFilterModal()" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 99998; backdrop-filter: blur(4px); transition: all 0.3s ease;"></div>
        
        
        <div id="filterModal" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 90%; max-width: 800px; max-height: 80vh; overflow-y: auto; background: white; padding: 24px; border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); z-index: 99999; transition: all 0.3s ease;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 2px solid #f3f4f6;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <h3 style="font-size: 18px; font-weight: 700; color: #111827; margin: 0;">🔍 Filter & Suche</h3>
                    <?php if($hasActiveFilters): ?>
                        <span style="background: #3b82f6; color: white; border-radius: 999px; padding: 4px 10px; font-size: 12px; font-weight: 700;">
                            <?php echo e(count(array_filter([$ganttFilters['search'] ?? '', $ganttFilters['status'] ?? '', $ganttFilters['employee'] ?? '', $ganttFilters['timeframe'] ?? '', $ganttFilters['sort'] ?? '']))); ?> aktiv
                        </span>
                    <?php endif; ?>
                </div>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <button onclick="clearAllFilters()" style="background: #fef2f2; color: #dc2626; padding: 6px 12px; border: 1px solid #fecaca; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s ease;"
                            onmouseover="this.style.background='#fee2e2'"
                            onmouseout="this.style.background='#fef2f2'">
                        🗑️ Zurücksetzen
                    </button>
                    <button onclick="toggleFilterModal()" style="background: #f3f4f6; color: #6b7280; padding: 6px 10px; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 16px; font-weight: 700; cursor: pointer; transition: all 0.2s ease; line-height: 1;"
                            onmouseover="this.style.background='#e5e7eb'; this.style.color='#111827'"
                            onmouseout="this.style.background='#f3f4f6'; this.style.color='#6b7280'"
                            title="Schließen">
                        ✕
                    </button>
                </div>
            </div>
            
            
            <div id="filterContent">
            <form method="GET" action="<?php echo e(route('gantt.index')); ?>" id="ganttFilterForm" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                <input type="hidden" name="view" value="projects">
                
                
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">Suche</label>
                    <input type="text" 
                           name="search" 
                           value="<?php echo e(Session::get('gantt_filters.search', '')); ?>" 
                           placeholder="Projektname..." 
                           style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;"
                           oninput="autoSubmitFilter()">
                </div>

                
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">Status</label>
                    <select name="status" 
                            style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; background: white;"
                            onchange="document.getElementById('ganttFilterForm').submit()">
                        <option value="">Alle Status</option>
                        <option value="in_bearbeitung" <?php echo e(Session::get('gantt_filters.status') === 'in_bearbeitung' ? 'selected' : ''); ?>>In Bearbeitung</option>
                        <option value="abgeschlossen" <?php echo e(Session::get('gantt_filters.status') === 'abgeschlossen' ? 'selected' : ''); ?>>Abgeschlossen</option>
                    </select>
                </div>

                
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">Mitarbeiter</label>
                    <select name="employee" 
                            style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; background: white;"
                            onchange="document.getElementById('ganttFilterForm').submit()">
                        <option value="">Alle Mitarbeiter</option>
                        <?php $__currentLoopData = $availableEmployees ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($emp->id); ?>" <?php echo e(Session::get('gantt_filters.employee') == $emp->id ? 'selected' : ''); ?>>
                                <?php echo e($emp->first_name); ?> <?php echo e($emp->last_name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">Zeitraum</label>
                    <select name="timeframe" 
                            style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; background: white;"
                            onchange="document.getElementById('ganttFilterForm').submit()">
                        <option value="">Alle Zeiträume</option>
                        <option value="current" <?php echo e(Session::get('gantt_filters.timeframe') === 'current' ? 'selected' : ''); ?>>Aktuelle Projekte</option>
                        <option value="future" <?php echo e(Session::get('gantt_filters.timeframe') === 'future' ? 'selected' : ''); ?>>Zukünftig</option>
                        <option value="past" <?php echo e(Session::get('gantt_filters.timeframe') === 'past' ? 'selected' : ''); ?>>Abgeschlossen</option>
                        <option value="this-month" <?php echo e(Session::get('gantt_filters.timeframe') === 'this-month' ? 'selected' : ''); ?>>Dieser Monat</option>
                        <option value="this-quarter" <?php echo e(Session::get('gantt_filters.timeframe') === 'this-quarter' ? 'selected' : ''); ?>>Dieses Quartal</option>
                    </select>
                </div>

                
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">Sortierung</label>
                    <select name="sort" 
                            style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; background: white;"
                            onchange="document.getElementById('ganttFilterForm').submit()">
                        <option value="">Standard</option>
                        <option value="name-asc" <?php echo e(Session::get('gantt_filters.sort') === 'name-asc' ? 'selected' : ''); ?>>Name (A-Z)</option>
                        <option value="name-desc" <?php echo e(Session::get('gantt_filters.sort') === 'name-desc' ? 'selected' : ''); ?>>Name (Z-A)</option>
                        <option value="date-start-asc" <?php echo e(Session::get('gantt_filters.sort') === 'date-start-asc' ? 'selected' : ''); ?>>Startdatum (aufsteigend)</option>
                        <option value="date-start-desc" <?php echo e(Session::get('gantt_filters.sort') === 'date-start-desc' ? 'selected' : ''); ?>>Startdatum (absteigend)</option>
                    </select>
                </div>
            </form>
            </div>
        </div>
    <?php endif; ?>

    <?php if($viewMode === 'employees'): ?>
        <?php echo $__env->make('gantt.partials.timeline-employees', [
            'timelineStart' => $timelineStart,
            'timelineEnd' => $timelineEnd,
            'totalTimelineDays' => $totalTimelineDays
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php else: ?>
        <?php echo $__env->make('gantt.partials.timeline-projects', [
            'timelineStart' => $timelineStart,
            'timelineEnd' => $timelineEnd,
            'totalTimelineDays' => $totalTimelineDays
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Day2Day-Manager\resources\views/gantt/index.blade.php ENDPATH**/ ?>