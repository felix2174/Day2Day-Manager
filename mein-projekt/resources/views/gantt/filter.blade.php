@extends('layouts.app')

@section('title', 'Gantt-Filter konfigurieren')

@section('content')
<style>
    .form-container {
        width: 100%;
        margin: 0;
        padding: 0;
    }
    .form-card {
        background: white;
        border-radius: 8px;
        padding: 24px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        border: 1px solid #e5e7eb;
    }
    .form-header {
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 1px solid #e5e7eb;
    }
    .form-title {
        font-size: 20px;
        font-weight: 600;
        color: #111827;
        margin: 0 0 8px 0;
    }
    .form-subtitle {
        color: #6b7280;
        font-size: 14px;
        margin: 0;
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-label {
        display: block;
        font-size: 14px;
        font-weight: 500;
        color: #374151;
        margin-bottom: 6px;
    }
    .form-input {
        width: 100%;
        padding: 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        transition: border-color 0.2s ease;
        box-sizing: border-box;
    }
    .form-input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    .form-help {
        font-size: 12px;
        color: #6b7280;
        margin-top: 4px;
    }
    .form-actions {
        margin-top: 24px;
        display: flex;
        gap: 12px;
    }
    .filter-section {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 20px;
    }
    .filter-section-title {
        font-size: 16px;
        font-weight: 600;
        color: #111827;
        margin: 0 0 12px 0;
    }
    .custom-date-range {
        display: none;
        gap: 12px;
        margin-top: 12px;
        padding: 12px;
        background: #f0f9ff;
        border: 1px solid #3b82f6;
        border-radius: 8px;
    }
    .custom-date-range.show {
        display: flex;
    }
    .date-input-group {
        flex: 1;
    }
    .filter-set-section {
        background: #f0f9ff;
        border: 2px solid #3b82f6;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 20px;
    }
    .filter-set-title {
        font-size: 16px;
        font-weight: 600;
        color: #1e40af;
        margin: 0 0 12px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .filter-set-list {
        display: grid;
        gap: 8px;
        margin-bottom: 16px;
    }
    .filter-set-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 8px 12px;
    }
    .filter-set-info {
        flex: 1;
        min-width: 0;
    }
    .filter-set-name {
        font-weight: 500;
        color: #111827;
        font-size: 14px;
    }
    .filter-set-description {
        color: #6b7280;
        font-size: 12px;
        margin-top: 2px;
    }
    .filter-set-actions {
        display: flex;
        gap: 6px;
        align-items: center;
    }
    .btn-small {
        padding: 4px 8px;
        font-size: 11px;
        border-radius: 4px;
        text-decoration: none;
        font-weight: 500;
        cursor: pointer;
        border: 1px solid;
        transition: all 0.2s ease;
    }
    .btn-load {
        background: #10b981;
        color: white;
        border-color: #10b981;
    }
    .btn-load:hover {
        background: #059669;
        border-color: #059669;
    }
    .btn-default {
        background: #f59e0b;
        color: white;
        border-color: #f59e0b;
    }
    .btn-default:hover {
        background: #d97706;
        border-color: #d97706;
    }
    .btn-delete {
        background: #ef4444;
        color: white;
        border-color: #ef4444;
    }
    .btn-delete:hover {
        background: #dc2626;
        border-color: #dc2626;
    }
    .save-set-form {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 12px;
        margin-top: 12px;
        display: none;
    }
    .save-set-form.show {
        display: block;
    }
    .default-badge {
        background: #fef3c7;
        color: #92400e;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
</style>

<div class="form-container">
    <div class="form-card">
        <div class="form-header">
            <h1 class="form-title">Gantt-Filter konfigurieren</h1>
            <p class="form-subtitle">Passen Sie die Filter für die Projekt-Timeline an</p>
            <div style="margin-top: 8px; display:flex; align-items:center; gap:8px;">
                @php $last = \Illuminate\Support\Facades\Cache::get('moco:last_refreshed_at'); @endphp
                <span style="font-size:12px; color:#6b7280;">MOCO-Stand: {{ $last ? \Carbon\Carbon::parse($last)->setTimezone('Europe/Berlin')->format('d.m.Y H:i') : 'unbekannt' }}</span>
                <form method="POST" action="{{ route('gantt.moco-refresh') }}" style="display:inline;">
                    @csrf
                    <button type="submit" title="MOCO-Cache aktualisieren" style="padding: 6px 10px; background:#ffffff; color:#374151; border:1px solid #e5e7eb; border-radius:8px; font-size:12px; cursor:pointer;">
                        Neu laden
                    </button>
                </form>
            </div>
        </div>

        <!-- Filter-Sets Management -->
        <div class="filter-set-section">
            <h3 class="filter-set-title">
                💾 Gespeicherte Filter-Sets
            </h3>
            
            @if($filterSets->count() > 0)
                <div class="filter-set-list">
                    @foreach($filterSets as $set)
                        <div class="filter-set-item">
                            <div class="filter-set-info">
                                <div class="filter-set-name">
                                    {{ $set->name }}
                                    @if($set->is_default)
                                        <span class="default-badge">Standard</span>
                                    @endif
                                </div>
                                @if($set->description)
                                    <div class="filter-set-description">{{ $set->description }}</div>
                                @endif
                            </div>
                            <div class="filter-set-actions">
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p style="color: #6b7280; font-style: italic; margin-bottom: 16px;">Noch keine Filter-Sets gespeichert.</p>
            @endif
            
                                
        <form method="POST" action="{{ route('gantt.filter.update') }}">
            @csrf

            <!-- Status-Filter -->
            <div class="filter-section">
                <h3 class="filter-section-title">Status-Filter</h3>
                <div class="form-group">
                    <label class="form-label">Projektstatus</label>
                    <select name="status" class="form-input">
                        <option value="">Alle Status anzeigen</option>
                        <option value="in_bearbeitung" {{ $filters['status'] == 'in_bearbeitung' ? 'selected' : '' }}>In Bearbeitung</option>
                        <option value="abgeschlossen" {{ $filters['status'] == 'abgeschlossen' ? 'selected' : '' }}>Abgeschlossen</option>
                    </select>
                    <div class="form-help">Zeigt nur Projekte mit dem ausgewählten Status an</div>
                </div>
            </div>

            <!-- Sortierung -->
            <div class="filter-section">
                <h3 class="filter-section-title">Sortierung</h3>
                <div class="form-group">
                    <label class="form-label">Sortierreihenfolge</label>
                    <select name="sort" class="form-input">
                        <option value="">Standard (nach Startdatum)</option>
                        <option value="name-asc" {{ $filters['sort'] == 'name-asc' ? 'selected' : '' }}>Name (A-Z)</option>
                        <option value="name-desc" {{ $filters['sort'] == 'name-desc' ? 'selected' : '' }}>Name (Z-A)</option>
                        <option value="date-start-asc" {{ $filters['sort'] == 'date-start-asc' ? 'selected' : '' }}>Startdatum (Früh-Spät)</option>
                        <option value="date-start-desc" {{ $filters['sort'] == 'date-start-desc' ? 'selected' : '' }}>Startdatum (Spät-Früh)</option>
                        <option value="date-end-asc" {{ $filters['sort'] == 'date-end-asc' ? 'selected' : '' }}>Enddatum (Früh-Spät)</option>
                        <option value="date-end-desc" {{ $filters['sort'] == 'date-end-desc' ? 'selected' : '' }}>Enddatum (Spät-Früh)</option>
                    </select>
                    <div class="form-help">Bestimmt die Reihenfolge der Projekte in der Timeline</div>
                </div>
            </div>

            <!-- Zugewiesene Mitarbeiter -->
            <div class="filter-section">
                <h3 class="filter-section-title">Zugewiesene Mitarbeiter</h3>
                <div class="form-group">
                    <label class="form-label">Zugewiesene Mitarbeiter</label>
                    <select name="employee" class="form-input">
                        <option value="">Alle Mitarbeiter anzeigen</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ ($filters['employee'] ?? '') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->first_name }} {{ $employee->last_name }} ({{ $employee->department }})
                            </option>
                        @endforeach
                    </select>
                    <div class="form-help">Zeigt nur Projekte an, bei denen der ausgewählte Mitarbeiter zugewiesen ist</div>
                </div>
            </div>

            <!-- Zeitraum-Filter -->
            <div class="filter-section">
                <h3 class="filter-section-title">Zeitraum-Filter</h3>
                <div class="form-group">
                    <label class="form-label">Zeitraum</label>
                    <select name="timeframe" id="timeframeSelect" class="form-input" onchange="toggleCustomDateRange()" {{ ($filters['employee'] ?? '') !== '' ? 'disabled' : '' }}>
                        <option value="">Alle Zeiträume anzeigen</option>
                        <option value="current" {{ $filters['timeframe'] == 'current' ? 'selected' : '' }}>Laufend (jetzt)</option>
                        <option value="future" {{ $filters['timeframe'] == 'future' ? 'selected' : '' }}>Zukünftig</option>
                        <option value="past" {{ $filters['timeframe'] == 'past' ? 'selected' : '' }}>Vergangen</option>
                        <option value="this-month" {{ $filters['timeframe'] == 'this-month' ? 'selected' : '' }}>Dieser Monat</option>
                        <option value="this-quarter" {{ $filters['timeframe'] == 'this-quarter' ? 'selected' : '' }}>Dieses Quartal</option>
                        <option value="custom" {{ $filters['timeframe'] == 'custom' ? 'selected' : '' }}>Beliebiger Zeitraum</option>
                    </select>
                    <div class="form-help">Begrenzt die Anzeige auf Projekte im gewählten Zeitraum</div>
                    @if(($filters['employee'] ?? '') !== '')
                        <div id="timeframeIgnoredHint" style="margin-top: 8px; padding: 8px 10px; background: #f3f4f6; color: #374151; border: 1px dashed #d1d5db; border-radius: 6px; font-size: 12px;">
                            Hinweis: Zeitraum wird ignoriert, da der Filter „Zugewiesene Mitarbeiter“ aktiv ist.
                        </div>
                    @else
                        <div id="timeframeIgnoredHint" style="display:none; margin-top: 8px; padding: 8px 10px; background: #f3f4f6; color: #374151; border: 1px dashed #d1d5db; border-radius: 6px; font-size: 12px;">
                            Hinweis: Zeitraum wird ignoriert, da der Filter „Zugewiesene Mitarbeiter“ aktiv ist.
                        </div>
                    @endif
                </div>

                <!-- Benutzerdefinierter Zeitraum -->
                <div id="customDateRange" class="custom-date-range {{ ($filters['employee'] ?? '') !== '' ? '' : ($filters['timeframe'] == 'custom' ? 'show' : '') }}">
                    <div class="date-input-group">
                        <label class="form-label">Von</label>
                        <input type="date" name="custom_date_from" value="{{ $filters['custom_date_from'] }}" class="form-input">
                    </div>
                    <div class="date-input-group">
                        <label class="form-label">Bis</label>
                        <input type="date" name="custom_date_to" value="{{ $filters['custom_date_to'] }}" class="form-input">
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" style="padding: 12px 24px; background: #10b981; color: white;
                                            border: none; border-radius: 12px; cursor: pointer;
                                            font-size: 14px; font-weight: 500; transition: all 0.2s ease;
                                            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                                            onmouseover='this.style.transform="translateY(-1px)"; this.style.boxShadow="0 4px 8px rgba(0, 0, 0, 0.15)"; this.style.background="#059669";'
                                            onmouseout='this.style.transform="translateY(0)"; this.style.boxShadow="0 2px 4px rgba(0, 0, 0, 0.1)"; this.style.background="#10b981";'>
                    Filter speichern
                </button>
                <a href="{{ route('gantt.index') }}" style="padding: 12px 24px; background: #ffffff; color: #374151;
                                             border: 1px solid #e5e7eb; border-radius: 12px; text-decoration: none;
                                             font-size: 14px; font-weight: 500; transition: all 0.2s ease;
                                             box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                                             onmouseover='this.style.transform="translateY(-1px)"; this.style.boxShadow="0 4px 8px rgba(0, 0, 0, 0.15)"; this.style.background="#f9fafb";'
                                             onmouseout='this.style.transform="translateY(0)"; this.style.boxShadow="0 2px 4px rgba(0, 0, 0, 0.1)"; this.style.background="#ffffff";'>
                    Abbrechen
                </a>
                <button type="button" onclick="document.getElementById('resetFiltersForm').submit();" style="padding: 12px 24px; background: #ffffff; color: #6b7280; border: 1px solid #e5e7eb; border-radius: 12px; cursor: pointer; font-size: 14px; font-weight: 500; transition: all 0.2s ease; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);"
                        onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 8px rgba(0, 0, 0, 0.15)'; this.style.background='#f9fafb'"
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(0, 0, 0, 0.1)'; this.style.background='#ffffff'">
                    Filter zurücksetzen
                </button>
            </div>
        </form>
        <!-- Verstecktes Formular für hartes Zurücksetzen, außerhalb des Update-Forms -->
        <form id="resetFiltersForm" method="POST" action="{{ route('gantt.filter.reset') }}" style="display:none;">
            @csrf
        </form>
        
    </div>
