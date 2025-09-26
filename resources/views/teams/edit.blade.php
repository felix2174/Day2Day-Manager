@extends('layouts.app')

@section('content')
<div style="width: 100%; margin: 0; padding: 0;">
    <div style="background: white; padding: 20px; margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="font-size: 24px; font-weight: bold; color: #111827; margin: 0;">Team bearbeiten</h1>
                <p style="color: #6b7280; margin: 5px 0 0 0;">Bearbeiten Sie die Informationen für {{ $team->name }}</p>
            </div>
            <a href="{{ route('teams.show', $team) }}" style="background: #ffffff; color: #374151; padding: 10px 20px; border-radius: 12px; text-decoration: none; font-size: 14px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">Zurück</a>
        </div>
    </div>

    <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px;">
        <form action="{{ route('teams.update', $team) }}" method="POST" style="max-width: 800px;">
            @csrf
            @method('PUT')

            <div style="margin-bottom: 15px;">
                <label for="name" style="display:block; margin-bottom:5px; font-weight:600; color:#374151;">Team-Name *</label>
                <input id="name" name="name" type="text" value="{{ old('name', $team->name) }}" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label for="department" style="display:block; margin-bottom:5px; font-weight:600; color:#374151;">Abteilung *</label>
                <select id="department" name="department" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="">Abteilung auswählen</option>
                    <option value="IT" {{ old('department', $team->department) == 'IT' ? 'selected' : '' }}>IT</option>
                    <option value="Management" {{ old('department', $team->department) == 'Management' ? 'selected' : '' }}>Management</option>
                    <option value="Support" {{ old('department', $team->department) == 'Support' ? 'selected' : '' }}>Support</option>
                    <option value="Design" {{ old('department', $team->department) == 'Design' ? 'selected' : '' }}>Design</option>
                    <option value="Marketing" {{ old('department', $team->department) == 'Marketing' ? 'selected' : '' }}>Marketing</option>
                    <option value="Sales" {{ old('department', $team->department) == 'Sales' ? 'selected' : '' }}>Sales</option>
                    <option value="HR" {{ old('department', $team->department) == 'HR' ? 'selected' : '' }}>HR</option>
                    <option value="Finance" {{ old('department', $team->department) == 'Finance' ? 'selected' : '' }}>Finance</option>
                </select>
            </div>

            <div style="margin-bottom: 15px;">
                <label for="description" style="display:block; margin-bottom:5px; font-weight:600; color:#374151;">Beschreibung</label>
                <textarea id="description" name="description" rows="4" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">{{ old('description', $team->description) }}</textarea>
            </div>

            <div style="margin-top: 20px; display:flex; gap:10px;">
                <button type="submit" style="padding: 12px 24px; background: #ffffff; color: #374151; border: none; border-radius: 12px; cursor: pointer; font-size: 14px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">Änderungen speichern</button>
                <a href="{{ route('teams.show', $team) }}" style="padding: 12px 24px; background: #ffffff; color: #374151; border: none; border-radius: 12px; text-decoration: none; display:inline-block; font-size:14px; box-shadow:0 2px 4px rgba(0,0,0,0.1);">Abbrechen</a>
            </div>
        </form>
    </div>
</div>
@endsection
