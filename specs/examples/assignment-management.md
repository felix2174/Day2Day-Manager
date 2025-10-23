# Feature Spec: Assignment Management (Projektbuchungen)

**Status:** ✅ Implemented  
**Erstellt:** 2025-09-15  
**Letzte Änderung:** 2025-10-23  
**Autor:** Day2Day-Manager Team

---

## 1. Feature-Beschreibung

### Was wird gebaut?
Ein System zur Zuordnung von Mitarbeitern zu Projekten mit definierten Stundenkontingenten pro Woche.

### Warum brauchen wir es?
Projektmanager müssen Ressourcen optimal verteilen und Kapazitätsengpässe frühzeitig erkennen. Ohne strukturierte Projektbuchungen ist keine verlässliche Ressourcenplanung möglich.

### Für wen ist es?
- **Projektmanager:** Weisen Mitarbeiter Projekten zu
- **Team Leads:** Sehen Auslastung ihres Teams
- **Management:** Überwachen Ressourcen-Utilization

---

## 2. User Stories

### Story 1: Mitarbeiter zu Projekt zuordnen
**Als** Projektmanager  
**möchte ich** einen Mitarbeiter einem Projekt zuordnen können  
**damit** ich sicherstelle, dass ausreichend Ressourcen für das Projekt verfügbar sind

### Story 2: Überbuchung erkennen
**Als** Projektmanager  
**möchte ich** gewarnt werden, wenn ein Mitarbeiter überbucht ist  
**damit** ich realistische Planung sicherstellen kann

### Story 3: Zeitraum definieren
**Als** Projektmanager  
**möchte ich** Start- und Enddatum für Zuordnungen festlegen  
**damit** ich zeitlich begrenzte Projektphasen abbilden kann

---

## 3. Akzeptanzkriterien

### AC1: Erfolgreiche Zuordnung erstellen
```gherkin
Given: Ich bin eingeloggt als Projektmanager
  And: Projekt "Webshop Relaunch" existiert
  And: Mitarbeiter "Max Mustermann" (40h/Woche) ist verfügbar
When: Ich gehe zu Projekt "Webshop Relaunch"
  And: Ich klicke "Mitarbeiter hinzufügen"
  And: Ich wähle "Max Mustermann"
  And: Ich setze 20h/Woche
  And: Ich setze Startdatum: 01.11.2025
  And: Ich setze Enddatum: 30.11.2025
  And: Ich klicke "Speichern"
Then: Zuordnung wird erstellt
  And: Max erscheint in Projekt-Mitarbeiter-Liste
  And: Max' Auslastung steigt von 0% auf 50%
  And: Erfolgsmeldung: "Max Mustermann wurde dem Projekt zugeordnet"
```

### AC2: Überbuchungs-Warnung wird angezeigt
```gherkin
Given: Max Mustermann (40h/Woche) ist bereits Projekt A mit 30h/Woche zugeordnet
When: Ich versuche Max zu Projekt B mit 25h/Woche zuzuordnen (Überlappung)
Then: Warnung wird angezeigt: "⚠️ Mitarbeiter wird zu 137.5% ausgelastet sein"
  And: System erlaubt Speichern (nur Warnung, kein Block)
  And: Bei Bestätigung: Zuordnung wird erstellt
  And: Max' Auslastung wird als "🔴 137.5%" angezeigt
```

### AC3: Zeitliche Überschneidung prüfen
```gherkin
Given: Max ist Projekt A zugeordnet (01.10.2025 - 31.10.2025, 30h/Woche)
When: Ich ordne Max Projekt B zu (15.10.2025 - 15.11.2025, 20h/Woche)
Then: System erkennt Überschneidung (15.-31.10.)
  And: Warnung zeigt: "In diesem Zeitraum bereits 30h/Woche gebucht"
  And: Berechnet Gesamt-Auslastung für Überschneidungszeit: 50h = 125%
```

