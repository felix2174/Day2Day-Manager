@extends('layouts.app')

@section('title', 'Abwesenheit bearbeiten')

@section('content')
    <div class="card">
        <h2>Abwesenheit bearbeiten</h2>
        <p style="color: #6c757d;">
            Mitarbeiter: <strong>{{ $absence->employee->first_name }} {{ $absence->employee->last_name }}</strong>
        </p>

        <form method="POST" action="{{ route('absences.update', $absence->id) }}" style="margin-top: 20px;">
            @csrf
            @method('PUT')

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Abwesenheitstyp *</label>
                <select name="type" required style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="urlaub" {{ $absence->type == 'urlaub' ? 'selected' : '' }}>Urlaub</option>
                    <option value="krankheit" {{ $absence->type == 'krankheit' ? 'selected' : '' }}>Krankheit</option>
                    <option value="fortbildung" {{ $absence->type == 'fortbildung' ? 'selected' : '' }}>Fortbildung</option>
                </select>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Von *</label>
                <input type="date" name="start_date" required value="{{ $absence->start_date }}"
                       style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Bis *</label>
                <input type="date" name="end_date" required value="{{ $absence->end_date }}"
                       style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Grund/Bemerkung</label>
                <textarea name="reason" rows="3"
                          style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">{{ $absence->reason }}</textarea>
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
