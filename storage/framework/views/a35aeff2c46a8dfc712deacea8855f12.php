
<div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
    <div style="padding: 20px;">
        <?php if($timelineByEmployee->count() > 0): ?>
            <div id="employeeGanttTooltip" style="display: none; position: fixed; background: white; border: 2px solid #1f2937; border-radius: 8px; padding: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 1000; max-width: 320px; pointer-events: none;">
                <div id="employeeTooltipContent"></div>
            </div>

            <div style="overflow-x: auto; padding-bottom: 10px;">
                <div id="employeeGanttScroll" data-timeline-start="<?php echo e($timelineStart->toDateString()); ?>" data-timeline-end="<?php echo e($timelineEnd->toDateString()); ?>" data-timeline-days="<?php echo e($totalTimelineDays); ?>" style="position: relative; width: 100%; overflow-y: hidden; cursor: grab; user-select: none; border: 1px solid #e5e7eb; border-radius: 8px; margin-bottom: 20px;" class="gantt-scroll-container">
                    <?php
                        $today = \Carbon\Carbon::now()->startOfDay();
                        $todayLeftPercent = null;
                        if ($today->gte($timelineStart) && $today->lte($timelineEnd)) {
                            $todayOffset = max(0, $timelineStart->diffInDays($today));
                            $todayLeftPercent = ($todayOffset / $totalTimelineDays) * 100;
                        }
                    ?>

                    <div style="display: flex; flex-direction: column; gap: 12px; padding: 12px 12px 16px; min-width: 100%;">
                        
                        <div style="display: flex; gap: 12px; align-items: stretch;">
                            <div style="width: 260px; min-width: 260px; border: 1px solid #e5e7eb; background: #f9fafb; border-radius: 8px; display: flex; align-items: center; padding: 12px 16px; font-weight: 600; color: #374151;">
                                Mitarbeiter
                            </div>
                            <div style="flex: 1; position: relative; height: 40px; border: 1px solid #e5e7eb; background: #f9fafb; border-radius: 8px; overflow: hidden;">
                                <?php if($todayLeftPercent !== null): ?>
                                    <div style="position: absolute; left: <?php echo e($todayLeftPercent); ?>%; top: 0; bottom: 0; width: 2px; background: rgba(37,99,235,0.8); pointer-events: none;"></div>
                                <?php endif; ?>
                                <?php $__currentLoopData = $timelineMonths; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $monthData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $periodStart = $monthData['start'];
                                        $periodEnd = $monthData['end'];
                                        $offsetDays = max(0, $timelineStart->diffInDays($periodStart));
                                        $widthDays = max(1, $periodStart->diffInDays($periodEnd) + 1);
                                        $leftPercent = ($offsetDays / $totalTimelineDays) * 100;
                                        $widthPercent = ($widthDays / $totalTimelineDays) * 100;
                                    ?>
                                    <div data-period-index="<?php echo e($index); ?>" data-is-current-period="<?php echo e($monthData['is_current'] ? 'true' : 'false'); ?>" data-period-start="<?php echo e($periodStart->toDateString()); ?>" data-period-end="<?php echo e($periodEnd->toDateString()); ?>" style="position: absolute; left: <?php echo e($leftPercent); ?>%; width: <?php echo e($widthPercent); ?>%; top: 0; bottom: 0; border-left: 1px solid #e5e7eb; border-right: 1px solid #e5e7eb; background: <?php echo e($monthData['is_current'] ? '#dbeafe' : 'transparent'); ?>; display: flex; align-items: center; justify-content: center; font-size: <?php echo e($timelineUnit === 'week' ? '11px' : '12px'); ?>; color: <?php echo e($monthData['is_current'] ? '#1e3a8a' : '#374151'); ?>; font-weight: 600;">
                                        <span style="background: rgba(255,255,255,0.85); padding: 2px 6px; border-radius: 4px;"><?php echo e($monthData['label']); ?></span>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>

                        
                        <div id="employeeRowsContainer" style="display: flex; flex-direction: column; gap: 12px;">
                            <?php $__currentLoopData = $timelineByEmployee; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $employee = $entry['employee'];
                                    $projects = $entry['projects'];
                                    $capacity = $entry['summary']['capacity'];
                                    $totalWeeklyLoad = $entry['summary']['total_weekly_load'];
                                    $overloadRatio = $entry['summary']['overload_ratio'];
                                    $employeeSpan = $entry['span'];
                                    $rowProjectCount = max(1, $projects->count());
                                    $timelineHeight = max(28, 28 + ($rowProjectCount - 1) * 32);
                                    $peakUtilization = $entry['summary']['peak_utilization_percent'] ?? 0;
                                    $avgUtilization = $entry['summary']['average_utilization_percent'] ?? 0;
                                    $hasAbsences = $entry['summary']['has_absences'] ?? false;
                                    
                                    // Use PEAK utilization for status (most critical metric)
                                    $statusBadge = null;
                                    if ($peakUtilization >= 999) {
                                        $statusBadge = ['label' => '🌴 Urlaub', 'color' => '#9ca3af', 'bg' => 'rgba(156, 163, 175, 0.1)'];
                                    } elseif ($peakUtilization >= 100) {
                                        $statusBadge = ['label' => '🔴 Überlast ' . round($peakUtilization) . '%', 'color' => '#dc2626', 'bg' => 'rgba(220, 38, 38, 0.1)'];
                                    } elseif ($peakUtilization >= 80) {
                                        $statusBadge = ['label' => '⚠️ Hoch ' . round($peakUtilization) . '%', 'color' => '#f59e0b', 'bg' => 'rgba(245, 158, 11, 0.1)'];
                                    } elseif ($peakUtilization >= 60) {
                                        $statusBadge = ['label' => '✓ Normal ' . round($peakUtilization) . '%', 'color' => '#3b82f6', 'bg' => 'rgba(59, 130, 246, 0.1)'];
                                    } elseif ($peakUtilization > 0) {
                                        $statusBadge = ['label' => '✅ Verfügbar ' . round($peakUtilization) . '%', 'color' => '#10b981', 'bg' => 'rgba(16, 185, 129, 0.1)'];
                                    }

                                    $employeeStart = $employeeSpan['start'];
                                    $employeeEnd = $employeeSpan['end'];
                                    $offsetDays = max(0, $timelineStart->diffInDays($employeeStart));
                                    $durationDays = max(1, $employeeStart->diffInDays($employeeEnd) + 1);
                                    $employeeLeftPercent = ($offsetDays / $totalTimelineDays) * 100;
                                    $employeeWidthPercent = ($durationDays / $totalTimelineDays) * 100;
                                ?>
                                <div class="employee-row" data-employee-id="<?php echo e($employee->id); ?>" data-row-key="employee-<?php echo e($employee->id); ?>" style="display: flex; gap: 12px; align-items: stretch;">
                                    <div class="employee-row-card" style="position: sticky; left: 0; width: 260px; min-width: 260px; border: 1px solid #e5e7eb; border-radius: 8px; background: white; padding: 12px 16px; display: flex; flex-direction: column; gap: 4px; cursor: grab; z-index: 3;">
                                        <div style="font-weight: 600; color: #111827; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?php echo e($employee->first_name); ?> <?php echo e($employee->last_name); ?>"><?php echo e($employee->first_name); ?> <?php echo e($employee->last_name); ?></div>
                                        <div style="color: #6b7280; font-size: 12px;">Kapazität: <?php echo e($capacity); ?>h/W</div>
                                        <div style="color: #6b7280; font-size: 12px;">Geplant: <?php echo e($totalWeeklyLoad); ?>h/W</div>
                                        <?php if(!empty($employee->department)): ?>
                                            <div style="color: #6b7280; font-size: 12px;"><?php echo e($employee->department); ?></div>
                                        <?php endif; ?>
                                        <?php if($statusBadge): ?>
                                            <span style="align-self: flex-start; margin-top: 4px; font-size: 11px; font-weight: 600; color: <?php echo e($statusBadge['color']); ?>; background: <?php echo e($statusBadge['bg']); ?>; border: 1px solid <?php echo e($statusBadge['color']); ?>; border-radius: 12px; padding: 4px 10px;"><?php echo e($statusBadge['label']); ?></span>
                                        <?php endif; ?>
                                        <?php if($avgUtilization > 0 && $avgUtilization != $peakUtilization): ?>
                                            <span style="align-self: flex-start; font-size: 11px; font-weight: 600; color: #6b7280; background: rgba(107, 114, 128, 0.1); border: 1px solid #9ca3af; border-radius: 12px; padding: 4px 10px;">Ø <?php echo e(round($avgUtilization)); ?>%</span>
                                        <?php endif; ?>
                                        <?php if($hasAbsences): ?>
                                            <span style="align-self: flex-start; font-size: 11px; font-weight: 600; color: #92400e; background: #fef3c7; border: 1px solid #fcd34d; border-radius: 12px; padding: 4px 10px;">📅 Abwesenheit</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="employee-buckets" style="flex: 1; position: relative; border: 1px solid #e5e7eb; border-radius: 8px; background: white; padding: 16px; min-height: <?php echo e($timelineHeight + 32); ?>px;">
                                        <?php if($projects->isEmpty()): ?>
                                            <div style="color: #9ca3af; font-size: 12px;">Keine Zuweisungen vorhanden.</div>
                                        <?php else: ?>
                                            <div style="position: relative; height: <?php echo e($timelineHeight); ?>px;">
                                                <div style="position: absolute; left: <?php echo e($employeeLeftPercent); ?>%; width: <?php echo e($employeeWidthPercent); ?>%; top: 0; bottom: 0; border-radius: 12px; background: rgba(59,130,246,0.12); border: 1px dashed rgba(59,130,246,0.3); z-index: 1;"></div>
                                                <?php if($todayLeftPercent !== null): ?>
                                                    
                                                    <div style="position: absolute; left: <?php echo e($todayLeftPercent); ?>%; top: -8px; bottom: -8px; width: 3px; background: linear-gradient(to bottom, #ef4444, #dc2626); z-index: 10; box-shadow: 0 0 8px rgba(239, 68, 68, 0.5); border-radius: 2px;"></div>
                                                    <div style="position: absolute; left: calc(<?php echo e($todayLeftPercent); ?>% - 40px); top: -28px; background: #ef4444; color: white; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3); z-index: 11; white-space: nowrap;">
                                                        📍 HEUTE
                                                    </div>
                                                <?php endif; ?>
                                                
                                                
                                                <?php
                                                    $employeeAbsencesList = $employeeAbsences->get($employee->id, collect());
                                                ?>
                                                <?php $__currentLoopData = $employeeAbsencesList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $absence): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php
                                                        $absenceStart = \Carbon\Carbon::parse($absence->start_date)->startOfDay();
                                                        $absenceEnd = \Carbon\Carbon::parse($absence->end_date)->endOfDay();
                                                        
                                                        // Clamp to timeline bounds
                                                        $clampedAbsenceStart = $absenceStart->lt($timelineStart) ? $timelineStart->copy() : $absenceStart;
                                                        $clampedAbsenceEnd = $absenceEnd->gt($timelineEnd) ? $timelineEnd->copy() : $absenceEnd;
                                                        
                                                        // Calculate position
                                                        $absenceOffsetDays = max(0, $timelineStart->diffInDays($clampedAbsenceStart));
                                                        $absenceDurationDays = max(1, $clampedAbsenceStart->diffInDays($clampedAbsenceEnd) + 1);
                                                        $absenceLeftPercent = ($absenceOffsetDays / $totalTimelineDays) * 100;
                                                        $absenceWidthPercent = ($absenceDurationDays / $totalTimelineDays) * 100;
                                                        
                                                        // Determine absence type and color
                                                        $absenceType = $absence->type ?? 'vacation';
                                                        $absenceIcon = match($absenceType) {
                                                            'vacation' => '🌴',
                                                            'sick' => '🏥',
                                                            'holiday' => '🎉',
                                                            default => '📅'
                                                        };
                                                        $absenceLabel = match($absenceType) {
                                                            'vacation' => 'Urlaub',
                                                            'sick' => 'Krank',
                                                            'holiday' => 'Feiertag',
                                                            default => 'Abwesend'
                                                        };
                                                    ?>
                                                    <div class="absence-bar" 
                                                         data-absence-type="<?php echo e($absenceType); ?>"
                                                         data-absence-label="<?php echo e($absenceLabel); ?>"
                                                         data-start-date="<?php echo e($absenceStart->format('d.m.Y')); ?>"
                                                         data-end-date="<?php echo e($absenceEnd->format('d.m.Y')); ?>"
                                                         style="position: absolute; 
                                                                left: <?php echo e($absenceLeftPercent); ?>%; 
                                                                width: <?php echo e($absenceWidthPercent); ?>%; 
                                                                top: 0; 
                                                                height: 100%; 
                                                                background: repeating-linear-gradient(45deg, #d1d5db, #d1d5db 10px, #e5e7eb 10px, #e5e7eb 20px); 
                                                                border: 2px solid #9ca3af; 
                                                                border-radius: 8px; 
                                                                z-index: 4; 
                                                                opacity: 0.85; 
                                                                cursor: pointer; 
                                                                display: flex; 
                                                                align-items: center; 
                                                                justify-content: center; 
                                                                font-size: 14px; 
                                                                font-weight: 700; 
                                                                color: #374151; 
                                                                box-shadow: 0 2px 8px rgba(0,0,0,0.15);"
                                                         onmouseenter="showAbsenceTooltip(event, '<?php echo e($absenceIcon); ?>', '<?php echo e($absenceLabel); ?>', '<?php echo e($absenceStart->format('d.m.Y')); ?>', '<?php echo e($absenceEnd->format('d.m.Y')); ?>')"
                                                         onmouseleave="hideAbsenceTooltip()">
                                                        <span style="text-shadow: 0 1px 2px rgba(255,255,255,0.8);"><?php echo e($absenceIcon); ?></span>
                                                    </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                
                                                <?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $projectIndex => $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php
                                                        $pStart = $project['start'];
                                                        $pEnd = $project['end'];
                                                        $clampedStart = $pStart->lt($timelineStart) ? $timelineStart->copy() : $pStart;
                                                        $clampedEnd = $pEnd->gt($timelineEnd) ? $timelineEnd->copy() : $pEnd;
                                                        $offsetDays = max(0, $timelineStart->diffInDays($clampedStart));
                                                        $durationDays = max(1, $clampedStart->diffInDays($clampedEnd) + 1);
                                                        $leftPercent = ($offsetDays / $totalTimelineDays) * 100;
                                                        $widthPercent = ($durationDays / $totalTimelineDays) * 100;
                                                        $hours = $project['weekly_hours'];
                                                        $utilizationRatio = $project['utilization_ratio'];
                                                        $isOverCapacity = $project['is_over_capacity'];
                                                        $bgColor = '#0ea5e9';
                                                        $borderStyle = 'none';
                                                        if ($project['sources']->contains('moco')) {
                                                            $bgColor = '#06b6d4';
                                                        }
                                                        if ($isOverCapacity) {
                                                            $borderStyle = '2px solid #dc2626';
                                                            $bgColor = '#fb7185';
                                                        } elseif ($utilizationRatio !== null && $utilizationRatio > 0.9) {
                                                            $borderStyle = '2px dashed #f59e0b';
                                                            $bgColor = '#fcd34d';
                                                        }
                                                    ?>
                                                    <div class="employee-project-row" data-assignment-id="<?php echo e($project['assignment_ids']->first()); ?>" data-employee-id="<?php echo e($employee->id); ?>" data-start-date="<?php echo e($project['start']->toDateString()); ?>" data-end-date="<?php echo e($project['end']->toDateString()); ?>" style="position: absolute; left: <?php echo e($leftPercent); ?>%; top: <?php echo e($projectIndex * 32); ?>px; width: <?php echo e($widthPercent); ?>%; height: 24px; z-index: 3; cursor: grab;">
                                                        <div class="employee-project-bar" style="width: 100%; height: 100%; border-radius: 12px; background: <?php echo e($bgColor); ?>; border: <?php echo e($borderStyle); ?>; display: flex; align-items: center; justify-content: center; color: #0f172a; font-size: 12px; font-weight: 600; box-shadow: 0 2px 6px rgba(0,0,0,0.15);">
                                                            <span style="padding: 0 10px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?php echo e($project['project_name']); ?>"><?php echo e(Str::limit($project['project_name'], 32)); ?></span>
                                                            <?php if($hours): ?>
                                                                <span style="margin-left: 8px; font-weight: 600; background: rgba(15,23,42,0.1); padding: 2px 6px; border-radius: 12px;"><?php echo e($hours); ?>h</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div style="margin-top: 20px; padding: 16px; background: #f9fafb; border-radius: 6px;">
                <h4 style="font-size: 14px; font-weight: 600; color: #374151; margin: 0 0 12px 0;">Legende</h4>
                <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 12px; height: 12px; background: #06b6d4; border-radius: 2px;"></div>
                        <span style="font-size: 12px; color: #374151;">Projekt-Zuweisung</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 12px; height: 12px; background: repeating-linear-gradient(45deg, #d1d5db, #d1d5db 3px, #e5e7eb 3px, #e5e7eb 6px); border: 1px solid #9ca3af; border-radius: 2px;"></div>
                        <span style="font-size: 12px; color: #374151;">🌴 Abwesenheit (Urlaub/Krank)</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 12px; height: 12px; background: #fb7185; border-radius: 2px;"></div>
                        <span style="font-size: 12px; color: #374151;">🔴 Überlast (&gt;100% Auslastung)</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 12px; height: 12px; background: #fcd34d; border-radius: 2px;"></div>
                        <span style="font-size: 12px; color: #374151;">⚠️ Hoch (80-100% Auslastung)</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 12px; height: 12px; background: #3b82f6; border-radius: 2px;"></div>
                        <span style="font-size: 12px; color: #374151;">✓ Normal (60-80% Auslastung)</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 12px; height: 12px; background: #10b981; border-radius: 2px;"></div>
                        <span style="font-size: 12px; color: #374151;">✅ Verfügbar (&lt;60% Auslastung)</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 2px; height: 16px; background: linear-gradient(to bottom, #2563eb, #60a5fa);"></div>
                        <span style="font-size: 12px; color: #374151; font-weight: 600;">Aktuelle KW / Monat</span>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 60px 20px; color: #6b7280;">
                <h3 style="font-size: 18px; font-weight: 500; color: #111827; margin: 0 0 8px 0;">Keine Mitarbeiter-Zuweisungen</h3>
                <p style="margin: 0 0 24px 0;">Sobald Mitarbeiter Projekten zugewiesen sind, erscheint hier die Wochenübersicht.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Absence Tooltip System
let absenceTooltip = null;

function createAbsenceTooltip() {
    if (!absenceTooltip) {
        absenceTooltip = document.createElement('div');
        absenceTooltip.id = 'absence-tooltip';
        absenceTooltip.style.cssText = `
            display: none;
            position: fixed;
            background: white;
            border: 2px solid #9ca3af;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.25);
            padding: 16px;
            z-index: 10000;
            max-width: 280px;
            pointer-events: none;
        `;
        document.body.appendChild(absenceTooltip);
    }
    return absenceTooltip;
}

function showAbsenceTooltip(event, icon, label, startDate, endDate) {
    const tooltip = createAbsenceTooltip();
    
    tooltip.innerHTML = `
        <div style="margin-bottom: 12px; padding-bottom: 12px; border-bottom: 2px solid #e5e7eb;">
            <h4 style="margin: 0 0 4px 0; font-size: 18px; font-weight: 700; color: #111827;">${icon} ${label}</h4>
            <p style="margin: 0; font-size: 13px; color: #6b7280;">Mitarbeiter nicht verfügbar</p>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <div>
                <div style="font-size: 11px; color: #6b7280; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Von</div>
                <div style="font-size: 14px; color: #111827; font-weight: 600;">${startDate}</div>
            </div>
            <div>
                <div style="font-size: 11px; color: #6b7280; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Bis</div>
                <div style="font-size: 14px; color: #111827; font-weight: 600;">${endDate}</div>
            </div>
        </div>
        <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #e5e7eb; font-size: 11px; color: #6b7280; text-align: center; font-style: italic;">
            ⚠️ Kapazität in diesem Zeitraum = 0 Stunden
        </div>
    `;
    
    tooltip.style.display = 'block';
    positionAbsenceTooltip(event, tooltip);
}

function hideAbsenceTooltip() {
    if (absenceTooltip) {
        absenceTooltip.style.display = 'none';
    }
}

function positionAbsenceTooltip(event, tooltip) {
    const tooltipRect = tooltip.getBoundingClientRect();
    const margin = 12;
    
    let left = event.clientX + margin;
    let top = event.clientY + margin;
    
    // Keep tooltip in viewport
    if (left + tooltipRect.width > window.innerWidth) {
        left = event.clientX - tooltipRect.width - margin;
    }
    if (top + tooltipRect.height > window.innerHeight) {
        top = event.clientY - tooltipRect.height - margin;
    }
    
    tooltip.style.left = left + 'px';
    tooltip.style.top = top + 'px';
}

// Update tooltip position on mouse move
document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('mousemove', function(e) {
        if (absenceTooltip && absenceTooltip.style.display === 'block') {
            const target = e.target.closest('.absence-bar');
            if (target) {
                positionAbsenceTooltip(e, absenceTooltip);
            }
        }
    });
});
</script>
<?php /**PATH C:\xampp\htdocs\mein-projekt\resources\views/gantt/partials/timeline-employees.blade.php ENDPATH**/ ?>