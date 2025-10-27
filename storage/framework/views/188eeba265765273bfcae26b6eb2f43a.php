<?php $__env->startSection('title', $employee->first_name . ' ' . $employee->last_name); ?>

<?php $__env->startSection('content'); ?>
<div style="width: 100%; margin: 0; padding: 0;">
    <!-- Page Header -->
    <div style="background: white; padding: 20px; margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                <h1 style="font-size: 24px; font-weight: bold; color: #111827; margin: 0;">
                    <?php echo e($employee->first_name); ?> <?php echo e($employee->last_name); ?>

                </h1>
                <p style="color: #6b7280; margin: 5px 0 0 0;">Mitarbeiter-Details und MOCO-Integration</p>
                <?php if($mocoData): ?>
                <div style="display: flex; gap: 20px; margin-top: 10px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="color: #6b7280; font-size: 14px;">MOCO ID:</span>
                        <span style="font-weight: 600; color: #111827;"><?php echo e($employee->moco_id); ?></span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="color: #6b7280; font-size: 14px;">Aktiv:</span>
                        <span style="font-weight: 600; color: <?php echo e($mocoData['active'] ? '#059669' : '#dc2626'); ?>;"><?php echo e($mocoData['active'] ? 'Ja' : 'Nein'); ?></span>
                    </div>
                    <?php if($mocoData['unit']): ?>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="color: #6b7280; font-size: 14px;">Abteilung:</span>
                        <span style="font-weight: 600; color: #111827;"><?php echo e($mocoData['unit']['name']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="<?php echo e(route('employees.edit', $employee)); ?>" style="background: #ffffff; color: #374151; padding: 10px 20px; border-radius: 12px; text-decoration: none; font-size: 14px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 8px;">
                        Bearbeiten
                    </a>
                <a href="<?php echo e(route('employees.index')); ?>" style="background: #ffffff; color: #374151; padding: 10px 20px; border-radius: 12px; text-decoration: none; font-size: 14px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 8px;">
                    ← Zurück zur Übersicht
                    </a>
                </div>
        </div>
    </div>

    <!-- Pie Chart -->
    <?php if(isset($projectDistribution) && count($projectDistribution) > 0): ?>
    <div style="background: white; padding: 20px; margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0;">Projektverteilung</h2>
            <div style="display: flex; gap: 12px; align-items: center;">
                <label for="timeRangeFilter" style="color: #6b7280; font-size: 14px; font-weight: 500;">Zeitraum:</label>
                <select 
                    id="timeRangeFilter" 
                    onchange="updatePieChart()"
                    style="padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; color: #374151; background: white; cursor: pointer; min-width: 150px;"
                >
                    <option value="7">Letzte Woche</option>
                    <option value="30" selected>Letzter Monat</option>
                    <option value="90">Letzte 3 Monate</option>
                    <option value="180">Letzte 6 Monate</option>
                    <option value="custom">Beliebiger Zeitraum</option>
                </select>
            </div>
        </div>
        
        <!-- Custom Date Range (hidden by default) -->
        <div id="customDateRange" style="display: none; margin-bottom: 20px; padding: 16px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px;">
            <div style="display: flex; gap: 16px; align-items: center;">
                <div style="flex: 1;">
                    <label style="display: block; color: #6b7280; font-size: 13px; margin-bottom: 6px;">Von:</label>
                    <input type="date" id="customStartDate" style="width: 100%; padding: 8px; border: 1px solid #e5e7eb; border-radius: 6px;">
                </div>
                <div style="flex: 1;">
                    <label style="display: block; color: #6b7280; font-size: 13px; margin-bottom: 6px;">Bis:</label>
                    <input type="date" id="customEndDate" style="width: 100%; padding: 8px; border: 1px solid #e5e7eb; border-radius: 6px;">
                </div>
                <button 
                    onclick="applyCustomDateRange()" 
                    style="margin-top: 20px; padding: 8px 16px; background: #3b82f6; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;"
                    onmouseover="this.style.background='#2563eb'"
                    onmouseout="this.style.background='#3b82f6'"
                >
                    Anwenden
                </button>
            </div>
        </div>
        
        <div id="pieChartContainer">
        
        <div style="display: flex; gap: 40px; align-items: flex-start;">
            <!-- Pie Chart -->
            <div style="flex-shrink: 0;">
                <div style="position: relative; width: 200px; height: 200px;">
                    <svg width="200" height="200" viewBox="0 0 200 200" style="transform: rotate(-90deg);">
                        <?php
                            $currentAngle = 0;
                            $colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316', '#6366f1', '#84cc16'];
                        ?>
                        <?php $__currentLoopData = $projectDistribution; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $percentage = $project['percentage'];
                                $angle = ($percentage / 100) * 360;
                                $radius = 80;
                                $circumference = 2 * pi() * $radius;
                                $dashArray = ($percentage / 100) * $circumference;
                                $dashOffset = -($currentAngle / 360) * $circumference;
                                $currentAngle += $angle;
                                $color = $colors[$index % count($colors)];
                            ?>
                            <circle
                                cx="100"
                                cy="100"
                                r="<?php echo e($radius); ?>"
                                fill="none"
                                stroke="<?php echo e($color); ?>"
                                stroke-width="60"
                                stroke-dasharray="<?php echo e($dashArray); ?> <?php echo e($circumference); ?>"
                                stroke-dashoffset="<?php echo e($dashOffset); ?>"
                                style="transition: all 0.3s ease;"
                            />
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <!-- Weißer Kreis in der Mitte -->
                        <circle cx="100" cy="100" r="40" fill="white" />
                    </svg>
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                        <div style="font-size: 32px; font-weight: 700; color: #111827;"><?php echo e($totalHours); ?></div>
                        <div style="font-size: 12px; color: #6b7280; margin-top: 4px;">Stunden</div>
                    </div>
                </div>
            </div>
            
            <!-- Projekt-Liste -->
            <div style="flex: 1;">
                <div style="display: grid; gap: 12px;">
                    <?php $__currentLoopData = $projectDistribution; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div style="display: flex; align-items: center; gap: 12px; background: #f9fafb; padding: 12px; border-radius: 8px;">
                            <div style="width: 16px; height: 16px; border-radius: 4px; background: <?php echo e($colors[$index % count($colors)]); ?>; flex-shrink: 0;"></div>
                            <div style="flex: 1; min-width: 0;">
                                <div style="font-weight: 600; color: #111827; font-size: 14px; margin-bottom: 2px;"><?php echo e($project['name']); ?></div>
                                <div style="font-size: 12px; color: #6b7280;"><?php echo e($project['hours']); ?>h</div>
                            </div>
                            <div style="flex-shrink: 0; text-align: right;">
                                <div style="font-size: 18px; font-weight: 700; color: #111827;"><?php echo e($project['percentage']); ?>%</div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
        </div><!-- End pieChartContainer -->
    </div>
    <?php endif; ?>

    <!-- Statistiken (nur MOCO-Daten) -->
    <?php if($mocoStats): ?>
    <div style="background: white; padding: 20px; margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
        <h2 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 20px 0;">Statistiken</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; text-align: center;">
                <div style="font-size: 2rem; font-weight: bold; color: #111827; margin-bottom: 4px;"><?php echo e($mocoStats['projects_count']); ?></div>
                <div style="font-size: 14px; color: #6b7280;">Zugewiesene Projekte</div>
            </div>
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; text-align: center;">
                <div style="font-size: 2rem; font-weight: bold; color: #111827; margin-bottom: 4px;"><?php echo e($mocoStats['future_absences']); ?></div>
                <div style="font-size: 14px; color: #6b7280;">Abwesenheiten</div>
            </div>
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; text-align: center;">
                <div style="font-size: 2rem; font-weight: bold; color: #111827; margin-bottom: 4px;"><?php echo e($mocoStats['active_projects']); ?></div>
                <div style="font-size: 14px; color: #6b7280;">Aktive Projekte</div>
            </div>
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; text-align: center;">
                <div style="font-size: 2rem; font-weight: bold; color: #111827; margin-bottom: 4px;"><?php echo e($mocoStats['completed_projects']); ?></div>
                <div style="font-size: 14px; color: #6b7280;">Abgeschlossene Projekte</div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Mitarbeiter-Details -->
    <div style="background: white; padding: 20px; margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
        <h2 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 20px 0;">Mitarbeiter-Details</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
            <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px;">
                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; font-weight: 500;">Name</div>
                <div style="font-size: 16px; font-weight: 600; color: #111827;"><?php echo e($employee->first_name); ?> <?php echo e($employee->last_name); ?></div>
            </div>
            <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px;">
                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; font-weight: 500;">Abteilung</div>
                <div style="font-size: 16px; font-weight: 600; color: #111827;"><?php echo e($employee->department); ?></div>
            </div>
            <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px;">
                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; font-weight: 500;">Wochenkapazität</div>
                <div style="font-size: 16px; font-weight: 600; color: #111827;"><?php echo e($employee->weekly_capacity); ?>h</div>
            </div>
            <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px;">
                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; font-weight: 500;">Status</div>
                <div style="font-size: 16px; font-weight: 600; color: #111827;">
                    <span style="background: <?php echo e($employee->is_active ? '#dcfce7' : '#f3f4f6'); ?>; color: <?php echo e($employee->is_active ? '#166534' : '#374151'); ?>; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                        <?php echo e($employee->is_active ? 'Aktiv' : 'Inaktiv'); ?>

                    </span>
                </div>
            </div>
            <?php if($employee->email): ?>
            <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px;">
                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; font-weight: 500;">E-Mail</div>
                <div style="font-size: 16px; font-weight: 600; color: #111827;"><?php echo e($employee->email); ?></div>
            </div>
            <?php endif; ?>
            <?php if($mocoData && $mocoData['email']): ?>
            <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px;">
                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; font-weight: 500;">MOCO E-Mail</div>
                <div style="font-size: 16px; font-weight: 600; color: #111827;"><?php echo e($mocoData['email']); ?></div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Zugewiesene Projekte -->
    <?php if($combinedProjects && $combinedProjects->count() > 0): ?>
    <div style="background: white; padding: 20px; margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0;">Zugewiesene Projekte (MOCO)</h2>
            <button 
                onclick="toggleProjectsSection()" 
                id="toggleProjectsBtn"
                style="background: #f3f4f6; color: #374151; padding: 8px 16px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer; transition: all 0.2s ease;"
                onmouseover="this.style.background='#e5e7eb'"
                onmouseout="this.style.background='#f3f4f6'"
            >
                <span id="toggleProjectsText">Ausklappen</span>
            </button>
        </div>
        <div id="projectsContent" style="display: none;">
            <div style="background: #f8f9fa; border: 1px solid #e5e7eb; border-radius: 6px; padding: 16px; margin-bottom: 20px;">
                <strong style="color: #374151;">Projekte aus MOCO, bei denen der Mitarbeiter in den Contracts zugewiesen ist:</strong>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px;">
            <?php $__currentLoopData = $combinedProjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                        <h3 style="font-size: 16px; font-weight: 600; color: #111827; margin: 0; flex: 1;"><?php echo e($project['name']); ?></h3>
                        <div style="display: flex; gap: 8px; margin-left: 16px;">
                                <?php if($project['active']): ?>
                                <span style="background: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                                    Aktiv
                                </span>
                                <?php else: ?>
                                <span style="background: #f3f4f6; color: #374151; padding: 2px 8px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                                    Abgeschlossen
                                </span>
                                <?php endif; ?>
                                <?php if($project['billable']): ?>
                                <span style="background: #dbeafe; color: #1e40af; padding: 2px 8px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                                    Abrechenbar
                                </span>
                                <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if(isset($project['identifier']) && $project['identifier']): ?>
                    <div style="color: #6b7280; font-size: 14px; margin-bottom: 8px;">Identifier: <?php echo e($project['identifier']); ?></div>
                    <?php endif; ?>
                    
                    <?php if(isset($project['start_date']) && $project['start_date']): ?>
                    <div style="color: #6b7280; font-size: 14px; margin-bottom: 8px;">Start: <?php echo e(\Carbon\Carbon::parse($project['start_date'])->format('d.m.Y')); ?></div>
                    <?php endif; ?>
                    
                    <?php if((isset($project['finish_date']) && $project['finish_date']) || (isset($project['end_date']) && $project['end_date'])): ?>
                    <div style="color: #6b7280; font-size: 14px; margin-bottom: 8px;">Ende: <?php echo e(\Carbon\Carbon::parse($project['finish_date'] ?? $project['end_date'])->format('d.m.Y')); ?></div>
                    <?php endif; ?>
                    
                    <?php if(isset($project['leader']) && $project['leader'] && isset($project['leader']['firstname']) && isset($project['leader']['lastname'])): ?>
                    <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #e2e8f0;">
                        <div style="font-size: 14px; color: #6b7280;">Projektleitung:</div>
                        <div style="font-size: 16px; font-weight: 600; color: #111827;"><?php echo e($project['leader']['firstname']); ?> <?php echo e($project['leader']['lastname']); ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- MOCO-Abwesenheiten -->
    <?php if(count($mocoAbsences) > 0): ?>
    <div style="background: white; padding: 20px; margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
        <h2 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 20px 0;">MOCO-Abwesenheiten</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px;">
            <?php $__currentLoopData = $mocoAbsences; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $absence): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $startDate = \Carbon\Carbon::parse($absence['start_date'] ?? '');
                    $endDate = \Carbon\Carbon::parse($absence['end_date'] ?? '');
                    $isCurrent = $startDate->lte(now()) && $endDate->gte(now());
                    $isFuture = $startDate->gt(now());
                ?>
                <div style="background: <?php echo e($isCurrent ? '#fef2f2' : '#f8fafc'); ?>; border: 1px solid <?php echo e($isCurrent ? '#fca5a5' : '#e2e8f0'); ?>; border-radius: 8px; padding: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                        <h3 style="font-size: 16px; font-weight: 600; color: #111827; margin: 0;"><?php echo e($absence['type'] ?? 'Abwesenheit'); ?></h3>
                        <div style="display: flex; gap: 8px;">
                            <?php if($isCurrent): ?>
                            <span style="background: #fef3c7; color: #92400e; padding: 2px 8px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                                Aktuell
                            </span>
                            <?php elseif($isFuture): ?>
                            <span style="background: #dbeafe; color: #1e40af; padding: 2px 8px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                                Geplant
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div style="color: #6b7280; font-size: 14px; margin-bottom: 4px;">
                        <?php echo e($startDate->format('d.m.Y')); ?> - <?php echo e($endDate->format('d.m.Y')); ?>

                    </div>
                    <div style="color: #6b7280; font-size: 14px; margin-bottom: 4px;">
                        <?php echo e($startDate->diffInDays($endDate) + 1); ?> Tage
                    </div>
                    <?php if($absence['note']): ?>
                    <div style="color: #6b7280; font-size: 14px; margin-top: 8px; padding-top: 8px; border-top: 1px solid #e2e8f0;">
                        <?php echo e($absence['note']); ?>

                    </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- MOCO-Activities (Zeiterfassung) -->
    <?php if(count($mocoActivities) > 0): ?>
    <div style="background: white; padding: 20px; margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0;">MOCO-Activities</h2>
            <div style="display: flex; gap: 12px; align-items: center;">
                <label for="activitiesTimeRangeFilter" style="color: #6b7280; font-size: 14px; font-weight: 500;">Zeitraum:</label>
                <select 
                    id="activitiesTimeRangeFilter" 
                    onchange="updateActivitiesList()"
                    style="padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; color: #374151; background: white; cursor: pointer; min-width: 150px;"
                >
                    <option value="7">Letzte Woche</option>
                    <option value="30" selected>Letzter Monat</option>
                    <option value="90">Letzte 3 Monate</option>
                    <option value="180">Letzte 6 Monate</option>
                    <option value="custom">Beliebiger Zeitraum</option>
                </select>
                <button 
                    onclick="toggleActivitiesSection()" 
                    id="toggleActivitiesBtn"
                    style="background: #f3f4f6; color: #374151; padding: 8px 16px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer; transition: all 0.2s ease;"
                    onmouseover="this.style.background='#e5e7eb'"
                    onmouseout="this.style.background='#f3f4f6'"
                >
                    <span id="toggleActivitiesText">Ausklappen</span>
                </button>
            </div>
        </div>
        
        <!-- Custom Date Range for Activities (hidden by default) -->
        <div id="customActivitiesDateRange" style="display: none; margin-bottom: 20px; padding: 16px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px;">
            <div style="display: flex; gap: 16px; align-items: center;">
                <div style="flex: 1;">
                    <label style="display: block; color: #6b7280; font-size: 13px; margin-bottom: 6px;">Von:</label>
                    <input type="date" id="customActivitiesStartDate" style="width: 100%; padding: 8px; border: 1px solid #e5e7eb; border-radius: 6px;">
                </div>
                <div style="flex: 1;">
                    <label style="display: block; color: #6b7280; font-size: 13px; margin-bottom: 6px;">Bis:</label>
                    <input type="date" id="customActivitiesEndDate" style="width: 100%; padding: 8px; border: 1px solid #e5e7eb; border-radius: 6px;">
                </div>
                <button 
                    onclick="applyCustomActivitiesDateRange()" 
                    style="margin-top: 20px; padding: 8px 16px; background: #3b82f6; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;"
                    onmouseover="this.style.background='#2563eb'"
                    onmouseout="this.style.background='#3b82f6'"
                >
                    Anwenden
                </button>
            </div>
        </div>
        
        <div id="activitiesContent" style="display: none;">
        <div style="background: #f8f9fa; border: 1px solid #e5e7eb; border-radius: 6px; padding: 16px; margin-bottom: 20px;">
            <strong style="color: #374151;"><?php echo e(count($mocoActivities)); ?> <?php echo e(count($mocoActivities) == 1 ? 'Activity' : 'Activities'); ?> im letzten Monat</strong>
        </div>
        <?php $__currentLoopData = $mocoActivities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-bottom: 12px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                    <div style="flex: 1;">
                        <h3 style="font-size: 16px; font-weight: 600; color: #111827; margin: 0 0 4px 0;"><?php echo e($activity['description'] ?? 'Zeiterfassung'); ?></h3>
                        <div style="color: #6b7280; font-size: 13px;">
                            <?php echo e(\Carbon\Carbon::parse($activity['date'] ?? '')->format('d.m.Y')); ?>

                        </div>
                    </div>
                    <div style="display: flex; gap: 8px; margin-left: 16px; flex-shrink: 0;">
                        <span style="background: #dbeafe; color: #1e40af; padding: 4px 10px; border-radius: 12px; font-size: 13px; font-weight: 600;">
                            <?php echo e($activity['hours'] ?? 0); ?>h
                        </span>
                        <?php if($activity['billable'] ?? false): ?>
                        <span style="background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                            Abrechenbar
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div style="display: grid; gap: 6px; margin-top: 12px; padding-top: 12px; border-top: 1px solid #e5e7eb;">
                    <?php if(isset($activity['project']) && isset($activity['project']['name'])): ?>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="color: #6b7280; font-size: 13px; min-width: 80px;">Projekt:</span>
                        <span style="color: #111827; font-size: 13px; font-weight: 500;"><?php echo e($activity['project']['name']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if(isset($activity['task']) && isset($activity['task']['name'])): ?>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="color: #6b7280; font-size: 13px; min-width: 80px;">Aufgabe:</span>
                        <span style="color: #111827; font-size: 13px;"><?php echo e($activity['task']['name']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if(isset($activity['customer']) && isset($activity['customer']['name'])): ?>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="color: #6b7280; font-size: 13px; min-width: 80px;">Kunde:</span>
                        <span style="color: #111827; font-size: 13px;"><?php echo e($activity['customer']['name']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if(isset($activity['hourly_rate']) && $activity['hourly_rate'] > 0): ?>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="color: #6b7280; font-size: 13px; min-width: 80px;">Stundensatz:</span>
                        <span style="color: #111827; font-size: 13px; font-weight: 600;"><?php echo e(number_format($activity['hourly_rate'], 2)); ?>€</span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
            <?php endif; ?>

    <!-- Lokale Abwesenheiten -->
    <?php if($absences->count() > 0): ?>
    <div style="background: white; padding: 20px; margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
        <h2 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 20px 0;">Lokale Abwesenheiten</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px;">
            <?php $__currentLoopData = $absences; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $absence): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $isCurrent = $absence->start_date <= now() && $absence->end_date >= now();
                    $isFuture = $absence->start_date > now();
            ?>
                <div style="background: <?php echo e($isCurrent ? '#fef2f2' : '#f8fafc'); ?>; border: 1px solid <?php echo e($isCurrent ? '#fca5a5' : '#e2e8f0'); ?>; border-radius: 8px; padding: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                        <h3 style="font-size: 16px; font-weight: 600; color: #111827; margin: 0;"><?php echo e(ucfirst($absence->type)); ?></h3>
                        <div style="display: flex; gap: 8px;">
                            <?php if($isCurrent): ?>
                            <span style="background: #fef3c7; color: #92400e; padding: 2px 8px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                                Aktuell
                            </span>
                            <?php elseif($isFuture): ?>
                            <span style="background: #dbeafe; color: #1e40af; padding: 2px 8px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                                Geplant
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div style="color: #6b7280; font-size: 14px; margin-bottom: 4px;">
                        <?php echo e(\Carbon\Carbon::parse($absence->start_date)->format('d.m.Y')); ?> - <?php echo e(\Carbon\Carbon::parse($absence->end_date)->format('d.m.Y')); ?>

                    </div>
                    <div style="color: #6b7280; font-size: 14px; margin-bottom: 4px;">
                        <?php echo e(\Carbon\Carbon::parse($absence->start_date)->diffInDays(\Carbon\Carbon::parse($absence->end_date)) + 1); ?> Tage
                    </div>
                    <?php if($absence->reason): ?>
                    <div style="color: #6b7280; font-size: 14px; margin-top: 8px; padding-top: 8px; border-top: 1px solid #e2e8f0;">
                        <?php echo e($absence->reason); ?>

                    </div>
                                <?php endif; ?>
                </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <?php endif; ?>
    </div>

<script>
function toggleProjectsSection() {
    const content = document.getElementById('projectsContent');
    const text = document.getElementById('toggleProjectsText');
    
    if (content.style.display === 'none') {
        // Ausklappen
        content.style.display = 'block';
        text.textContent = 'Einklappen';
    } else {
        // Einklappen
        content.style.display = 'none';
        text.textContent = 'Ausklappen';
    }
}

function toggleActivitiesSection() {
    const content = document.getElementById('activitiesContent');
    const text = document.getElementById('toggleActivitiesText');
    
    if (content.style.display === 'none') {
        // Ausklappen
        content.style.display = 'block';
        text.textContent = 'Einklappen';
    } else {
        // Einklappen
        content.style.display = 'none';
        text.textContent = 'Ausklappen';
    }
}

function updatePieChart() {
    const select = document.getElementById('timeRangeFilter');
    const customDateRange = document.getElementById('customDateRange');
    
    if (select.value === 'custom') {
        customDateRange.style.display = 'block';
    } else {
        customDateRange.style.display = 'none';
        loadPieChartData(select.value);
    }
}

function applyCustomDateRange() {
    const startDate = document.getElementById('customStartDate').value;
    const endDate = document.getElementById('customEndDate').value;
    
    if (!startDate || !endDate) {
        alert('Bitte wählen Sie beide Datumsfelder aus.');
        return;
    }
    
    if (new Date(startDate) > new Date(endDate)) {
        alert('Das Start-Datum muss vor dem End-Datum liegen.');
        return;
    }
    
    loadPieChartData('custom', startDate, endDate);
}

function loadPieChartData(days, customStart = null, customEnd = null) {
    const employeeId = <?php echo e($employee->id); ?>;
    const container = document.getElementById('pieChartContainer');
    
    // Zeige Lade-Indikator
    container.innerHTML = '<div style="padding: 40px; text-align: center; color: #6b7280;">Laden...</div>';
    
    // Build URL with parameters
    let url = `/employees/${employeeId}/pie-chart-data?days=${days}`;
    if (customStart && customEnd) {
        url += `&start_date=${customStart}&end_date=${customEnd}`;
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            renderPieChart(data);
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = '<div style="padding: 40px; text-align: center; color: #dc2626;">Fehler beim Laden der Daten.</div>';
        });
}

function renderPieChart(data) {
    const container = document.getElementById('pieChartContainer');
    const colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316', '#6366f1', '#84cc16'];
    
    let currentAngle = 0;
    let svgSegments = '';
    
    data.projectDistribution.forEach((project, index) => {
        const percentage = project.percentage;
        const angle = (percentage / 100) * 360;
        const radius = 80;
        const circumference = 2 * Math.PI * radius;
        const dashArray = (percentage / 100) * circumference;
        const dashOffset = -(currentAngle / 360) * circumference;
        currentAngle += angle;
        const color = colors[index % colors.length];
        
        svgSegments += `<circle cx="100" cy="100" r="${radius}" fill="none" stroke="${color}" stroke-width="60" stroke-dasharray="${dashArray} ${circumference}" stroke-dashoffset="${dashOffset}" style="transition: all 0.3s ease;" />`;
    });
    
    let projectList = '';
    data.projectDistribution.forEach((project, index) => {
        const color = colors[index % colors.length];
        projectList += `
            <div style="display: flex; align-items: center; gap: 12px; background: #f9fafb; padding: 12px; border-radius: 8px;">
                <div style="width: 16px; height: 16px; border-radius: 4px; background: ${color}; flex-shrink: 0;"></div>
                <div style="flex: 1; min-width: 0;">
                    <div style="font-weight: 600; color: #111827; font-size: 14px; margin-bottom: 2px;">${project.name}</div>
                    <div style="font-size: 12px; color: #6b7280;">${project.hours}h</div>
                </div>
                <div style="flex-shrink: 0; text-align: right;">
                    <div style="font-size: 18px; font-weight: 700; color: #111827;">${project.percentage}%</div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = `
        <div style="display: flex; gap: 40px; align-items: flex-start;">
            <div style="flex-shrink: 0;">
                <div style="position: relative; width: 200px; height: 200px;">
                    <svg width="200" height="200" viewBox="0 0 200 200" style="transform: rotate(-90deg);">
                        ${svgSegments}
                        <circle cx="100" cy="100" r="40" fill="white" />
                    </svg>
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                        <div style="font-size: 32px; font-weight: 700; color: #111827;">${data.totalHours}</div>
                        <div style="font-size: 12px; color: #6b7280; margin-top: 4px;">Stunden</div>
                    </div>
                </div>
            </div>
            <div style="flex: 1;">
                <div style="display: grid; gap: 12px;">
                    ${projectList}
                </div>
            </div>
        </div>
    `;
}

// Activities Filter Functions
function updateActivitiesList() {
    const select = document.getElementById('activitiesTimeRangeFilter');
    const customDateRange = document.getElementById('customActivitiesDateRange');
    
    if (select.value === 'custom') {
        customDateRange.style.display = 'block';
    } else {
        customDateRange.style.display = 'none';
        loadActivitiesData(select.value);
    }
}

function applyCustomActivitiesDateRange() {
    const startDate = document.getElementById('customActivitiesStartDate').value;
    const endDate = document.getElementById('customActivitiesEndDate').value;
    
    if (!startDate || !endDate) {
        alert('Bitte wählen Sie beide Datumsfelder aus.');
        return;
    }
    
    if (new Date(startDate) > new Date(endDate)) {
        alert('Das Start-Datum muss vor dem End-Datum liegen.');
        return;
    }
    
    loadActivitiesData('custom', startDate, endDate);
}

function loadActivitiesData(days, customStart = null, customEnd = null) {
    const employeeId = <?php echo e($employee->id); ?>;
    const container = document.getElementById('activitiesContent');
    
    // Zeige Lade-Indikator
    container.innerHTML = '<div style="padding: 40px; text-align: center; color: #6b7280;">Laden...</div>';
    container.style.display = 'block'; // Zeige Container
    
    // Build URL with parameters
    let url = `/employees/${employeeId}/activities-data?days=${days}`;
    if (customStart && customEnd) {
        url += `&start_date=${customStart}&end_date=${customEnd}`;
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            renderActivitiesList(data);
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = '<div style="padding: 40px; text-align: center; color: #dc2626;">Fehler beim Laden der Daten.</div>';
        });
}

function renderActivitiesList(data) {
    const container = document.getElementById('activitiesContent');
    
    const activitiesCount = data.activities.length;
    const select = document.getElementById('activitiesTimeRangeFilter');
    let timeRangeText = '';
    
    switch(select.value) {
        case '7': timeRangeText = 'letzte Woche'; break;
        case '30': timeRangeText = 'letzten Monat'; break;
        case '90': timeRangeText = 'letzten 3 Monate'; break;
        case '180': timeRangeText = 'letzten 6 Monate'; break;
        case 'custom': timeRangeText = 'gewählten Zeitraum'; break;
        default: timeRangeText = 'gewählten Zeitraum';
    }
    
    let html = `
        <div style="background: #f8f9fa; border: 1px solid #e5e7eb; border-radius: 6px; padding: 16px; margin-bottom: 20px;">
            <strong style="color: #374151;">${activitiesCount} ${activitiesCount === 1 ? 'Activity' : 'Activities'} im ${timeRangeText}</strong>
        </div>
    `;
    
    data.activities.forEach(activity => {
        const date = new Date(activity.date);
        const formattedDate = date.toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric' });
        
        html += `
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-bottom: 12px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                    <div style="flex: 1;">
                        <h3 style="font-size: 16px; font-weight: 600; color: #111827; margin: 0 0 4px 0;">${activity.description || 'Zeiterfassung'}</h3>
                        <div style="color: #6b7280; font-size: 13px;">${formattedDate}</div>
                    </div>
                    <div style="display: flex; gap: 8px; margin-left: 16px; flex-shrink: 0;">
                        <span style="background: #dbeafe; color: #1e40af; padding: 4px 10px; border-radius: 12px; font-size: 13px; font-weight: 600;">
                            ${activity.hours || 0}h
                        </span>
                        ${activity.billable ? '<span style="background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 500;">Abrechenbar</span>' : ''}
                    </div>
                </div>
                
                <div style="display: grid; gap: 6px; margin-top: 12px; padding-top: 12px; border-top: 1px solid #e5e7eb;">
                    ${activity.project && activity.project.name ? `
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="color: #6b7280; font-size: 13px; min-width: 80px;">Projekt:</span>
                            <span style="color: #111827; font-size: 13px; font-weight: 500;">${activity.project.name}</span>
                        </div>
                    ` : ''}
                    ${activity.task && activity.task.name ? `
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="color: #6b7280; font-size: 13px; min-width: 80px;">Aufgabe:</span>
                            <span style="color: #111827; font-size: 13px;">${activity.task.name}</span>
                        </div>
                    ` : ''}
                    ${activity.customer && activity.customer.name ? `
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="color: #6b7280; font-size: 13px; min-width: 80px;">Kunde:</span>
                            <span style="color: #111827; font-size: 13px;">${activity.customer.name}</span>
                        </div>
                    ` : ''}
                    ${activity.hourly_rate && activity.hourly_rate > 0 ? `
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="color: #6b7280; font-size: 13px; min-width: 80px;">Stundensatz:</span>
                            <span style="color: #111827; font-size: 13px; font-weight: 600;">${activity.hourly_rate.toFixed(2)}€</span>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\mein-projekt\resources\views/employees/show.blade.php ENDPATH**/ ?>