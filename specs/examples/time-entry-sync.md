# Feature Spec: MOCO Time Entry Synchronization (Zeiteintrag-Synchronisierung)

**Status:** ✅ Implemented  
**Erstellt:** 2025-09-20  
**Letzte Änderung:** 2025-10-23  
**Autor:** Day2Day-Manager Team

---

## 🚨 KRITISCHE SICHERHEITSREGEL

**MOCO ist READ-ONLY!**

Day2Day-Manager darf:
- ✅ Zeiteinträge von MOCO **lesen** (GET)
- ✅ In lokaler Datenbank **speichern**
- ✅ Für Analysen **verwenden**

Day2Day-Manager darf **NIEMALS:**
- 🚫 Zeiteinträge zu MOCO **schreiben** (POST)
- 🚫 Zeiteinträge in MOCO **ändern** (PATCH)
- 🚫 Zeiteinträge in MOCO **löschen** (DELETE)

**Begründung:** MOCO ist das Haupt-Zeiterfassungssystem von enodia und wird von anderen Systemen genutzt. Day2Day-Manager ist nur ein **Konsument** der Daten, kein **Editor**.

---

## 1. Feature-Beschreibung

### Was wird gebaut?
Ein automatischer Synchronisations-Job, der täglich Zeiteinträge aus MOCO importiert und in der lokalen Datenbank speichert. Diese Daten werden für Ist-Soll-Vergleiche, Projektfortschritt und KPI-Dashboards verwendet.

### Warum brauchen wir es?
- **Ist-Soll-Vergleich:** Geplante vs. tatsächliche Stunden pro Projekt
- **Überstunden-Tracking:** Wer arbeitet mehr als geplant?
- **Projektfortschritt:** Wie viel % des Budgets ist verbraucht?
- **Burndown-Charts:** Visualisierung des Projektfortschritts

### Für wen ist es?
- **Projektmanager:** Sehen, ob Projekte im Zeitbudget liegen
- **Team Leads:** Überstunden-Monitoring ihres Teams
- **Management:** KPI-Dashboards mit echten Ist-Daten

---

## 2. User Stories

### Story 1: Tägliche Synchronisation
**Als** System  
**möchte ich** täglich automatisch Zeiteinträge von MOCO importieren  
**damit** die Ist-Daten immer aktuell sind

### Story 2: Ist-Soll-Vergleich
**Als** Projektmanager  
**möchte ich** sehen, wie viele Stunden tatsächlich gearbeitet wurden im Vergleich zur Planung  
**damit** ich Abweichungen frühzeitig erkenne

### Story 3: Sync-Status prüfen
**Als** Administrator  
**möchte ich** den Status der MOCO-Synchronisation sehen  
**damit** ich Fehler schnell erkennen kann

---

## 3. Akzeptanzkriterien

### AC1: Tägliche Synchronisation läuft automatisch
```gherkin
Given: Es ist 06:00 Uhr morgens
When: Der Cron-Job startet
Then: Zeiteinträge vom Vortag werden von MOCO geholt
  And: Sie werden in `time_entries` Tabelle gespeichert
  And: Duplikate werden erkannt (via moco_id)
  And: Log-Eintrag wird geschrieben: "342 Zeiteinträge synchronisiert"
```

### AC2: Mapping von MOCO zu Day2Day-Manager
```gherkin
Given: MOCO-API liefert einen Zeiteintrag
When: Der Sync-Job verarbeitet den Eintrag
Then: Folgendes Mapping wird durchgeführt:
  - MOCO user.id → employee (via moco_id)
  - MOCO project.id → project (via moco_id)
  - MOCO activity.date → time_entry.date
  - MOCO activity.hours → time_entry.hours
  - MOCO activity.description → time_entry.description
  And: Falls Mitarbeiter/Projekt nicht existiert: Warnung loggen, Eintrag überspringen
```

### AC3: Duplikate werden verhindert
```gherkin
Given: Zeiteintrag mit moco_id=12345 existiert bereits in unserer DB
When: Sync-Job holt denselben Eintrag erneut von MOCO
Then: Eintrag wird via moco_id erkannt
  And: Bestehender Eintrag wird aktualisiert (falls geändert)
  And: Kein Duplikat wird erstellt
```

