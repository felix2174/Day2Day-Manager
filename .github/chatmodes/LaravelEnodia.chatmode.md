# Laravel enodia Chatmode

## Zweck
Dieser Chatmode fokussiert auf Laravel Best Practices speziell für Day2Day-Manager, mit Schwerpunkt auf Business-Logik für Kapazitätsplanung, KPIs und Ressourcen-Management.

## Code-Stil für enodia

### Deutsche Kommentare für Business-Logik
```php
<?php

namespace App\Services;

class CapacityPlanningService
{
    /**
     * Berechnet die verfügbare Kapazität eines Mitarbeiters für einen Zeitraum.
     * 
     * Berücksichtigt:
     * - Wochenarbeitszeit (Standard: 40h)
     * - Urlaub und Krankheit
     * - Feiertage
     * - Bestehende Projektbuchungen
     * 
     * @param Employee $employee Der Mitarbeiter
     * @param Carbon $startDate Startdatum
     * @param Carbon $endDate Enddatum
     * @return float Verfügbare Stunden
     */
    public function calculateAvailableCapacity(
        Employee $employee,
        Carbon $startDate,
        Carbon $endDate
    ): float {
        // Basiskapazität: Wochenarbeitszeit * Anzahl Wochen
        $baseCapacity = $employee->weekly_hours * $this->getWeeks($startDate, $endDate);
        
        // Abzüge: Urlaub, Krankheit, Feiertage
        $absenceHours = $this->calculateAbsenceHours($employee, $startDate, $endDate);
        
        // Bereits gebuchte Stunden
        $bookedHours = $this->getBookedHours($employee, $startDate, $endDate);
        
        return max(0, $baseCapacity - $absenceHours - $bookedHours);
    }
}
```

### Englische technische Begriffe
```php
// ✅ GUT: Business-Begriffe auf Deutsch, technische auf Englisch
class Assignment extends Model
{
    protected $fillable = [
        'employee_id',
        'project_id',
        'hours_per_week',  // Technisch
        'start_date',       // Technisch
        'end_date',         // Technisch
    ];
    
    /**
     * Prüft, ob die Zuordnung den Mitarbeiter überbucht.
     * 
     * Überprüfung basiert auf:
     * - Wochenarbeitszeit des Mitarbeiters
     * - Parallele Projektbuchungen
     * - Geplante Abwesenheiten
     */
    public function isOverbooking(): bool
    {
        // Implementation...
    }
}
```

## Laravel Best Practices für Day2Day-Manager

### 1. Eloquent Relationships

```php
// Employee Model
class Employee extends Model
{
    // Beziehungen
    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }
    
    public function absences()
    {
        return $this->hasMany(Absence::class);
    }
    
    public function timeEntries()
    {
        return $this->hasMany(TimeEntry::class);
    }
    
    // Scopes für Business-Logic
    public function scopeAvailable($query, $date)
    {
        return $query->whereDoesntHave('absences', function ($q) use ($date) {
            $q->where('start_date', '<=', $date)
              ->where('end_date', '>=', $date);
        });
    }
    
    // Accessor für Ampelfarbe
    public function getUtilizationColorAttribute(): string
    {
        $utilization = $this->current_utilization;
        
        if ($utilization < 80) return 'green';
        if ($utilization < 100) return 'yellow';
        return 'red';
    }
}
```

### 2. Service-Klassen für komplexe Business-Logik

