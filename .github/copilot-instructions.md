# GitHub Copilot Development Rules - Day2Day-Manager

**Projekt:** Day2Day-Manager (Laravel 12 + MOCO-Integration)  
**Entwickler:** Jörg Michno, Felix  
**Erstellt:** 29.10.2025  
**Version:** 2.0

---

## 🎯 Grundprinzipien

### 1. Dauerhafte Lösungen statt Quick-Fixes ⭐

- ❌ **NIEMALS** temporäre Workarounds ohne Dokumentation
- ✅ **IMMER** nachhaltige, wartbare Lösungen entwickeln
- ✅ Code muss in 6 Monaten noch verständlich sein
- ✅ Bei Kompromissen: Dokumentiere das "Warum" ausführlich
- ✅ Refactoring > Quick-Fix

### 2. Test-First-Ansatz 🧪

- ✅ Nach JEDER Code-Änderung: Testanleitung bereitstellen
- ✅ Klare Test-Schritte mit erwarteten Ergebnissen
- ✅ Validierung BEVOR zum nächsten Schritt
- ✅ Format: "🧪 JETZT KANNST DU TESTEN" als Standard
- ✅ Keine Batch-Implementierung ohne Zwischentests

### 3. Inkrementelle Entwicklung 📈

- ✅ Kleine, testbare Schritte (max. 2-3 Dateien gleichzeitig)
- ✅ Jeder Schritt muss für sich funktionieren
- ✅ Keine "Big Bang"-Deployments
- ✅ Rollback-Strategie bei jedem Feature dokumentieren

### 4. Dokumentation ist Code 📝

- ✅ `PROJECT_ROADMAP.md` IMMER aktualisieren nach Änderungen
- ✅ Inline-Kommentare für komplexe Logik (WARUM, nicht WAS)
- ✅ Changelog bei jeder bedeutenden Änderung
- ✅ Architektur-Entscheidungen dokumentieren (ADR-Stil)

---

## 🏗️ Architektur-Regeln

### Hybrid-Strategie: UI + MOCO 🔄

```
┌─────────────────────────────────────────────────────┐
│  MOCO (Read-Only)        │  Day2Day-Manager (Master)│
│  ─────────────────       │  ───────────────────────│
│  • Projekte (Sync)       │  • Assignments (UI)     │
│  • Mitarbeiter (Sync)    │  • Workflows (UI)       │
│  • Zeiterfassung (Sync)  │  • Status (UI)          │
│  • Abwesenheiten (Sync)  │  • Teams (UI)           │
└─────────────────────────────────────────────────────┘
```

**Eiserne Regeln:**
- ✅ MOCO-Daten werden synchronisiert, NICHT überschrieben
- ✅ Lokale UI-Daten haben IMMER Vorrang bei Konflikten
- ✅ MOCO liefert zusätzliche Informationen, keine Wahrheit
- ✅ Bei Sync-Konflikten: User-Warnung, keine automatische Lösung

### Model-Relationships

#### Assignments als Single Source of Truth

```php
// RICHTIG: Explicit Pivot-Model mit Logik
public function employees()
{
    return $this->belongsToMany(Employee::class, 'assignments')
        ->withPivot('weekly_hours', 'start_date', 'end_date', 'task_name')
        ->withTimestamps();
}

// FALSCH: Implizite Pivot ohne Kontrolle
public function employees()
{
    return $this->belongsToMany(Employee::class);
}
```

**Regeln:**
- ✅ `assignments`-Tabelle ist Master für alle Zuweisungen
- ✅ Keine doppelten Daten in mehreren Tabellen (DRY)
- ✅ MOCO-Daten ergänzen, ersetzen NICHT lokale Daten

### Helper-Methoden-Pattern: Fallback-Hierarchie

```php
/**
 * Datenquellen-Priorität (Fallback-Kette):
 * 1. Übergebene Parameter ($mocoTeamData)
 * 2. Lokale DB-Daten (assignments)
 * 3. Fallback-Daten (z.B. responsible_id)
 * 4. Graceful Empty State
 */
public function getAssignedPersonsList($mocoTeamData = null): array
{
    if ($mocoTeamData) { return $mocoData; }
    if ($this->assignments->isNotEmpty()) { return $assignments; }
    if ($this->responsible) { return [$responsible]; }
    return [];
}
```

