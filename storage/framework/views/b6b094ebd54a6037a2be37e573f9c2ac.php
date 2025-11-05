<?php $__env->startSection('title', 'Mitarbeiter'); ?>

<?php
use Illuminate\Support\Facades\DB;
?>

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
                        <div style="color: #6b7280; font-size: 12px; text-transform: uppercase; font-weight: 600;">Inaktiv</div>
                        <div style="color: #6b7280; font-weight: 600; font-size: 18px; margin-top: 2px;"><?php echo e($employees->where('is_active', false)->count()); ?></div>
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
                <label for="filter-active-status" style="font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase;">Mitarbeiter-Status</label>
                <select id="filter-active-status" onchange="applyFilters()" style="padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 13px; min-width: 160px;">
                    <option value="active">Nur Aktive</option>
                    <option value="all">Alle</option>
                    <option value="inactive">Nur Inaktive</option>
                </select>
            </div>
            <div style="display: flex; flex-direction: column;">
                <label for="filter-status" style="font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase;">Auslastungs-Status</label>
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
                <input id="filter-search" type="text" placeholder="Mitarbeiter" style="padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 13px; min-width: 200px;">
            </div>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 12px; border-radius: 6px; margin-bottom: 16px;">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if(session('warning')): ?>
        <div style="background: #ffffff; border: 1px solid #ea580c; border-left: 4px solid #ea580c; color: #ea580c; padding: 12px; border-radius: 6px; margin-bottom: 16px;">
            <?php echo e(session('warning')); ?>

        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div style="background: #ffffff; border: 1px solid #dc2626; border-left: 4px solid #dc2626; color: #dc2626; padding: 12px; border-radius: 6px; margin-bottom: 16px;">
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
                            // Prüfe ob Mitarbeiter aktuell abwesend ist
                            $isAbsentNow = DB::table('absences')
                                ->where('employee_id', $employee->id)
                                ->where('start_date', '<=', now())
                                ->where('end_date', '>=', now())
                                ->exists();
                            
                            // Bestimme Verfügbarkeitsstatus
                            if ($isAbsentNow) {
                                $availabilityStatus = 'absent';
                                $availabilityLabel = 'Abwesend';
                                $availabilityColor = '#dc2626'; // Rot
                            } elseif (!$employee->is_active) {
                                $availabilityStatus = 'inactive';
                                $availabilityLabel = 'Inaktiv';
                                $availabilityColor = '#737373'; // Grau
                            } else {
                                $availabilityStatus = 'active';
                                $availabilityLabel = 'Aktiv';
                                $availabilityColor = '#16a34a'; // Grün
                            }

                            // Alte KPI-Farben für Auslastungsspalten
                            $statusColors = [
                                'critical' => '#dc2626',
                                'warning' => '#ea580c',
                                'balanced' => '#16a34a',
                                'underutilized' => '#737373',
                                'unknown' => '#a3a3a3'
                            ];
                        ?>
                        <tr class="employee-row" data-status="<?php echo e($employee->kpi_status_4w); ?>" data-bottleneck="<?php echo e($employee->kpi_bottleneck ? '1' : '0'); ?>" data-department="<?php echo e(strtolower($employee->department)); ?>" data-search="<?php echo e(strtolower($employee->first_name . ' ' . $employee->last_name)); ?>" data-is-active="<?php echo e($employee->is_active ? '1' : '0'); ?>" style="border-bottom: 1px solid #e5e7eb;">
                            <td style="padding: 12px;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #3b82f6, #6366f1); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 12px;">
                                        <?php echo e(strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1))); ?>

                                    </div>
                                    <div>
                                        <div style="font-weight: 600; color: #111827;">
                                            <?php echo e($employee->first_name); ?> <?php echo e($employee->last_name); ?>

                                            <?php if(!$employee->is_active): ?>
                                                <span style="background: #e5e7eb; color: #6b7280; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 500; margin-left: 6px;">⚪ Inaktiv</span>
                                            <?php endif; ?>
                                        </div>
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
                                <span style="color: <?php echo e($availabilityColor); ?>; font-size: 13px; font-weight: 600;">
                                    <?php echo e($availabilityLabel); ?>

                                </span>
                            </td>
                            <td style="padding: 12px;">
                                <div style="display: flex; gap: 6px;">
                                    <a href="<?php echo e(route('employees.show', $employee)); ?>" style="background: #ffffff; color: #374151; padding: 6px 12px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 500; border: 1px solid #e5e7eb; transition: all 0.15s ease;">Anzeigen</a>
                                    <a href="<?php echo e(route('employees.edit', $employee)); ?>" style="background: #ffffff; color: #374151; padding: 6px 12px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 500; border: 1px solid #e5e7eb; transition: all 0.15s ease;">Bearbeiten</a>
                                    <form action="<?php echo e(route('employees.destroy', $employee)); ?>" method="POST" style="display: inline;" onsubmit="return confirm('🗑️ Mitarbeiter <?php echo e($employee->first_name); ?> <?php echo e($employee->last_name); ?> wirklich löschen?\n\n<?php echo e($employee->source === 'moco' ? '⚠️ ACHTUNG: Dies ist ein MOCO-Mitarbeiter! Beim nächsten Sync wird er wieder synchronisiert.' : ($employee->source === 'manual' ? '✓ Dies ist ein manuell angelegter Mitarbeiter.' : 'Dies ist ein Test-Mitarbeiter.')); ?>')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" style="background: #ffffff; color: #374151; padding: 6px 12px; border-radius: 8px; border: 1px solid #e5e7eb; font-size: 12px; font-weight: 500; cursor: pointer; transition: all 0.15s ease;">Löschen</button>
                                    </form>
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
    </div>