</div>

<script>
function toggleCustomDateRange() {
    const timeframeSelect = document.getElementById('timeframeSelect');
    const employeeSelect = document.querySelector('select[name="employee"]');
    const customDateRange = document.getElementById('customDateRange');
    
    if (employeeSelect && (employeeSelect.value || '').length > 0) {
        // Wenn Mitarbeiter aktiv ist, ist Zeitraum deaktiviert und ohne Custom-Range
        customDateRange.classList.remove('show');
        return;
    }
    if (timeframeSelect.value === 'custom') {
        customDateRange.classList.add('show');
    } else {
        customDateRange.classList.remove('show');
    }
    
    // Aktualisiere versteckte Felder für Set-Speicherung
    updateHiddenFields();
}

function resetFilters() {
    // Setze alle Formularfelder zurück
    const form = document.querySelector('form[action*="filter.update"]');
    form.reset();
    
    // Verstecke benutzerdefinierten Zeitraum
    document.getElementById('customDateRange').classList.remove('show');
    
    // Setze alle Select-Felder auf Standardwerte
    document.querySelector('select[name="status"]').value = '';
    document.querySelector('select[name="sort"]').value = '';
    document.querySelector('select[name="employee"]').value = '';
    document.querySelector('select[name="timeframe"]').value = '';
        document.querySelector('input[name="custom_date_from"]').value = '';
        document.querySelector('input[name="custom_date_to"]').value = '';
    
    // Aktualisiere versteckte Felder
    updateHiddenFields();

    // Zeitraum-Select wieder aktivieren (falls zuvor deaktiviert)
    const timeframeSelect = document.getElementById('timeframeSelect');
    if (timeframeSelect) timeframeSelect.disabled = false;

    // Kein automatischer Submit mehr – der sichtbare Reset-Button postet zur Reset-Route
}

