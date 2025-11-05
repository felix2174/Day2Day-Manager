@extends('layouts.app')

@section('title', 'Projekt bearbeiten')

@section('content')
<style>
    .form-container {
        width: 100%;
        margin: 0;
        padding: 20px;
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
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .form-header-left {
        flex: 1;
    }
    .form-header-actions {
        display: flex;
        gap: 12px;
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
    .form-textarea {
        width: 100%;
        padding: 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        transition: border-color 0.2s ease;
        resize: vertical;
        min-height: 80px;
        box-sizing: border-box;
    }
    .form-textarea:focus {
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
    /* Multi-Select Styling */
    select[multiple] option {
        padding: 8px 12px !important;
        border-radius: 4px;
        margin: 2px 0;
    }
    select[multiple] option:checked {
        background: linear-gradient(135deg, #3b82f6, #6366f1) !important;
        color: white !important;
        font-weight: 600;
    }
    select[multiple] option:hover {
        background: #f0f9ff !important;
    }
    /* Checkbox List Styling */
    .checkbox-list {
        max-height: 300px;
        overflow-y: auto;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 12px;
        background: #f9fafb;
    }
    .checkbox-item {
        display: flex;
        align-items: center;
        padding: 10px 12px;
        margin-bottom: 6px;
        background: white;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
        border: 1px solid #e5e7eb;
    }
    .checkbox-item:hover {
        background: #f0f9ff;
        border-color: #3b82f6;
    }
    .checkbox-item input[type="checkbox"] {
        width: 18px;
        height: 18px;
        margin-right: 12px;
        cursor: pointer;
        accent-color: #3b82f6;
    }
    .checkbox-item label {
        cursor: pointer;
        flex: 1;
        font-size: 14px;
        color: #374151;
        margin: 0;
    }
    .checkbox-item.checked {
        background: #eff6ff;
        border-color: #3b82f6;
    }
    .checkbox-item.checked label {
        font-weight: 600;
        color: #1e40af;
    }
    .header-btn {
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .header-btn-back {
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #e5e7eb;
    }
    .header-btn-back:hover {
        background: #e5e7eb;
    }
    .header-btn-save {
        background: #3b82f6;
        color: white;
        box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
    }
    .header-btn-save:hover {
        background: #2563eb;
        box-shadow: 0 4px 8px rgba(59, 130, 246, 0.4);
        transform: translateY(-1px);
    }
</style>

<div class="form-container">
    <div class="form-card">
        <div class="form-header">
            <div class="form-header-left">
                <h1 class="form-title">Projekt bearbeiten</h1>
                <p class="form-subtitle">Bearbeiten Sie die Projektinformationen</p>
            </div>
            <div class="form-header-actions">
                <a href="{{ route('projects.show', $project) }}" class="header-btn header-btn-back">
                    ← Zurück
                </a>
                <button type="submit" form="project-form" class="header-btn header-btn-save">
                    ✓ Speichern
                </button>
            </div>
        </div>

        <form method="POST" action="{{ route('projects.update', $project->id) }}" id="project-form">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label">Projektname *</label>
                <input type="text" name="name" required maxlength="100" value="{{ $project->name }}" class="form-input">
            </div>

            <div class="form-group">
                <label class="form-label">Beschreibung</label>
                <textarea name="description" rows="3" class="form-textarea">{{ $project->description }}</textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Status *</label>
                <select name="status" required class="form-input">
                    <option value="active" {{ $project->status == 'active' ? 'selected' : '' }}>In Bearbeitung</option>
                    <option value="completed" {{ $project->status == 'completed' ? 'selected' : '' }}>Abgeschlossen</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Verantwortlicher</label>
                <select name="responsible_id" class="form-input">
                    <option value="">Kein Verantwortlicher</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" {{ $project->responsible_id == $employee->id ? 'selected' : '' }}>
                            {{ $employee->first_name }} {{ $employee->last_name }} ({{ $employee->department }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Startdatum *</label>
                <input type="date" name="start_date" required value="{{ $project->start_date ? $project->start_date->format('Y-m-d') : '' }}" class="form-input">
            </div>

            <div class="form-group">
                <label class="form-label">Enddatum</label>
                <input type="date" name="end_date" value="{{ $project->end_date ? $project->end_date->format('Y-m-d') : '' }}" class="form-input">
            </div>

            <div class="form-group">
                <label class="form-label">Geschätzte Stunden</label>
                <input type="number" name="estimated_hours" min="0" step="1" value="{{ $project->estimated_hours ?? '' }}" class="form-input" placeholder="z.B. 120">
                <div class="form-help">Geschätzte Gesamtstunden für das Projekt</div>
            </div>

            <div class="form-group">
                <label class="form-label">Stundensatz (€)</label>
                <input type="number" name="hourly_rate" min="0" step="0.01" value="{{ $project->hourly_rate ?? '' }}" class="form-input" placeholder="z.B. 75.00">
                <div class="form-help">Stundensatz in Euro</div>
            </div>

            <div class="form-group">
                <label class="form-label">Fortschritt (%)</label>
                <input type="number" name="progress" min="0" max="100" value="{{ $project->progress ?? '' }}" class="form-input" placeholder="z.B. 85">
                <div class="form-help">Aktueller Projektfortschritt in Prozent</div>
            </div>

            <!-- ========== MITARBEITER-ZUWEISUNG MIT CHECKBOXEN ========== -->
            <div class="form-group">
                <label class="form-label">Zugewiesene Mitarbeiter</label>
                <div class="checkbox-list">
                    @foreach($employees as $employee)
                        <div class="checkbox-item {{ in_array($employee->id, $assignedEmployeeIds) ? 'checked' : '' }}" onclick="toggleCheckbox(this)">
                            <input 
                                type="checkbox" 
                                name="employee_ids[]" 
                                value="{{ $employee->id }}"
                                id="employee_{{ $employee->id }}"
                                {{ in_array($employee->id, $assignedEmployeeIds) ? 'checked' : '' }}
                                onclick="event.stopPropagation();">
                            <label for="employee_{{ $employee->id }}">
                                {{ $employee->first_name }} {{ $employee->last_name }} 
                                <span style="color: #6b7280; font-size: 12px;">({{ $employee->department }})</span>
                            </label>
                        </div>
                    @endforeach
                </div>
                <div class="form-help">
                    ✓ Setzen Sie ein Häkchen bei jedem Mitarbeiter, der dem Projekt zugewiesen werden soll.<br>
                    Aktuell zugewiesen: <strong><span id="selected-count">{{ count($assignedEmployeeIds) }}</span></strong> {{ count($assignedEmployeeIds) === 1 ? 'Mitarbeiter' : 'Mitarbeiter' }}
                </div>
            </div>

            <script>
                function toggleCheckbox(div) {
                    const checkbox = div.querySelector('input[type="checkbox"]');
                    checkbox.checked = !checkbox.checked;
                    updateCheckboxState(div, checkbox.checked);
                    updateCounter();
                }

                function updateCheckboxState(div, isChecked) {
                    if (isChecked) {
                        div.classList.add('checked');
                    } else {
                        div.classList.remove('checked');
                    }
                }

                function updateCounter() {
                    const checkedCount = document.querySelectorAll('.checkbox-item input[type="checkbox"]:checked').length;
                    document.getElementById('selected-count').textContent = checkedCount;
                }

                // Initialize on page load
                document.addEventListener('DOMContentLoaded', function() {
                    // Checkbox functionality
                    document.querySelectorAll('.checkbox-item input[type="checkbox"]').forEach(checkbox => {
                        checkbox.addEventListener('change', function() {
                            updateCheckboxState(this.closest('.checkbox-item'), this.checked);
                            updateCounter();
                        });
                    });
                });
            </script>
        </form>
    </div>
</div>
@endsection