</div>

<script>
    (function() {
        const activeStatusFilter = document.getElementById('filter-active-status');
        const statusFilter = document.getElementById('filter-status');
        const bottleneckFilter = document.getElementById('filter-bottleneck');
        const departmentFilter = document.getElementById('filter-department');
        const searchFilter = document.getElementById('filter-search');
        const rows = Array.from(document.querySelectorAll('.employee-row'));

        function applyFilters() {
            const activeStatusValue = activeStatusFilter.value;
            const statusValue = statusFilter.value;
            const bottleneckOnly = bottleneckFilter.checked;
            const departmentValue = departmentFilter.value.trim().toLowerCase();
            const searchValue = searchFilter.value.trim().toLowerCase();

            rows.forEach(row => {
                const rowIsActive = row.dataset.isActive === '1';
                const rowStatus = row.dataset.status ?? 'unknown';
                const rowBottleneck = row.dataset.bottleneck === '1';
                const rowDepartment = row.dataset.department ?? '';
                const rowSearch = row.dataset.search ?? '';

                let visible = true;

                // Filter: Aktiv/Inaktiv
                if (activeStatusValue === 'active' && !rowIsActive) {
                    visible = false;
                } else if (activeStatusValue === 'inactive' && rowIsActive) {
                    visible = false;
                }
                // 'all' zeigt beide

                if (visible && statusValue !== 'all' && rowStatus !== statusValue) {
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

        [activeStatusFilter, statusFilter, bottleneckFilter].forEach(el => el.addEventListener('change', applyFilters));
        [departmentFilter, searchFilter].forEach(el => el.addEventListener('input', applyFilters));
        
        // Trigger initial filter (default: active only)
        applyFilters();
    })();
</script>

<style>
    /* Button Hover Effects - Einheitlich für alle Buttons */
    td a[href*="employees.show"]:hover,
    td a[href*="employees.edit"]:hover,
    td button[type="submit"]:hover {
        background: #f9fafb !important;
        border-color: #d1d5db !important;
    }

    /* Smooth transitions already inline, but ensure consistency */
    td a, td button {
        transition: all 0.15s ease;
    }
</style>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Day2Day-Manager\resources\views/employees/index.blade.php ENDPATH**/ ?>