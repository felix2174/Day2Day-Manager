
<div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
    <div style="padding: 20px;">
        <?php if($projects->count() > 0): ?>
            <?php
                $timelineStartIso = $timelineStart->format('Y-m-d');
                $timelineEndIso = $timelineEnd->format('Y-m-d');
                $timelineSpanDays = max(1, $timelineStart->diffInDays($timelineEnd) + 1);
            ?>

            <div id="ganttTooltip" style="display: none; position: fixed; background: white; border: 2px solid #3b82f6; border-radius: 8px; padding: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 1000; max-width: 300px; pointer-events: none;">
                <div id="tooltipContent"></div>
            </div>

            <div style="overflow-x: auto; padding-bottom: 10px;">
                <div id="ganttScrollContainer" data-timeline-start="<?php echo e($timelineStartIso); ?>" data-timeline-end="<?php echo e($timelineEndIso); ?>" data-timeline-days="<?php echo e($timelineSpanDays); ?>" style="position: relative; width: 100%; overflow-y: hidden; cursor: grab; user-select: none; border: 1px solid #e5e7eb; border-radius: 8px;" class="gantt-scroll-container">
                    <?php
                        $today = \Carbon\Carbon::now()->startOfDay();
                        $todayRatio = null;
                        if ($today->gte($timelineStart) && $today->lte($timelineEnd)) {
                            $todayOffset = max(0, $timelineStart->diffInDays($today));
                            $todayRatio = $todayOffset / $timelineSpanDays;
                        }
                    ?>

                    
                    <?php if($todayRatio !== null): ?>
                        <div style="position: absolute; left: calc(272px + <?php echo e($todayRatio); ?> * (100% - 284px)); top: 64px; bottom: 12px; width: 3px; background: rgba(239, 68, 68, 0.28); z-index: 5; pointer-events: none; box-shadow: 0 0 12px rgba(239, 68, 68, 0.4);"></div>
                    <?php endif; ?>

                    <div id="ganttContent" style="display: flex; flex-direction: column; gap: 12px; padding: 12px 12px 16px; min-width: 100%;">
                        
                        <div style="display: flex; gap: 12px; align-items: stretch;">
                            <div style="width: 260px; min-width: 260px; border: 1px solid #e5e7eb; background: #f9fafb; border-radius: 8px; display: flex; align-items: center; padding: 12px 16px; font-weight: 600; color: #374151;">Projekt</div>
                            <div style="flex: 1; position: relative; height: 40px; border: 1px solid #e5e7eb; background: #f9fafb; border-radius: 8px; overflow: visible; z-index: 10;">
                                <?php $__currentLoopData = $timelineMonths; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $monthData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $periodStart = $monthData['start'];
                                        $periodEnd = $monthData['end'];
                                        $offsetDays = max(0, $timelineStart->diffInDays($periodStart));
                                        $widthDays = max(1, $periodStart->diffInDays($periodEnd) + 1);
                                        $leftPercent = ($offsetDays / $timelineSpanDays) * 100;
                                        $widthPercent = ($widthDays / $timelineSpanDays) * 100;
                                    ?>
                                    <div data-period-index="<?php echo e($index); ?>" data-is-current-period="<?php echo e($monthData['is_current'] ? 'true' : 'false'); ?>" data-period-start="<?php echo e($periodStart->toDateString()); ?>" data-period-end="<?php echo e($periodEnd->toDateString()); ?>" style="position: absolute; left: <?php echo e($leftPercent); ?>%; width: <?php echo e($widthPercent); ?>%; top: 0; bottom: 0; border-left: 1px solid #e5e7eb; border-right: 1px solid #e5e7eb; background: <?php echo e($monthData['is_current'] ? '#dbeafe' : 'transparent'); ?>; display: flex; align-items: center; justify-content: center; font-size: <?php echo e($timelineUnit === 'week' ? '11px' : '12px'); ?>; color: <?php echo e($monthData['is_current'] ? '#1e3a8a' : '#374151'); ?>; font-weight: 600;">
                                        <span style="background: rgba(255,255,255,0.85); padding: 2px 6px; border-radius: 4px;"><?php echo e($monthData['label']); ?></span>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>

                        
                        <?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $metrics = $projectMetrics[$project->id] ?? null;
                                if (!$metrics) { continue; }
                                $projectData = $timelineByProject[$project->id] ?? ['summary' => [], 'assignments' => collect(), 'team_names' => collect(), 'has_assignments' => false];
                                $projectAssignments = collect($projectData['assignments'] ?? [])->values();
                                $projectSummary = $projectData['summary'] ?? [];
                                $projectStart = $metrics['startDate'];
                                $projectEnd = $metrics['endDate'];
                                $projectClampedStart = $projectStart->lt($timelineStart) ? $timelineStart->copy() : $projectStart;
                                $projectClampedEnd = $projectEnd->gt($timelineEnd) ? $timelineEnd->copy() : $projectEnd;
                                $projectOffsetDays = max(0, $timelineStart->diffInDays($projectClampedStart));
                                $projectDurationDays = max(1, $projectClampedStart->diffInDays($projectClampedEnd) + 1);
                                $projectLeftPercent = ($projectOffsetDays / $timelineSpanDays) * 100;
                                $projectWidthPercent = ($projectDurationDays / $timelineSpanDays) * 100;
                                $statusColor = match (true) {
                                    $projectClampedEnd->isPast() => '#6b7280',
                                    $metrics['bottleneck'] => '#dc2626',
                                    $metrics['riskScore'] >= 60 => '#f97316',
                                    $metrics['riskScore'] >= 40 => '#facc15',
                                    default => '#10b981',
                                };
                                $progress = round($project->progress ?? 0);
                            ?>
                            <?php
                                $assignmentCount = $projectAssignments->count();
                                $timelineHeight = 40 + ($assignmentCount > 0 ? $assignmentCount * 32 : 0);
                            ?>
                                <?php
                                    $projectData = $timelineByProject[$project->id] ?? ['summary' => [], 'assignments' => collect(), 'team_names' => collect(), 'has_assignments' => false];
                                    $projectAssignments = collect($projectData['assignments'] ?? [])->values();
                                    $projectSummary = $projectData['summary'] ?? [];
                                ?>
                                <div class="gantt-project-row" data-project-id="<?php echo e($project->id); ?>" style="border: 1px solid #e5e7eb; border-radius: 8px; background: white; padding: 16px; display: flex; flex-direction: column; gap: 16px;">
                                    <div style="display: grid; grid-template-columns: 260px 1fr; gap: 12px; align-items: center;">
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            
                                            <div style="position: relative; display: inline-block;">
                                                <button type="button" onclick="toggleProjectMenu(<?php echo e($project->id); ?>)" style="background: none; border: none; cursor: pointer; padding: 4px 8px; color: #6b7280; font-size: 18px; line-height: 1; transition: all 0.2s;" onmouseover="this.style.color='#111827'" onmouseout="this.style.color='#6b7280'">
                                                    ⋮
                                                </button>
                                                <div id="projectMenu<?php echo e($project->id); ?>" style="display: none; position: absolute; top: 100%; left: 0; background: white; border: 1px solid #d1d5db; border-radius: 8px; box-shadow: 0 8px 24px rgba(0,0,0,0.12); z-index: 1000; min-width: 220px; margin-top: 4px; overflow: hidden;">
                                                    <div style="padding: 4px 0;">
                                                        <button type="button" onclick="openAddEmployeeModal(<?php echo e($project->id); ?>)" style="width: 100%; text-align: left; padding: 10px 16px; background: none; border: none; cursor: pointer; font-size: 14px; color: #374151; display: flex; align-items: center; gap: 10px; transition: all 0.15s;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='white'">
                                                            <span style="font-size: 16px; color: #10b981;">➕</span>
                                                            <span style="font-weight: 500;">Mitarbeiter hinzufügen</span>
                                                        </button>
                                                        <div style="height: 1px; background: #e5e7eb; margin: 4px 0;"></div>
                                                        <button type="button" onclick="window.location.href='<?php echo e(route('projects.show', $project->id)); ?>'" style="width: 100%; text-align: left; padding: 10px 16px; background: none; border: none; cursor: pointer; font-size: 14px; color: #374151; display: flex; align-items: center; gap: 10px; transition: all 0.15s;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='white'">
                                                            <span style="font-size: 16px; color: #3b82f6;">📊</span>
                                                            <span style="font-weight: 500;">Projektdetails</span>
                                                        </button>
                                                        <button type="button" onclick="openRemoveEmployeesModal(<?php echo e($project->id); ?>)" style="width: 100%; text-align: left; padding: 10px 16px; background: none; border: none; cursor: pointer; font-size: 14px; color: #374151; display: flex; align-items: center; gap: 10px; transition: all 0.15s;" onmouseover="this.style.background='#fef2f2'; this.querySelector('span:last-child').style.color='#dc2626'" onmouseout="this.style.background='white'; this.querySelector('span:last-child').style.color='#374151'">
                                                            <span style="font-size: 16px; color: #ef4444;">🗑️</span>
                                                            <span style="font-weight: 500; transition: color 0.15s;">Mitarbeiter entfernen</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div style="font-weight: 600; color: #111827; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?php echo e($project->name); ?>"><?php echo e($project->name); ?></div>
                                        </div>
                                        <div style="position: relative; height: 24px; border: 1px solid #e5e7eb; border-radius: 12px; background: #f9fafb;">
                                            <div class="gantt-bar" data-project-name="<?php echo e($project->name); ?>" data-start-date="<?php echo e($projectStart->format('d.m.Y')); ?>" data-end-date="<?php echo e($projectEnd->format('d.m.Y')); ?>" data-required-hours="<?php echo e((int)round($metrics['requiredPerWeek'])); ?>" data-available-hours="<?php echo e((int)round($metrics['availablePerWeek'])); ?>" data-progress="<?php echo e($progress); ?>" data-status="<?php echo e(ucfirst($project->status)); ?>" data-capacity-ratio="<?php echo e($metrics['capacityRatio'] ?? ''); ?>" data-risk-score="<?php echo e(round($metrics['riskScore'])); ?>" style="position: absolute; top: 0; height: 100%; border-radius: 12px; background: <?php echo e($statusColor); ?>; left: <?php echo e($projectLeftPercent); ?>%; width: <?php echo e($projectWidthPercent); ?>%; min-width: 1.5%; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px; font-weight: 600; box-shadow: 0 2px 6px rgba(0,0,0,0.15);">
                                                <span title="<?php echo e($project->name); ?>" style="padding: 0 10px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo e(Str::limit($project->name, 32)); ?></span>
                                                <span style="margin-left: 8px; font-weight: 600; background: rgba(255,255,255,0.2); padding: 2px 6px; border-radius: 12px;"><?php echo e($progress); ?>%</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div style="display: flex; flex-direction: column; gap: 8px;">
                                        <?php
                                            // Load fresh assignments directly from database for this project
                                            $freshAssignments = \App\Models\Assignment::where('project_id', $project->id)
                                                ->with('employee')
                                                ->orderBy('display_order')
                                                ->get();
                                            
                                            // Convert to the format expected by the view
                                            $assignmentsForView = $freshAssignments->map(function($assignment) use ($timelineStart, $timelineEnd) {
                                                $start = $assignment->start_date ? \Carbon\Carbon::parse($assignment->start_date) : now();
                                                $end = $assignment->end_date ? \Carbon\Carbon::parse($assignment->end_date) : now()->addWeeks(2);
                                                
                                                return [
                                                    'employee_id' => $assignment->employee_id,
                                                    'employee_name' => $assignment->employee ? $assignment->employee->first_name . ' ' . $assignment->employee->last_name : 'Unbekannt',
                                                    'task_name' => $assignment->task_name,
                                                    'task_description' => $assignment->task_description,
                                                    'start' => $start,
                                                    'end' => $end,
                                                    'weekly_hours' => $assignment->weekly_hours,
                                                    'assignment_id' => $assignment->id,
                                                    'assignment_ids' => [$assignment->id],
                                                ];
                                            });
                                            
                                            // Group assignments by employee
                                            $assignmentsByEmployee = $assignmentsForView->groupBy('employee_id');
                                        ?>
                                        <?php $__empty_1 = true; $__currentLoopData = $assignmentsByEmployee; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employeeId => $employeeAssignments): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <?php
                                                $firstAssignment = $employeeAssignments->first();
                                                $employeeName = $firstAssignment['employee_name'] ?? 'Unbekannt';
                                                
                                                // Skip employees with "Unbekannt" name
                                                if ($employeeName === 'Unbekannt') {
                                                    continue;
                                                }
                                            ?>
                                            
                                            <div style="display: grid; grid-template-columns: 260px 1fr; gap: 12px; align-items: center; margin-top: 8px;">
                                                <div style="height: 28px; display: flex; align-items: center; background: #f3f4f6; border: 1px solid #d1d5db; border-radius: 8px; padding: 0 12px; color: #111827; font-size: 13px; font-weight: 600; gap: 8px;">
                                                    
                                                    <div style="position: relative; display: inline-block;">
                                                        <button type="button" onclick="toggleEmployeeMenu(<?php echo e($project->id); ?>, <?php echo e($employeeId); ?>)" style="background: none; border: none; cursor: pointer; padding: 2px 4px; color: #6b7280; font-size: 16px; line-height: 1; transition: all 0.2s;" onmouseover="this.style.color='#111827'" onmouseout="this.style.color='#6b7280'">
                                                            ⋮
                                                        </button>
                                                        <div id="employeeMenu<?php echo e($project->id); ?>_<?php echo e($employeeId); ?>" style="display: none; position: absolute; top: 100%; left: 0; background: white; border: 1px solid #d1d5db; border-radius: 8px; box-shadow: 0 8px 24px rgba(0,0,0,0.12); z-index: 1000; min-width: 220px; margin-top: 4px; overflow: hidden;">
                                                            <div style="padding: 4px 0;">
                                                                <button type="button" onclick="openAddTaskModal(<?php echo e($project->id); ?>, <?php echo e($employeeId); ?>)" style="width: 100%; text-align: left; padding: 10px 16px; background: none; border: none; cursor: pointer; font-size: 14px; color: #374151; display: flex; align-items: center; gap: 10px; transition: all 0.15s;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='white'">
                                                                    <span style="font-size: 16px; color: #10b981;">➕</span>
                                                                    <span style="font-weight: 500;">Aufgabe hinzufügen</span>
                                                                </button>
                                                                <div style="height: 1px; background: #e5e7eb; margin: 4px 0;"></div>
                                                                <button type="button" onclick="openManageTasksModal(<?php echo e($project->id); ?>, <?php echo e($employeeId); ?>, '<?php echo e($employeeName); ?>')" style="width: 100%; text-align: left; padding: 10px 16px; background: none; border: none; cursor: pointer; font-size: 14px; color: #374151; display: flex; align-items: center; gap: 10px; transition: all 0.15s;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='white'">
                                                                    <span style="font-size: 16px; color: #3b82f6;">📋</span>
                                                                    <span style="font-weight: 500;">Aufgaben verwalten</span>
                                                                </button>
                                                                <button type="button" onclick="openEmployeeUtilizationModal(<?php echo e($employeeId); ?>, '<?php echo e($employeeName); ?>')" style="width: 100%; text-align: left; padding: 10px 16px; background: none; border: none; cursor: pointer; font-size: 14px; color: #374151; display: flex; align-items: center; gap: 10px; transition: all 0.15s;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='white'">
                                                                    <span style="font-size: 16px; color: #8b5cf6;">📊</span>
                                                                    <span style="font-weight: 500;">Auslastung anzeigen</span>
                                                                </button>
                                                                <div style="height: 1px; background: #e5e7eb; margin: 4px 0;"></div>
                                                                <button type="button" onclick="if(confirm('Möchten Sie <?php echo e($employeeName); ?> wirklich aus diesem Projekt entfernen?')) { removeEmployeeFromProject(<?php echo e($project->id); ?>, <?php echo e($employeeId); ?>, '<?php echo e($employeeName); ?>'); }" style="width: 100%; text-align: left; padding: 10px 16px; background: none; border: none; cursor: pointer; font-size: 14px; color: #374151; display: flex; align-items: center; gap: 10px; transition: all 0.15s;" onmouseover="this.style.background='#fef2f2'; this.querySelector('span:last-child').style.color='#dc2626'" onmouseout="this.style.background='white'; this.querySelector('span:last-child').style.color='#374151'">
                                                                    <span style="font-size: 16px; color: #ef4444;">🗑️</span>
                                                                    <span style="font-weight: 500; transition: color 0.15s;">Aus Projekt entfernen</span>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <span class="employee-name" 
                                                          data-employee-id="<?php echo e($employeeId); ?>"
                                                          data-employee-name="<?php echo e($employeeName); ?>"
                                                          data-task-count="<?php echo e(count($employeeAssignments)); ?>"
                                                          data-total-hours="<?php echo e(collect($employeeAssignments)->sum('weekly_hours')); ?>"
                                                          style="cursor: pointer;"
                                                          title="<?php echo e($employeeName); ?>"><?php echo e($employeeName); ?></span>
                                                </div>
                                                
                                                <div style="position: relative; height: 28px; border-radius: 8px; background: #f9fafb; border: 1px solid #e5e7eb;">
                                                    <?php $__currentLoopData = $employeeAssignments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $taskIndex => $assignment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <?php
                                                            $assignmentStart = $assignment['start'];
                                                            $assignmentEnd = $assignment['end'];
                                                            $clampedStart = $assignmentStart->lt($timelineStart) ? $timelineStart->copy() : $assignmentStart;
                                                            $clampedEnd = $assignmentEnd->gt($timelineEnd) ? $timelineEnd->copy() : $assignmentEnd;
                                                            $offsetDays = max(0, $timelineStart->diffInDays($clampedStart));
                                                            $durationDays = max(1, $clampedStart->diffInDays($clampedEnd) + 1);
                                                            $leftPercent = ($offsetDays / $timelineSpanDays) * 100;
                                                            $widthPercent = ($durationDays / $timelineSpanDays) * 100;
                                                            $hours = $assignment['weekly_hours'];
                                                            $assignmentId = collect($assignment['assignment_ids'] ?? [])->first();
                                                            
                                                            // Try to get task_name from assignment, fallback to primary_activity or generic label
                                                            $taskName = $assignment['task_name'] ?? ($assignment['primary_activity'] ?? 'Aufgabe ' . ($taskIndex + 1));
                                                            
                                                            $barColor = '#0ea5e9'; // Blue for tasks
                                                        ?>
                                                        <div class="project-task-bar" 
                                                             data-project-id="<?php echo e($project->id); ?>" 
                                                             data-assignment-id="<?php echo e($assignmentId ?? ''); ?>" 
                                                             data-employee-id="<?php echo e($employeeId); ?>"
                                                             data-task-name="<?php echo e($taskName); ?>"
                                                             data-task-description="<?php echo e($assignment['task_description'] ?? ''); ?>"
                                                             data-start-date="<?php echo e($assignmentStart->format('d.m.Y')); ?>"
                                                             data-end-date="<?php echo e($assignmentEnd->format('d.m.Y')); ?>"
                                                             data-weekly-hours="<?php echo e($hours ?? 20); ?>"
                                                             data-duration-days="<?php echo e($assignmentStart->diffInDays($assignmentEnd) + 1); ?>"
                                                             style="position: absolute; top: 2px; left: <?php echo e($leftPercent); ?>%; width: <?php echo e($widthPercent); ?>%; height: calc(100% - 4px); border-radius: 8px; background: <?php echo e($barColor); ?>; display: flex; align-items: center; justify-content: center; color: white; font-size: 11px; font-weight: 600; box-shadow: 0 2px 4px rgba(0,0,0,0.15); cursor: pointer; transition: all 0.2s;">
                                                            <span style="padding: 0 8px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo e(Str::limit($taskName, 32)); ?></span>
                                                            <?php if($hours): ?>
                                                                <span style="margin-left: 6px; font-weight: 600; background: rgba(255,255,255,0.2); padding: 2px 6px; border-radius: 8px; font-size: 10px;"><?php echo e($hours); ?>h</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </div>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <div style="display: grid; grid-template-columns: 260px 1fr; gap: 12px; align-items: center; color: #9ca3af; font-size: 12px; padding: 8px 0;">
                                                <div style="padding-left: 32px;">Keine Mitarbeiter zugewiesen.</div>
                                                <div style="height: 1px; background: #e5e7eb;"></div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>

            <div style="margin-top: 20px; padding: 16px; background: #f9fafb; border-radius: 6px;">
                <h4 style="font-size: 14px; font-weight: 600; color: #374151; margin: 0 0 12px 0;">Legende</h4>
                <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 12px; height: 12px; background: #10b981; border-radius: 2px;"></div>
                        <span style="font-size: 12px; color: #374151;">In Bearbeitung</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 12px; height: 12px; background: #3b82f6; border-radius: 2px;"></div>
                        <span style="font-size: 12px; color: #374151;">Geplante Projekte</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 12px; height: 12px; background: #ef4444; border-radius: 2px;"></div>
                        <span style="font-size: 12px; color: #374151;">Engpass (Kapazität &lt; Bedarf)</span>
                    </div>
                    <div style="display: flex; alignItems: center; gap: 8px;">
                        <div style="width: 12px; height: 12px; background: #f59e0b; border-radius: 2px;"></div>
                        <span style="font-size: 12px; color: #374151;">Aktueller Fortschritt</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 12px; height: 12px; background: #6b7280; border-radius: 2px;"></div>
                        <span style="font-size: 12px; color: #374151;">Abgeschlossene Projekte</span>
                    </div>
                    <div style="display: flex; alignItems: center; gap: 8px;">
                        <div style="width: 2px; height: 16px; background: linear-gradient(to bottom, #3b82f6, #60a5fa);"></div>
                        <span style="font-size: 12px; color: #374151; font-weight: 600;">Aktueller Monat</span>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 60px 20px; color: #6b7280;">
                <h3 style="font-size: 18px; font-weight: 500; color: #111827; margin: 0 0 8px 0;">Keine Projekte vorhanden</h3>
                <p style="margin: 0 0 24px 0;">Erstelle Projekte oder synchronisiere mit MOCO, um die Timeline zu füllen.</p>
            </div>
        <?php endif; ?>
    </div>