```php
<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Project;
use Carbon\Carbon;

class KpiAnalysisService
{
    /**
     * Berechnet Team-KPIs für ein Projekt.
     * 
     * Metriken:
     * - Durchschnittliche Auslastung
     * - Projektfortschritt (Ist vs. Soll)
     * - Überstunden
     * - Risiko-Score
     */
    public function calculateProjectKpis(Project $project): array
    {
        $assignments = $project->assignments()->with('employee', 'timeEntries')->get();
        
        return [
            'team_utilization' => $this->calculateTeamUtilization($assignments),
            'progress_percentage' => $this->calculateProgress($project),
            'hours_planned' => $this->sumPlannedHours($assignments),
            'hours_actual' => $this->sumActualHours($assignments),
            'variance' => $this->calculateVariance($project),
            'risk_level' => $this->assessRisk($project),
            'burndown_data' => $this->generateBurndownData($project),
        ];
    }
    
    /**
     * Findet überbuchte Mitarbeiter im Zeitraum.
     * 
     * Ein Mitarbeiter ist überbucht, wenn die Summe
     * seiner Projektbuchungen > 100% seiner Wochenarbeitszeit ist.
     */
    public function findOverbookedEmployees(Carbon $startDate, Carbon $endDate): Collection
    {
        return Employee::with(['assignments' => function ($query) use ($startDate, $endDate) {
            $query->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate]);
            });
        }])
        ->get()
        ->filter(function ($employee) {
            $totalHours = $employee->assignments->sum('hours_per_week');
            return $totalHours > $employee->weekly_hours;
        });
    }
}
```

### 3. Repository Pattern für Datenabfragen

```php
<?php

namespace App\Repositories;

use App\Models\TimeEntry;
use Carbon\Carbon;

class TimeEntryRepository
{
    /**
     * Holt Zeiteinträge für Ist-Soll-Vergleich.
     * 
     * Gruppiert nach Projekt und Mitarbeiter für KPI-Dashboards.
     */
    public function getForComparison(Carbon $startDate, Carbon $endDate): Collection
    {
        return TimeEntry::with(['employee', 'project'])
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->groupBy(['project_id', 'employee_id'])
            ->map(function ($entries) {
                return [
                    'planned_hours' => $entries->sum('planned_hours'),
                    'actual_hours' => $entries->sum('actual_hours'),
                    'variance' => $entries->sum('actual_hours') - $entries->sum('planned_hours'),
                ];
            });
    }
    
    /**
     * Synchronisiert Zeiteinträge von MOCO (READ-ONLY).
     * 
     * Wird täglich per Cron-Job ausgeführt.
     */
    public function syncFromMoco(Carbon $date): int
    {
        $mocoService = app(MocoService::class);
        $entries = $mocoService->getTimeEntries($date->toDateString());
        
        $synced = 0;
        foreach ($entries as $entry) {
            TimeEntry::updateOrCreate(
                ['moco_id' => $entry['id']], // Externe ID
                [
                    'employee_id' => $this->mapMocoUser($entry['user']['id']),
                    'project_id' => $this->mapMocoProject($entry['project']['id']),
                    'date' => $entry['date'],
                    'hours' => $entry['hours'],
                    'description' => $entry['description'],
                    'synced_at' => now(),
                ]
            );
            $synced++;
        }
        
        return $synced;
    }
}
```

### 4. Jobs für asynchrone Verarbeitung

```php
<?php

namespace App\Jobs;

use App\Services\MocoService;
use App\Repositories\TimeEntryRepository;
use Carbon\Carbon;

class SyncMocoTimeEntriesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function __construct(
        private Carbon $date
    ) {}
    
    /**
     * Synchronisiert MOCO-Zeiteinträge (READ-ONLY).
     * 
     * Wird täglich ausgeführt für:
     * - Ist-Soll-Vergleich
     * - Burndown-Charts
     * - Projektfortschritt
     */
    public function handle(TimeEntryRepository $repository): void
    {
        Log::info("📥 Starte MOCO-Sync für {$this->date->toDateString()}");
        
        try {
            $synced = $repository->syncFromMoco($this->date);
            
            Log::info("✅ {$synced} Zeiteinträge synchronisiert");
            
            // KPI-Cache invalidieren
            Cache::tags(['kpis', 'dashboard'])->flush();
            
        } catch (\Exception $e) {
            Log::error("❌ MOCO-Sync fehlgeschlagen: {$e->getMessage()}");
            throw $e; // Für Retry-Mechanismus
        }
    }
}
```