**Regeln:**
- ✅ IMMER mehrere Fallbacks (min. 3 Stufen)
- ✅ Null-Checks vor JEDEM Objekt-Zugriff
- ✅ Graceful Degradation statt Fehler/Exceptions
- ✅ Leerzustände sind valide (keine Fehler werfen)

---

## 🚀 MOCO-Integration-Regeln

### Sync-Strategie (Read-Only)

| Entität         | Richtung      | Frequenz  | Master      | Überschreibbar |
|-----------------|---------------|-----------|-------------|----------------|
| Projects        | MOCO → Lokal  | Täglich   | MOCO        | ❌ Nein        |
| Employees       | MOCO → Lokal  | Täglich   | MOCO        | ❌ Nein        |
| TimeEntries     | MOCO → Lokal  | Stündlich | MOCO        | ❌ Nein        |
| Absences        | MOCO → Lokal  | Täglich   | MOCO        | ❌ Nein        |
| **Assignments** | **UI → Lokal**| **Manuell**| **UI**     | **✅ Ja**      |

**Goldene Regeln:**
- ✅ MOCO-Sync überschreibt NIEMALS manuelle UI-Assignments
- ✅ Alle Sync-Commands haben `--dry-run` Flag (Pflicht!)
- ✅ Detailliertes Logging bei jedem Sync (Info/Warning/Error)
- ✅ Fehlertoleranz: Partieller Sync bei API-Fehlern
- ✅ Cache-Fallback wenn MOCO nicht erreichbar

### API-Fehlerbehandlung

```php
// RICHTIG: Defensive Programming
try {
    $data = $mocoService->getProjects();
    
    if (empty($data)) {
        Log::warning('MOCO returned empty data', ['endpoint' => 'projects']);
        return Cache::get('moco:projects', []); // Cached Fallback
    }
    
    Cache::put('moco:projects', $data, now()->addHours(24));
    return $data;
    
} catch (\Exception $e) {
    Log::error('MOCO API Error', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    return Cache::get('moco:projects', []); // Immer Fallback
}

// FALSCH: Ungeschützte API-Calls
$data = $mocoService->getProjects(); // Kann crashen
```

**Regeln:**
- ✅ Try-Catch um ALLE externen API-Calls
- ✅ IMMER Fallback auf lokale/gecachte Daten
- ✅ Logging mit Context (was, warum, wann)
- ✅ User-freundliche Fehlermeldungen (keine Stack-Traces)

---

## 🧪 Testing-Rules

### Test-Driven-Changes (TDD-Light)

**Workflow für JEDE Änderung:**

```
1. Code-Änderung → Implementation fertig
2. Test-Anleitung → Detailliert erstellen
3. Warten → User testet (KEINE weiteren Änderungen!)
4. Validierung → Feedback verarbeiten
5. Weiter → Nächster Schritt ODER Bugfix
```

**Test-Anleitung Format:**

```markdown
## 🧪 JETZT KANNST DU TESTEN

### Was wurde geändert:
- [Kurze Beschreibung der Änderung]

### Test-Schritte:
1. Schritt 1 mit konkretem Befehl/Aktion
2. Erwartetes Ergebnis (konkret, messbar)
3. Screenshot-Aufforderung wenn UI-Änderung

### Erwartetes Ergebnis:
- ✅ Erfolg-Kriterium 1
- ✅ Erfolg-Kriterium 2

### Falls Fehler auftreten:
- Zeige mir: [Konkrete Debug-Info]
- Oder: Screenshot von [Spezifischer Screen]
```

---

## 📝 Dokumentations-Rules

### PROJECT_ROADMAP.md Updates

**Aktualisieren bei:**
- ✅ Neue Phase gestartet
- ✅ Meilenstein erreicht/verfehlt
- ✅ Problem gelöst (mit Lösungsweg)
- ✅ Architektur-Entscheidung (ADR-Stil)
- ✅ Breaking Change
- ✅ Wichtige Erkenntnisse

**Format:**

