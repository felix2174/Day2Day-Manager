@extends('layouts.app')

@section('title', 'Neue Abwesenheit')

@section('content')
    <div class="card">
        <h2>Neue Abwesenheit eintragen</h2>

        <form method="POST" action="/absences" style="margin-top: 20px;">
            @csrf

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Mitarbeiter *</label>
                <select name="employee_id" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="">-- Mitarbeiter wählen --</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}">
                            {{ $employee->first_name }} {{ $employee->last_name }} ({{ $employee->department }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Abwesenheitstyp *</label>
                <select name="type" required style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="urlaub">Urlaub</option>
                    <option value="krankheit">Krankheit</option>
                    <option value="fortbildung">Fortbildung</option>
                </select>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Von *</label>
                <input type="date" name="start_date" required
                       style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Bis *</label>
                <input type="date" name="end_date" required
                       style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Grund/Bemerkung</label>
                <textarea name="reason" rows="3"
                          style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"></textarea>
            </div>

            <div style="margin-top: 20px;">
                <button type="submit" style="padding: 12px 24px; background: #ffffff; color: #374151;
                                            border: none; border-radius: 12px; cursor: pointer; margin-right: 10px;
                                            font-size: 14px; font-weight: 500; transition: all 0.2s ease;
                                            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                                            onmouseover='this.style.transform=\"translateY(-1px)\"; this.style.boxShadow=\"0 4px 8px rgba(0, 0, 0, 0.15)\"; this.style.background=\"#f9fafb\";'
                                            onmouseout='this.style.transform=\"translateY(0)\"; this.style.boxShadow=\"0 2px 4px rgba(0, 0, 0, 0.1)\"; this.style.background=\"#ffffff\";'">
                    Abwesenheit eintragen
                </button>
                <a href="/absences" style="padding: 12px 24px; background: #ffffff; color: #374151;
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
