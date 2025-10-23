# Debugging Chatmode

## Zweck
Dieser Chatmode hilft bei der Analyse und Behebung von Laravel-Fehlern. Fokus auf häufige Fehler in Day2Day-Manager und systematisches Debugging.

## 🔍 Systematisches Debugging-Vorgehen

### 1. Fehler lesen und verstehen
### 2. Stack Trace analysieren
### 3. Logs prüfen
### 4. Hypothese aufstellen
### 5. Testen und verifizieren

## Häufige Laravel-Fehler in Day2Day-Manager

### 1. 404 - Route Not Found

```
404 | NOT FOUND
```

**Ursachen:**
- Route nicht definiert
- Tippfehler in der URL
- Route-Cache veraltet

**Debugging:**
```bash
# 1. Alle Routen anzeigen
php artisan route:list

# 2. Nach spezifischer Route suchen
php artisan route:list | grep employees

# 3. Route-Cache löschen
php artisan route:clear
```

**Lösung:**
```php
// routes/web.php
Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
```

### 2. Class Not Found

```
Error: Class "App\Http\Controllers\EmployeeController" not found
```

**Ursachen:**
- Controller-Datei existiert nicht
- Namespace falsch
- Composer-Autoload nicht aktualisiert

**Debugging:**
```bash
# 1. Prüfen, ob Datei existiert
ls app/Http/Controllers/EmployeeController.php

# 2. Composer-Autoload neu generieren
composer dump-autoload

# 3. Cache löschen
php artisan clear-compiled
```

**Lösung:**
```php
<?php
// Namespace MUSS mit Verzeichnisstruktur übereinstimmen
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Employee;

class EmployeeController extends Controller
{
    // ...
}
```

### 3. View Not Found

```
InvalidArgumentException: View [employees.index] not found
```

**Ursachen:**
- Blade-Datei existiert nicht
- Dateipfad falsch
- Dateiendung fehlt (.blade.php)

**Debugging:**
```bash
# 1. Prüfen, ob View existiert
ls resources/views/employees/index.blade.php

# 2. View-Cache löschen
php artisan view:clear

# 3. Views neu kompilieren
php artisan view:cache
```

**Lösung:**
```php
// Blade-Notation: Punkte trennen Verzeichnisse
// employees.index → resources/views/employees/index.blade.php
return view('employees.index', compact('employees'));
```

### 4. Undefined Variable

```
ErrorException: Undefined variable $employees
```

**Ursachen:**
- Variable nicht an View übergeben
- Tippfehler im Variablennamen
- Variable ist null

**Debugging:**
```php
// Im Controller:
public function index()
{
    $employees = Employee::all();
    
    // Debug: Was ist in $employees?
    dd($employees); // Stoppt hier und zeigt Inhalt
    
    return view('employees.index', compact('employees'));
}
```

**Lösung:**
```php
// ✅ RICHTIG: Variable übergeben
return view('employees.index', [
    'employees' => $employees
]);

// Oder mit compact():
return view('employees.index', compact('employees'));
```

**Absicherung in der View:**
```blade
@if(isset($employees) && $employees->count() > 0)
    @foreach($employees as $employee)
        {{ $employee->name }}
    @endforeach
@else
    <p>Keine Mitarbeiter gefunden.</p>
@endif
```

### 5. SQLSTATE Errors (Datenbankfehler)

```
SQLSTATE[42S02]: Base table or view not found: 1146 Table 'employees' doesn't exist
```

**Ursachen:**
- Migrationen nicht ausgeführt
- Falsche Datenbank-Verbindung
- Tabellenname falsch

**Debugging:**
```bash
# 1. Prüfen, welche Migrationen gelaufen sind
php artisan migrate:status

# 2. Datenbank-Verbindung testen
php artisan tinker
>>> DB::connection()->getPdo()

# 3. Migrationen ausführen
php artisan migrate

# 4. Für Development: Neu aufsetzen
php artisan migrate:fresh --seed
```

**Häufige Datenbankfehler:**

```
# Unique Constraint Violation
SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry

Lösung: Prüfe auf doppelte Einträge vor dem Insert
```

```
# Foreign Key Constraint
SQLSTATE[23000]: Cannot add or update a child row: a foreign key constraint fails

Lösung: Stelle sicher, dass referenzierte Datensätze existieren
```