</div>


<div id="addEmployeeModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; padding: 24px; width: 90%; max-width: 500px; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);">
        <h3 style="font-size: 20px; font-weight: 600; color: #111827; margin: 0 0 20px 0;">Mitarbeiter zum Projekt hinzufügen</h3>
        
        <form id="addEmployeeForm" method="POST" action="">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="project_id" id="modalProjectId" value="">
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px;">Mitarbeiter auswählen</label>
                <select name="employee_id" required style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; color: #111827; background: white;">
                    <option value="">-- Mitarbeiter wählen --</option>
                    <?php $__currentLoopData = $availableEmployees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($employee->id); ?>"><?php echo e($employee->first_name); ?> <?php echo e($employee->last_name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" onclick="closeAddEmployeeModal()" style="padding: 10px 20px; background: white; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; font-weight: 500; color: #374151; cursor: pointer; transition: all 0.2s ease;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='white'">
                    Abbrechen
                </button>
                <button type="submit" style="padding: 10px 20px; background: #3b82f6; border: none; border-radius: 8px; font-size: 14px; font-weight: 500; color: white; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2);" onmouseover="this.style.background='#2563eb'" onmouseout="this.style.background='#3b82f6'">
                    Hinzufügen
                </button>
            </div>
        </form>
    </div>