```markdown
### [DD.MM.YYYY] - [HH:MM] Uhr
**[Phase] - [Meilenstein/Thema]**

**Änderungen:**
- ✅ Was erreicht (Done)
- 🔄 Was in Arbeit (In Progress)
- ⏳ Was geplant (Planned)
- 🐛 Gefundene Probleme (Issues)
- 💡 Erkenntnisse (Learnings)
- ⚠️ Risiken (Risks)

**Technische Details:**
- Dateien: [Liste der geänderten Dateien]
- Breaking Changes: [Falls vorhanden]
- Migration nötig: [Ja/Nein]
```

### Code-Kommentare

```php
// RICHTIG: Erklärt WARUM und ALTERNATIVEN
/**
 * Verwendet responsible_id als Fallback für Assignments
 * 
 * GRUND: MOCO-API liefert keine Contract-Daten über getProjectTeam()
 * ALTERNATIVE: Manuelle UI-Zuweisung bleibt jederzeit möglich
 * RISIKO: Verantwortlicher könnte nicht gesetzt sein (Validierung vorhanden)
 * DECISION: Graceful Degradation > Hard Error
 */
if (!$this->assignments->count() && $this->responsible_id) {
    return [$this->responsible->name];
}

// FALSCH: Erklärt nur WAS (Code sagt das schon)
// Prüft ob Assignments leer sind
if (!$this->assignments->count()) {
    // ...
}
```

---

## 🔧 Command-Rules

### Command-Struktur (Pflicht-Template)

**JEDER Command MUSS haben:**

1. **Dry-Run Flag**
```php
protected $signature = 'command:name 
                        {--dry-run : Zeigt nur Vorschau ohne Änderungen}';
```

2. **Verbose Output mit Icons**
```php
$this->info('✅ Success: ' . $message);
$this->warn('⚠️  Warning: ' . $message);
$this->error('❌ Error: ' . $message);
$this->line('ℹ️  Info: ' . $message);
```

3. **Progress Bar für Loops**
```php
$bar = $this->output->createProgressBar($items->count());
foreach ($items as $item) {
    // Process
    $bar->advance();
}
$bar->finish();
$this->newLine(2);
```

4. **Zusammenfassungs-Tabelle**
```php
$this->table(
    ['Status', 'Count', 'Details'],
    [
        ['✅ Created', $created, 'New items'],
        ['🔄 Updated', $updated, 'Changed items'],
        ['⚠️ Skipped', $skipped, 'Already exists'],
        ['❌ Errors', $errors, 'Failed to process'],
    ]
);
```

5. **Exit Codes**
```php
return $created > 0 ? 0 : 1; // 0 = Success, 1+ = Error
```

---

## 🎨 UI/UX-Rules

### Blade-Template-Prinzipien

```blade
{{-- RICHTIG: Defensive Rendering --}}
@forelse($project->assignments as $assignment)
    <div class="employee">
        {{ $assignment->employee->name ?? 'Unbekannter Mitarbeiter' }}
        <span class="hours">
            {{ $assignment->weekly_hours ?? 0 }}h/Woche
        </span>
    </div>
@empty
    <div class="text-gray-500">
        Keine Mitarbeiter zugewiesen.
        @can('manage-projects')
            <button onclick="openAssignModal({{ $project->id }})">
                + Hinzufügen
            </button>
        @endcan
    </div>
@endforelse

{{-- FALSCH: Ungeschützte Zugriffe --}}
@foreach($project->assignments as $assignment)
    {{ $assignment->employee->name }} {{-- Crasht bei NULL --}}
@endforeach
```

**Regeln:**
- ✅ `@forelse` statt `@foreach` bei Collections (Pflicht!)
- ✅ Null-Coalescing `??` bei JEDEM Objekt-Zugriff
- ✅ `@can` für Permissions (niemals Logik ohne Auth)
- ✅ User-freundliche Leerzustände mit Aktionen

---

## 🚨 Anti-Patterns (Niemals tun!)

### 1. Silent Data Loss
```php
// ❌ NIEMALS ohne Logging/Backup löschen
Assignment::where('project_id', $id)->delete();

// ✅ RICHTIG: Mit Logging und Soft-Delete
$assignments = Assignment::where('project_id', $id)->get();
Log::info('Deleting assignments', [
    'project_id' => $id,
    'count' => $assignments->count(),
    'user_id' => auth()->id()
]);
$assignments->each->delete(); // Nutzt Soft-Delete wenn definiert
```