### 6. Call to a member function on null

```
Error: Call to a member function save() on null
```

**Ursachen:**
- Datensatz nicht gefunden (find() gibt null zurück)
- Relationship nicht geladen

**Debugging:**
```php
// ❌ GEFAHR: find() kann null zurückgeben
$employee = Employee::find($id);
$employee->save(); // Fehler wenn $id nicht existiert!

// ✅ BESSER: findOrFail() wirft 404
$employee = Employee::findOrFail($id);
$employee->save(); // Sicher

// ✅ ODER: Null-Check
$employee = Employee::find($id);
if ($employee) {
    $employee->save();
} else {
    abort(404, 'Mitarbeiter nicht gefunden');
}
```

### 7. Mass Assignment Exception

```
Illuminate\Database\Eloquent\MassAssignmentException: Add [name] to fillable property
```

**Ursachen:**
- Feld nicht in $fillable definiert
- Sicherheitsfeature von Laravel

**Lösung:**
```php
// Im Model
class Employee extends Model
{
    protected $fillable = [
        'name',
        'email',
        'weekly_hours',
        // Alle Felder die per create() gesetzt werden sollen
    ];
}
```

### 8. Token Mismatch (CSRF)

```
419 | PAGE EXPIRED
TokenMismatchException
```

**Ursachen:**
- CSRF-Token fehlt in Form
- Session abgelaufen
- Cookie-Problem

**Lösung:**
```blade
{{-- IMMER @csrf in Formularen! --}}
<form method="POST" action="/employees">
    @csrf
    {{-- Formularfelder --}}
    <button type="submit">Speichern</button>
</form>
```

## Stack Trace richtig lesen

```
Exception in EmployeeController.php line 42:
Call to undefined method App\Models\Employee::getAllActive()

#0 app/Http/Controllers/EmployeeController.php(42): App\Models\Employee::getAllActive()
#1 vendor/laravel/framework/src/Illuminate/Routing/Controller.php(54): App\Http\Controllers\EmployeeController->index()
#2 vendor/laravel/framework/src/Illuminate/Routing/ControllerDispatcher.php(45): Illuminate\Routing\Controller->callAction()
```

**Was sagt uns das?**
1. **Fehler:** `Call to undefined method Employee::getAllActive()`
2. **Wo:** `EmployeeController.php` Zeile 42
3. **Aufruf-Kette:** EmployeeController → Laravel Router → Request

**Debugging-Schritte:**
1. Gehe zu `EmployeeController.php` Zeile 42
2. Prüfe: Existiert die Methode `getAllActive()` im Employee Model?
3. Wenn nicht: Entweder erstellen oder richtigen Methodennamen verwenden

## Debugging-Commands

### Artisan-Befehle zum Debuggen

```bash
# Cache löschen (oft hilfreich!)
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Alles auf einmal
php artisan optimize:clear

# Datenbank-Status
php artisan migrate:status
php artisan db:show

# Tinker (interaktive Shell)
php artisan tinker
>>> Employee::count()
>>> Employee::find(1)
>>> Employee::where('name', 'LIKE', '%Max%')->get()
```

### Log-Analyse

```bash
# Logs in Echtzeit verfolgen
tail -f storage/logs/laravel.log

# Letzte 50 Zeilen
tail -n 50 storage/logs/laravel.log

# Nach Fehler suchen
grep -i "error" storage/logs/laravel.log
grep -i "exception" storage/logs/laravel.log
```

### Debug in Code einfügen

```php
// 1. dd() - Dump and Die (zeigt und stoppt)
dd($employee, $projects, $assignments);

// 2. dump() - Zeigt an, läuft weiter
dump('Debug-Punkt erreicht');
dump($employee);

// 3. Log schreiben
use Illuminate\Support\Facades\Log;

Log::debug('Employee geladen', ['id' => $employee->id]);
Log::info('Assignments gezählt', ['count' => $assignments->count()]);
Log::warning('Überbuchung erkannt!', ['employee' => $employee->name]);
Log::error('MOCO-Sync fehlgeschlagen', ['error' => $exception->getMessage()]);

// 4. Query-Logging aktivieren
DB::enableQueryLog();
$employees = Employee::with('assignments')->get();
dd(DB::getQueryLog()); // Zeigt alle SQL-Queries
```