function toggleSaveSetForm() {
    const form = document.getElementById('saveSetForm');
    const isVisible = form.classList.contains('show');
    
    if (isVisible) {
        form.classList.remove('show');
    } else {
        // Aktualisiere versteckte Felder mit aktuellen Werten
        updateHiddenFields();
        form.classList.add('show');
    }
}

function updateHiddenFields() {
    // Aktualisiere versteckte Felder mit aktuellen Filter-Werten
    document.getElementById('hiddenStatus').value = document.querySelector('select[name="status"]').value || '';
    document.getElementById('hiddenSort').value = document.querySelector('select[name="sort"]').value || '';
    document.getElementById('hiddenEmployee').value = document.querySelector('select[name="employee"]').value || '';
    document.getElementById('hiddenTimeframe').value = document.querySelector('select[name="timeframe"]').value || '';
    document.getElementById('hiddenCustomDateFrom').value = document.querySelector('input[name="custom_date_from"]').value || '';
    document.getElementById('hiddenCustomDateTo').value = document.querySelector('input[name="custom_date_to"]').value || '';
}

// Event Listener für alle Filter-Änderungen
document.addEventListener('DOMContentLoaded', function() {
    // Alle Filter-Felder überwachen
    const filterFields = document.querySelectorAll('select[name="status"], select[name="sort"], select[name="employee"], select[name="timeframe"], input[name="custom_date_from"], input[name="custom_date_to"]');
    
    filterFields.forEach(field => {
        field.addEventListener('change', updateHiddenFields);
    });
    
    // Initial versteckte Felder setzen
    updateHiddenFields();

    // Dynamischer Hinweis: Zeitraum wird ignoriert, wenn Mitarbeiter gesetzt ist
    const employeeSelect = document.querySelector('select[name="employee"]');
    const hint = document.getElementById('timeframeIgnoredHint');
    function updateTimeframeHint() {
        if (!employeeSelect || !hint) return;
        const hasEmployee = (employeeSelect.value || '').length > 0;
        hint.style.display = hasEmployee ? '' : 'none';
        // Zeitraum-Select aktiv/inaktiv schalten
        const timeframeSelect = document.getElementById('timeframeSelect');
        if (timeframeSelect) {
            timeframeSelect.disabled = hasEmployee;
        }
        // Custom-Date-Range entsprechend verbergen
        const cdr = document.getElementById('customDateRange');
        if (cdr && hasEmployee) {
            cdr.classList.remove('show');
        }
    }
    if (employeeSelect && hint) {
        employeeSelect.addEventListener('change', updateTimeframeHint);
        updateTimeframeHint();
    }
});
</script>
@endsection



