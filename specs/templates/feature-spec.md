# Feature Spec: [Feature Name]

**Status:** 🟡 Draft / 🟢 Approved / 🔵 In Development / ✅ Implemented  
**Erstellt:** YYYY-MM-DD  
**Letzte Änderung:** YYYY-MM-DD  
**Autor:** [Dein Name]  
**Review:** [Name des Reviewers, optional]

---

## 1. Feature-Beschreibung

### Was wird gebaut?
[Kurze, prägnante Beschreibung des Features in 1-2 Sätzen]

### Warum brauchen wir es?
[Business Context: Welches Problem löst das Feature? Welchen Wert bringt es?]

### Für wen ist es?
[Zielgruppe: Projektmanager, Developer, Admin, etc.]

---

## 2. User Stories

### Story 1: [Titel]
**Als** [Rolle]  
**möchte ich** [Aktion]  
**damit** [Nutzen/Ziel]

**Beispiel:**
**Als** Projektmanager  
**möchte ich** Mitarbeiter Projekten zuordnen können  
**damit** ich die Ressourcen optimal verteilen kann

### Story 2: [Titel]
[Weitere User Stories...]

---

## 3. Akzeptanzkriterien

Nutze das **Given-When-Then** Format für klare, testbare Kriterien.

### AC1: [Szenario-Name]
**Given:** [Ausgangssituation]  
**When:** [Aktion des Users]  
**Then:** [Erwartetes Ergebnis]

**Beispiel:**
```gherkin
Given: Ich bin auf der Projekt-Seite
  And: Projekt "Webshop" existiert
  And: Mitarbeiter "Max Mustermann" ist verfügbar
When: Ich ordne "Max Mustermann" dem Projekt "Webshop" zu
  And: Ich setze 20h/Woche
  And: Ich klicke "Speichern"
Then: Die Zuordnung wird erstellt
  And: Max erscheint in der Projekt-Mitarbeiter-Liste
  And: Seine Auslastung steigt um 50% (bei 40h-Woche)
```

### AC2: [Weiteres Szenario]
**Given:** ...  
**When:** ...  
**Then:** ...

### AC3: [Negativ-Szenario / Fehlerfall]
**Given:** ...  
**When:** ...  
**Then:** [Fehlermeldung oder Validierung]

---

## 4. Business Rules

Definiere die Geschäftslogik explizit:

### BR1: [Regel-Name]
**Regel:** [Beschreibung der Regel]  
**Beispiel:** [Konkretes Beispiel]  
**Ausnahmen:** [Falls es Ausnahmen gibt]

**Beispiel:**
### BR1: Überbuchungs-Warnung
**Regel:** System warnt, wenn Mitarbeiter > 100% ausgelastet ist  
**Beispiel:** Max hat 40h/Woche, wird aber 50h zugeordnet → Warnung  
**Ausnahmen:** Warnung ist nur visuell (rote Ampel), Überbuchung ist technisch erlaubt  

### BR2: [Weitere Regel]
...

---

## 5. Datenmodell

### 5.1 Datenbank-Tabellen

#### Neue Tabelle: `[table_name]`
| Spalte | Typ | Constraints | Beschreibung |
|--------|-----|-------------|--------------|
| `id` | bigint | PRIMARY KEY, AUTO_INCREMENT | Eindeutige ID |
| `name` | varchar(255) | NOT NULL | Name |
| `created_at` | timestamp | | Erstellungszeitpunkt |

**Indizes:**
- `idx_name` auf `name` (für schnelle Suche)

**Foreign Keys:**
- `project_id` → `projects.id` (CASCADE on delete)

#### Änderungen an existierender Tabelle: `[table_name]`
- **Neue Spalte:** `column_name` (Typ, Constraints)
- **Geänderte Spalte:** `old_column` → `new_column`

### 5.2 Eloquent Models

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class [ModelName] extends Model
{
    protected $fillable = [
        'name',
        'description',
        // ...
    ];
    
    // Relationships
    public function relatedModel()
    {
        return $this->belongsTo(RelatedModel::class);
    }
    
    // Accessors (falls nötig)
    public function getComputedAttributeAttribute(): string
    {
        // Berechnung...
    }
    
    // Scopes (falls nötig)
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
```

---

## 6. API-Verträge (falls API-Endpoints)

### 6.1 GET /api/[resource]
**Beschreibung:** [Was macht der Endpoint?]

**Request:**
```http
GET /api/employees?status=active HTTP/1.1
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Max Mustermann",
      "email": "max@example.com",
      "weekly_hours": 40
    }
  ],
  "meta": {
    "total": 1,
    "page": 1
  }
}
```

**Fehler (404 Not Found):**
```json
{
  "error": "Resource not found",
  "message": "Employee with ID 999 does not exist"
}
```

### 6.2 POST /api/[resource]
**Beschreibung:** [Was macht der Endpoint?]

**Request:**
```http
POST /api/employees HTTP/1.1
Content-Type: application/json
Authorization: Bearer {token}