</div>


<div id="addTaskModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; padding: 24px; width: 90%; max-width: 550px; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);">
        <h3 style="font-size: 20px; font-weight: 600; color: #111827; margin: 0 0 20px 0;">Aufgabe hinzufügen</h3>
        
        <form id="addTaskForm" method="POST" action="">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="project_id" id="taskModalProjectId" value="">
            <input type="hidden" name="employee_id" id="taskModalEmployeeId" value="">
            <input type="hidden" name="end_date" id="taskEndDate" value="">
            
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px;">Aufgabenname *</label>
                <input type="text" name="task_name" required style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; color: #111827;" placeholder="z.B. Frontend-Entwicklung">
            </div>
            
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 12px;">Zeitraum festlegen</label>
                <div style="display: flex; gap: 20px; margin-bottom: 16px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="radio" name="duration_mode" value="fixed" checked onchange="updateDurationMode()" style="cursor: pointer;">
                        <span style="font-size: 14px; color: #374151;">Fester Zeitraum</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="radio" name="duration_mode" value="duration" onchange="updateDurationMode()" style="cursor: pointer;">
                        <span style="font-size: 14px; color: #374151;">Dauer ab Start</span>
                    </label>
                </div>
                
                
                <div id="fixedDurationMode">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label style="display: block; font-size: 13px; color: #6b7280; margin-bottom: 6px;">Von</label>
                            <input type="date" name="start_date" id="taskStartDate" required style="width: 100%; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 13px; color: #6b7280; margin-bottom: 6px;">Bis</label>
                            <input type="date" name="end_date_fixed" style="width: 100%; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                        </div>
                    </div>
                </div>
                
                
                <div id="flexibleDurationMode" style="display: none;">
                    <div style="margin-bottom: 12px;">
                        <label style="display: block; font-size: 13px; color: #6b7280; margin-bottom: 6px;">Startet am</label>
                        <input type="date" id="taskStartDateFlexible" style="width: 100%; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;" onchange="calculateEndDate()">
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                        <div>
                            <label style="display: block; font-size: 13px; color: #6b7280; margin-bottom: 6px;">Dauer</label>
                            <input type="number" id="taskDuration" min="1" value="5" style="width: 100%; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;" oninput="calculateEndDate()">
                        </div>
                        <div>
                            <label style="display: block; font-size: 13px; color: #6b7280; margin-bottom: 6px;">Einheit</label>
                            <select id="taskDurationUnit" style="width: 100%; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; background: white;" onchange="calculateEndDate()">
                                <option value="days">Tage</option>
                                <option value="weeks">Wochen</option>
                            </select>
                        </div>
                    </div>
                    <div style="padding: 12px; background: #f3f4f6; border-radius: 8px; font-size: 13px; color: #374151;">
                        <strong>Endet am:</strong> <span id="calculatedEndDate">-</span>
                    </div>
                </div>
            </div>
            
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px;">Wochenstunden</label>
                <input type="number" name="weekly_hours" min="1" max="40" value="20" style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; color: #111827;">
            </div>
            
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px;">Beschreibung (optional)</label>
                <textarea name="task_description" rows="3" style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; color: #111827; resize: vertical;" placeholder="Details zur Aufgabe..."></textarea>
            </div>
            
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" onclick="closeAddTaskModal()" style="padding: 10px 20px; background: white; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; font-weight: 500; color: #374151; cursor: pointer; transition: all 0.2s ease;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='white'">
                    Abbrechen
                </button>
                <button type="submit" style="padding: 10px 20px; background: #10b981; border: none; border-radius: 8px; font-size: 14px; font-weight: 500; color: white; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);" onmouseover="this.style.background='#059669'" onmouseout="this.style.background='#10b981'">
                    Aufgabe erstellen
                </button>
            </div>
        </form>
    </div>
