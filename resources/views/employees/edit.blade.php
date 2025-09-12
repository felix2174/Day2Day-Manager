@extends('layouts.app')

@section('title', 'Mitarbeiter bearbeiten')

@section('content')
    <div class="card">
        <h2>Mitarbeiter bearbeiten</h2>

        <form method="POST" action="/employees/{{ $employee->id }}" style="margin-top: 20px;">
            @csrf
            @method('PUT')

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Vorname *</label>
                <input type="text" name="first_name" required maxlength="50" value="{{ $employee->first_name }}"
                       style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Nachname *</label>
                <input type="text" name="last_name" required maxlength="50" value="{{ $employee->last_name }}"
                       style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Abteilung *</label>
                <input type="text" name="department" required maxlength="100" value="{{ $employee->department }}"
                       style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Wochenkapazität (Stunden) *</label>
                <input type="number" name="weekly_capacity" required min="1" max="60" value="{{ $employee->weekly_capacity }}"
                       style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>

            <div style="margin-top: 20px;">
                <button type="submit" style="padding: 10px 30px; background: #28a745; color: white;
                                            border: none; border-radius: 4px; cursor: pointer; margin-right: 10px;">
                    Änderungen speichern
                </button>
                <a href="/employees" style="padding: 10px 30px; background: #6c757d; color: white;
                                           border-radius: 4px; text-decoration: none; display: inline-block;">
                    Abbrechen
                </a>
            </div>
        </form>
    </div>
@endsection