## Häufige Day2Day-Manager-spezifische Probleme

### Problem: MOCO-API gibt 401 zurück

```
HTTP 401 Unauthorized
```

**Debugging:**
```php
// 1. API-Key prüfen
dd(config('services.moco.api_key'));

// 2. .env prüfen
MOCO_API_KEY=your-key-here

// 3. Config-Cache löschen
php artisan config:clear

// 4. Request testen
$response = Http::withToken(config('services.moco.api_key'))
    ->get('https://enodia.mocoapp.com/api/v1/users');
    
dd($response->status(), $response->body());
```

### Problem: Kapazitätsberechnung falsch

```php
// Debugging-Strategie:
public function calculateAvailableCapacity(Employee $employee)
{
    // 1. Basis prüfen
    $baseHours = $employee->weekly_hours;
    dump("Base: {$baseHours}");
    
    // 2. Gebuchte Stunden
    $bookedHours = $employee->assignments->sum('hours_per_week');
    dump("Booked: {$bookedHours}");
    
    // 3. Abwesenheiten
    $absenceHours = $this->calculateAbsences($employee);
    dump("Absences: {$absenceHours}");
    
    // 4. Ergebnis
    $available = $baseHours - $bookedHours - $absenceHours;
    dd("Available: {$available}");
    
    return $available;
}
```

### Problem: N+1 Query-Problem

```bash
# Laravel Debugbar installieren (nur in Development!)
composer require barryvdh/laravel-debugbar --dev

# Zeigt alle Queries im Browser
# Wenn 50+ Queries für eine Seite → N+1 Problem!
```

**Lösung:**
```php
// ❌ LANGSAM: N+1
$employees = Employee::all();
foreach ($employees as $employee) {
    echo $employee->assignments->count(); // Query für jeden Employee!
}

// ✅ SCHNELL: Eager Loading
$employees = Employee::withCount('assignments')->get();
foreach ($employees as $employee) {
    echo $employee->assignments_count; // Keine extra Query!
}
```

## Debugging-Checkliste

Wenn etwas nicht funktioniert, gehe diese Liste durch:

- [ ] **Error Message gelesen?** Was sagt der Fehler genau?
- [ ] **Stack Trace analysiert?** Wo genau tritt der Fehler auf?
- [ ] **Logs geprüft?** `storage/logs/laravel.log` checken
- [ ] **Cache gelöscht?** `php artisan optimize:clear`
- [ ] **Migrationen laufen?** `php artisan migrate:status`
- [ ] **Composer aktuell?** `composer dump-autoload`
- [ ] **Umgebung richtig?** `.env` Datei prüfen
- [ ] **dd() eingebaut?** Variablen ausgeben und prüfen
- [ ] **Tinker getestet?** Im REPL testen
- [ ] **Dokumentation gelesen?** Laravel-Docs checken

## Profi-Tipps

### 1. Ray verwenden (modernes Debugging-Tool)

```bash
composer require spatie/laravel-ray --dev
```

```php
// Besser als dd() - zeigt in separatem Fenster
ray($employee);
ray('Debug-Punkt erreicht')->blue();
ray()->pause(); // Pausiert Ausführung
```

### 2. Telescope für Request-Analyse

```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

Öffne `/telescope` im Browser → Siehe alle Requests, Queries, Jobs, etc.

### 3. Clockwork für Performance-Analyse

```bash
composer require itsgoingd/clockwork --dev
```

Browser-Extension installieren → Zeigt Performance-Daten

## Zusammenfassung

**Bei Fehlern:**
1. Fehlermeldung genau lesen
2. Stack Trace von oben nach unten
3. Logs prüfen
4. dd() strategisch einsetzen
5. Cache löschen
6. Tinker zum Testen nutzen

**Häufigste Fehler:**
- Route nicht definiert → `route:list`
- View nicht gefunden → Dateipfad prüfen
- Variable undefined → An View übergeben?
- SQLSTATE → Migrationen laufen lassen
- CSRF Token → @csrf vergessen

**Tools:**
- `php artisan optimize:clear` (Cache löschen)
- `php artisan tinker` (Interaktiv testen)
- Laravel Debugbar (Query-Analyse)
- Telescope (Request-Monitoring)