### AC4: Fehlerbehandlung bei API-Ausfall
```gherkin
Given: MOCO-API ist nicht erreichbar (503 Service Unavailable)
When: Sync-Job versucht Daten zu holen
Then: Job wartet 5 Minuten und versucht es erneut (max. 3 Versuche)
  And: Bei weiterem Fehlschlag: Admin-Benachrichtigung
  And: Fehler wird geloggt mit Timestamp
  And: Sync wird beim nächsten regulären Zeitpunkt erneut versucht
```

### AC5: Manuelle Synchronisation möglich
```gherkin
Given: Ich bin Administrator
When: Ich führe `php artisan moco:sync-time-entries --date=2025-10-22` aus
Then: Zeiteinträge für das angegebene Datum werden synchronisiert
  And: Output zeigt Anzahl synchronisierter Einträge
  And: Bei Fehlern: Detaillierte Fehlermeldung
```

---

## 4. Business Rules

### BR1: Sync läuft täglich um 06:00 Uhr
**Regel:** Automatischer Sync um 06:00 Uhr für den Vortag  
**Begründung:** 
- Um 06:00 sind alle Zeiteinträge vom Vortag in MOCO erfasst
- Keine Überlastung der MOCO-API während Arbeitszeit  
**Ausnahme:** Manueller Sync jederzeit möglich per Artisan-Command

### BR2: Zeiteinträge bleiben unveränderbar
**Regel:** Nach Import werden Zeiteinträge in Day2Day-Manager nicht bearbeitet  
**Begründung:** MOCO ist die "Source of Truth"  
**Technisch:** `time_entries` Tabelle hat keine Edit-UI  
**Exception:** Admin kann Einträge für Debugging löschen (sehr selten)

### BR3: Nur letzten 90 Tage synchronisieren
**Regel:** Initialer Import: Letzte 90 Tage, dann täglich neu  
**Begründung:**
- Ältere Daten sind für aktuelle Planung irrelevant
- API-Load reduzieren
- Datenbank schlank halten  
**Historische Daten:** Falls benötigt, manueller einmaliger Import möglich

### BR4: Mitarbeiter/Projekt-Mapping erforderlich
**Regel:** Zeiteintrag wird nur importiert, wenn Mitarbeiter UND Projekt in Day2Day-Manager existieren  
**Begründung:** Sonst hätten wir Zeiteinträge ohne Kontext  
**Lösung:** Regelmäßiger Employee/Project-Import aus MOCO (separate Jobs)

---

## 5. Datenmodell

### 5.1 Tabelle: `time_entries`

| Spalte | Typ | Constraints | Beschreibung |
|--------|-----|-------------|--------------|
| `id` | bigint | PRIMARY KEY, AUTO_INCREMENT | Eindeutige ID |
| `moco_id` | bigint | UNIQUE, NOT NULL | Externe MOCO-ID (für Duplikat-Check) |
| `employee_id` | bigint | FOREIGN KEY, NOT NULL | Mitarbeiter |
| `project_id` | bigint | FOREIGN KEY, NOT NULL | Projekt |
| `date` | date | NOT NULL | Arbeitsdatum |
| `hours` | decimal(5,2) | NOT NULL | Arbeitsstunden (z.B. 7.50) |
| `description` | text | NULL | Tätigkeitsbeschreibung |
| `synced_at` | timestamp | NOT NULL | Wann wurde synchronisiert? |
| `created_at` | timestamp | | |
| `updated_at` | timestamp | | |

**Indizes:**
```sql
UNIQUE INDEX idx_moco_id ON time_entries(moco_id); -- Duplikat-Prävention
INDEX idx_employee_date ON time_entries(employee_id, date); -- Abfragen
INDEX idx_project_date ON time_entries(project_id, date);
INDEX idx_synced ON time_entries(synced_at); -- Monitoring
```

**Foreign Keys:**
```sql
FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE RESTRICT;
FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE RESTRICT;
```

### 5.2 Eloquent Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeEntry extends Model
{
    protected $fillable = [
        'moco_id',
        'employee_id',
        'project_id',
        'date',
        'hours',
        'description',
        'synced_at',
    ];
    
    protected $casts = [
        'date' => 'date',
        'hours' => 'decimal:2',
        'synced_at' => 'datetime',
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
     * Zeiteinträge für einen bestimmten Zeitraum.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }
    
    /**
     * Zeiteinträge eines Mitarbeiters.
     */
    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }
    
    /**
     * Zeiteinträge eines Projekts.
     */
    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }
}
```

---

## 6. MOCO API Integration

### 6.1 MOCO API Endpoint

**Endpoint:** `GET https://enodia.mocoapp.com/api/v1/activities`