### 2. Hardcoded Values
```php
// ❌ NIEMALS Magic Numbers/IDs
$admin = Employee::find(1);

// ✅ RICHTIG: Config/Konstanten
$admin = Employee::where('email', config('app.admin_email'))->first();
// ODER
$admin = Employee::where('role', Employee::ROLE_ADMIN)->first();
```

### 3. Unvalidated Input
```php
// ❌ NIEMALS Mass-Assignment ohne Validation
Project::create($request->all());

// ✅ RICHTIG: Strikte Validation
$validated = $request->validate([
    'name' => 'required|string|max:255',
    'start_date' => 'required|date',
    'responsible_id' => 'required|exists:employees,id',
]);
Project::create($validated);
```

### 4. N+1 Queries
```php
// ❌ NIEMALS: Lazy Loading in Loops
foreach ($projects as $project) {
    echo $project->responsible->name; // N+1 Query!
}

// ✅ RICHTIG: Eager Loading
$projects = Project::with('responsible')->get();
foreach ($projects as $project) {
    echo $project->responsible?->name ?? 'Unbekannt';
}
```

---

## 🔄 Feature-Development-Cycle

```
┌─────────────────────────────────────────────┐
│ 1. ANALYSE                                  │
│    ├─ Problem verstehen (User-Perspektive) │
│    ├─ Bestehenden Code reviewen            │
│    └─ Lösungsansätze bewerten (min. 2)     │
├─────────────────────────────────────────────┤
│ 2. DESIGN                                   │
│    ├─ Dauerhafte Lösung entwerfen          │
│    ├─ Fallbacks planen (min. 3 Stufen)     │
│    ├─ Tests definieren (What to test)      │
│    └─ Risiken identifizieren               │
├─────────────────────────────────────────────┤
│ 3. IMPLEMENTATION                           │
│    ├─ Code schreiben (kleine Schritte)     │
│    ├─ Inline-Kommentare (WARUM)            │
│    ├─ Self-Review (Checklist durchgehen)   │
│    └─ Git-Commit mit ausführlicher Message │
├─────────────────────────────────────────────┤
│ 4. TESTING                                  │
│    ├─ Test-Anleitung erstellen (Template)  │
│    ├─ User-Test abwarten (KEINE neuen Änderungen!)│
│    ├─ Feedback verarbeiten                 │
│    └─ Bugfixes ODER Weiter zu Schritt 5    │
├─────────────────────────────────────────────┤
│ 5. DOKUMENTATION                            │
│    ├─ PROJECT_ROADMAP.md update            │
│    ├─ Changelog erweitern                  │
│    ├─ README.md bei Breaking Changes       │
│    └─ Commit Message finalisieren          │
├─────────────────────────────────────────────┤
│ 6. DEPLOYMENT                               │
│    ├─ Staging-Test (falls vorhanden)       │
│    ├─ Produktiv-Rollout (off-peak)         │
│    ├─ Monitoring für 24h                   │
│    └─ Hotfix-Plan bereit                   │
└─────────────────────────────────────────────┘
```

**Eiserne Regel:**
- ✅ NIEMALS einen Schritt überspringen
- ✅ Bei Problemen: Zurück zu Schritt 1 (nicht weitermachen)
- ✅ Dokumentation VOR Deployment (nicht nachträglich)

---

## 💬 Kommunikations-Format

### Response-Struktur (Template)

```markdown
# 🎯 [Kurzer prägnanter Titel]

## 📊 Analyse / Situation
[Was ist der aktuelle Zustand? Was wurde festgestellt?]

## ✅ Lösung / Vorschlag
[Was ist die beste Lösung? Warum diese und nicht andere?]

## 🚀 Implementation
[Konkrete Schritte / Code-Änderungen]

## 🧪 Test
[Wie kann User testen? Erwartete Ergebnisse?]

## 📋 Nächste Schritte
[Was passiert danach? Optionen für User]
```

**Kommunikations-Regeln:**
- ✅ Kurz & sachlich (max. 3-4 Absätze pro Sektion)
- ✅ Klare Handlungsanweisungen (kein Rätselraten)
- ✅ IMMER Test-Anleitung bei Code-Änderungen
- ✅ Emojis für visuelle Struktur (nicht für Deko)
- ✅ Code-Blöcke mit Sprache (```php, nicht nur ```)

