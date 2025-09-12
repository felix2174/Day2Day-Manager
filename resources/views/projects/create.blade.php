@extends('layouts.app')

@section('title', 'Neues Projekt')

@section('content')
    <div class="card">
        <h2>Neues Projekt anlegen</h2>

        <form method="POST" action="/projects" style="margin-top: 20px;">
            @csrf

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Projektname *</label>
                <input type="text" name="name" required maxlength="100"
                       style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Beschreibung</label>
                <textarea name="description" rows="3"
                          style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"></textarea>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Status *</label>
                <select name="status" required style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="planning">Planung</option>
                    <option value="active" selected>Aktiv</option>
                    <option value="completed">Abgeschlossen</option>
                </select>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Startdatum *</label>
                <input type="date" name="start_date" required value="{{ date('Y-m-d') }}"
                       style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Enddatum</label>
                <input type="date" name="end_date"
                       style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>

            <div style="margin-top: 20px;">
                <button type="submit" style="padding: 10px 30px; background: #28a745; color: white;
                                            border: none; border-radius: 4px; cursor: pointer; margin-right: 10px;">
                    Projekt anlegen
                </button>
                <a href="/projects" style="padding: 10px 30px; background: #6c757d; color: white;
                                          border-radius: 4px; text-decoration: none; display: inline-block;">
                    Abbrechen
                </a>
            </div>
        </form>
    </div>
@endsection
