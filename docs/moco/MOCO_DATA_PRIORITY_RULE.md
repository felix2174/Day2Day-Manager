# MOCO-Datenpriorität Regel

## Grundprinzip: MOCO-Daten haben IMMER Vorrang

### Hauptregel
**ALLE Daten aus MOCO haben grundsätzlich Vorrang vor lokalen Datenbank-Daten.**

Dieses Projektverwaltungstool dient als Frontend für MOCO-Daten. Unabhängig davon, ob bereits Daten in der lokalen Datenbank hinterlegt sind, gilt:

**MOCO ist die Single Source of Truth (einzige Quelle der Wahrheit).**

### Implementierungsrichtlinien

#### 1. Datenanzeige
- **IMMER** zuerst MOCO-Daten prüfen und anzeigen
- Lokale Datenbank-Daten dienen nur als Cache/Fallback
- Wenn MOCO-Daten verfügbar sind, werden lokale Daten ignoriert

#### 2. Daten-Reihenfolge (Priorität)
```
1. MOCO API-Daten (höchste Priorität)
2. Lokale Datenbank (nur als Fallback)
3. Standardwerte (nur wenn nichts anderes verfügbar)
```

#### 3. Spezifische Felder
- **Status:** Ausschließlich aus MOCO `finish_date` berechnen
- **Erstellungsdatum:** MOCO `created_at`, nicht lokales `created_at`
- **Projektnamen:** MOCO `name`
- **Zeiträume:** MOCO `start_date` und `finish_date`
- **Stundensätze:** MOCO `hourly_rate`
- **Team-Zuweisungen:** MOCO `contracts` (Personen & Teams)
- **Aufgaben:** MOCO `tasks`
- **Verantwortliche:** MOCO `leader`

#### 4. Code-Beispiele

**RICHTIG:**
```php
// MOCO-Daten haben Vorrang
if ($mocoData && isset($mocoData['created_at'])) {
    $createdDate = $mocoData['created_at']; // MOCO
} else {
    $createdDate = $project->created_at; // Fallback
}
```

**FALSCH:**
```php
// Lokale Daten verwenden, obwohl MOCO verfügbar ist
$createdDate = $project->created_at;
```

#### 5. Synchronisation
- Lokale Datenbank wird NUR zur Performance-Optimierung verwendet
- Synchronisation aktualisiert lokale Daten mit MOCO-Daten
- Bei Konflikten: MOCO-Daten überschreiben lokale Daten

#### 6. Anzeigelogik
- Projekt-Karten: MOCO-Daten laden und anzeigen
- Detail-Ansichten: MOCO-Daten priorisieren
- Listen/Übersichten: Aus lokaler DB für Performance, aber mit MOCO-Sync

### Ausnahmen
Die einzigen erlaubten lokalen Daten sind:
- **Interne IDs** für Datenbankrelationen
- **Cache-Zeitstempel** für Synchronisation
- **Benutzer-spezifische Einstellungen** (UI-Präferenzen)

### Wichtige Hinweise
- ❌ KEINE Eigeninterpretationen oder Berechnungen, wenn MOCO-Daten verfügbar sind
- ❌ KEINE lokalen Daten anzeigen, wenn MOCO-Daten existieren
- ✅ IMMER MOCO-Daten als Quelle verwenden
- ✅ Lokale DB nur als Performance-Cache behandeln

### Zusammenfassung
**"MOCO First, Local Last"** - In allen Situationen haben MOCO-Daten absolute Priorität über lokale Datenbank-Daten.
























