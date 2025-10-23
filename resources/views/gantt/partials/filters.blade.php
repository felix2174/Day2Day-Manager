{{-- Filterleiste für die Gantt-Ansicht --}}
<div style="margin-top: 20px; display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
    <form method="POST" action="{{ route('gantt.filter.update') }}" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
        @csrf
        <input type="hidden" name="sort" value="{{ $filters['sort'] ?? '' }}">
        <input type="hidden" name="custom_date_from" value="{{ $filters['custom_date_from'] ?? '' }}">
        <input type="hidden" name="custom_date_to" value="{{ $filters['custom_date_to'] ?? '' }}">
        <input type="hidden" name="zoom" value="{{ $currentZoom }}">

        <div style="display: flex; flex-direction: column;">
            <label style="font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase;">Status</label>
            <select name="status" onchange="this.form.submit()" style="padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 13px; min-width: 160px;">
                <option value="" {{ ($filters['status'] ?? '') === '' ? 'selected' : '' }}>Alle Status</option>
                <option value="in_bearbeitung" {{ ($filters['status'] ?? '') === 'in_bearbeitung' ? 'selected' : '' }}>In Bearbeitung</option>
                <option value="abgeschlossen" {{ ($filters['status'] ?? '') === 'abgeschlossen' ? 'selected' : '' }}>Abgeschlossen</option>
            </select>
        </div>

        <div style="display: flex; flex-direction: column;">
            <label style="font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase;">Zeitraum</label>
            <select name="timeframe" style="padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 13px; min-width: 160px;" onchange="this.form.submit()">
                <option value="" {{ ($filters['timeframe'] ?? '') === '' ? 'selected' : '' }}>Alle Zeiträume</option>
                <option value="current" {{ ($filters['timeframe'] ?? '') === 'current' ? 'selected' : '' }}>Aktive Projekte</option>
                <option value="future" {{ ($filters['timeframe'] ?? '') === 'future' ? 'selected' : '' }}>Zukünftig</option>
                <option value="past" {{ ($filters['timeframe'] ?? '') === 'past' ? 'selected' : '' }}>Abgeschlossen</option>
                <option value="this-month" {{ ($filters['timeframe'] ?? '') === 'this-month' ? 'selected' : '' }}>Dieser Monat</option>
                <option value="this-quarter" {{ ($filters['timeframe'] ?? '') === 'this-quarter' ? 'selected' : '' }}>Dieses Quartal</option>
                <option value="custom" {{ ($filters['timeframe'] ?? '') === 'custom' ? 'selected' : '' }}>Beliebiger Zeitraum</option>
            </select>
        </div>
        @if(($filters['timeframe'] ?? '') === 'custom')
        <div style="display: flex; flex-direction: column;">
            <label style="font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase;">Von</label>
            <input type="date" name="custom_date_from" value="{{ $filters['custom_date_from'] ?? '' }}" style="padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 13px; min-width: 160px;">
        </div>
        <div style="display: flex; flex-direction: column;">
            <label style="font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase;">Bis</label>
            <input type="date" name="custom_date_to" value="{{ $filters['custom_date_to'] ?? '' }}" style="padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 13px; min-width: 160px;">
        </div>
        @endif
        <div style="display: flex; flex-direction: column;">
            <label style="font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase;">Verantwortliche</label>
            <select name="employee" onchange="this.form.submit()" style="padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 13px; min-width: 200px;">
                <option value="" {{ ($filters['employee'] ?? '') === '' ? 'selected' : '' }}>Alle Verantwortlichen</option>
                @foreach($availableEmployees as $employee)
                    <option value="{{ $employee->id }}" {{ ($filters['employee'] ?? '') == $employee->id ? 'selected' : '' }}>
                        {{ $employee->first_name }} {{ $employee->last_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="display: flex; flex-direction: column;">
            <label style="font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase;">Suche</label>
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Name oder Projekt" style="padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 13px; min-width: 220px;" onblur="this.form.submit()">
        </div>
    </form>

    <form method="POST" action="{{ route('gantt.filter.reset') }}" style="margin-left: auto;">
        @csrf
        <button type="submit" style="padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 8px; background: #111827; font-size: 13px; color: #ffffff; cursor: pointer;">Filter zurücksetzen</button>
    </form>
</div>

@if(!empty($activeFilters))
    <div style="margin-top: 16px; display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
        <span style="color: #6b7280; font-size: 13px; font-weight: 500;">Aktive Filter:</span>
        @foreach($activeFilters as $filter)
            <span style="padding: 4px 10px; border-radius: 16px; border: 1px solid #e5e7eb; background: #f9fafb; font-size: 12px; color: #374151;">
                {{ $filter['label'] }}
            </span>
        @endforeach
    </div>
@endif
