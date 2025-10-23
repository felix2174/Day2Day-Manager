<?php $__env->startSection('title', 'Mitarbeiter'); ?>

<?php $__env->startSection('content'); ?>
<div style="width: 100%; margin: 0; padding: 0;">
    <div style="background: white; padding: 20px; margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 24px;">
            <div style="flex: 1;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h1 style="font-size: 24px; font-weight: bold; color: #111827; margin: 0;">Mitarbeiter-Verwaltung</h1>
                        <p style="color: #6b7280; margin: 4px 0 0 0; font-size: 14px;">Auslastung & Bottlenecks auf Basis aktueller MOCO-Daten</p>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <a href="<?php echo e(route('employees.export')); ?>" style="background: #ffffff; color: #374151; padding: 10px 16px; border-radius: 10px; text-decoration: none; font-size: 13px; font-weight: 500; border: 1px solid #e5e7eb;">Excel Export</a>
                        <a href="<?php echo e(route('employees.import')); ?>" style="background: #ffffff; color: #374151; padding: 10px 16px; border-radius: 10px; text-decoration: none; font-size: 13px; font-weight: 500; border: 1px solid #e5e7eb;">CSV Import</a>
                        <a href="<?php echo e(route('employees.create')); ?>" style="background: #111827; color: white; padding: 10px 16px; border-radius: 10px; text-decoration: none; font-size: 13px; font-weight: 500;">Neuer Mitarbeiter</a>
                    </div>
                </div>

                <div style="margin-top: 16px; padding: 12px 16px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px; display: flex; gap: 24px; flex-wrap: wrap;">
                    <div style="min-width: 140px;">
                        <div style="color: #6b7280; font-size: 12px; text-transform: uppercase; font-weight: 600;">Gesamt</div>
                        <div style="color: #111827; font-weight: 600; font-size: 18px; margin-top: 2px;"><?php echo e($employees->count()); ?></div>
                    </div>
                    <div style="min-width: 140px;">
                        <div style="color: #6b7280; font-size: 12px; text-transform: uppercase; font-weight: 600;">Aktiv</div>
                        <div style="color: #059669; font-weight: 600; font-size: 18px; margin-top: 2px;"><?php echo e($employees->where('is_active', true)->count()); ?></div>
                    </div>
                    <div style="min-width: 140px;">
                        <div style="color: #6b7280; font-size: 12px; text-transform: uppercase; font-weight: 600;">Überlastet</div>
                        <div style="color: #b91c1c; font-weight: 600; font-size: 18px; margin-top: 2px;"><?php echo e($statusCounts['critical'] ?? 0); ?></div>
                    </div>
                    <div style="min-width: 140px;">
                        <div style="color: #6b7280; font-size: 12px; text-transform: uppercase; font-weight: 600;">Hohe Auslastung</div>
                        <div style="color: #f59e0b; font-weight: 600; font-size: 18px; margin-top: 2px;"><?php echo e($statusCounts['warning'] ?? 0); ?></div>
                    </div>
                    <div style="min-width: 140px;">
                        <div style="color: #6b7280; font-size: 12px; text-transform: uppercase; font-weight: 600;">Im Soll</div>
                        <div style="color: #059669; font-weight: 600; font-size: 18px; margin-top: 2px;"><?php echo e($statusCounts['balanced'] ?? 0); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div style="margin-top: 20px; display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
            <div style="display: flex; flex-direction: column;">
                <label for="filter-status" style="font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase;">Status</label>
                <select id="filter-status" style="padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 13px; min-width: 160px;">
                    <option value="all">Alle Status</option>
                    <option value="critical">Überlastet (<?php echo e($statusCounts['critical'] ?? 0); ?>)</option>
                    <option value="warning">Hohe Auslastung (<?php echo e($statusCounts['warning'] ?? 0); ?>)</option>
                    <option value="balanced">Im Soll (<?php echo e($statusCounts['balanced'] ?? 0); ?>)</option>
                    <option value="underutilized">Unterlast (<?php echo e($statusCounts['underutilized'] ?? 0); ?>)</option>
                    <option value="unknown">Ohne Daten (<?php echo e($statusCounts['unknown'] ?? 0); ?>)</option>
                </select>
            </div>
            <div style="display: flex; flex-direction: column;">
                <label style="font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase;">Bottleneck</label>
                <label style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 8px; background: #f9fafb; cursor: pointer; font-size: 13px; color: #374151;">
                    <input type="checkbox" id="filter-bottleneck" style="width: 16px; height: 16px; accent-color: #ef4444;">
                    Nur kritische Mitarbeiter
                </label>
            </div>
            <div style="display: flex; flex-direction: column;">
                <label for="filter-department" style="font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase;">Abteilung</label>
                <input id="filter-department" type="text" placeholder="Abteilung suchen" style="padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 13px; min-width: 180px;">
            </div>
            <div style="display: flex; flex-direction: column;">
                <label for="filter-search" style="font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase;">Suche</label>
                <input id="filter-search" type="text" placeholder="Name oder Projekt" style="padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 13px; min-width: 200px;">
            </div>
        </div>
    </div>

    <?php if(!empty($kpiWarnings)): ?>
        <div style="background: #fef3c7; border: 1px solid #fcd34d; color: #92400e; padding: 12px; border-radius: 6px; margin-bottom: 16px;">
            <strong>Hinweis:</strong>
            <ul style="margin: 8px 0 0 20px;">
                <?php $__currentLoopData = $kpiWarnings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warning): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($warning); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if(session('success')): ?>
        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 12px; border-radius: 6px; margin-bottom: 16px;">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 12px; border-radius: 6px; margin-bottom: 16px;">
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>

    <div style="display: flex; gap: 20px;">
        <div style="flex: 1; background: white; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
            <table id="employees-table" style="width: 100%; border-collapse: collapse;">
                <thead style="background: #f9fafb;">
                    <tr>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb;">Name</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb;">Abteilung</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb;">Auslastung (4W)</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb;">Auslastung (12W)</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb;">Top-Projekt</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb;">Status</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb;">Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $statusColors = [
                                'critical' => '#ef4444',
                                'warning' => '#f59e0b',
                                'balanced' => '#10b981',
                                'underutilized' => '#6b7280',
                                'unknown' => '#9ca3af'
                            ];

                            $statusLabel = [
                                'critical' => 'Überlastet',
                                'warning' => 'Hohe Auslastung',
                                'balanced' => 'Im Soll',
                                'underutilized' => 'Unterlast',
                                'unknown' => 'Unbekannt',
                            ];
                        ?>
                        <tr class="employee-row" data-status="<?php echo e($employee->kpi_status_4w); ?>" data-bottleneck="<?php echo e($employee->kpi_bottleneck ? '1' : '0'); ?>" data-department="<?php echo e(strtolower($employee->department)); ?>" data-search="<?php echo e(strtolower($employee->first_name . ' ' . $employee->last_name . ' ' . ($employee->kpi_top_project['name'] ?? ''))); ?>" style="border-bottom: 1px solid #e5e7eb; background: <?php echo e($employee->kpi_bottleneck ? '#fef2f2' : 'transparent'); ?>;">
                            <td style="padding: 12px;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #3b82f6, #6366f1); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 12px;">
                                        <?php echo e(strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1))); ?>

                                    </div>
                                    <div>
                                        <div style="font-weight: 600; color: #111827;"><?php echo e($employee->first_name); ?> <?php echo e($employee->last_name); ?></div>
                                        <div style="font-size: 12px; color: #6b7280;">Kapazität: <?php echo e(round($employee->moco_weekly_capacity)); ?>h/Woche</div>
                                        <?php if($employee->kpi_absence_alert && $employee->kpi_absence_summary): ?>
                                            <div style="font-size: 11px; color: #b91c1c; margin-top: 4px;"><?php echo e($employee->kpi_absence_summary); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 12px;">
                                <span style="background: #dbeafe; color: #1d4ed8; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500;"><?php echo e($employee->department); ?></span>
                            </td>
                            <td style="padding: 12px;">
                                <?php if($employee->kpi_available): ?>
                                    <div style="font-weight: 600; color: <?php echo e($statusColors[$employee->kpi_status_4w] ?? '#6b7280'); ?>;"><?php echo e($employee->kpi_util_4w); ?>%</div>
                                    <div style="font-size: 11px; color: #6b7280;"><?php echo e($employee->kpi_hours_4w); ?>h</div>
                                <?php else: ?>
                                    <span style="font-size: 12px; color: #9ca3af;">Keine Daten</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px;">
                                <?php if($employee->kpi_available): ?>
                                    <div style="font-weight: 600; color: <?php echo e($statusColors[$employee->kpi_status_12w] ?? '#6b7280'); ?>;"><?php echo e($employee->kpi_util_12w); ?>%</div>
                                    <div style="font-size: 11px; color: #6b7280;"><?php echo e($employee->kpi_hours_12w); ?>h</div>
                                <?php else: ?>
                                    <span style="font-size: 12px; color: #9ca3af;">Keine Daten</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px;">
                                <?php if($employee->kpi_available && $employee->kpi_top_project): ?>
                                    <div style="font-weight: 600; color: #111827;"><?php echo e($employee->kpi_top_project['name']); ?></div>
                                    <div style="font-size: 11px; color: #6b7280;"><?php echo e($employee->kpi_top_project['hours']); ?>h (<?php echo e($employee->kpi_top_project['share']); ?>%)</div>
                                <?php else: ?>
                                    <span style="font-size: 12px; color: #9ca3af;">Keine Daten</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px;">
                                <?php if($employee->kpi_available): ?>
                                    <span style="background: <?php echo e(($statusColors[$employee->kpi_status_4w] ?? '#9ca3af')); ?>15; color: <?php echo e($statusColors[$employee->kpi_status_4w] ?? '#374151'); ?>; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; display: inline-block;">
                                        <?php echo e($statusLabel[$employee->kpi_status_4w]); ?>

                                    </span>
                                <?php else: ?>
                                    <span style="background: #f3f4f6; color: #6b7280; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; display: inline-block;">Unbekannt</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px;">
                                <div style="display: flex; gap: 6px;">
                                    <a href="<?php echo e(route('employees.show', $employee)); ?>" style="background: #ffffff; color: #374151; padding: 6px 12px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">Anzeigen</a>
                                    <a href="<?php echo e(route('employees.edit', $employee)); ?>" style="background: #ffffff; color: #374151; padding: 6px 12px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">Bearbeiten</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" style="padding: 40px; text-align: center; color: #6b7280;">Keine Mitarbeiter gefunden</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div style="width: 360px; background: white; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);">
            <div style="padding: 24px;">
                <div style="margin-bottom: 20px; border-bottom: 1px solid #e5e7eb; padding-bottom: 16px;">
                    <h3 style="font-size: 18px; font-weight: 700; color: #111827; margin: 0;">Abwesenheiten</h3>
                    <p style="font-size: 13px; color: #6b7280; margin: 4px 0 0 0;">Nächste 30 Tage</p>
                </div>

                <div style="background: linear-gradient(135deg, #e0f2fe, #bfdbfe); border-radius: 8px; padding: 16px; margin-bottom: 20px;">
                    <div style="font-size: 12px; font-weight: 600; color: #0c4a6e; text-transform: uppercase;">Team-Verfügbarkeit</div>
                    <div style="margin-top: 12px; background: #e5e7eb; height: 20px; border-radius: 10px; overflow: hidden; position: relative;">
                        <div style="background: <?php echo e($teamAvailability >= 80 ? '#22c55e' : ($teamAvailability >= 60 ? '#fbbf24' : '#ef4444')); ?>; width: <?php echo e($teamAvailability); ?>%; height: 100%;"></div>
                        <div style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 600; color: #111827;"><?php echo e($teamAvailability); ?>%</div>
                    </div>
                    <div style="font-size: 11px; color: #0c4a6e; margin-top: 8px;"><?php echo e($absenceStats['total']); ?> Abwesenheiten geplant</div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-bottom: 16px;">
                    <div style="background: #dbeafe; border-radius: 8px; padding: 12px; text-align: center;">
                        <div style="font-size: 20px; font-weight: 700; color: #1d4ed8;"><?php echo e($absenceStats['urlaub']); ?></div>
                        <div style="font-size: 10px; color: #1d4ed8; text-transform: uppercase;">Urlaub</div>
                    </div>
                    <div style="background: #fee2e2; border-radius: 8px; padding: 12px; text-align: center;">
                        <div style="font-size: 20px; font-weight: 700; color: #dc2626;"><?php echo e($absenceStats['krankheit']); ?></div>
                        <div style="font-size: 10px; color: #dc2626; text-transform: uppercase;">Krankheit</div>
                    </div>
                    <div style="background: #fef3c7; border-radius: 8px; padding: 12px; text-align: center;">
                        <div style="font-size: 20px; font-weight: 700; color: #d97706;"><?php echo e($absenceStats['fortbildung']); ?></div>
                        <div style="font-size: 10px; color: #d97706; text-transform: uppercase;">Fortbildung</div>
                    </div>
                </div>

                <div>
                    <h4 style="font-size: 14px; font-weight: 700; color: #374151; margin: 0 0 12px 0;">Kommende Abwesenheiten</h4>
                    <?php if($upcomingAbsences->count() > 0): ?>
                        <div style="display: grid; gap: 12px; max-height: 500px; overflow-y: auto;">
                            <?php $__currentLoopData = $upcomingAbsences; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $absence): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $start = \Carbon\Carbon::parse($absence->start_date);
                                    $end = \Carbon\Carbon::parse($absence->end_date);
                                ?>
                                <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px;">
                                    <div style="font-weight: 600; color: #111827;"><?php echo e($absence->employee_name); ?></div>
                                    <div style="font-size: 11px; color: #6b7280;"><?php echo e($absence->department); ?></div>
                                    <div style="font-size: 12px; color: #1d4ed8; font-weight: 600; margin-top: 6px;"><?php echo e($start->format('d.m.Y')); ?> - <?php echo e($end->format('d.m.Y')); ?></div>
                                    <div style="font-size: 11px; color: #6b7280;"><?php echo e($absence->type); ?> • <?php echo e($start->diffInDays($end) + 1); ?> Tage</div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <div style="text-align: center; padding: 24px; border: 1px dashed #e5e7eb; border-radius: 8px; color: #6b7280;">Keine Abwesenheiten geplant</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        const statusFilter = document.getElementById('filter-status');
        const bottleneckFilter = document.getElementById('filter-bottleneck');
        const departmentFilter = document.getElementById('filter-department');
        const searchFilter = document.getElementById('filter-search');
        const rows = Array.from(document.querySelectorAll('.employee-row'));

        function applyFilters() {
            const statusValue = statusFilter.value;
            const bottleneckOnly = bottleneckFilter.checked;
            const departmentValue = departmentFilter.value.trim().toLowerCase();
            const searchValue = searchFilter.value.trim().toLowerCase();

            rows.forEach(row => {
                const rowStatus = row.dataset.status ?? 'unknown';
                const rowBottleneck = row.dataset.bottleneck === '1';
                const rowDepartment = row.dataset.department ?? '';
                const rowSearch = row.dataset.search ?? '';

                let visible = true;

                if (statusValue !== 'all' && rowStatus !== statusValue) {
                    visible = false;
                }

                if (visible && bottleneckOnly && !rowBottleneck) {
                    visible = false;
                }

                if (visible && departmentValue && !rowDepartment.includes(departmentValue)) {
                    visible = false;
                }

                if (visible && searchValue && !rowSearch.includes(searchValue)) {
                    visible = false;
                }

                row.style.display = visible ? '' : 'none';
            });
        }

        [statusFilter, bottleneckFilter].forEach(el => el.addEventListener('change', applyFilters));
        [departmentFilter, searchFilter].forEach(el => el.addEventListener('input', applyFilters));
    })();
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\mein-projekt\resources\views/employees/index.blade.php ENDPATH**/ ?>