</div>


<div id="employeeUtilizationModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; padding: 20px;">
    <div style="max-width: 900px; margin: 40px auto; background: white; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); max-height: 85vh; overflow-y: auto;">
        
        <div style="padding: 24px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; background: white; z-index: 1; border-radius: 16px 16px 0 0;">
            <div>
                <h3 style="margin: 0; font-size: 20px; font-weight: 600; color: #111827;">Mitarbeiter-Auslastung</h3>
                <p id="utilizationEmployeeName" style="margin: 4px 0 0 0; font-size: 14px; color: #6b7280;"></p>
            </div>
            <button type="button" onclick="closeEmployeeUtilizationModal()" style="background: none; border: none; font-size: 24px; color: #6b7280; cursor: pointer; padding: 0; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 8px; transition: all 0.2s;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='none'">
                ×
            </button>
        </div>
        
        
        <div id="utilizationContent">
            
        </div>
    </div>
</div>


<div id="manageTasksModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; padding: 20px;">
    <div style="max-width: 800px; margin: 40px auto; background: white; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); max-height: 80vh; overflow-y: auto;">
        
        <div style="padding: 24px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h3 style="margin: 0; font-size: 20px; font-weight: 600; color: #111827;">Aufgaben verwalten</h3>
                <p id="manageTasksEmployeeName" style="margin: 4px 0 0 0; font-size: 14px; color: #6b7280;"></p>
            </div>
            <button type="button" onclick="closeManageTasksModal()" style="background: none; border: none; font-size: 24px; color: #6b7280; cursor: pointer; padding: 0; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 8px; transition: all 0.2s;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='none'">
                ×
            </button>
        </div>
        
        
        <div id="tasksListContainer" style="padding: 24px;">
            
        </div>
    </div>
