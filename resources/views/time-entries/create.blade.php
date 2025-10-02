@extends('layouts.app')

@section('content')
<div class="container">
    <!-- Header -->
    <div style="margin-bottom: 24px;">
        <h1 style="font-size: 28px; font-weight: 700; color: #111827; margin: 0 0 8px 0;">Neuer Zeiteintrag</h1>
        <p style="color: #6b7280; margin: 0;">Erfassen Sie die Arbeitszeit für ein Projekt</p>
    </div>

    <!-- Formular -->
    <div style="background: white; border-radius: 12px; border: 1px solid #e5e7eb; padding: 24px;">
        <form method="POST" action="{{ route('time-entries.store') }}">
            @csrf
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px;">
                <!-- Mitarbeiter -->
                <div>
                    <label style="display: block; font-weight: 500; color: #374151; margin-bottom: 8px;">
                        Mitarbeiter <span style="color: #dc2626;">*</span>
                    </label>
                    <select name="employee_id" required style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; background: white; font-size: 14px;">
                        <option value="">Mitarbeiter auswählen</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ $employeeId == $employee->id ? 'selected' : '' }}>
                                {{ $employee->first_name }} {{ $employee->last_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('employee_id')
                        <div style="color: #dc2626; font-size: 14px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Projekt -->
                <div>
                    <label style="display: block; font-weight: 500; color: #374151; margin-bottom: 8px;">
                        Projekt <span style="color: #dc2626;">*</span>
                    </label>
                    <select name="project_id" required style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; background: white; font-size: 14px;">
                        <option value="">Projekt auswählen</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ $projectId == $project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('project_id')
                        <div style="color: #dc2626; font-size: 14px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Datum -->
                <div>
                    <label style="display: block; font-weight: 500; color: #374151; margin-bottom: 8px;">
                        Datum <span style="color: #dc2626;">*</span>
                    </label>
                    <input type="date" name="date" value="{{ $date }}" required style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                    @error('date')
                        <div style="color: #dc2626; font-size: 14px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Stunden -->
                <div>
                    <label style="display: block; font-weight: 500; color: #374151; margin-bottom: 8px;">
                        Stunden <span style="color: #dc2626;">*</span>
                    </label>
                    <input type="number" name="hours" step="0.1" min="0.1" max="24" placeholder="z.B. 7.5" required style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                    @error('hours')
                        <div style="color: #dc2626; font-size: 14px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Abrechenbar -->
                <div>
                    <label style="display: flex; align-items: center; gap: 8px; font-weight: 500; color: #374151;">
                        <input type="checkbox" name="billable" value="1" style="width: 16px; height: 16px;">
                        Abrechenbar
                    </label>
                    <div style="color: #6b7280; font-size: 14px; margin-top: 4px;">
                        Diese Stunden können dem Kunden in Rechnung gestellt werden
                    </div>
                </div>
            </div>

            <!-- Beschreibung -->
            <div style="margin-top: 24px;">
                <label style="display: block; font-weight: 500; color: #374151; margin-bottom: 8px;">
                    Beschreibung
                </label>
                <textarea name="description" rows="4" placeholder="Was wurde in dieser Zeit gemacht?" style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; resize: vertical;"></textarea>
                @error('description')
                    <div style="color: #dc2626; font-size: 14px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            <!-- Buttons -->
            <div style="display: flex; gap: 16px; margin-top: 32px; padding-top: 24px; border-top: 1px solid #e5e7eb;">
                <button type="submit" style="background: #2563eb; color: white; padding: 12px 24px; border: none; border-radius: 8px; font-weight: 500; cursor: pointer;">
                    Zeiteintrag erstellen
                </button>
                <a href="{{ route('time-entries.index') }}" style="background: #f3f4f6; color: #374151; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 500;">
                    Abbrechen
                </a>
            </div>
        </form>
    </div>
</div>
@endsection









