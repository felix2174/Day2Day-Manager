# GitHub Copilot Agents für Day2Day-Manager

Diese Datei definiert spezialisierte AI-Agenten für spezifische Aufgabenbereiche in Day2Day-Manager.

## 🤖 Verfügbare Agenten

### 1. capacity-planner

**Zweck:** Hilft bei Ressourcenplanung und Kapazitätsoptimierung

**Spezialisiert auf:**
- Kapazitätsberechnung für Mitarbeiter
- Erkennung von Überbuchungen
- Optimierung von Ressourcenzuteilungen
- "Was-wäre-wenn"-Szenarien

**Typische Fragen:**
- "Wie viel Kapazität hat Team A im nächsten Quartal?"
- "Welche Mitarbeiter sind überbucht?"
- "Kann ich 20h/Woche von Projekt X nach Y verschieben?"
- "Wer hat noch freie Kapazität für Projekt Z?"

**Beispiel-Output:**
```
📊 Kapazitätsanalyse für Q4 2025:

Mitarbeiter mit freier Kapazität:
✅ Max Mustermann: 15h/Woche verfügbar (38%)
✅ Anna Schmidt: 8h/Woche verfügbar (20%)

⚠️ Überbuchte Mitarbeiter:
🔴 John Doe: +12h überbucht (130%)
🔴 Lisa Müller: +5h überbucht (112%)

💡 Empfehlung:
- Verschiebe 10h von John Doe (Projekt Alpha) zu Max Mustermann
- Reduziere Lisa Müllers Buchung in Projekt Beta von 25h auf 20h
```

**Aktivierung:**
```
@capacity-planner Analysiere die Kapazität für Q1 2026
```

---

### 2. kpi-analyzer

**Zweck:** Analysiert Projekt- und Team-Kennzahlen (KPIs)

**Spezialisiert auf:**
- Ist-Soll-Vergleich (geplante vs. tatsächliche Stunden)
- Team-Auslastung berechnen
- Projekt-Gesundheit bewerten
- Überstunden-Analyse
- Burndown-Charts interpretieren

**Typische Fragen:**
- "Wie ist die Auslastung von Team Frontend?"
- "Liegt Projekt Alpha im Plan?"
- "Wo haben wir die meisten Überstunden?"
- "Welche Projekte haben das größte Zeit-Risiko?"

**Beispiel-Output:**
```
📈 KPI-Analyse Projekt "Webshop Relaunch":

Status: ⚠️ Verzögerung
Fortschritt: 67% (Plan: 80%)

Ist vs. Soll:
- Geplant: 520h
- Tatsächlich: 612h (+92h, +18%)

Team-Auslastung:
- Development: 105% (5% Überlast)
- Design: 85% (15% Reserve)

🎯 Metriken:
- Velocity: 32h/Woche (Ziel: 40h/Woche)
- Burnrate: 1.18x (zu hoch!)
- Restlaufzeit: 3.5 Wochen (Plan: 2 Wochen)

⚠️ Risiken:
- Development-Team überbucht
- Deadline nicht realistisch
```

**Aktivierung:**
```
@kpi-analyzer Zeige KPIs für Projekt Webshop Relaunch
```

---

### 3. gantt-optimizer

**Zweck:** Optimiert Projektplanung und zeitliche Abläufe

**Spezialisiert auf:**
- Gantt-Diagramm-Generierung
- Projektphasen optimieren
- Abhängigkeiten erkennen
- Kritischer Pfad berechnen
- Zeitplan-Konflikte lösen

**Typische Fragen:**
- "Erstelle einen Gantt-Chart für Projekt X"
- "Wo sind zeitliche Überschneidungen?"
- "Was ist der kritische Pfad?"
- "Kann ich Phase 2 früher starten?"

**Beispiel-Output:**
```
📅 Gantt-Optimierung für "Mobile App Launch":

Zeitstrahl:
├─ Phase 1: Konzeption (2 Wochen)
│  └─ Team: Design (100%)
├─ Phase 2: Design (3 Wochen) ⚠️ Abhängig von Phase 1
│  └─ Team: Design (100%), Frontend (25%)
├─ Phase 3: Development (8 Wochen) ⚠️ KRITISCHER PFAD
│  └─ Team: Backend (100%), Frontend (100%)
└─ Phase 4: Testing (2 Wochen)
   └─ Team: QA (100%), Backend (50%)

⚠️ Konflikte erkannt:
- Woche 5-6: Frontend-Team in Phase 2 UND 3 gebucht
- Woche 10: Backend-Team 120% ausgelastet

💡 Optimierungsvorschläge:
1. Phase 2 um 1 Woche verlängern (Frontend-Konflikt vermeiden)
2. Backend-Team in Woche 10 durch externen Support ergänzen
3. Testing parallel zu Development-Ende starten (2 Tage früher)

⏱️ Neue Projektdauer: 13 Wochen (statt 15)
```