### AC4: Zuordnung bearbeiten
```gherkin
Given: Max ist Projekt A mit 20h/Woche zugeordnet
When: Ich ändere Stunden auf 30h/Woche
Then: Zuordnung wird aktualisiert
  And: Auslastung wird neu berechnet
  And: Historie der Änderung wird gespeichert (wer, wann)
```

### AC5: Zuordnung löschen
```gherkin
Given: Max ist Projekt A zugeordnet
When: Ich klicke "Zuordnung löschen"
  And: Ich bestätige die Sicherheitsabfrage
Then: Zuordnung wird gelöscht
  And: Max' Auslastung wird angepasst
  And: Erfolgsmeldung: "Zuordnung wurde entfernt"
```

---

## 4. Business Rules

### BR1: Stunden pro Woche - Gültiger Bereich
**Regel:** Stunden pro Woche müssen zwischen 0.5 und 60 liegen  
**Begründung:**
- Minimum 0.5h: Kleinere Werte sind nicht sinnvoll verwaltbar
- Maximum 60h: Realistisches Maximum (bei Überstunden)  
**Beispiel:** 20h/Woche = 50% bei 40h-Woche  
**Validierung:** `hours_per_week: min:0.5, max:60`

### BR2: Überbuchung ist erlaubt (mit Warnung)
**Regel:** System erlaubt technisch Überbuchung (>100%), zeigt aber deutliche Warnung  
**Begründung:**
- Realität: Manchmal ist Überbuchung gewollt (z.B. bei Krankheitsvertretung)
- Manager trifft bewusste Entscheidung
- System zeigt Ampelfarbe:
  - 🟢 Grün: 0-79%
  - 🟡 Gelb: 80-99%
  - 🔴 Rot: ≥100%

### BR3: Zeiträume dürfen überlappen
**Regel:** Ein Mitarbeiter kann mehrere Projekte parallel haben (zeitliche Überlappung erlaubt)  
**Beispiel:**
- Projekt A: 01.10.-31.10. (30h/Woche)
- Projekt B: 15.10.-15.11. (10h/Woche)
- Überlappung: 15.-31.10. → 40h/Woche (100% Auslastung)

### BR4: Enddatum ist optional
**Regel:** Enddatum kann leer sein = unbefristete Zuordnung  
**Anwendungsfall:** Mitarbeiter arbeitet dauerhaft in einem Projekt  
**Validierung:** `end_date: nullable, after_or_equal:start_date`

### BR5: Historische Zuordnungen bleiben erhalten
**Regel:** Zuordnungen werden nicht gelöscht beim Löschen von Projekt/Mitarbeiter, sondern als "archiviert" markiert  
**Begründung:** Für Reporting (Ist-Stunden-Auswertung) brauchen wir historische Daten  
**Technisch:** Soft Deletes mit `deleted_at`

---

## 5. Datenmodell

### 5.1 Tabelle: `assignments`

| Spalte | Typ | Constraints | Beschreibung |
|--------|-----|-------------|--------------|
| `id` | bigint | PRIMARY KEY, AUTO_INCREMENT | Eindeutige ID |
| `employee_id` | bigint | NOT NULL, FOREIGN KEY | Mitarbeiter |
| `project_id` | bigint | NOT NULL, FOREIGN KEY | Projekt |
| `hours_per_week` | decimal(5,2) | NOT NULL | Stunden pro Woche (z.B. 20.50) |
| `start_date` | date | NOT NULL | Startdatum der Zuordnung |
| `end_date` | date | NULL | Enddatum (optional) |
| `notes` | text | NULL | Optionale Notizen |
| `created_at` | timestamp | | Erstellungszeitpunkt |
| `updated_at` | timestamp | | Letzte Änderung |
| `deleted_at` | timestamp | NULL | Soft Delete |

