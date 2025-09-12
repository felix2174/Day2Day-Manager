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
                <button type="submit" style="padding: 10px 30px; background: #28a745; color: white;
                                            border: none; border-radius: 4px; cursor: pointer; margin-right: 10px;">
                    Abwesenheit eintragen
                </button>
                <a href="/absences" style="padding: 10px 30px; background: #6c757d; color: white;
                                          border-radius: 4px; text-decoration: none; display: inline-block;">
                    Abbrechen
                </a>
            </div>
        </form>
    </div>
@endsection