### 5. Form Requests für Validation

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssignmentRequest extends FormRequest
{
    /**
     * Validierungsregeln für Projektbuchungen.
     * 
     * Business Rules:
     * - Startdatum muss vor Enddatum liegen
     * - Stunden pro Woche dürfen Mitarbeiter nicht überbuchen
     * - Projekt und Mitarbeiter müssen existieren
     */
    public function rules(): array
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'project_id' => 'required|exists:projects,id',
            'hours_per_week' => 'required|numeric|min:0|max:40',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
        ];
    }
    
    /**
     * Custom Validation: Prüfung auf Überbuchung.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->wouldCauseOverbooking()) {
                $validator->errors()->add(
                    'hours_per_week',
                    'Diese Buchung würde den Mitarbeiter überbuchen.'
                );
            }
        });
    }
    
    private function wouldCauseOverbooking(): bool
    {
        $employee = Employee::find($this->employee_id);
        $existingHours = $employee->assignments()
            ->where('id', '!=', $this->route('assignment'))
            ->whereOverlapping($this->start_date, $this->end_date)
            ->sum('hours_per_week');
            
        return ($existingHours + $this->hours_per_week) > $employee->weekly_hours;
    }
}
```

### 6. Resource Controllers mit API-Responses

```php
<?php

namespace App\Http\Controllers\Api;

use App\Models\Employee;
use App\Services\CapacityPlanningService;
use Illuminate\Http\JsonResponse;

class CapacityController extends Controller
{
    public function __construct(
        private CapacityPlanningService $capacityService
    ) {}
    
    /**
     * GET /api/capacity/available
     * 
     * Zeigt verfügbare Kapazitäten aller Mitarbeiter.
     */
    public function available(Request $request): JsonResponse
    {
        $startDate = Carbon::parse($request->input('start_date'));
        $endDate = Carbon::parse($request->input('end_date'));
        
        $capacities = Employee::all()->map(function ($employee) use ($startDate, $endDate) {
            return [
                'id' => $employee->id,
                'name' => $employee->name,
                'available_hours' => $this->capacityService->calculateAvailableCapacity(
                    $employee,
                    $startDate,
                    $endDate
                ),
                'utilization' => $employee->current_utilization,
                'status_color' => $employee->utilization_color,
            ];
        });
        
        return response()->json($capacities);
    }
    
    /**
     * GET /api/capacity/overbooked
     * 
     * Zeigt überbuchte Mitarbeiter (Kapazitätsengpässe).
     */
    public function overbooked(Request $request): JsonResponse
    {
        $startDate = Carbon::parse($request->input('start_date', now()));
        $endDate = Carbon::parse($request->input('end_date', now()->addMonth()));
        
        $overbooked = $this->capacityService->findOverbookedEmployees($startDate, $endDate);
        
        return response()->json([
            'count' => $overbooked->count(),
            'employees' => $overbooked,
            'severity' => $overbooked->count() > 5 ? 'critical' : 'warning',
        ]);
    }
}
```

## Testing Best Practices

### Feature Tests für Business-Logik

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Employee;
use App\Models\Assignment;

class CapacityPlanningTest extends TestCase
{
    /**
     * Test: Mitarbeiter ist überbucht, wenn Summe > 100%.
     */
    public function test_detects_overbooking(): void
    {
        $employee = Employee::factory()->create(['weekly_hours' => 40]);
        
        // Buchung 1: 30 Stunden/Woche
        Assignment::factory()->create([
            'employee_id' => $employee->id,
            'hours_per_week' => 30,
            'start_date' => now(),
            'end_date' => now()->addMonth(),
        ]);
        
        // Buchung 2: 20 Stunden/Woche (überbucht!)
        Assignment::factory()->create([
            'employee_id' => $employee->id,
            'hours_per_week' => 20,
            'start_date' => now(),
            'end_date' => now()->addMonth(),
        ]);
        
        $this->assertTrue($employee->fresh()->isOverbooked());
    }
}
```

## Zusammenfassung

**Code-Stil:**
- Deutsche Kommentare für Business-Logik
- Englische technische Begriffe
- Sprechende Methodennamen
- Type Hints überall

**Architektur:**
- Services für Business-Logik
- Repositories für Datenabfragen
- Jobs für asynchrone Tasks
- Form Requests für Validation

**Business-Fokus:**
- Kapazitätsplanung
- KPI-Dashboards
- Ist-Soll-Vergleich
- Überbuchungs-Erkennung
- Zeiteintrags-Synchronisation (READ-ONLY von MOCO)