**Indizes:**
```sql
INDEX idx_employee_date ON assignments(employee_id, start_date, end_date);
INDEX idx_project ON assignments(project_id);
INDEX idx_active ON assignments(deleted_at); -- Für Soft Deletes
```

**Foreign Keys:**
```sql
FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE RESTRICT;
FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE RESTRICT;
```

**RESTRICT statt CASCADE:** Wir wollen nicht, dass Zuordnungen automatisch gelöscht werden, wenn ein Projekt gelöscht wird (Datenintegrität für Reports).

### 5.2 Eloquent Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assignment extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'employee_id',
        'project_id',
        'hours_per_week',
        'start_date',
        'end_date',
        'notes',
    ];
    
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'hours_per_week' => 'decimal:2',
    ];
    
    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    
    // Scopes
    
    /**
     * Nur aktive Zuordnungen (nicht gelöscht, aktuell laufend).
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->where('start_date', '<=', now())
              ->where(function ($q2) {
                  $q2->whereNull('end_date')
                     ->orWhere('end_date', '>=', now());
              });
        });
    }
    
    /**
     * Zuordnungen die einen bestimmten Zeitraum überlappen.
     */
    public function scopeOverlapping($query, $startDate, $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            // Zuordnung startet vor/während Zeitraum UND
            // endet während/nach Zeitraum (oder ist unbefristet)
            $q->where('start_date', '<=', $endDate)
              ->where(function ($q2) use ($startDate) {
                  $q2->whereNull('end_date')
                     ->orWhere('end_date', '>=', $startDate);
              });
        });
    }
    
    // Helper Methods
    
    /**
     * Prüft ob diese Zuordnung aktuell aktiv ist.
     */
    public function isActive(): bool
    {
        if ($this->start_date->isFuture()) {
            return false; // Noch nicht gestartet
        }
        
        if ($this->end_date && $this->end_date->isPast()) {
            return false; // Bereits beendet
        }
        
        return true;
    }
    
    /**
     * Berechnet Auslastung in Prozent für diesen Mitarbeiter.
     */
    public function getUtilizationPercentageAttribute(): float
    {
        return ($this->hours_per_week / $this->employee->weekly_hours) * 100;
    }
}
```

---

## 6. API-Verträge

### 6.1 GET /api/projects/{id}/assignments
**Beschreibung:** Holt alle Zuordnungen für ein Projekt

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "employee": {
        "id": 42,
        "name": "Max Mustermann",
        "weekly_hours": 40
      },
      "hours_per_week": 20,
      "utilization_percentage": 50,
      "start_date": "2025-11-01",
      "end_date": "2025-11-30",
      "is_active": true,
      "notes": null
    }
  ]
}
```

### 6.2 POST /api/assignments
**Beschreibung:** Erstellt neue Zuordnung

**Request:**
```json
{
  "employee_id": 42,
  "project_id": 15,
  "hours_per_week": 20,
  "start_date": "2025-11-01",
  "end_date": "2025-11-30",
  "notes": "Frontend-Entwicklung"
}
```

**Response (201 Created):**
```json
{
  "data": {
    "id": 123,
    "employee_id": 42,
    "project_id": 15,
    "hours_per_week": 20,
    "start_date": "2025-11-01",
    "end_date": "2025-11-30",
    "warning": "⚠️ Mitarbeiter ist zu 75% ausgelastet (nach dieser Zuordnung)"
  }
}
```

**Validation Error (422):**
```json
{
  "error": "Validation failed",
  "errors": {
    "hours_per_week": ["Stunden müssen zwischen 0.5 und 60 liegen"],
    "end_date": ["Enddatum muss nach Startdatum liegen"]
  }
}
```

---

## 7. Edge Cases & Spezialfälle

### EC1: Mitarbeiter ohne Wochenarbeitszeit
**Szenario:** Mitarbeiter hat `weekly_hours = NULL`  
**Erwartetes Verhalten:** Validierung schlägt fehl: "Mitarbeiter muss Wochenarbeitszeit haben"  
**Beispiel:** Neuer Mitarbeiter wurde angelegt, aber Feld vergessen