</div>

<script>
// Toggle Project Menu
function toggleProjectMenu(projectId) {
    const menu = document.getElementById('projectMenu' + projectId);
    const allMenus = document.querySelectorAll('[id^="projectMenu"], [id^="employeeMenu"]');
    
    // Close all other menus
    allMenus.forEach(m => {
        if (m.id !== 'projectMenu' + projectId) {
            m.style.display = 'none';
        }
    });
    
    menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
}

// Toggle Employee Menu
function toggleEmployeeMenu(projectId, employeeId) {
    const menu = document.getElementById('employeeMenu' + projectId + '_' + employeeId);
    const allMenus = document.querySelectorAll('[id^="projectMenu"], [id^="employeeMenu"]');
    
    // Close all other menus
    allMenus.forEach(m => {
        if (m.id !== 'employeeMenu' + projectId + '_' + employeeId) {
            m.style.display = 'none';
        }
    });
    
    menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
}

// Close menus when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('[onclick^="toggleProjectMenu"]') && !e.target.closest('[onclick^="toggleEmployeeMenu"]') && !e.target.closest('[id^="projectMenu"]') && !e.target.closest('[id^="employeeMenu"]')) {
        document.querySelectorAll('[id^="projectMenu"], [id^="employeeMenu"]').forEach(menu => {
            menu.style.display = 'none';
        });
    }
});

// Add Employee Modal
function openAddEmployeeModal(projectId) {
    document.getElementById('modalProjectId').value = projectId;
    document.getElementById('addEmployeeForm').action = '/gantt/projects/' + projectId + '/employees';
    document.getElementById('addEmployeeModal').style.display = 'flex';
    // Close the dropdown
    document.getElementById('projectMenu' + projectId).style.display = 'none';
}

function closeAddEmployeeModal() {
    document.getElementById('addEmployeeModal').style.display = 'none';
}

// Add Task Modal
function openAddTaskModal(projectId, employeeId) {
    document.getElementById('taskModalProjectId').value = projectId;
    document.getElementById('taskModalEmployeeId').value = employeeId;
    document.getElementById('addTaskForm').action = '/gantt/projects/' + projectId + '/employees/' + employeeId + '/tasks';
    document.getElementById('addTaskModal').style.display = 'flex';
    // Close the dropdown
    document.getElementById('employeeMenu' + projectId + '_' + employeeId).style.display = 'none';
    // Set default start date to today
    document.getElementById('taskStartDate').value = new Date().toISOString().split('T')[0];
    updateDurationMode();
}

function closeAddTaskModal() {
    document.getElementById('addTaskModal').style.display = 'none';
    document.getElementById('addTaskForm').reset();
}

// Duration Mode Toggle
function updateDurationMode() {
    const mode = document.querySelector('input[name="duration_mode"]:checked').value;
    const fixedMode = document.getElementById('fixedDurationMode');
    const flexibleMode = document.getElementById('flexibleDurationMode');
    
    if (mode === 'fixed') {
        fixedMode.style.display = 'block';
        flexibleMode.style.display = 'none';
    } else {
        fixedMode.style.display = 'none';
        flexibleMode.style.display = 'block';
        calculateEndDate();
    }
}

// Calculate End Date based on duration
function calculateEndDate() {
    const startDate = document.getElementById('taskStartDate').value;
    const duration = parseInt(document.getElementById('taskDuration').value) || 0;
    const unit = document.getElementById('taskDurationUnit').value;
    
    if (!startDate || duration === 0) {
        document.getElementById('calculatedEndDate').textContent = '-';
        return;
    }
    
    const start = new Date(startDate);
    let end = new Date(start);
    
    if (unit === 'days') {
        end.setDate(end.getDate() + duration);
    } else if (unit === 'weeks') {
        end.setDate(end.getDate() + (duration * 7));
    }
    
    document.getElementById('calculatedEndDate').textContent = end.toLocaleDateString('de-DE');
    document.getElementById('taskEndDate').value = end.toISOString().split('T')[0];
}

// Close modals when clicking outside
document.getElementById('addEmployeeModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeAddEmployeeModal();
    }
});

document.getElementById('addTaskModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeAddTaskModal();
    }
});

document.getElementById('manageTasksModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeManageTasksModal();
    }
});

document.getElementById('employeeUtilizationModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeEmployeeUtilizationModal();
    }
});

// Keyboard support - ESC closes modals
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' || e.keyCode === 27) {
        // Close all modals
        closeAddEmployeeModal();
        closeAddTaskModal();
        closeManageTasksModal();
        closeEmployeeUtilizationModal();
        
        // Close all dropdown menus
        const allMenus = document.querySelectorAll('[id^="projectMenu"], [id^="employeeMenu"]');
        allMenus.forEach(menu => menu.style.display = 'none');
    }
});

// Task Tooltip System
let taskTooltip = null;

function createTaskTooltip() {
    if (!taskTooltip) {
        taskTooltip = document.createElement('div');
        taskTooltip.id = 'task-tooltip';
        taskTooltip.style.cssText = `
            display: none;
            position: fixed;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 16px;
            z-index: 10000;
            max-width: 320px;
            pointer-events: none;
        `;
        document.body.appendChild(taskTooltip);
    }
    return taskTooltip;
}

function showTaskTooltip(event) {
    const bar = event.currentTarget;
    const tooltip = createTaskTooltip();
    
    const taskName = bar.dataset.taskName || 'Unbekannte Aufgabe';
    const description = bar.dataset.taskDescription || 'Keine Beschreibung';
    const startDate = bar.dataset.startDate || '-';
    const endDate = bar.dataset.endDate || '-';
    const weeklyHours = bar.dataset.weeklyHours || '20';
    const durationDays = bar.dataset.durationDays || '0';
    
    tooltip.innerHTML = `
        <div style="margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #e5e7eb;">
            <h4 style="margin: 0 0 4px 0; font-size: 16px; font-weight: 600; color: #111827;">${taskName}</h4>
            <p style="margin: 0; font-size: 13px; color: #6b7280; line-height: 1.5;">${description}</p>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
            <div>
                <div style="font-size: 11px; color: #6b7280; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Startdatum</div>
                <div style="font-size: 14px; color: #111827; font-weight: 500;">${startDate}</div>
            </div>
            <div>
                <div style="font-size: 11px; color: #6b7280; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Enddatum</div>
                <div style="font-size: 14px; color: #111827; font-weight: 500;">${endDate}</div>
            </div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <div>
                <div style="font-size: 11px; color: #6b7280; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Dauer</div>
                <div style="font-size: 14px; color: #111827; font-weight: 500;">${durationDays} Tage</div>
            </div>
            <div>
                <div style="font-size: 11px; color: #6b7280; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Wochenstunden</div>
                <div style="font-size: 14px; color: #111827; font-weight: 500;">${weeklyHours}h / Woche</div>
            </div>
        </div>
        <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #e5e7eb; font-size: 11px; color: #6b7280; text-align: center;">
            ⚡ Klicken Sie auf ⋮ beim Mitarbeiter für mehr Optionen
        </div>
    `;
    
    tooltip.style.display = 'block';
    positionTooltip(event, tooltip);
}