{
  "name": "Max Mustermann",
  "email": "max@example.com",
  "weekly_hours": 40
}
```

**Response (201 Created):**
```json
{
  "data": {
    "id": 42,
    "name": "Max Mustermann",
    "email": "max@example.com",
    "weekly_hours": 40,
    "created_at": "2025-10-23T14:30:00Z"
  }
}
```

**Validation Error (422 Unprocessable Entity):**
```json
{
  "error": "Validation failed",
  "errors": {
    "email": ["The email has already been taken."]
  }
}
```

---

## 7. UI/UX (falls Frontend-Changes)

### 7.1 Wireframes / Mockups
[Link zu Figma, Bilder einbinden, oder ASCII-Art für einfache Layouts]

```
┌─────────────────────────────────────────┐
│ Mitarbeiter-Verwaltung            [+]   │
├─────────────────────────────────────────┤
│ Name              Email       Stunden   │
├─────────────────────────────────────────┤
│ Max Mustermann    max@e.com   40  🟢    │
│ Anna Schmidt      anna@e.com  40  🟡    │
│ John Doe          john@e.com  40  🔴    │
└─────────────────────────────────────────┘
```

### 7.2 Navigation
- Wo im System ist das Feature erreichbar?
- Welcher Menüpunkt?
- Welche Route?

### 7.3 Interaktionen
- Was passiert beim Klick auf [Button]?
- Welche Validierungen gibt es?
- Wie sieht Feedback aus (Erfolgsmeldung, Fehler)?

---

## 8. Validierungsregeln

| Feld | Regel | Fehlermeldung |
|------|-------|---------------|
| `name` | required, string, max:255 | "Name ist erforderlich" |
| `email` | required, email, unique:employees | "Email bereits vergeben" |
| `weekly_hours` | required, numeric, min:1, max:60 | "Stunden müssen zwischen 1 und 60 liegen" |

---

## 9. Edge Cases & Spezialfälle

### EC1: [Edge Case Name]
**Szenario:** [Beschreibung des ungewöhnlichen Falls]  
**Erwartetes Verhalten:** [Wie soll das System reagieren?]  
**Beispiel:** [Konkretes Beispiel]

**Beispiel:**
### EC1: Mitarbeiter ohne Email
**Szenario:** Mitarbeiter hat keine Email-Adresse (z.B. Werkstudent ohne Firmen-Mail)  
**Erwartetes Verhalten:** Email ist optional, aber bei Verwendung muss sie unique sein  
**Beispiel:** Werkstudent "Tom Test" ohne Email → erlaubt

### EC2: Sehr langer Name
**Szenario:** Name mit > 100 Zeichen  
**Erwartetes Verhalten:** Validierung: Max 255 Zeichen  
**Beispiel:** "Dr. Maximilian-Alexander von Mustermann-Schmidtenburg III." (68 Zeichen) → OK

### EC3: [Weiterer Edge Case]
...

---

## 10. Sicherheitsüberlegungen

### S1: Authentication & Authorization
- Wer darf das Feature nutzen?
- Welche Permissions sind nötig?

**Beispiel:**
- Nur eingeloggte User
- Permission: `manage-employees`

### S2: Input Validation
- Alle Eingaben validieren (siehe Abschnitt 8)
- XSS-Schutz durch Blade (automatisch)
- SQL-Injection-Schutz durch Eloquent (automatisch)

### S3: MOCO-Schutz (falls relevant)
```
⚠️ KRITISCH: Feature darf NICHT zu MOCO schreiben!

Erlaubt:
✅ Daten von MOCO lesen (GET)

Verboten:
🚫 Zu MOCO schreiben (POST/PATCH/DELETE)
```

---

## 11. Performance-Überlegungen

### P1: Datenbank-Queries
- **N+1 Problem vermeiden:** Eager Loading nutzen
- **Indizes:** Welche Felder werden oft gesucht?
- **Pagination:** Bei großen Datensätzen (> 100 Einträge)

**Beispiel:**
```php
// ❌ LANGSAM: N+1
$employees = Employee::all();
foreach ($employees as $employee) {
    echo $employee->projects->count(); // N Queries!
}

