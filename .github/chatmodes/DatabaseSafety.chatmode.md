# Database Safety Chatmode

## Zweck
Dieser Chatmode schützt vor versehentlichen Änderungen an Produktionsdaten und stellt sicher, dass die MOCO-API niemals für Schreibvorgänge verwendet wird.

## 🚨 KRITISCHE REGELN

### MOCO ist READ-ONLY!
```php
// ✅ ERLAUBT: Daten von MOCO lesen
$response = Http::get('https://enodia.mocoapp.com/api/v1/users');

// 🚫 VERBOTEN: Daten zu MOCO schreiben
Http::post('https://enodia.mocoapp.com/...');   // NIEMALS!
Http::patch('https://enodia.mocoapp.com/...');  // NIEMALS!
Http::delete('https://enodia.mocoapp.com/...');  // NIEMALS!
```

**WARUM?** MOCO ist Firmeneigentum und wird von anderen Systemen genutzt. Day2Day-Manager darf nur lesen!

### Produktionsdatenbank-Schutz

Immer Umgebung prüfen vor gefährlichen Operationen:

```php
// ✅ SICHER: Umgebungsprüfung vor Seedern
if (app()->environment('production')) {
    throw new \Exception('⛔ Seeder dürfen nicht in Produktion ausgeführt werden!');
}

// ✅ SICHER: Umgebungsprüfung vor Migrate Fresh
if (app()->environment('production')) {
    $this->error('⛔ migrate:fresh ist in Produktion VERBOTEN!');
    return 1;
}
```

### Sichere Seeder-Patterns

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DevelopmentSeeder extends Seeder
{
    public function run(): void
    {
        // 🔒 Produktionsschutz
        if (app()->environment('production')) {
            throw new \Exception(
                '⛔ DevelopmentSeeder darf nicht in Produktion laufen! ' .
                'Nutze stattdessen MOCO-Import.'
            );
        }

        // ✅ Nur in dev/local/testing
        $this->call([
            EmployeeSeeder::class,
            ProjectSeeder::class,
            AssignmentSeeder::class,
        ]);
        
        $this->command->info('✅ Entwicklungsdaten erfolgreich geladen');
    }
}
```

### Datenbankmigrationen sicher gestalten

```php
// ✅ SICHER: Migrationen sind non-destructive
public function up(): void
{
    Schema::create('assignments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('employee_id')->constrained();
        $table->foreignId('project_id')->constrained();
        $table->decimal('hours_per_week', 5, 2);
        $table->date('start_date');
        $table->date('end_date')->nullable();
        $table->timestamps();
    });
}

// 🚫 GEFÄHRLICH: Ohne Backup/Prüfung
public function up(): void
{
    Schema::dropIfExists('time_entries'); // ⛔ Datenverlust!
}

// ✅ BESSER: Mit Sicherheitsprüfung
public function up(): void
{
    if (app()->environment('production')) {
        throw new \Exception('Diese Migration löscht Daten! Erst Backup erstellen!');
    }
    Schema::dropIfExists('time_entries');
}
```

## Environment Checks

### Standard-Prüfungen für gefährliche Operationen

```php
// Commands die Daten ändern/löschen
class DangerousCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->confirm('⚠️ Dies löscht Daten. Fortfahren?')) {
            return 1;
        }
        
        if (app()->environment('production')) {
            $this->error('⛔ In Produktion nicht erlaubt!');
            return 1;
        }
        
        // Sichere Ausführung
        return 0;
    }
}
```

### MOCO-Import Commands (READ-ONLY)

```php
class ImportMocoDataCommand extends Command
{
    protected $signature = 'moco:import {type}';
    protected $description = 'Import data from MOCO (READ-ONLY)';
    
    public function handle(): int
    {
        $this->info('📥 Starte MOCO-Import (READ-ONLY)...');
        
        // ✅ NUR GET-Requests
        $response = Http::withToken(config('services.moco.api_key'))
            ->get('https://enodia.mocoapp.com/api/v1/' . $this->argument('type'));
            
        if ($response->successful()) {
            // Daten in Day2Day-Manager speichern
            $this->processImport($response->json());
            $this->info('✅ Import erfolgreich');
        }
        
        return 0;
    }
    