function hideTaskTooltip() {
    if (taskTooltip) {
        taskTooltip.style.display = 'none';
    }
}

function positionTooltip(event, tooltip) {
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

// Attach event listeners to all task bars
document.addEventListener('DOMContentLoaded', function() {
    attachTaskTooltipListeners();
});

function attachTaskTooltipListeners() {
    const taskBars = document.querySelectorAll('.project-task-bar');
    taskBars.forEach(bar => {
        bar.addEventListener('mouseenter', showTaskTooltip);
        bar.addEventListener('mouseleave', hideTaskTooltip);
        bar.addEventListener('mousemove', function(e) {
            if (taskTooltip && taskTooltip.style.display === 'block') {
                positionTooltip(e, taskTooltip);
            }
        });
        
        // Hover effect
        bar.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 4px 12px rgba(0,0,0,0.25)';
        });
        bar.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 2px 4px rgba(0,0,0,0.15)';
        });
    });
    
    // Attach employee name tooltips
    attachEmployeeTooltipListeners();
}

// Employee Tooltip System
let employeeTooltip = null;

function createEmployeeTooltip() {
    if (!employeeTooltip) {
        employeeTooltip = document.createElement('div');
        employeeTooltip.id = 'employee-tooltip';
        employeeTooltip.style.cssText = `
            display: none;
            position: fixed;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 16px;
            z-index: 10000;
            max-width: 300px;
            pointer-events: none;
        `;
        document.body.appendChild(employeeTooltip);
    }
    return employeeTooltip;
}

function showEmployeeTooltip(event) {
    const nameElement = event.currentTarget;
    const tooltip = createEmployeeTooltip();
    
    const employeeName = nameElement.dataset.employeeName || 'Unbekannt';
    const taskCount = nameElement.dataset.taskCount || '0';
    const totalHours = nameElement.dataset.totalHours || '0';
    
    tooltip.innerHTML = `
        <div style="margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #e5e7eb;">
            <h4 style="margin: 0 0 4px 0; font-size: 16px; font-weight: 600; color: #111827;">👤 ${employeeName}</h4>
            <p style="margin: 0; font-size: 12px; color: #6b7280;">Mitarbeiter in diesem Projekt</p>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <div style="background: #f3f4f6; padding: 12px; border-radius: 8px;">
                <div style="font-size: 11px; color: #6b7280; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Aufgaben</div>
                <div style="font-size: 20px; color: #111827; font-weight: 700;">${taskCount}</div>
            </div>
            <div style="background: #f3f4f6; padding: 12px; border-radius: 8px;">
                <div style="font-size: 11px; color: #6b7280; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Stunden/Woche</div>
                <div style="font-size: 20px; color: #111827; font-weight: 700;">${totalHours}h</div>
            </div>
        </div>
        <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #e5e7eb; font-size: 11px; color: #6b7280; text-align: center;">
            ⚡ Klicken Sie auf ⋮ für Optionen
        </div>
    `;
    
    tooltip.style.display = 'block';
    positionEmployeeTooltip(event, tooltip);
}

function hideEmployeeTooltip() {
    if (employeeTooltip) {
        employeeTooltip.style.display = 'none';
    }
}