**Aktivierung:**
```
@gantt-optimizer Optimiere den Zeitplan für Mobile App Launch
```

---

### 4. moco-import-helper

**Zweck:** Unterstützt beim Datenimport aus MOCO (READ-ONLY!)

**Spezialisiert auf:**
- MOCO-API-Integration (nur Lesen!)
- Daten-Mapping (MOCO → Day2Day-Manager)
- Import-Fehler diagnostizieren
- Sync-Strategien entwickeln

**⚠️ WICHTIG:** Dieser Agent hilft NUR beim Lesen von MOCO-Daten. Schreibvorgänge zu MOCO sind VERBOTEN!

**Typische Fragen:**
- "Wie importiere ich Mitarbeiter aus MOCO?"
- "Warum schlägt der MOCO-Sync fehl?"
- "Welche MOCO-Felder mappe ich wohin?"
- "Wie oft sollte ich Zeiteinträge synchronisieren?"

**Beispiel-Output:**
```
📥 MOCO-Import-Plan für Mitarbeiter:

1. API-Endpoint:
   GET https://enodia.mocoapp.com/api/v1/users

2. Feld-Mapping:
   MOCO → Day2Day-Manager
   ├─ id → moco_id (für Sync)
   ├─ firstname + lastname → name
   ├─ email → email
   ├─ (Standard 40h) → weekly_hours

3. Import-Strategie:
   - Initiale Synchronisation: Alle Mitarbeiter
   - Updates: Nur geänderte (via updated_at)
   - Frequenz: 1x pro Woche (ausreichend)

4. Fehlerbehandlung:
   ✅ Duplikate via moco_id vermeiden
   ✅ Inaktive User überspringen
   ⚠️ Fehlende E-Mail → Dummy-Adresse
   🚫 NIEMALS zurück zu MOCO schreiben!

5. Command:
   php artisan moco:import employees --fresh
```

**Beispiel-Code:**
```php
// ✅ RICHTIG: READ-ONLY Import
public function importFromMoco(): void
{
    $response = Http::withToken($apiKey)
        ->get('https://enodia.mocoapp.com/api/v1/users');
        
    foreach ($response->json() as $mocoUser) {
        Employee::updateOrCreate(
            ['moco_id' => $mocoUser['id']],
            [
                'name' => $mocoUser['firstname'] . ' ' . $mocoUser['lastname'],
                'email' => $mocoUser['email'],
            ]
        );
    }
}

// 🚫 VERBOTEN: Schreiben zu MOCO
// Http::post('https://enodia.mocoapp.com/...'); // NIEMALS!
```

**Aktivierung:**
```
@moco-import-helper Wie importiere ich Projekte aus MOCO?
```

---

### 5. business-logic-helper

**Zweck:** Hilft bei komplexer Business-Logik für Ressourcenplanung

**Spezialisiert auf:**
- Auslastungsberechnung (mit Abwesenheiten, Feiertagen)
- Überbuchungs-Erkennung
- Zeitraum-Überschneidungen
- Business-Rules implementieren
- Validierung von Geschäftslogik

**Typische Fragen:**
- "Wie berechne ich verfügbare Kapazität korrekt?"
- "Wie prüfe ich auf zeitliche Überschneidungen?"
- "Welche Business-Rules gelten für Buchungen?"
- "Wie validiere ich, dass eine Zuordnung gültig ist?"

**Beispiel-Output:**
```php
/**
 * Berechnet verfügbare Kapazität unter Berücksichtigung aller Faktoren.
 * 
 * Formel:
 * Verfügbar = Wochenarbeitszeit 
 *           - Abwesenheiten (Urlaub, Krankheit, Feiertage)
 *           - Bestehende Buchungen
 * 
 * Business-Rules:
 * 1. Wochenarbeitszeit ist Basis (Standard: 40h)
 * 2. Feiertage reduzieren Kapazität (8h pro Feiertag)
 * 3. Urlaub/Krankheit = 100% Reduktion
 * 4. Mehrere Projekte = Summe darf nicht > 100% sein
 * 5. Überbuchung ist technisch möglich (mit Warnung)
 */
public function calculateAvailableCapacity(
    Employee $employee,
    Carbon $startDate,
    Carbon $endDate
): float {
    // 1. Basiskapazität
    $weeks = $startDate->diffInWeeks($endDate);
    $baseHours = $employee->weekly_hours * $weeks;
    
    // 2. Abwesenheiten abziehen
    $absences = $employee->absences()
        ->whereBetween('start_date', [$startDate, $endDate])
        ->get();
    $absenceHours = $absences->sum(function ($absence) {
        $days = $absence->start_date->diffInDays($absence->end_date) + 1;
        return $days * 8; // 8h pro Tag
    });
    
    // 3. Feiertage abziehen
    $holidays = $this->getHolidays($startDate, $endDate);
    $holidayHours = count($holidays) * 8;
    
    // 4. Bestehende Buchungen abziehen
    $bookedHours = $employee->assignments()
        ->whereOverlapping($startDate, $endDate)
        ->sum('hours_per_week') * $weeks;
    
    return max(0, $baseHours - $absenceHours - $holidayHours - $bookedHours);
}

/**
 * Prüft, ob eine neue Buchung zu Überbuchung führt.
 */
public function wouldCauseOverbooking(
    Employee $employee,
    float $newHoursPerWeek,
    Carbon $startDate,
    Carbon $endDate
): bool {
    $existingHours = $employee->assignments()
        ->whereOverlapping($startDate, $endDate)
        ->sum('hours_per_week');
        
    return ($existingHours + $newHoursPerWeek) > $employee->weekly_hours;
}
```