    // 🚫 NIEMALS sowas implementieren:
    // public function syncToMoco() { ... } // VERBOTEN!
}
```

## Test-Daten vs. Production-Daten

### Entwicklung (local/testing)
```php
// ✅ Dummy-Daten für Entwicklung
Employee::factory()->count(10)->create();
Project::factory()->count(5)->create();
```

### Staging (staging)
```php
// ✅ MOCO-Import oder anonymisierte Produktionsdaten
php artisan moco:import employees
php artisan moco:import projects
```

### Produktion (production)
```php
// ✅ NUR MOCO-Import, KEINE Seeders!
php artisan moco:import employees
php artisan moco:import projects
php artisan moco:sync-time-entries  // Täglicher Cron-Job
```

## Warnhinweise

Wenn jemand fragt nach:
- "Wie synchronisiere ich Daten ZURÜCK zu MOCO?" → **🚫 Das ist nicht erlaubt!**
- "Wie update ich einen MOCO-Zeiteintrag?" → **🚫 Day2Day-Manager schreibt nicht zu MOCO!**
- "Kann ich migrate:fresh in Produktion nutzen?" → **🚫 NIEMALS! Datenverlust!**
- "Warum funktioniert mein Http::post zu MOCO nicht?" → **🚫 Weil es verboten ist!**

## Checkliste für sichere Datenbank-Operations

- [ ] Umgebung prüfen (`app()->environment()`)
- [ ] Bestätigung einholen bei kritischen Aktionen
- [ ] Nur GET-Requests zu MOCO API
- [ ] Keine destructive Migrationen ohne Backup
- [ ] Seeders nur in dev/testing
- [ ] Logging für alle Datenänderungen
- [ ] Transactions für zusammenhängende Operations

## Beispiel: Sicherer MOCO-Service

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MocoService
{
    private const BASE_URL = 'https://enodia.mocoapp.com/api/v1';
    
    /**
     * ✅ READ-ONLY: Mitarbeiter von MOCO holen
     */
    public function getEmployees(): array
    {
        Log::info('📥 MOCO: Lade Mitarbeiter (READ-ONLY)');
        
        $response = Http::withToken($this->getApiKey())
            ->get(self::BASE_URL . '/users');
            
        return $response->successful() ? $response->json() : [];
    }
    
    /**
     * ✅ READ-ONLY: Zeiteinträge von MOCO holen
     */
    public function getTimeEntries(string $date): array
    {
        Log::info("📥 MOCO: Lade Zeiteinträge für {$date} (READ-ONLY)");
        
        $response = Http::withToken($this->getApiKey())
            ->get(self::BASE_URL . '/activities', [
                'from' => $date,
                'to' => $date,
            ]);
            
        return $response->successful() ? $response->json() : [];
    }
    
    /**
     * 🚫 DIESE METHODEN EXISTIEREN NICHT!
     * Wenn du sie brauchst, machst du etwas falsch.
     */
    // public function createTimeEntry() { ... }  // VERBOTEN!
    // public function updateEmployee() { ... }    // VERBOTEN!
    // public function deleteProject() { ... }     // VERBOTEN!
    
    private function getApiKey(): string
    {
        return config('services.moco.api_key');
    }
}
```

## Zusammenfassung

**✅ ERLAUBT:**
- Daten von MOCO importieren (GET)
- Daten in Day2Day-Manager bearbeiten
- Seeders in dev/testing

**🚫 VERBOTEN:**
- Zu MOCO schreiben (POST/PATCH/DELETE)
- migrate:fresh in Produktion
- Seeders in Produktion

**⚠️ VORSICHT:**
- Immer Umgebung prüfen
- Backups vor Migrationen
- Bestätigung bei Datenlöschung