### EC2: Projekt ist bereits archiviert
**Szenario:** Versuch, Mitarbeiter zu archiviertem Projekt zuzuordnen  
**Erwartetes Verhalten:** Warnung: "Projekt ist archiviert. Fortfahren?"  
**Beispiel:** Altes Projekt wird reaktiviert

### EC3: Sehr lange Zuordnung (>5 Jahre)
**Szenario:** Zuordnung über mehrere Jahre (z.B. Dauerprojekt)  
**Erwartetes Verhalten:** Erlaubt, aber Warnung: "Sehr lange Zuordnung - ist das korrekt?"  
**Beispiel:** IT-Support-Team arbeitet dauerhaft in "Internal Support"

### EC4: Negative Überlappung (Enddatum < Startdatum)
**Szenario:** Enddatum liegt vor Startdatum  
**Erwartetes Verhalten:** Validierung schlägt fehl  
**Validierung:** `end_date: after_or_equal:start_date`

### EC5: Dezimalstunden (z.B. 20.5h)
**Szenario:** Manager will 20.5h/Woche buchen  
**Erwartetes Verhalten:** Erlaubt (decimal mit 2 Nachkommastellen)  
**Beispiel:** Teilzeit-Mitarbeiter mit krummen Werten

### EC6: Mitarbeiter wird gelöscht (Soft Delete)
**Szenario:** Mitarbeiter wird aus System entfernt  
**Erwartetes Verhalten:**
- Zuordnungen bleiben erhalten (wegen RESTRICT Foreign Key)
- In UI: Zuordnungen zu gelöschten Mitarbeitern ausblenden
- In Reports: Weiterhin sichtbar (für historische Auswertung)

---

## 8. Validierungsregeln

```php
// app/Http/Requests/StoreAssignmentRequest.php

public function rules(): array
{
    return [
        'employee_id' => 'required|exists:employees,id',
        'project_id' => 'required|exists:projects,id',
        'hours_per_week' => 'required|numeric|min:0.5|max:60',
        'start_date' => 'required|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
        'notes' => 'nullable|string|max:1000',
    ];
}

public function messages(): array
{
    return [
        'employee_id.required' => 'Bitte wähle einen Mitarbeiter aus.',
        'employee_id.exists' => 'Mitarbeiter existiert nicht.',
        'hours_per_week.min' => 'Mindestens 0.5 Stunden pro Woche.',
        'hours_per_week.max' => 'Maximal 60 Stunden pro Woche.',
        'end_date.after_or_equal' => 'Enddatum muss nach Startdatum liegen.',
    ];
}

// Custom Validation
public function withValidator($validator)
{
    $validator->after(function ($validator) {
        // Prüfe auf Überbuchung (Warnung, kein Error)
        if ($this->wouldCauseOverbooking()) {
            session()->flash('warning', 
                '⚠️ Diese Zuordnung führt zu Überbuchung. Fortfahren?'
            );
        }
    });
}

private function wouldCauseOverbooking(): bool
{
    $employee = Employee::find($this->employee_id);
    
    $existingHours = Assignment::where('employee_id', $this->employee_id)
        ->overlapping($this->start_date, $this->end_date ?? now()->addYears(10))
        ->sum('hours_per_week');
        
    $totalHours = $existingHours + $this->hours_per_week;
    
    return $totalHours > $employee->weekly_hours;
}
```

---

## 9. Testing-Strategie

### 9.1 Unit Tests