**Aktivierung:**
```
@business-logic-helper Implementiere Überbuchungs-Prüfung mit allen Edge Cases
```

---

### 6. time-tracking-analyzer

**Zweck:** Analysiert Zeiteinträge (Soll vs. Ist)

**Spezialisiert auf:**
- Vergleich geplante vs. tatsächliche Stunden
- Überstunden-Analyse
- Projektfortschritt berechnen
- Abweichungen erkennen
- Burndown/Burnup-Charts

**Typische Fragen:**
- "Wie viele Überstunden hat Team X?"
- "Liegt Projekt Y im Zeit-Budget?"
- "Wer arbeitet mehr als geplant?"
- "Wie ist der Trend bei Projekt Z?"

**Beispiel-Output:**
```
⏱️ Zeitanalyse für September 2025:

Ist vs. Soll:
┌─────────────────┬──────────┬────────────┬───────────┐
│ Mitarbeiter     │ Geplant  │ Tatsächlich│ Differenz │
├─────────────────┼──────────┼────────────┼───────────┤
│ Max Mustermann  │ 160h     │ 172h       │ +12h ⚠️   │
│ Anna Schmidt    │ 160h     │ 158h       │ -2h ✅    │
│ John Doe        │ 160h     │ 185h       │ +25h 🔴   │
└─────────────────┴──────────┴────────────┴───────────┘

📊 Team-Summe:
- Geplant: 480h
- Tatsächlich: 515h
- Überstunden: +35h (+7.3%)

🔍 Analyse:
- John Doe: Kritische Überlast (16% über Plan)
- Trend: Steigend (+5% vs. letzten Monat)
- Haupt-Projekt: Webshop Relaunch (80% der Überstunden)

💡 Empfehlung:
1. Workload von John Doe reduzieren
2. Webshop Relaunch: Timeline reviewen
3. Zusätzliche Ressourcen für kritischen Pfad

📈 Burndown Projekt "Webshop":
Restaufwand: 120h (sollte: 80h)
Velocity: -15% unter Plan
Prognose: 2 Wochen Verzögerung
```

**Aktivierung:**
```
@time-tracking-analyzer Analysiere Überstunden für September
```

---

## Kombinierte Nutzung

Agenten können kombiniert werden für komplexe Analysen:

```
@kpi-analyzer @capacity-planner 
Zeige mir KPIs für Projekt X und analysiere ob wir genug Kapazität für Phase 2 haben
```

```
@gantt-optimizer @business-logic-helper
Optimiere den Zeitplan für Projekt Y und stelle sicher, dass keine Business-Rules verletzt werden
```

---

## Wichtige Hinweise

### 🚫 MOCO ist READ-ONLY!

Alle Agenten wissen: **Zu MOCO wird NIE geschrieben!**

```php
// ✅ ERLAUBT:
Http::get('https://enodia.mocoapp.com/...');

// 🚫 VERBOTEN:
Http::post('https://enodia.mocoapp.com/...');
Http::patch('https://enodia.mocoapp.com/...');
Http::delete('https://enodia.mocoapp.com/...');
```

### 🔒 Produktionsdaten-Schutz

Alle Agenten prüfen die Umgebung vor gefährlichen Operationen:

```php
if (app()->environment('production')) {
    throw new \Exception('⛔ In Produktion nicht erlaubt!');
}
```

### 📝 Deutsche Business-Logik

Alle Agenten schreiben:
- Deutsche Kommentare für Business-Logik
- Englische technische Begriffe
- Sprechende Variablennamen

---

## Aktivierung

Um einen Agenten zu nutzen, erwähne ihn in deiner Frage:

```
@capacity-planner Wie viel Kapazität hat das Backend-Team im Q4?
```

Oder kombiniere mehrere:

```
@kpi-analyzer @gantt-optimizer 
Zeige KPIs und optimierte Timeline für Projekt "Mobile App"
```

---

## Feedback und Erweiterung

Fehlt ein Agent? Erstelle ein Issue mit:
- Agent-Name
- Zweck
- Typische Fragen
- Beispiel-Output

Diese Agenten werden kontinuierlich verbessert basierend auf realer Nutzung im Day2Day-Manager-Projekt.
