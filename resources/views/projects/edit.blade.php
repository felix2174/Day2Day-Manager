@extends('layouts.app')

@section('title', 'Projekt bearbeiten')

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
</style>

<div class="form-container">
    <div class="form-card">
        <div class="form-header">
            <h1 class="form-title">Projekt bearbeiten</h1>
            <p class="form-subtitle">Bearbeiten Sie die Projektinformationen</p>
        </div>

        <form method="POST" action="{{ route('projects.update', $project->id) }}">
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
                    <option value="planning" {{ $project->status == 'planning' ? 'selected' : '' }}>Planung</option>
                    <option value="active" {{ $project->status == 'active' ? 'selected' : '' }}>Aktiv</option>
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
                <input type="date" name="start_date" required value="{{ $project->start_date }}" class="form-input">
            </div>

            <div class="form-group">
                <label class="form-label">Enddatum</label>
                <input type="date" name="end_date" value="{{ $project->end_date }}" class="form-input">
            </div>

            <div class="form-group">
                <label class="form-label">Geschätzte Stunden</label>
                <input type="number" name="estimated_hours" min="0" step="1" value="{{ $project->estimated_hours }}" class="form-input" placeholder="z.B. 120">
                <div class="form-help">Geschätzte Gesamtstunden für das Projekt</div>
            </div>

            <div class="form-group">
                <label class="form-label">Stundensatz (€)</label>
                <input type="number" name="hourly_rate" min="0" step="0.01" value="{{ $project->hourly_rate }}" class="form-input" placeholder="z.B. 75.00">
                <div class="form-help">Stundensatz in Euro</div>
            </div>

            <div class="form-group">
                <label class="form-label">Fortschritt (%)</label>
                <input type="number" name="progress" min="0" max="100" value="{{ $project->progress }}" class="form-input" placeholder="z.B. 85">
                <div class="form-help">Aktueller Projektfortschritt in Prozent</div>
            </div>

            <div class="form-actions">
                <button type="submit" style="padding: 12px 24px; background: #ffffff; color: #374151;
                                            border: none; border-radius: 12px; cursor: pointer;
                                            font-size: 14px; font-weight: 500; transition: all 0.2s ease;
                                            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                                            onmouseover='this.style.transform=\"translateY(-1px)\"; this.style.boxShadow=\"0 4px 8px rgba(0, 0, 0, 0.15)\"; this.style.background=\"#f9fafb\";'
                                            onmouseout='this.style.transform=\"translateY(0)\"; this.style.boxShadow=\"0 2px 4px rgba(0, 0, 0, 0.1)\"; this.style.background=\"#ffffff\";'">
                    Änderungen speichern
                </button>
                <a href="{{ route('projects.show', $project) }}" style="padding: 12px 24px; background: #ffffff; color: #374151;
                                             border: none; border-radius: 12px; text-decoration: none;
                                             font-size: 14px; font-weight: 500; transition: all 0.2s ease;
                                             box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                                             onmouseover='this.style.transform=\"translateY(-1px)\"; this.style.boxShadow=\"0 4px 8px rgba(0, 0, 0, 0.15)\"; this.style.background=\"#f9fafb\";'
                                             onmouseout='this.style.transform=\"translateY(0)\"; this.style.boxShadow=\"0 2px 4px rgba(0, 0, 0, 0.1)\"; this.style.background=\"#ffffff\";'">
                    Abbrechen
                </a>
            </div>
        </form>
    </div>
</div>
@endsection