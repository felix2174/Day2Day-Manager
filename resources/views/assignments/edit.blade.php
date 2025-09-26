@extends('layouts.app')

@section('title', 'Zuweisung bearbeiten')

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
    .alert {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 14px;
    }
    .alert-error {
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }
</style>

<div class="form-container">
    @if(session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    <div class="form-card">
        <div class="form-header">
            <h1 class="form-title">Zuweisung bearbeiten</h1>
            <p class="form-subtitle">
                Mitarbeiter: <strong>{{ $assignment->employee->first_name }} {{ $assignment->employee->last_name }}</strong><br>
                Projekt: <strong>{{ $assignment->project->name }}</strong>
            </p>
        </div>

        <form method="POST" action="{{ route('assignments.update', $assignment->id) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label">Wochenstunden *</label>
                <input type="number" name="weekly_hours" required min="1" max="60" value="{{ $assignment->weekly_hours }}" class="form-input">
                <div class="form-help">
                    Freie Kapazität: {{ $assignment->employee->weekly_capacity - $assignment->employee->assignments->where('id', '!=', $assignment->id)->sum('weekly_hours') }}h
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Priorität *</label>
                <select name="priority_level" required class="form-input">
                    <option value="low" {{ $assignment->priority_level == 'low' ? 'selected' : '' }}>Niedrig</option>
                    <option value="medium" {{ $assignment->priority_level == 'medium' ? 'selected' : '' }}>Mittel</option>
                    <option value="high" {{ $assignment->priority_level == 'high' ? 'selected' : '' }}>Hoch</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Startdatum *</label>
                <input type="date" name="start_date" required value="{{ $assignment->start_date }}" class="form-input">
            </div>

            <div class="form-group">
                <label class="form-label">Enddatum</label>
                <input type="date" name="end_date" value="{{ $assignment->end_date }}" class="form-input">
            </div>

            <div style="margin-top: 20px;">
                <button type="submit" style="padding: 12px 24px; background: #ffffff; color: #374151;
                                            border: none; border-radius: 12px; cursor: pointer; margin-right: 10px;
                                            font-size: 14px; font-weight: 500; transition: all 0.2s ease;
                                            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                                            onmouseover='this.style.transform=\"translateY(-1px)\"; this.style.boxShadow=\"0 4px 8px rgba(0, 0, 0, 0.15)\"; this.style.background=\"#f9fafb\";'
                                            onmouseout='this.style.transform=\"translateY(0)\"; this.style.boxShadow=\"0 2px 4px rgba(0, 0, 0, 0.1)\"; this.style.background=\"#ffffff\";'">
                    Änderungen speichern
                </button>
                <a href="/assignments" style="padding: 12px 24px; background: #ffffff; color: #374151;
                                             border: none; border-radius: 12px; text-decoration: none; display: inline-block;
                                             font-size: 14px; font-weight: 500; transition: all 0.2s ease;
                                             box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                                             onmouseover='this.style.transform=\"translateY(-1px)\"; this.style.boxShadow=\"0 4px 8px rgba(0, 0, 0, 0.15)\"; this.style.background=\"#f9fafb\";'
                                             onmouseout='this.style.transform=\"translateY(0)\"; this.style.boxShadow=\"0 2px 4px rgba(0, 0, 0, 0.1)\"; this.style.background=\"#ffffff\";'">
                    Abbrechen
                </a>
            </div>
        </form>
    </div>
@endsection