// ✅ SCHNELL: Eager Loading
$employees = Employee::withCount('projects')->get();
foreach ($employees as $employee) {
    echo $employee->projects_count; // Keine extra Queries
}
```

### P2: Caching
- Welche Daten können gecacht werden?
- Cache-Invalidierung: Wann muss Cache gelöscht werden?

### P3: Frontend
- Lazy Loading für Bilder
- JavaScript-Optimierung

---

## 12. Testing-Strategie

### 12.1 Unit Tests
```php
// Tests für Business-Logik

public function test_calculates_utilization_correctly(): void
{
    $employee = Employee::factory()->create(['weekly_hours' => 40]);
    
    Assignment::factory()->create([
        'employee_id' => $employee->id,
        'hours_per_week' => 30,
    ]);
    
    $this->assertEquals(75, $employee->fresh()->utilization);
}
```

### 12.2 Feature Tests
```php
// Tests für User-Flows (basierend auf Akzeptanzkriterien)

public function test_creates_employee_successfully(): void
{
    $this->actingAs($admin)
        ->post('/employees', [
            'name' => 'Max Mustermann',
            'email' => 'max@example.com',
            'weekly_hours' => 40,
        ])
        ->assertRedirect('/employees')
        ->assertSessionHas('success');
        
    $this->assertDatabaseHas('employees', [
        'email' => 'max@example.com'
    ]);
}
```

### 12.3 Browser Tests (optional)
- Dusk-Tests für komplexe UI-Interaktionen
- Nur wenn unbedingt nötig (langsam!)

---

## 13. Migrations & Deployment

### 13.1 Datenbank-Migrationen
```php
// database/migrations/2025_10_23_create_assignments_table.php

public function up(): void
{
    Schema::create('assignments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
        $table->foreignId('project_id')->constrained()->cascadeOnDelete();
        $table->decimal('hours_per_week', 5, 2);
        $table->date('start_date');
        $table->date('end_date')->nullable();
        $table->timestamps();
        
        $table->index(['employee_id', 'start_date']);
    });
}
```

### 13.2 Seeders (nur für Development!)
```php
// database/seeders/DevelopmentSeeder.php

public function run(): void
{
    if (app()->environment('production')) {
        throw new \Exception('⛔ Seeders nicht in Produktion!');
    }
    
    Employee::factory()->count(10)->create();
}
```

### 13.3 Deployment-Schritte
1. **Backup:** Datenbank-Backup erstellen
2. **Migrationen:** `php artisan migrate`
3. **Cache löschen:** `php artisan optimize:clear`
4. **Tests:** `php artisan test` (vor Deploy!)
5. **Monitoring:** Logs nach Deploy prüfen

---

## 14. Offene Fragen

Fragen, die vor oder während der Implementierung geklärt werden müssen:

- [ ] **Q1:** [Frage]  
      **A:** [Antwort, falls bekannt]

- [ ] **Q2:** Sollen wir Soft Deletes nutzen?  
      **A:** Ja, wir wollen gelöschte Mitarbeiter in Reports noch sehen können

- [ ] **Q3:** ...

---

## 15. Abhängigkeiten

### Voraussetzungen
- [ ] Feature X muss vorher implementiert sein
- [ ] Migration Y muss laufen
- [ ] Package Z muss installiert sein

### Blockiert
- Dieses Feature blockiert: [Liste von abhängigen Features]

---

## 16. Zeitschätzung

**Entwicklung:** X Stunden  
**Testing:** Y Stunden  
**Code Review:** Z Stunden  
**Gesamt:** X+Y+Z Stunden

**Meilensteine:**
- [ ] Datenmodell & Migrationen (2h)
- [ ] Backend-Logik (4h)
- [ ] Frontend (3h)
- [ ] Tests (2h)
- [ ] Review & Bugfixes (1h)

---

## 17. Changelog

| Datum | Änderung | Autor |
|-------|----------|-------|
| 2025-10-23 | Initial Draft | [Dein Name] |
| 2025-10-24 | Added Edge Cases nach Review | [Dein Name] |

---

## 18. Anhänge

- [Link zu Mockups]
- [Link zu verwandten Specs]
- [Link zu Dokumentation]