function positionEmployeeTooltip(event, tooltip) {
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

function attachEmployeeTooltipListeners() {
    const employeeNames = document.querySelectorAll('.employee-name');
    employeeNames.forEach(nameElement => {
        nameElement.addEventListener('mouseenter', showEmployeeTooltip);
        nameElement.addEventListener('mouseleave', hideEmployeeTooltip);
        nameElement.addEventListener('mousemove', function(e) {
            if (employeeTooltip && employeeTooltip.style.display === 'block') {
                positionEmployeeTooltip(e, employeeTooltip);
            }
        });
    });
}

// Remove Employees Modal Functions
function openRemoveEmployeesModal(projectId) {
    // Close project menu
    const projectMenu = document.getElementById('projectMenu' + projectId);
    if (projectMenu) projectMenu.style.display = 'none';
    
    // Show modal with employee list (to be implemented)
    alert('Mitarbeiter entfernen - Funktion wird implementiert.\nHier können Sie Mitarbeiter aus dem Projekt entfernen.');
}

// Manage Tasks Modal Functions
function openManageTasksModal(projectId, employeeId, employeeName) {
    // Close employee menu
    const employeeMenu = document.getElementById('employeeMenu' + projectId + '_' + employeeId);
    if (employeeMenu) employeeMenu.style.display = 'none';
    
    // Set employee name
    document.getElementById('manageTasksEmployeeName').textContent = employeeName;
    
    // Load tasks via AJAX
    fetch(`/gantt/projects/${projectId}/employees/${employeeId}/tasks`)
        .then(response => response.json())
        .then(data => {
            renderTasksList(data.tasks, projectId, employeeId);
            document.getElementById('manageTasksModal').style.display = 'block';
        })
        .catch(error => {
            console.error('Error loading tasks:', error);
            alert('Fehler beim Laden der Aufgaben.');
        });
}

function closeManageTasksModal() {
    document.getElementById('manageTasksModal').style.display = 'none';
}

function renderTasksList(tasks, projectId, employeeId) {
    const container = document.getElementById('tasksListContainer');
    
    if (tasks.length === 0) {
        container.innerHTML = `
            <div style="text-align: center; padding: 40px; color: #6b7280;">
                <div style="font-size: 48px; margin-bottom: 16px;">📋</div>
                <p style="font-size: 16px; margin: 0;">Keine Aufgaben vorhanden</p>
            </div>
        `;
        return;
    }
    
    let html = '<div style="display: flex; flex-direction: column; gap: 12px;">';
    
    tasks.forEach((task, index) => {
        const startDate = new Date(task.start_date).toLocaleDateString('de-DE');
        const endDate = new Date(task.end_date).toLocaleDateString('de-DE');
        const duration = Math.ceil((new Date(task.end_date) - new Date(task.start_date)) / (1000 * 60 * 60 * 24));
        
        html += `
            <div id="task-${task.id}" style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px; transition: all 0.2s;">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
                    <div style="flex: 1;">
                        <h4 style="margin: 0 0 4px 0; font-size: 16px; font-weight: 600; color: #111827;">${task.task_name}</h4>
                        <p style="margin: 0; font-size: 13px; color: #6b7280;">${task.task_description || 'Keine Beschreibung'}</p>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <button onclick="editTask(${task.id}, ${projectId}, ${employeeId})" style="padding: 6px 12px; background: #3b82f6; border: none; border-radius: 6px; color: white; font-size: 13px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#2563eb'" onmouseout="this.style.background='#3b82f6'">
                            ✏️ Bearbeiten
                        </button>
                        <button onclick="deleteTask(${task.id}, ${projectId}, ${employeeId}, '${task.task_name}')" style="padding: 6px 12px; background: #ef4444; border: none; border-radius: 6px; color: white; font-size: 13px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='#ef4444'">
                            🗑️ Löschen
                        </button>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; padding: 12px; background: white; border-radius: 8px;">
                    <div>
                        <div style="font-size: 11px; color: #6b7280; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Zeitraum</div>
                        <div style="font-size: 14px; color: #111827; font-weight: 500;">${startDate} - ${endDate}</div>
                        <div style="font-size: 12px; color: #6b7280; margin-top: 2px;">${duration} Tage</div>
                    </div>
                    <div>
                        <div style="font-size: 11px; color: #6b7280; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Wochenstunden</div>
                        <div style="font-size: 14px; color: #111827; font-weight: 500;">${task.weekly_hours || 20}h / Woche</div>
                    </div>
                    <div>
                        <div style="font-size: 11px; color: #6b7280; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Reihenfolge</div>
                        <div style="font-size: 14px; color: #111827; font-weight: 500;">#${task.display_order || index + 1}</div>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

function editTask(taskId, projectId, employeeId) {
    const taskElement = document.getElementById('task-' + taskId);
    if (!taskElement) return;
    
    // Get current task data
    fetch(`/gantt/tasks/${taskId}`)
        .then(response => response.json())
        .then(data => {
            const task = data.task;
            
            // Replace task display with inline edit form
            taskElement.innerHTML = `
                <form onsubmit="saveTaskEdit(event, ${taskId}, ${projectId}, ${employeeId})" style="background: white; border: 2px solid #3b82f6; border-radius: 12px; padding: 16px;">
                    <div style="margin-bottom: 12px;">
                        <label style="display: block; font-size: 13px; color: #6b7280; margin-bottom: 4px; font-weight: 500;">Aufgabenname</label>
                        <input type="text" name="task_name" value="${task.task_name}" required style="width: 100%; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                    </div>
                    <div style="margin-bottom: 12px;">
                        <label style="display: block; font-size: 13px; color: #6b7280; margin-bottom: 4px; font-weight: 500;">Beschreibung</label>
                        <textarea name="task_description" rows="2" style="width: 100%; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">${task.task_description || ''}</textarea>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                        <div>
                            <label style="display: block; font-size: 13px; color: #6b7280; margin-bottom: 4px; font-weight: 500;">Startdatum</label>
                            <input type="date" name="start_date" value="${task.start_date}" required style="width: 100%; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 13px; color: #6b7280; margin-bottom: 4px; font-weight: 500;">Enddatum</label>
                            <input type="date" name="end_date" value="${task.end_date}" required style="width: 100%; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                        </div>
                    </div>
                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 13px; color: #6b7280; margin-bottom: 4px; font-weight: 500;">Wochenstunden</label>
                        <input type="number" name="weekly_hours" value="${task.weekly_hours || 20}" min="1" max="40" style="width: 100%; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                    </div>
                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
                        <button type="button" onclick="cancelTaskEdit(${taskId}, ${projectId}, ${employeeId})" style="padding: 8px 16px; background: #f3f4f6; border: none; border-radius: 6px; color: #374151; font-size: 13px; cursor: pointer; font-weight: 500;">
                            Abbrechen
                        </button>
                        <button type="submit" style="padding: 8px 16px; background: #10b981; border: none; border-radius: 6px; color: white; font-size: 13px; cursor: pointer; font-weight: 500;">
                            💾 Speichern
                        </button>
                    </div>
                </form>
            `;
        })
        .catch(error => {
            console.error('Error loading task:', error);
            alert('Fehler beim Laden der Aufgabe.');
        });
}

function cancelTaskEdit(taskId, projectId, employeeId) {
    // Reload the tasks list to restore original view
    openManageTasksModal(projectId, employeeId, document.getElementById('manageTasksEmployeeName').textContent);
}

function saveTaskEdit(event, taskId, projectId, employeeId) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    const data = {
        task_name: formData.get('task_name'),
        task_description: formData.get('task_description'),
        start_date: formData.get('start_date'),
        end_date: formData.get('end_date'),
        weekly_hours: formData.get('weekly_hours'),
    };
    
    fetch(`/gantt/tasks/${taskId}`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload tasks list to show updated task
            openManageTasksModal(projectId, employeeId, document.getElementById('manageTasksEmployeeName').textContent);
            
            // Also reload the gantt page to reflect changes
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            alert('Fehler beim Speichern: ' + (data.message || 'Unbekannter Fehler'));
        }
    })
    .catch(error => {
        console.error('Error saving task:', error);
        alert('Fehler beim Speichern der Aufgabe.');
    });
}

function deleteTask(taskId, projectId, employeeId, taskName) {
    if (!confirm(`Möchten Sie die Aufgabe "${taskName}" wirklich löschen?`)) {
        return;
    }
    
    fetch(`/gantt/tasks/${taskId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove task from UI with animation
            const taskElement = document.getElementById('task-' + taskId);
            if (taskElement) {
                taskElement.style.opacity = '0';
                taskElement.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    taskElement.remove();
                    // Close modal and reload page to update gantt timeline
                    closeManageTasksModal();
                    window.location.reload();
                }, 300);
            }
        } else {
            alert('Fehler beim Löschen: ' + (data.message || 'Unbekannter Fehler'));
        }
    })
    .catch(error => {
        console.error('Error deleting task:', error);
        alert('Fehler beim Löschen der Aufgabe.');
    });
}

// Employee Utilization Modal Functions
function openEmployeeUtilizationModal(employeeId, employeeName) {
    // Show loading state
    document.getElementById('utilizationEmployeeName').textContent = employeeName;
    document.getElementById('utilizationContent').innerHTML = '<div style="text-align: center; padding: 40px;"><div style="font-size: 48px; margin-bottom: 16px;">⏳</div><p style="color: #6b7280;">Lade Auslastungsdaten...</p></div>';
    document.getElementById('employeeUtilizationModal').style.display = 'block';
    
    // Load utilization data
    fetch(`/gantt/employees/${employeeId}/utilization`)
        .then(response => response.json())
        .then(data => {
            renderUtilizationView(data, employeeName);
        })
        .catch(error => {
            console.error('Error loading utilization:', error);
            document.getElementById('utilizationContent').innerHTML = '<div style="text-align: center; padding: 40px; color: #ef4444;"><div style="font-size: 48px; margin-bottom: 16px;">⚠️</div><p>Fehler beim Laden der Auslastungsdaten.</p></div>';
        });
}

function closeEmployeeUtilizationModal() {
    document.getElementById('employeeUtilizationModal').style.display = 'none';
}

