@extends('layouts.app')

@section('title', 'Neuer Mitarbeiter')

@section('content')
    <div class="card">
        <h2>Neuen Mitarbeiter anlegen</h2>

        <form method="POST" action="/employees" style="margin-top: 20px;">
            @csrf

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Vorname *</label>
                <input type="text" name="first_name" required maxlength="50"
                       style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Nachname *</label>
                <input type="text" name="last_name" required maxlength="50"
                       style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Abteilung *</label>
                <input type="text" name="department" required maxlength="100"
                       style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Wochenkapazität (Stunden) *</label>
                <input type="number" name="weekly_capacity" required min="1" max="60" value="40"
                       style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                <small style="display: block; color: #6c757d; margin-top: 5px;">
                    Vollzeit: 40h | Teilzeit: 20-35h | Minijob: &lt;20h
                </small>
            </div>

            <div style="margin-top: 20px;">
                <button type="submit" style="padding: 10px 30px; background: #28a745; color: white;
                                            border: none; border-radius: 4px; cursor: pointer; margin-right: 10px;">
                    Mitarbeiter anlegen
                </button>
                <a href="/employees" style="padding: 10px 30px; background: #6c757d; color: white;
                                           border-radius: 4px; text-decoration: none; display: inline-block;">
                    Abbrechen
                </a>
            </div>
        </form>
    </div>
@endsection
