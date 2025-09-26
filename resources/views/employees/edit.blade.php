@extends('layouts.app')

@section('title', 'Mitarbeiter bearbeiten')

@section('content')
    <div class="card">
        <h2>Mitarbeiter bearbeiten</h2>

        <form method="POST" action="{{ route('employees.update', $employee->id) }}" style="margin-top: 20px;">
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
                <input type="number" name="weekly_capacity" required min="1" max="40" value="{{ $employee->weekly_capacity }}"
                       style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>

            <div style="margin-top: 20px;">
                <button type="submit"
                        style="padding: 12px 24px; background: #ffffff; color: #374151;
                   border: none; border-radius: 12px; cursor: pointer;
                   margin-right: 10px; font-size: 14px; font-family: inherit;
                   box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s ease;"
                        onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 8px rgba(0, 0, 0, 0.15)'; this.style.background='#f9fafb';"
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(0, 0, 0, 0.1)'; this.style.background='#ffffff';">
                    Änderungen speichern
                </button>
                <a href="/employees"
                   style="padding: 12px 24px; background: #ffffff; color: #374151;
              border: none; border-radius: 12px; text-decoration: none;
              display: inline-block; font-size: 14px;
              box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s ease;"
                   onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 8px rgba(0, 0, 0, 0.15)'; this.style.background='#f9fafb';"
                   onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(0, 0, 0, 0.1)'; this.style.background='#ffffff';">
                    Abbrechen
                </a>
            </div>
        </form>
    </div>
@endsection
