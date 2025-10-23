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
                <label style="display: block; margin-bottom: 5px;">Verantwortlicher</label>
                <select name="responsible_id" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="">Kein Verantwortlicher</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->first_name }} {{ $employee->last_name }} ({{ $employee->department }})</option>
                    @endforeach
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

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Geschätzte Stunden</label>
                <input type="number" name="estimated_hours" min="0" step="1"
                       style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
                       placeholder="z.B. 120">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Fortschritt (%)</label>
                <input type="number" name="progress" min="0" max="100" value="0"
                       style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>

            <div style="margin-top: 20px;">
                <button type="submit" style="padding: 12px 24px; background: #ffffff; color: #374151;
                                            border: none; border-radius: 12px; cursor: pointer; margin-right: 10px;
                                            font-size: 14px; font-weight: 500; transition: all 0.2s ease;
                                            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                                            onmouseover='this.style.transform=\"translateY(-1px)\"; this.style.boxShadow=\"0 4px 8px rgba(0, 0, 0, 0.15)\"; this.style.background=\"#f9fafb\";'
                                            onmouseout='this.style.transform=\"translateY(0)\"; this.style.boxShadow=\"0 2px 4px rgba(0, 0, 0, 0.1)\"; this.style.background=\"#ffffff\";'">
                    Projekt anlegen
                </button>
                <a href="/projects" style="padding: 12px 24px; background: #ffffff; color: #374151;
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