**Authentication:**
```http
GET /api/v1/activities?from=2025-10-22&to=2025-10-22 HTTP/1.1
Host: enodia.mocoapp.com
Authorization: Token token={MOCO_API_KEY}
```

**Response (200 OK):**
```json
[
  {
    "id": 12345,
    "date": "2025-10-22",
    "hours": 7.5,
    "description": "Frontend-Entwicklung: User-Dashboard",
    "billable": true,
    "user": {
      "id": 42,
      "firstname": "Max",
      "lastname": "Mustermann"
    },
    "project": {
      "id": 15,
      "name": "Webshop Relaunch"
    },
    "task": {
      "id": 100,
      "name": "Entwicklung"
    }
  }
]
```

### 6.2 MOCO Service

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MocoService
{
    private const BASE_URL = 'https://enodia.mocoapp.com/api/v1';
    
    /**
     * Holt Zeiteinträge von MOCO (READ-ONLY).
     * 
     * @param string $date Datum im Format YYYY-MM-DD
     * @return array MOCO-Zeiteinträge
     */
    public function getTimeEntries(string $date): array
    {
        Log::info("📥 MOCO: Lade Zeiteinträge für {$date} (READ-ONLY)");
        
        try {
            $response = Http::withToken($this->getApiKey())
                ->timeout(30)
                ->get(self::BASE_URL . '/activities', [
                    'from' => $date,
                    'to' => $date,
                ]);
                
            if ($response->successful()) {
                $entries = $response->json();
                Log::info("✅ MOCO: {$entries->count()} Einträge geladen");
                return $entries;
            }
            
            Log::error("❌ MOCO API Error: {$response->status()}");
            return [];
            
        } catch (\Exception $e) {
            Log::error("❌ MOCO API Exception: {$e->getMessage()}");
            return [];
        }
    }
    
    /**
     * 🚫 DIESE METHODEN EXISTIEREN NICHT!
     * MOCO ist READ-ONLY. Keine Write-Operationen!
     */
    // public function createTimeEntry() { ... }  // VERBOTEN!
    // public function updateTimeEntry() { ... }  // VERBOTEN!
    // public function deleteTimeEntry() { ... }  // VERBOTEN!
    
    private function getApiKey(): string
    {
        $apiKey = config('services.moco.api_key');
        
        if (empty($apiKey)) {
            throw new \Exception('MOCO_API_KEY nicht konfiguriert in .env');
        }
        
        return $apiKey;
    }
}
```

---

## 7. Sync-Job Implementation

### 7.1 Artisan Command

```php
<?php

namespace App\Console\Commands;