---

## 📊 Performance-Rules

### Database Query Optimization

```php
// RICHTIG: Selective Eager Loading
$projects = Project::with([
    'assignments' => function($query) {
        $query->select('id', 'project_id', 'employee_id', 'weekly_hours')
              ->where('start_date', '<=', now())
              ->where('end_date', '>=', now());
    },
    'assignments.employee:id,first_name,last_name'
])->get();

// FALSCH: Alles laden (Memory Killer)
$projects = Project::with('assignments.employee')->get();
```

**Performance-Regeln:**
- ✅ Eager Loading für ALLE Relationships in Loops
- ✅ Select nur benötigte Felder (explizit angeben)
- ✅ Pagination bei >100 Datensätzen (Pflicht!)
- ✅ Caching für statische/selten ändernde Daten (TTL definieren)
- ✅ Indexes auf Foreign Keys und Search-Felder

---

## 🔐 Security-Rules

### Input Validation (Laravel FormRequest bevorzugt)

```php
// RICHTIG: Strikte Multi-Layer Validation
$validated = $request->validate([
    'employee_ids' => [
        'required',
        'array',
        'min:1',
        'max:20', // Limit für Batch-Operations
    ],
    'employee_ids.*' => [
        'required',
        'integer',
        'exists:employees,id',
        'distinct', // Keine Duplikate
    ],
    'weekly_hours' => [
        'required',
        'numeric',
        'min:0',
        'max:40',
        'regex:/^\d+(\.\d{1,2})?$/', // Max 2 Dezimalstellen
    ],
]);

// FALSCH: Minimale/Keine Validation
$employeeIds = $request->input('employee_ids'); // ❌ Gefährlich
```

**Security-Regeln:**
- ✅ IMMER `validate()` vor DB-Operations (ausnahmslos!)
- ✅ `exists:` für ALLE Foreign Keys
- ✅ Range-Checks für numerische Werte
- ✅ CSRF-Token bei ALLEN POST/PUT/DELETE (Laravel-Standard)
- ✅ Authorization-Check VOR Validation (`$this->authorize()`)

---

## 🎯 Die 10 Gebote

1. **Dauerhafte Lösungen** > Quick-Fixes (immer!)
2. **Testen vor Weitermachen** bei jedem Schritt (Pflicht!)
3. **Dokumentation ist Pflicht** nicht Optional (gleichzeitig mit Code)
4. **MOCO-Daten Read-Only** UI ist Master (niemals überschreiben)
5. **Graceful Degradation** immer Fallbacks (min. 3 Stufen)
6. **Null-Safety überall** defensive Programming (jeder Zugriff)
7. **Dry-Run bei Commands** zum Testen (immer implementieren)
8. **Logging bei kritischen Ops** mit Context (Info/Warning/Error)
9. **User-freundlich** klare Fehlermeldungen (keine Tech-Sprache)
10. **Performance** N+1 Queries vermeiden (Eager Loading)

---

## 📚 Checklisten

### Code-Review Checklist (vor Commit)

- [ ] Tests geschrieben/aktualisiert?
- [ ] Inline-Kommentare für komplexe Logik?
- [ ] Null-Checks bei allen Objekt-Zugriffen?
- [ ] Eager Loading statt Lazy Loading?
- [ ] Validation für alle User-Inputs?
- [ ] Fehlerbehandlung mit Logging?
- [ ] Dry-Run Flag bei Commands?
- [ ] PROJECT_ROADMAP.md aktualisiert?
- [ ] Breaking Changes dokumentiert?
- [ ] Migration erstellt (falls DB-Änderung)?

### Deployment Checklist

- [ ] Alle Tests passed (lokal)?
- [ ] Staging-Deployment erfolgreich?
- [ ] Migrations ausgeführt?
- [ ] Cache geleert (falls nötig)?
- [ ] Monitoring aktiv?
- [ ] Rollback-Plan bereit?
- [ ] Dokumentation aktualisiert?
- [ ] User/Team informiert?

---

**Erstellt:** 29.10.2025  
**Version:** 2.0  
**Review:** Jörg Michno  
**Nächstes Review:** 29.11.2025
