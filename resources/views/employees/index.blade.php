@extends('layouts.app')

@section('title', 'Mitarbeiter')

@php
use Illuminate\Support\Facades\DB;
// Statistiken
$totalCount = $employees->count();
$activeCount = $employees->where('is_active', true)->count();
$inactiveCount = $employees->where('is_active', false)->count();
$criticalCount = $statusCounts['critical'] ?? 0;
$warningCount = $statusCounts['warning'] ?? 0;
$balancedCount = $statusCounts['balanced'] ?? 0;
@endphp

@section('content')
<div style="width: 100%; margin: 0; padding: 0;">
    <!-- Ultra-kompakter Header (eine Zeile) -->
    <div class="card-header" style="padding: 12px 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; gap: 16px;">
            <!-- Links: Statistiken -->
            <div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
                <div style="background: #f3f4f6; padding: 6px 12px; border-radius: 8px; display: inline-flex; align-items: center; gap: 10px;">
                    <div style="display: inline-flex; align-items: center; gap: 4px;">
                        <span style="color: #6b7280; font-size: 11px; font-weight: 500; text-transform: uppercase;">Gesamt</span>
                        <span style="color: #111827; font-size: 14px; font-weight: 700;">{{ $totalCount }}</span>
                    </div>
                    <span style="color: #d1d5db;">·</span>
                    <div style="display: inline-flex; align-items: center; gap: 4px;">
                        <span style="color: #10b981; font-size: 11px; font-weight: 500; text-transform: uppercase;">Aktiv</span>
                        <span style="color: #10b981; font-size: 14px; font-weight: 700;">{{ $activeCount }}</span>
                    </div>
                    <span style="color: #d1d5db;">·</span>
                    <div style="display: inline-flex; align-items: center; gap: 4px;">
                        <span style="color: #9ca3af; font-size: 11px; font-weight: 500; text-transform: uppercase;">Inaktiv</span>
                        <span style="color: #9ca3af; font-size: 14px; font-weight: 700;">{{ $inactiveCount }}</span>
                    </div>
                </div>

                <!-- Auslastungs-Badges -->
                <div style="background: #f3f4f6; padding: 6px 12px; border-radius: 8px; display: inline-flex; align-items: center; gap: 10px;">
                    <div style="display: inline-flex; align-items: center; gap: 4px;">
                        <span style="color: #dc2626; font-size: 11px; font-weight: 500; text-transform: uppercase;">Überlastet</span>
                        <span style="color: #dc2626; font-size: 14px; font-weight: 700;">{{ $criticalCount }}</span>
                    </div>
                    <span style="color: #d1d5db;">·</span>
                    <div style="display: inline-flex; align-items: center; gap: 4px;">
                        <span style="color: #f59e0b; font-size: 11px; font-weight: 500; text-transform: uppercase;">Hoch</span>
                        <span style="color: #f59e0b; font-size: 14px; font-weight: 700;">{{ $warningCount }}</span>
                    </div>
                    <span style="color: #d1d5db;">·</span>
                    <div style="display: inline-flex; align-items: center; gap: 4px;">
                        <span style="color: #10b981; font-size: 11px; font-weight: 500; text-transform: uppercase;">OK</span>
                        <span style="color: #10b981; font-size: 14px; font-weight: 700;">{{ $balancedCount }}</span>
                    </div>
                </div>

                <!-- Filter Button -->
                <button id="toggleFiltersBtn" onclick="toggleFilterModal()" 
                        style="background: #ffffff; color: #374151; padding: 6px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.15s ease; display: inline-flex; align-items: center; gap: 6px;"
                        onmouseover="this.style.background='#f9fafb'; this.style.borderColor='#d1d5db'"
                        onmouseout="this.style.background='#ffffff'; this.style.borderColor='#e5e7eb'">
                    <span>🔍</span>
                    <span>Filter</span>
                    <span id="activeFilterCount" style="display: none; background: #3b82f6; color: white; border-radius: 10px; padding: 2px 6px; font-size: 11px; font-weight: 700;"></span>
                </button>
            </div>

            <!-- Rechts: Buttons -->
            <div style="display: flex; gap: 8px;">
                <a href="{{ route('employees.export') }}" class="btn btn-secondary btn-sm">Excel Export</a>
                <a href="{{ route('employees.import') }}" class="btn btn-secondary btn-sm">CSV Import</a>
                <a href="{{ route('employees.create') }}" class="btn btn-primary btn-sm">+ Neuer Mitarbeiter</a>
            </div>
        </div>
    </div>

    <!-- Filter Modal (versteckt) -->
    <div id="filterModalBackdrop" onclick="toggleFilterModal()" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 99998; backdrop-filter: blur(4px);"></div>
    
    <div id="filterModal" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 90%; max-width: 700px; background: white; padding: 24px; border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); z-index: 99999;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 2px solid #f3f4f6;">
            <h3 style="font-size: 18px; font-weight: 700; color: #111827; margin: 0;">🔍 Filter & Suche</h3>
            <button onclick="toggleFilterModal()" style="background: transparent; border: none; font-size: 24px; cursor: pointer; color: #9ca3af; padding: 4px;"
                    onmouseover="this.style.color='#374151'" onmouseout="this.style.color='#9ca3af'">✕</button>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
            <!-- Suche -->
            <div style="grid-column: span 2;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 6px; text-transform: uppercase;">Mitarbeiter suchen</label>
                <input id="filter-search" type="text" placeholder="Name eingeben..." class="form-input" style="width: 100%;">
            </div>

            <!-- Status -->
            <div>
                <label style="display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 6px; text-transform: uppercase;">Mitarbeiter-Status</label>
                <select id="filter-active-status" onchange="applyFilters()" class="form-select" style="width: 100%;">
                    <option value="active">Nur Aktive</option>
                    <option value="all">Alle</option>
                    <option value="inactive">Nur Inaktive</option>
                </select>
            </div>

            <!-- Auslastung -->
            <div>
                <label style="display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 6px; text-transform: uppercase;">Auslastungs-Status</label>
                <select id="filter-status" class="form-select" style="width: 100%;">
                    <option value="all">Alle Status</option>
                    <option value="critical">Überlastet ({{ $criticalCount }})</option>
                    <option value="warning">Hohe Auslastung ({{ $warningCount }})</option>
                    <option value="balanced">Im Soll ({{ $balancedCount }})</option>
                    <option value="underutilized">Unterlast ({{ $statusCounts['underutilized'] ?? 0 }})</option>
                    <option value="unknown">Ohne Daten ({{ $statusCounts['unknown'] ?? 0 }})</option>
                </select>
            </div>

            <!-- Abteilung -->
            <div>
                <label style="display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 6px; text-transform: uppercase;">Abteilung</label>
                <input id="filter-department" type="text" placeholder="Abteilung suchen..." class="form-input" style="width: 100%;">
            </div>

            <!-- Bottleneck -->
            <div>
                <label style="display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 6px; text-transform: uppercase;">Bottleneck</label>
                <label style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 14px; border: 1px solid #e5e7eb; border-radius: 8px; background: #f9fafb; cursor: pointer; font-size: 13px; color: #374151; width: 100%;">
                    <input type="checkbox" id="filter-bottleneck" style="width: 16px; height: 16px; accent-color: #dc2626;">
                    Nur kritische Mitarbeiter
                </label>
            </div>
        </div>

        <!-- Modal Footer -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 24px; padding-top: 16px; border-top: 1px solid #f3f4f6;">
            <button onclick="resetEmployeeFilters()" class="btn btn-ghost btn-sm">↺ Alle Filter zurücksetzen</button>
            <button onclick="toggleFilterModal()" class="btn btn-primary btn-sm">Schließen</button>
        </div>
    </div>

    @if(session('success'))
        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 12px; border-radius: 6px; margin-bottom: 16px;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('warning'))
        <div style="background: #ffffff; border: 1px solid #ea580c; border-left: 4px solid #ea580c; color: #ea580c; padding: 12px; border-radius: 6px; margin-bottom: 16px;">
            {{ session('warning') }}
        </div>
    @endif

    @if(session('error'))
        <div style="background: #ffffff; border: 1px solid #dc2626; border-left: 4px solid #dc2626; color: #dc2626; padding: 12px; border-radius: 6px; margin-bottom: 16px;">
            {{ session('error') }}
        </div>
    @endif

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
                    @forelse($employees as $employee)
                        @php
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
                        @endphp
                        <tr class="employee-row" data-status="{{ $employee->kpi_status_4w }}" data-bottleneck="{{ $employee->kpi_bottleneck ? '1' : '0' }}" data-department="{{ strtolower($employee->department) }}" data-search="{{ strtolower($employee->first_name . ' ' . $employee->last_name) }}" data-is-active="{{ $employee->is_active ? '1' : '0' }}" style="border-bottom: 1px solid #e5e7eb;">
                            <td style="padding: 12px;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #3b82f6, #6366f1); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 12px;">
                                        {{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div style="font-weight: 600; color: #111827;">
                                            {{ $employee->first_name }} {{ $employee->last_name }}
                                            @if(!$employee->is_active)
                                                <span style="background: #e5e7eb; color: #6b7280; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 500; margin-left: 6px;">⚪ Inaktiv</span>
                                            @endif
                                        </div>
                                        <div style="font-size: 12px; color: #6b7280;">Kapazität: {{ round($employee->moco_weekly_capacity) }}h/Woche</div>
                                        @if($employee->kpi_absence_alert && $employee->kpi_absence_summary)
                                            <div style="font-size: 11px; color: #b91c1c; margin-top: 4px;">{{ $employee->kpi_absence_summary }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 12px;">
                                <span style="background: #dbeafe; color: #1d4ed8; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500;">{{ $employee->department }}</span>
                            </td>
                            <td style="padding: 12px;">
                                @if($employee->kpi_available)
                                    <div style="font-weight: 600; color: {{ $statusColors[$employee->kpi_status_4w] ?? '#6b7280' }};">{{ $employee->kpi_util_4w }}%</div>
                                    <div style="font-size: 11px; color: #6b7280;">{{ $employee->kpi_hours_4w }}h</div>
                                @else
                                    <span style="font-size: 12px; color: #9ca3af;">Keine Daten</span>
                                @endif
                            </td>
                            <td style="padding: 12px;">
                                @if($employee->kpi_available)
                                    <div style="font-weight: 600; color: {{ $statusColors[$employee->kpi_status_12w] ?? '#6b7280' }};">{{ $employee->kpi_util_12w }}%</div>
                                    <div style="font-size: 11px; color: #6b7280;">{{ $employee->kpi_hours_12w }}h</div>
                                @else
                                    <span style="font-size: 12px; color: #9ca3af;">Keine Daten</span>
                                @endif
                            </td>
                            <td style="padding: 12px;">
                                @if($employee->kpi_available && $employee->kpi_top_project)
                                    <div style="font-weight: 600; color: #111827;">{{ $employee->kpi_top_project['name'] }}</div>
                                    <div style="font-size: 11px; color: #6b7280;">{{ $employee->kpi_top_project['hours'] }}h ({{ $employee->kpi_top_project['share'] }}%)</div>
                                @else
                                    <span style="font-size: 12px; color: #9ca3af;">Keine Daten</span>
                                @endif
                            </td>
                            <td style="padding: 12px;">
                                <span style="color: {{ $availabilityColor }}; font-size: 13px; font-weight: 600;">
                                    {{ $availabilityLabel }}
                                </span>
                            </td>
                            <td style="padding: 12px;">
                                <div style="display: flex; gap: 6px;">
                                    <a href="{{ route('employees.show', $employee) }}" style="background: #ffffff; color: #374151; padding: 6px 12px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 500; border: 1px solid #e5e7eb; transition: all 0.15s ease;">Anzeigen</a>
                                    <a href="{{ route('employees.edit', $employee) }}" style="background: #ffffff; color: #374151; padding: 6px 12px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 500; border: 1px solid #e5e7eb; transition: all 0.15s ease;">Bearbeiten</a>
                                    <form action="{{ route('employees.destroy', $employee) }}" method="POST" style="display: inline;" onsubmit="return confirm('🗑️ Mitarbeiter {{ $employee->first_name }} {{ $employee->last_name }} wirklich löschen?\n\n{{ $employee->source === 'moco' ? '⚠️ ACHTUNG: Dies ist ein MOCO-Mitarbeiter! Beim nächsten Sync wird er wieder synchronisiert.' : ($employee->source === 'manual' ? '✓ Dies ist ein manuell angelegter Mitarbeiter.' : 'Dies ist ein Test-Mitarbeiter.') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="background: #ffffff; color: #374151; padding: 6px 12px; border-radius: 8px; border: 1px solid #e5e7eb; font-size: 12px; font-weight: 500; cursor: pointer; transition: all 0.15s ease;">Löschen</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding: 40px; text-align: center; color: #6b7280;">Keine Mitarbeiter gefunden</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // ==================== FILTER MODAL ====================
    function toggleFilterModal() {
        const modal = document.getElementById('filterModal');
        const backdrop = document.getElementById('filterModalBackdrop');
        const isVisible = modal.style.display === 'block';
        
        modal.style.display = isVisible ? 'none' : 'block';
        backdrop.style.display = isVisible ? 'none' : 'block';
        document.body.style.overflow = isVisible ? '' : 'hidden';
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('filterModal');
            if (modal && modal.style.display === 'block') {
                toggleFilterModal();
            }
        }
    });

    function updateFilterButton() {
        const searchValue = document.getElementById('filter-search')?.value || '';
        const activeStatus = document.getElementById('filter-active-status')?.value || 'active';
        const status = document.getElementById('filter-status')?.value || 'all';
        const department = document.getElementById('filter-department')?.value || '';
        const bottleneck = document.getElementById('filter-bottleneck')?.checked || false;
        
        let activeFilters = 0;
        if (searchValue) activeFilters++;
        if (activeStatus !== 'active') activeFilters++;
        if (status !== 'all') activeFilters++;
        if (department) activeFilters++;
        if (bottleneck) activeFilters++;
        
        const btn = document.getElementById('toggleFiltersBtn');
        const countBadge = document.getElementById('activeFilterCount');
        
        if (activeFilters > 0) {
            btn.style.background = '#3b82f6';
            btn.style.color = '#ffffff';
            btn.style.borderColor = '#3b82f6';
            countBadge.textContent = activeFilters;
            countBadge.style.display = 'inline';
        } else {
            btn.style.background = '#ffffff';
            btn.style.color = '#374151';
            btn.style.borderColor = '#e5e7eb';
            countBadge.style.display = 'none';
        }
    }

    function resetEmployeeFilters() {
        document.getElementById('filter-search').value = '';
        document.getElementById('filter-active-status').value = 'active';
        document.getElementById('filter-status').value = 'all';
        document.getElementById('filter-department').value = '';
        document.getElementById('filter-bottleneck').checked = false;
        applyFilters();
        updateFilterButton();
    }

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
            
            // Filter-Button aktualisieren
            updateFilterButton();
        }

        [activeStatusFilter, statusFilter, bottleneckFilter].forEach(el => el.addEventListener('change', applyFilters));
        [departmentFilter, searchFilter].forEach(el => el.addEventListener('input', applyFilters));
        
        // Global verfügbar machen
        window.applyFilters = applyFilters;
        
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
@endsection