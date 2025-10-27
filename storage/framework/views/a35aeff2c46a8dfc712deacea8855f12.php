
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
                                    $overloadBadge = null;
                                    if ($overloadRatio !== null) {
                                        if ($overloadRatio > 1.05) {
                                            $overloadBadge = ['label' => 'Überlast', 'color' => '#dc2626'];
                                        } elseif ($overloadRatio > 0.9) {
                                            $overloadBadge = ['label' => 'Grenze', 'color' => '#f59e0b'];
                                        }
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
                                        <?php if($overloadBadge): ?>
                                            <span style="align-self: flex-start; margin-top: 4px; font-size: 11px; font-weight: 600; color: <?php echo e($overloadBadge['color']); ?>; background: rgba(220, 38, 38, 0.05); border: 1px solid <?php echo e($overloadBadge['color']); ?>; border-radius: 12px; padding: 2px 8px;"><?php echo e($overloadBadge['label']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="employee-buckets" style="flex: 1; position: relative; border: 1px solid #e5e7eb; border-radius: 8px; background: white; padding: 16px; min-height: <?php echo e($timelineHeight + 32); ?>px;">
                                        <?php if($projects->isEmpty()): ?>
                                            <div style="color: #9ca3af; font-size: 12px;">Keine Zuweisungen vorhanden.</div>
                                        <?php else: ?>
                                            <div style="position: relative; height: <?php echo e($timelineHeight); ?>px;">
                                                <div style="position: absolute; left: <?php echo e($employeeLeftPercent); ?>%; width: <?php echo e($employeeWidthPercent); ?>%; top: 0; bottom: 0; border-radius: 12px; background: rgba(59,130,246,0.12); border: 1px dashed rgba(59,130,246,0.3); z-index: 1;"></div>
                                                <?php if($todayLeftPercent !== null): ?>
                                                    <div style="position: absolute; left: <?php echo e($todayLeftPercent); ?>%; top: 0; bottom: 0; width: 1px; background: rgba(37,99,235,0.25); z-index: 2;"></div>
                                                <?php endif; ?>
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
                        <div style="width: 12px; height: 12px; background: #fcd34d; border-radius: 2px;"></div>
                        <span style="font-size: 12px; color: #374151;">Geplante Überlastung (&gt; Kapazität)</span>
                    </div>
                    <div style="display: flex; alignments: center; gap: 8px;">
                        <div style="width: 12px; height: 12px; background: #fef3c7; border-radius: 2px;"></div>
                        <span style="font-size: 12px; color: #374151;">Abwesenheit im Zeitraum</span>
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
<?php /**PATH C:\xampp\htdocs\mein-projekt\resources\views/gantt/partials/timeline-employees.blade.php ENDPATH**/ ?>