

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">📅 Abwesenheiten</h1>
            <p class="text-gray-600 mt-1">Übersicht aller synchronisierten Abwesenheiten aus MOCO</p>
        </div>
        <div class="flex gap-3">
            <a href="<?php echo e(route('absences.create')); ?>" class="btn btn-primary">
                ➕ Neue Abwesenheit
            </a>
            <a href="<?php echo e(route('moco.index')); ?>" class="btn btn-secondary">
                🔄 Synchronisieren
            </a>
        </div>
    </div>

    <!-- Statistik-Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
            <div class="text-gray-600 text-sm font-medium">Gesamt</div>
            <div class="text-3xl font-bold text-gray-900 mt-1"><?php echo e($stats['total']); ?></div>
            <div class="text-gray-500 text-xs mt-1">Abwesenheiten</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
            <div class="text-gray-600 text-sm font-medium">Urlaub</div>
            <div class="text-3xl font-bold text-green-600 mt-1"><?php echo e($stats['urlaub']); ?></div>
            <div class="text-gray-500 text-xs mt-1">🏖️ Geplante Urlaubstage</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
            <div class="text-gray-600 text-sm font-medium">Krankheit</div>
            <div class="text-3xl font-bold text-red-600 mt-1"><?php echo e($stats['krankheit']); ?></div>
            <div class="text-gray-500 text-xs mt-1">🤒 Krankheitstage</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
            <div class="text-gray-600 text-sm font-medium">Fortbildung</div>
            <div class="text-3xl font-bold text-purple-600 mt-1"><?php echo e($stats['fortbildung']); ?></div>
            <div class="text-gray-500 text-xs mt-1">📚 Fortbildungstage</div>
        </div>
    </div>

    <!-- Filter-Sektion (Horizontal Layout mit Auto-Apply) -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">🔍 Filter</h3>
                <a href="<?php echo e(route('absences.index')); ?>" class="text-gray-500 hover:text-gray-700 transition" title="Filter zurücksetzen">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </a>
            </div>
            <form method="GET" action="<?php echo e(route('absences.index')); ?>" id="filterForm" class="flex flex-wrap gap-4">
                <!-- Mitarbeiter-Filter -->
                <div class="flex-1 min-w-[200px]">
                    <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-2">Mitarbeiter</label>
                    <select name="employee_id" id="employee_id" onchange="document.getElementById('filterForm').submit()" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Alle Mitarbeiter</option>
                        <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($employee->id); ?>" <?php echo e(request('employee_id') == $employee->id ? 'selected' : ''); ?>>
                                <?php echo e($employee->first_name); ?> <?php echo e($employee->last_name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <!-- Typ-Filter -->
                <div class="flex-1 min-w-[180px]">
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Typ</label>
                    <select name="type" id="type" onchange="document.getElementById('filterForm').submit()" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Alle Typen</option>
                        <option value="urlaub" <?php echo e(request('type') == 'urlaub' ? 'selected' : ''); ?>>🏖️ Urlaub</option>
                        <option value="krankheit" <?php echo e(request('type') == 'krankheit' ? 'selected' : ''); ?>>🤒 Krankheit</option>
                        <option value="fortbildung" <?php echo e(request('type') == 'fortbildung' ? 'selected' : ''); ?>>📚 Fortbildung</option>
                    </select>
                </div>

                <!-- Datum Von -->
                <div class="flex-1 min-w-[160px]">
                    <label for="from" class="block text-sm font-medium text-gray-700 mb-2">Von</label>
                    <input type="date" name="from" id="from" value="<?php echo e(request('from')); ?>" onchange="document.getElementById('filterForm').submit()" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Datum Bis -->
                <div class="flex-1 min-w-[160px]">
                    <label for="to" class="block text-sm font-medium text-gray-700 mb-2">Bis</label>
                    <input type="date" name="to" id="to" value="<?php echo e(request('to')); ?>" onchange="document.getElementById('filterForm').submit()" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
            </form>
        </div>
    </div>

    <!-- Mitarbeiter-Accordions -->
    <div class="space-y-4">
        <?php $__empty_1 = true; $__currentLoopData = $absencesByEmployee; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employeeId => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $employee = $data['employee'];
                $absences = $data['absences'];
                $totalCount = $data['total_count'];
                $totalDays = $data['total_days'];
                $byType = $data['by_type'];
            ?>
            
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <!-- Accordion Header -->
                <div class="accordion-header px-6 py-4 cursor-pointer hover:bg-gray-50 transition" 
                     onclick="toggleAccordion('employee-<?php echo e($employeeId); ?>')">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <span class="text-blue-600 font-bold text-lg">
                                    <?php echo e(strtoupper(substr($employee->first_name ?? '', 0, 1))); ?><?php echo e(strtoupper(substr($employee->last_name ?? '', 0, 1))); ?>

                                </span>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">
                                    <?php echo e($employee->first_name ?? ''); ?> <?php echo e($employee->last_name ?? 'Unbekannt'); ?>

                                </h3>
                                <p class="text-sm text-gray-500">
                                    <?php echo e($totalCount); ?> <?php echo e($totalCount === 1 ? 'Abwesenheit' : 'Abwesenheiten'); ?> · 
                                    <?php echo e($totalDays); ?> <?php echo e($totalDays === 1 ? 'Tag' : 'Tage'); ?> gesamt
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <!-- Mini-Statistiken -->
                            <div class="flex gap-3 text-xs">
                                <?php if($byType['urlaub'] > 0): ?>
                                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded">
                                        🏖️ <?php echo e($byType['urlaub']); ?>

                                    </span>
                                <?php endif; ?>
                                <?php if($byType['krankheit'] > 0): ?>
                                    <span class="px-2 py-1 bg-red-100 text-red-700 rounded">
                                        🤒 <?php echo e($byType['krankheit']); ?>

                                    </span>
                                <?php endif; ?>
                                <?php if($byType['fortbildung'] > 0): ?>
                                    <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded">
                                        📚 <?php echo e($byType['fortbildung']); ?>

                                    </span>
                                <?php endif; ?>
                            </div>
                            <!-- Toggle Icon -->
                            <svg id="icon-employee-<?php echo e($employeeId); ?>" class="w-5 h-5 text-gray-400 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Accordion Content (Collapsed by default) -->
                <div id="content-employee-<?php echo e($employeeId); ?>" class="accordion-content" style="display: none;">
                    <div class="border-t border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">
                                        Typ
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Zeitraum
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">
                                        Dauer
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Grund
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">
                                        Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php $__currentLoopData = $absences; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $absence): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $start = \Carbon\Carbon::parse($absence->start_date);
                                        $end = \Carbon\Carbon::parse($absence->end_date);
                                        $now = now();
                                        $isActive = $start <= $now && $end >= $now;
                                        $isUpcoming = $start > $now;
                                        $duration = $start->diffInDays($end) + 1;
                                        $isSameDay = $start->isSameDay($end);
                                    ?>
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-3 whitespace-nowrap">
                                            <?php if($absence->type === 'urlaub'): ?>
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    🏖️ Urlaub
                                                </span>
                                            <?php elseif($absence->type === 'krankheit'): ?>
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    🤒 Krank
                                                </span>
                                            <?php else: ?>
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                                    📚 Fortbildung
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-900">
                                            <?php if($isSameDay): ?>
                                                <?php echo e($start->format('d.m.Y')); ?>

                                            <?php else: ?>
                                                <span class="font-medium"><?php echo e($start->format('d.m.Y')); ?></span>
                                                <span class="text-gray-400 mx-1">→</span>
                                                <span class="font-medium"><?php echo e($end->format('d.m.Y')); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-700 font-medium">
                                            <?php echo e($duration); ?> <?php echo e($duration === 1 ? 'Tag' : 'Tage'); ?>

                                        </td>
                                        <td class="px-6 py-3 text-sm text-gray-500">
                                            <?php echo e($absence->reason ?? '-'); ?>

                                        </td>
                                        <td class="px-6 py-3 whitespace-nowrap">
                                            <?php if($isActive): ?>
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    ⏳ Aktiv
                                                </span>
                                            <?php elseif($isUpcoming): ?>
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    📅 Geplant
                                                </span>
                                            <?php else: ?>
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-600">
                                                    ✓ Beendet
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <div class="text-gray-500">
                    <div class="text-6xl mb-4">📭</div>
                    <div class="text-xl font-semibold mb-2">Keine Abwesenheiten gefunden</div>
                    <p class="text-sm mb-4">Ändere die Filter oder führe eine Synchronisation durch</p>
                    <a href="<?php echo e(route('moco.index')); ?>" class="inline-block text-blue-600 hover:text-blue-800 font-medium">
                        → Jetzt synchronisieren
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- JavaScript für Accordion -->
    <script>
        function toggleAccordion(id) {
            const content = document.getElementById('content-' + id);
            const icon = document.getElementById('icon-' + id);
            
            if (content.style.display === 'none') {
                content.style.display = 'block';
                icon.style.transform = 'rotate(180deg)';
            } else {
                content.style.display = 'none';
                icon.style.transform = 'rotate(0deg)';
            }
        }
    </script>

    <!-- Info-Box -->
    <div class="mt-6 bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-blue-700">
                    <strong>Hinweis:</strong> Abwesenheiten werden automatisch aus MOCO synchronisiert. 
                    Letzte Synchronisation: <?php echo e(\Illuminate\Support\Facades\Cache::get('moco:absences:last_sync')?->diffForHumans() ?? 'Noch nie'); ?>

                </p>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Day2Day-Manager\resources\views/absences/index.blade.php ENDPATH**/ ?>