@extends('layouts.app')

@section('title', 'Mitarbeiter Import')

@section('content')
<div style="width: 100%; margin: 0; padding: 0;">
    <!-- Page Header -->
    <div style="background: white; padding: 20px; margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="font-size: 24px; font-weight: bold; color: #111827; margin: 0;">Mitarbeiter Import</h1>
                <p style="color: #6b7280; margin: 5px 0 0 0;">Importieren Sie Mitarbeiter aus einer CSV-Datei</p>
            </div>
            <a href="{{ route('employees.index') }}" style="background: #ffffff; color: #374151; padding: 10px 20px; border-radius: 12px; text-decoration: none; font-size: 14px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">Zurück</a>
        </div>
    </div>

    <!-- Import Form -->
    <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px;">
        <form action="{{ route('employees.import.process') }}" method="POST" enctype="multipart/form-data" style="max-width: 600px;">
            @csrf
            
            <div style="margin-bottom: 20px;">
                <label for="file" style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">CSV-Datei auswählen *</label>
                <input type="file" id="file" name="file" accept=".csv,.txt" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                <p style="color: #6b7280; font-size: 12px; margin-top: 4px;">Maximale Dateigröße: 2MB. Unterstützte Formate: CSV, TXT</p>
            </div>

            <div style="background: #f9fafb; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
                <h3 style="font-size: 16px; font-weight: 600; color: #111827; margin: 0 0 10px 0;">CSV-Format</h3>
                <p style="color: #6b7280; font-size: 14px; margin: 0 0 10px 0;">Die CSV-Datei muss folgende Spalten enthalten (getrennt durch Semikolon):</p>
                <div style="font-family: monospace; font-size: 12px; color: #374151; background: white; padding: 10px; border-radius: 4px; border: 1px solid #e5e7eb;">
                    Vorname;Nachname;Abteilung;Wochenkapazität;Aktiv;E-Mail
                </div>
                <p style="color: #6b7280; font-size: 12px; margin: 8px 0 0 0;">Beispiel: Max;Mustermann;IT;40;1;max.mustermann@example.com</p>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" style="background: #3b82f6; color: white; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 500;">📥 Importieren</button>
                <a href="{{ route('employees.index') }}" style="background: #ffffff; color: #374151; padding: 12px 24px; border: 1px solid #d1d5db; border-radius: 8px; text-decoration: none; font-size: 14px; display: inline-block;">Abbrechen</a>
            </div>
        </form>
    </div>
</div>
@endsection