```php
// tests/Unit/AssignmentTest.php

public function test_is_active_returns_true_for_current_assignment(): void
{
    $assignment = Assignment::factory()->create([
        'start_date' => now()->subWeek(),
        'end_date' => now()->addWeek(),
    ]);
    
    $this->assertTrue($assignment->isActive());
}

public function test_is_active_returns_false_for_past_assignment(): void
{
    $assignment = Assignment::factory()->create([
        'start_date' => now()->subMonth(),
        'end_date' => now()->subWeek(),
    ]);
    
    $this->assertFalse($assignment->isActive());
}

public function test_overlapping_scope_finds_overlapping_assignments(): void
{
    $employee = Employee::factory()->create();
    
    // Assignment 1: 01.10. - 31.10.
    Assignment::factory()->create([
        'employee_id' => $employee->id,
        'start_date' => '2025-10-01',
        'end_date' => '2025-10-31',
    ]);
    
    // Assignment 2: 15.10. - 15.11. (überlappt)
    Assignment::factory()->create([
        'employee_id' => $employee->id,
        'start_date' => '2025-10-15',
        'end_date' => '2025-11-15',
    ]);
    
    // Suche Überlappung 01.10. - 20.10.
    $overlapping = Assignment::where('employee_id', $employee->id)
        ->overlapping('2025-10-01', '2025-10-20')
        ->count();
        
    $this->assertEquals(2, $overlapping); // Beide überlappen
}
```

### 9.2 Feature Tests

```php
// tests/Feature/AssignmentManagementTest.php

public function test_creates_assignment_successfully(): void
{
    $this->actingAs($projectManager)
        ->post('/assignments', [
            'employee_id' => $employee->id,
            'project_id' => $project->id,
            'hours_per_week' => 20,
            'start_date' => '2025-11-01',
            'end_date' => '2025-11-30',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');
        
    $this->assertDatabaseHas('assignments', [
        'employee_id' => $employee->id,
        'project_id' => $project->id,
        'hours_per_week' => 20,
    ]);
}

public function test_shows_warning_for_overbooking(): void
{
    // Existierende Zuordnung: 30h
    Assignment::factory()->create([
        'employee_id' => $employee->id,
        'hours_per_week' => 30,
        'start_date' => now(),
        'end_date' => now()->addMonth(),
    ]);
    
    // Neue Zuordnung: 25h (überbucht!)
    $this->actingAs($projectManager)
        ->post('/assignments', [
            'employee_id' => $employee->id,
            'project_id' => $project->id,
            'hours_per_week' => 25,
            'start_date' => now(),
            'end_date' => now()->addMonth(),
        ])
        ->assertSessionHas('warning'); // Warnung soll vorhanden sein
}
```

---

## 10. Migrations & Deployment

```php
// database/migrations/2025_09_15_create_assignments_table.php

public function up(): void
{
    Schema::create('assignments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('employee_id')
            ->constrained()
            ->restrictOnDelete(); // RESTRICT statt CASCADE
        $table->foreignId('project_id')
            ->constrained()
            ->restrictOnDelete();
        $table->decimal('hours_per_week', 5, 2); // 999.99 Max
        $table->date('start_date');
        $table->date('end_date')->nullable();
        $table->text('notes')->nullable();
        $table->timestamps();
        $table->softDeletes();
        
        // Indizes für Performance
        $table->index(['employee_id', 'start_date', 'end_date']);
        $table->index('project_id');
    });
}
```

---

## 11. Zusammenfassung

**Was wurde implementiert:**
- ✅ Zuordnung von Mitarbeitern zu Projekten
- ✅ Überbuchungs-Erkennung mit Warnungen
- ✅ Zeitraum-Management (Start/Ende)
- ✅ Überlappungs-Erkennung
- ✅ Soft Deletes für Historienerhalt
- ✅ API-Endpoints
- ✅ Umfassende Tests

**Business Value:**
- Strukturierte Ressourcenplanung
- Früherkennung von Kapazitätsengpässen
- Datengrundlage für KPI-Dashboards

**Technische Highlights:**
- Eloquent Scopes für komplexe Queries
- Custom Validation mit Warnungen
- Performance-optimiert durch Indizes