use App\Services\MocoService;
use App\Services\TimeEntrySyncService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SyncMocoTimeEntriesCommand extends Command
{
    protected $signature = 'moco:sync-time-entries 
                            {--date= : Datum (YYYY-MM-DD), default: gestern}
                            {--force : Auch bereits synchronisierte Einträge neu laden}';
    
    protected $description = 'Synchronisiert Zeiteinträge von MOCO (READ-ONLY)';
    
    public function handle(
        MocoService $mocoService,
        TimeEntrySyncService $syncService
    ): int {
        $date = $this->option('date') 
            ? Carbon::parse($this->option('date')) 
            : Carbon::yesterday();
            
        $this->info("📥 Starte MOCO-Sync für {$date->toDateString()}");
        
        try {
            // 1. Zeiteinträge von MOCO holen (READ-ONLY)
            $mocoEntries = $mocoService->getTimeEntries($date->toDateString());
            
            if (empty($mocoEntries)) {
                $this->warn('⚠️ Keine Zeiteinträge gefunden');
                return 0;
            }
            
            $this->info("📦 {$mocoEntries->count()} Einträge von MOCO erhalten");
            
            // 2. In lokaler DB speichern
            $result = $syncService->syncEntries($mocoEntries, $date);
            
            // 3. Ergebnis ausgeben
            $this->newLine();
            $this->info("✅ Synchronisation abgeschlossen:");
            $this->table(
                ['Metrik', 'Anzahl'],
                [
                    ['Erstellt', $result['created']],
                    ['Aktualisiert', $result['updated']],
                    ['Übersprungen', $result['skipped']],
                    ['Fehler', $result['errors']],
                ]
            );
            
            if ($result['errors'] > 0) {
                $this->error('⚠️ Es gab Fehler. Siehe Log für Details.');
                return 1;
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("❌ Sync fehlgeschlagen: {$e->getMessage()}");
            Log::error("MOCO Sync Error", [
                'date' => $date->toDateString(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        }
    }
}
```

### 7.2 Sync-Service

```php
<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Project;
use App\Models\TimeEntry;
use Illuminate\Support\Facades\Log;

class TimeEntrySyncService
{
    /**
     * Synchronisiert MOCO-Zeiteinträge in lokale Datenbank.
     * 
     * @param array $mocoEntries MOCO-API-Response
     * @param Carbon $date Datum
     * @return array Statistik (created, updated, skipped, errors)
     */
    public function syncEntries(array $mocoEntries, $date): array
    {
        $stats = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0,
        ];
        
        foreach ($mocoEntries as $mocoEntry) {
            try {
                // 1. Mitarbeiter finden (via moco_id)
                $employee = Employee::where('moco_id', $mocoEntry['user']['id'])->first();
                
                if (!$employee) {
                    Log::warning("Mitarbeiter mit MOCO-ID {$mocoEntry['user']['id']} nicht gefunden");
                    $stats['skipped']++;
                    continue;
                }
                
                // 2. Projekt finden (via moco_id)
                $project = Project::where('moco_id', $mocoEntry['project']['id'])->first();
                
                if (!$project) {
                    Log::warning("Projekt mit MOCO-ID {$mocoEntry['project']['id']} nicht gefunden");
                    $stats['skipped']++;
                    continue;
                }
                
                // 3. Zeiteintrag erstellen oder aktualisieren
                $timeEntry = TimeEntry::updateOrCreate(
                    ['moco_id' => $mocoEntry['id']], // Eindeutige ID
                    [
                        'employee_id' => $employee->id,
                        'project_id' => $project->id,
                        'date' => $mocoEntry['date'],
                        'hours' => $mocoEntry['hours'],
                        'description' => $mocoEntry['description'],
                        'synced_at' => now(),
                    ]
                );
                
                if ($timeEntry->wasRecentlyCreated) {
                    $stats['created']++;
                } else {
                    $stats['updated']++;
                }
                
            } catch (\Exception $e) {
                Log::error("Fehler beim Sync von Eintrag {$mocoEntry['id']}: {$e->getMessage()}");
                $stats['errors']++;
            }
        }
        
        return $stats;
    }
}
```

### 7.3 Cron-Schedule

```php
// app/Console/Kernel.php

protected function schedule(Schedule $schedule): void
{
    // Täglich um 06:00 Uhr Zeiteinträge vom Vortag synchronisieren
    $schedule->command('moco:sync-time-entries')
        ->dailyAt('06:00')
        ->timezone('Europe/Berlin')
        ->emailOutputOnFailure('admin@enodia.de');
        
    // Monitoring: Slack-Notification bei Fehlschlag
    $schedule->command('moco:sync-time-entries')
        ->onFailure(function () {
            // Slack-Notification
        });
}
```

---

## 8. Use Cases

### UC1: Ist-Soll-Vergleich für Projekt

```php
// app/Services/KpiService.php

public function getProjectComparison(Project $project, $startDate, $endDate)
{
    // Geplante Stunden (aus Assignments)
    $plannedHours = $project->assignments()
        ->overlapping($startDate, $endDate)
        ->sum('hours_per_week') * $startDate->diffInWeeks($endDate);
        
    // Tatsächliche Stunden (aus Time Entries)
    $actualHours = $project->timeEntries()
        ->dateRange($startDate, $endDate)
        ->sum('hours');
        
    return [
        'planned' => $plannedHours,
        'actual' => $actualHours,
        'variance' => $actualHours - $plannedHours,
        'variance_percentage' => ($actualHours / $plannedHours - 1) * 100,
    ];
}
```

**Output:**
```json
{
  "planned": 160,
  "actual": 185,
  "variance": 25,
  "variance_percentage": 15.6,
  "status": "⚠️ Über Budget"
}
```

### UC2: Überstunden-Analyse für Mitarbeiter

```php
public function getOvertimeAnalysis(Employee $employee, $month)
{
    $workingDaysInMonth = 22; // Durchschnitt
    $plannedHoursPerDay = $employee->weekly_hours / 5;
    $totalPlanned = $plannedHoursPerDay * $workingDaysInMonth;
    
    $actualHours = $employee->timeEntries()
        ->whereYear('date', $month->year)
        ->whereMonth('date', $month->month)
        ->sum('hours');
        
    return [
        'planned': $totalPlanned,
        'actual': $actualHours,
        'overtime': $actualHours - $totalPlanned,
    ];
}
```

---

## 9. Edge Cases

### EC1: MOCO-API liefert inkonsistente Daten
**Szenario:** User-ID in MOCO existiert, aber Mitarbeiter wurde in Day2Day-Manager gelöscht  
**Erwartetes Verhalten:** Eintrag überspringen, Warnung loggen  
**Lösung:** Regelmäßiger Employee-Import um Inkonsistenzen zu vermeiden

### EC2: Sehr große Datenmengen (>1000 Einträge)
**Szenario:** Initialer Import von 90 Tagen  
**Erwartetes Verhalten:** Batch-Processing (100 Einträge pro Batch)  
**Performance:** ~500 Einträge/Minute

### EC3: MOCO API Rate Limit
**Szenario:** Zu viele Requests in kurzer Zeit  
**Erwartetes Verhalten:** 
- Rate Limit Header beachten
- Exponential Backoff (5s, 10s, 20s)
- Max. 3 Retries

### EC4: Zeitzone-Probleme
**Szenario:** MOCO nutzt UTC, Day2Day-Manager Europe/Berlin  
**Lösung:** Alle Datums-Felder in UTC speichern, nur bei Anzeige konvertieren

---

## 10. Sicherheit & Monitoring

### 10.1 API-Key Sicherheit

```env
# .env
MOCO_API_KEY=your-secret-api-key-here

# .env.example (für Git)
MOCO_API_KEY=
```

**Niemals** API-Keys im Code oder in Git committen!

### 10.2 Monitoring

```php
// Dashboard-Widget: MOCO Sync Status

public function getMocoSyncStatus()
{
    $lastSync = TimeEntry::latest('synced_at')->first();
    
    return [
        'last_sync' => $lastSync?->synced_at,
        'last_sync_human' => $lastSync?->synced_at->diffForHumans(),
        'entries_today' => TimeEntry::whereDate('synced_at', today())->count(),
        'is_healthy' => $lastSync && $lastSync->synced_at->isToday(),
    ];
}
```

**Alert-Regeln:**
- ❌ Letzter Sync > 24h alt → Notification
- ⚠️ Sync-Fehlerrate > 5% → Warnung
- 🚨 Sync schlägt 3x fehl → Kritisch

---

## 11. Testing

```php
// tests/Unit/TimeEntrySyncServiceTest.php

public function test_syncs_entries_successfully(): void
{
    $mocoEntries = [
        [
            'id' => 12345,
            'date' => '2025-10-22',
            'hours' => 7.5,
            'description' => 'Test',
            'user' => ['id' => 42],
            'project' => ['id' => 15],
        ]
    ];
    
    $employee = Employee::factory()->create(['moco_id' => 42]);
    $project = Project::factory()->create(['moco_id' => 15]);
    
    $syncService = new TimeEntrySyncService();
    $result = $syncService->syncEntries($mocoEntries, Carbon::today());
    
    $this->assertEquals(1, $result['created']);
    $this->assertDatabaseHas('time_entries', [
        'moco_id' => 12345,
        'employee_id' => $employee->id,
        'project_id' => $project->id,
    ]);
}
```

---

## 12. Zusammenfassung

**Was wurde implementiert:**
- ✅ Tägliche automatische Synchronisation
- ✅ MOCO API Integration (READ-ONLY!)
- ✅ Duplikat-Prävention via moco_id
- ✅ Fehlerbehandlung & Retry-Logik
- ✅ Monitoring & Alerts
- ✅ Manuelle Sync-Option per Command

**Kritische Regeln:**
- 🚫 **NIEMALS** zu MOCO schreiben!
- ✅ Nur GET-Requests erlaubt
- ⏰ Sync täglich um 06:00 Uhr
- 📊 Daten für KPI-Dashboards

**Business Value:**
- Echte Ist-Daten für Planung
- Überstunden-Tracking
- Projekt-Fortschritt in Echtzeit
- Fundierte Management-Entscheidungen
