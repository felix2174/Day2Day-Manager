@extends('layouts.app')

@section('title', 'Zuweisung bearbeiten')

@section('content')
    @if(session('error'))
        <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 20px;">
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <h2>Zuweisung bearbeiten</h2>
        <p style="color: #6c757d;">
            Mitarbeiter: <strong>{{ $assignment->employee->first_name }} {{ $assignment->employee->last_name }}</strong><br>
            Projekt: <strong>{{ $assignment->project->name }}</strong>
        </p>

        <form method="POST" action="/assignments/{{ $assignment->id }}" style="margin-top: 20px;">
            @csrf
            @method('PUT')

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Wochenstunden *</label>
                <input type="number" name="weekly_hours" required min="1" max="60" value="{{ $assignment->weekly_hours }}"
                       style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                <small style="display: block; color: #6c757d; margin-top: 5px;">
                    Freie Kapazität: {{ $assignment->employee->weekly_capacity - $assignment->employee->assignments->where('id', '!=', $assignment->id)->sum('weekly_hours') }}h
                </small>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Priorität *</label>
                <select name="priority_level" required style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="low" {{ $assignment->priority_level == 'low' ? 'selected' : '' }}>Niedrig</option>
                    <option value="medium" {{ $assignment->priority_level == 'medium' ? 'selected' : '' }}>Mittel</option>
                    <option value="high" {{ $assignment->priority_level == 'high' ? 'selected' : '' }}>Hoch</option>
                </select>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Startdatum *</label>
                <input type="date" name="start_date" required value="{{ $assignment->start_date }}"
                       style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Enddatum</label>
                <input type="date" name="end_date" value="{{ $assignment->end_date }}"
                       style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>

            <div style="margin-top: 20px;">
                <button type="submit" style="padding: 10px 30px; background: #28a745; color: white;
                                            border: none; border-radius: 4px; cursor: pointer; margin-right: 10px;">
                    Änderungen speichern
                </button>
                <a href="/assignments" style="padding: 10px 30px; background: #6c757d; color: white;
                                             border-radius: 4px; text-decoration: none; display: inline-block;">
                    Abbrechen
                </a>
            </div>
        </form>
    </div>
@endsection