function renderUtilizationView(data, employeeName) {
    const container = document.getElementById('utilizationContent');
    const tasks = data.tasks || [];
    const peakHours = data.peak_weekly_hours || 0;
    const averageHours = data.average_weekly_hours || 0;
    const totalHours = data.total_weekly_hours || 0;
    const projectCount = data.project_count || 0;
    const hasOverlaps = data.has_overlaps || false;
    const overlapWeeks = data.overlap_weeks || 0;
    const maxCapacity = 40;
    
    // Use PEAK hours for status calculation (most important metric)
    const peakPercent = Math.round((peakHours / maxCapacity) * 100);
    const avgPercent = Math.round((averageHours / maxCapacity) * 100);
    
    let statusColor = '#10b981'; // Green
    let statusText = 'Gut verfügbar';
    let statusIcon = '✅';
    if (peakPercent >= 100) {
        statusColor = '#ef4444'; // Red
        statusText = 'Überlastet!';
        statusIcon = '🔴';
    } else if (peakPercent >= 80) {
        statusColor = '#f59e0b'; // Orange
        statusText = 'Hoch ausgelastet';
        statusIcon = '⚠️';
    } else if (peakPercent >= 60) {
        statusColor = '#3b82f6'; // Blue
        statusText = 'Normal ausgelastet';
        statusIcon = '✓';
    }
    
    let html = `
        <div style="padding: 24px;">
            <div style="margin-bottom: 24px;">
                <h4 style="margin: 0 0 16px 0; font-size: 18px; font-weight: 600; color: #111827;">Intelligente Auslastungs-Analyse</h4>
                
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 20px;">
                    <div style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); padding: 20px; border-radius: 12px; color: white;">
                        <div style="font-size: 12px; opacity: 0.9; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">🔝 Peak-Auslastung</div>
                        <div style="font-size: 32px; font-weight: 700; margin-bottom: 4px;">${peakHours}h</div>
                        <div style="font-size: 13px; opacity: 0.9;">Höchste Woche (${peakPercent}%)</div>
                    </div>
                    <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 20px; border-radius: 12px; color: white;">
                        <div style="font-size: 12px; opacity: 0.9; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">📊 Durchschnitt</div>
                        <div style="font-size: 32px; font-weight: 700; margin-bottom: 4px;">${averageHours}h</div>
                        <div style="font-size: 13px; opacity: 0.9;">Ø pro aktive Woche (${avgPercent}%)</div>
                    </div>
                    <div style="background: linear-gradient(135deg, ${statusColor} 0%, ${statusColor}dd 100%); padding: 20px; border-radius: 12px; color: white;">
                        <div style="font-size: 12px; opacity: 0.9; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Status</div>
                        <div style="font-size: 32px; font-weight: 700; margin-bottom: 4px;">${statusIcon}</div>
                        <div style="font-size: 13px; opacity: 0.9;">${statusText}</div>
                    </div>
                </div>
                
                ${hasOverlaps ? `
                <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; padding: 16px; margin-bottom: 20px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="font-size: 24px;">⚠️</div>
                        <div style="flex: 1;">
                            <div style="font-size: 14px; font-weight: 600; color: #dc2626; margin-bottom: 4px;">Überlappungen erkannt!</div>
                            <div style="font-size: 13px; color: #991b1b;">${overlapWeeks} Woche(n) mit über 40h/Woche - Bitte Aufgaben neu verteilen!</div>
                        </div>
                    </div>
                </div>
                ` : `
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 16px; margin-bottom: 20px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="font-size: 24px;">✅</div>
                        <div style="flex: 1;">
                            <div style="font-size: 14px; font-weight: 600; color: #16a34a; margin-bottom: 4px;">Keine kritischen Überlappungen</div>
                            <div style="font-size: 13px; color: #15803d;">Alle Aufgaben zeitlich gut verteilt</div>
                        </div>
                    </div>
                </div>
                `}
                
                <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px; margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="font-size: 13px; color: #6b7280; font-weight: 500;">Peak-Kapazität (wichtigste Metrik)</span>
                        <span style="font-size: 14px; color: #111827; font-weight: 600;">${peakHours}h / ${maxCapacity}h</span>
                    </div>
                    <div style="width: 100%; height: 12px; background: #e5e7eb; border-radius: 6px; overflow: hidden;">
                        <div style="height: 100%; background: ${statusColor}; width: ${Math.min(peakPercent, 100)}%; transition: width 0.3s;"></div>
                    </div>
                </div>
                
                <div style="background: #f3f4f6; border-radius: 12px; padding: 16px; margin-bottom: 20px;">
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; text-align: center;">
                        <div>
                            <div style="font-size: 11px; color: #6b7280; margin-bottom: 4px;">Projekte</div>
                            <div style="font-size: 18px; font-weight: 700; color: #111827;">${projectCount}</div>
                        </div>
                        <div>
                            <div style="font-size: 11px; color: #6b7280; margin-bottom: 4px;">Aufgaben</div>
                            <div style="font-size: 18px; font-weight: 700; color: #111827;">${tasks.length}</div>
                        </div>
                        <div>
                            <div style="font-size: 11px; color: #6b7280; margin-bottom: 4px;">Gesamt h/Wo.</div>
                            <div style="font-size: 18px; font-weight: 700; color: #111827;">${totalHours}h</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <h5 style="margin: 0 0 16px 0; font-size: 16px; font-weight: 600; color: #111827;">Aufgaben über alle Projekte</h5>
    `;
    
    if (tasks.length === 0) {
        html += `
            <div style="text-align: center; padding: 40px; color: #6b7280;">
                <div style="font-size: 48px; margin-bottom: 16px;">📋</div>
                <p>Keine Aufgaben gefunden</p>
            </div>
        `;
    } else {
        html += '<div style="display: flex; flex-direction: column; gap: 12px;">';
        
        tasks.forEach(task => {
            const startDate = new Date(task.start_date).toLocaleDateString('de-DE');
            const endDate = new Date(task.end_date).toLocaleDateString('de-DE');
            const duration = Math.ceil((new Date(task.end_date) - new Date(task.start_date)) / (1000 * 60 * 60 * 24));
            
            html += `
                <div style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
                        <div style="flex: 1;">
                            <div style="font-size: 14px; font-weight: 600; color: #111827; margin-bottom: 2px;">${task.task_name}</div>
                            <div style="font-size: 13px; color: #6b7280; margin-bottom: 6px;">${task.project_name}</div>
                            <div style="font-size: 12px; color: #9ca3af;">${task.task_description || 'Keine Beschreibung'}</div>
                        </div>
                        <div style="background: #0ea5e9; color: white; padding: 4px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; white-space: nowrap;">
                            ${task.weekly_hours}h/Woche
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; padding: 12px; background: #f9fafb; border-radius: 8px;">
                        <div>
                            <div style="font-size: 11px; color: #6b7280; margin-bottom: 4px;">Zeitraum</div>
                            <div style="font-size: 13px; color: #111827; font-weight: 500;">${startDate} - ${endDate}</div>
                        </div>
                        <div>
                            <div style="font-size: 11px; color: #6b7280; margin-bottom: 4px;">Dauer</div>
                            <div style="font-size: 13px; color: #111827; font-weight: 500;">${duration} Tage</div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
    }
    
    html += '</div>';
    container.innerHTML = html;
}

// Remove Employee from Project
function removeEmployeeFromProject(projectId, employeeId, employeeName) {
    // Close employee menu
    const employeeMenu = document.getElementById('employeeMenu' + projectId + '_' + employeeId);
    if (employeeMenu) employeeMenu.style.display = 'none';
    
    // Submit deletion via form
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/gantt/projects/' + projectId + '/employees/' + employeeId + '/remove';
    
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '<?php echo e(csrf_token()); ?>';
    
    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'DELETE';
    
    form.appendChild(csrfInput);
    form.appendChild(methodInput);
    document.body.appendChild(form);
    form.submit();
}
</script>
<?php /**PATH C:\xampp\htdocs\mein-projekt\resources\views/gantt/partials/timeline-projects.blade.php ENDPATH**/ ?>