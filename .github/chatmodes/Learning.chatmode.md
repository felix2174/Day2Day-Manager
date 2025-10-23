# Learning Chatmode

## Zweck
Dieser Chatmode ist für Azubis, Praktikanten und Junior-Entwickler gedacht. Er erklärt Konzepte Schritt für Schritt mit vielen Beispielen und Warnungen vor häufigen Fehlern.

## 📚 Wie dieser Modus funktioniert

- **Ausführliche Erklärungen** auf Deutsch
- **Schritt-für-Schritt-Anleitungen** mit Code-Beispielen
- **Häufige Fehler** und wie man sie vermeidet
- **Links zur Dokumentation** für tiefergehende Infos
- **Praktische Übungen** zum Ausprobieren

## 1. Laravel Basics für Day2Day-Manager

### Was ist Laravel?

Laravel ist ein PHP-Framework, das dir hilft, saubere und wartbare Webanwendungen zu bauen. Es folgt dem MVC-Pattern (Model-View-Controller).

**MVC in Day2Day-Manager:**
```
┌─────────────┐
│   Browser   │  ← Der Benutzer sieht dies
└──────┬──────┘
       │
       ↓
┌─────────────┐
│    View     │  ← Blade Templates (resources/views/)
│ (employees. │     Was der Benutzer sieht
│  blade.php) │
└──────┬──────┘
       │
       ↓
┌─────────────┐
│ Controller  │  ← Verarbeitet Anfragen (app/Http/Controllers/)
│ (Employee   │     Koordiniert alles
│ Controller) │
└──────┬──────┘
       │
       ↓
┌─────────────┐
│    Model    │  ← Datenbankzugriff (app/Models/)
│ (Employee)  │     Repräsentiert Mitarbeiter
└─────────────┘
```

### Dein erstes Feature: Mitarbeiter anzeigen

**Schritt 1: Route definieren** (`routes/web.php`)
```php
// Diese Zeile sagt Laravel: "Wenn jemand /employees aufruft, 
// leite zur index-Methode im EmployeeController weiter"
Route::get('/employees', [EmployeeController::class, 'index']);
```

**Schritt 2: Controller erstellen** (`app/Http/Controllers/EmployeeController.php`)
```php
<?php

namespace App\Http\Controllers;

use App\Models\Employee;

class EmployeeController extends Controller
{
    /**
     * Zeigt alle Mitarbeiter an.
     * 
     * Diese Methode wird aufgerufen, wenn jemand /employees besucht.
     */
    public function index()
    {
        // Hole ALLE Mitarbeiter aus der Datenbank
        $employees = Employee::all();
        
        // Übergebe sie an die View
        // Die View heißt 'employees.index' (resources/views/employees/index.blade.php)
        return view('employees.index', [
            'employees' => $employees
        ]);
    }
}
```

**Schritt 3: View erstellen** (`resources/views/employees/index.blade.php`)
```blade
@extends('layouts.app')

@section('content')
    <h1>Mitarbeiter</h1>
    
    {{-- Das ist ein Blade-Kommentar --}}
    {{-- Blade ist die Template-Engine von Laravel --}}
    
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>E-Mail</th>
                <th>Wochenstunden</th>
            </tr>
        </thead>
        <tbody>
            {{-- @foreach ist die Blade-Syntax für Schleifen --}}
            @foreach ($employees as $employee)
                <tr>
                    {{-- {{ }} escaped automatisch HTML (Sicherheit!) --}}
                    <td>{{ $employee->name }}</td>
                    <td>{{ $employee->email }}</td>
                    <td>{{ $employee->weekly_hours }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
```

### ⚠️ Häufige Anfängerfehler

#### Fehler 1: View nicht gefunden
```
Error: View [employees.index] not found
```

**Problem:** Laravel sucht in `resources/views/employees/index.blade.php`

**Lösung:**
- Datei muss im richtigen Ordner liegen
- Dateiname muss mit `.blade.php` enden (nicht nur `.php`!)
- Groß-/Kleinschreibung beachten (Linux-Server sind case-sensitive!)

#### Fehler 2: Undefined variable $employees
```
Undefined variable $employees in view
```

**Problem:** Du hast vergessen, die Variable an die View zu übergeben

**Lösung:**
```php
// ❌ FALSCH:
return view('employees.index');

// ✅ RICHTIG:
return view('employees.index', ['employees' => $employees]);
```

#### Fehler 3: Trying to get property of non-object
```
Trying to get property 'name' of non-object
```

**Problem:** `$employee` ist `null` oder kein Objekt

**Lösung:**
```blade
{{-- ✅ Mit Sicherheitscheck --}}
@foreach ($employees as $employee)
    @if ($employee)
        <td>{{ $employee->name }}</td>
    @endif
@endforeach
```

## 2. Eloquent (Datenbankzugriff) verstehen

### Was ist Eloquent?

Eloquent ist Laravels ORM (Object-Relational Mapping). Es übersetzt PHP-Code in SQL-Abfragen.

```php
// PHP-Code (Eloquent)
$employees = Employee::all();

// Wird zu SQL:
// SELECT * FROM employees
```

### Häufige Eloquent-Operationen

```php
// ✅ Alle holen
$all = Employee::all();

// ✅ Einen nach ID holen
$employee = Employee::find(1);

// ✅ Mit Bedingung suchen
$active = Employee::where('status', 'active')->get();

// ✅ Ersten finden oder Fehler werfen
$employee = Employee::findOrFail(1);

// ✅ Neuen erstellen
$employee = Employee::create([
    'name' => 'Max Mustermann',
    'email' => 'max@example.com',
    'weekly_hours' => 40,
]);

// ✅ Updaten
$employee->name = 'Maximilian Mustermann';
$employee->save();

// ✅ Löschen
$employee->delete();
```

### ⚠️ Mass Assignment Protection

```php
// ❌ FEHLER: MassAssignmentException
$employee = Employee::create($request->all());

// Warum? Sicherheit! Ohne Protection könnte jemand
// im Request Felder mitschicken, die er nicht ändern darf
// (z.B. 'is_admin' => true)
```

**Lösung:** Im Model definieren, was erlaubt ist:

```php
class Employee extends Model
{
    // Diese Felder dürfen per create() gesetzt werden
    protected $fillable = [
        'name',
        'email',
        'weekly_hours',
    ];
    
    // Alternative: Diese Felder sind geschützt
    // protected $guarded = ['id', 'is_admin'];
}
```

## 3. Beziehungen (Relationships) verstehen

### One-to-Many: Ein Mitarbeiter hat viele Buchungen

```php
// Im Employee Model
public function assignments()
{
    // Ein Mitarbeiter hat viele (hasMany) Buchungen
    return $this->hasMany(Assignment::class);
}

// Verwendung:
$employee = Employee::find(1);
$assignments = $employee->assignments; // Holt alle Buchungen

// Automatisch erstellen:
$employee->assignments()->create([
    'project_id' => 5,
    'hours_per_week' => 20,
    'start_date' => now(),
]);
```

### Many-to-Many: Mitarbeiter haben viele Projekte (und umgekehrt)

```php
// Im Employee Model
public function projects()
{
    // Über die Pivot-Tabelle 'assignments'
    return $this->belongsToMany(Project::class, 'assignments')
                ->withPivot('hours_per_week', 'start_date', 'end_date')
                ->withTimestamps();
}

// Verwendung:
$employee = Employee::find(1);
foreach ($employee->projects as $project) {
    echo $project->name;
    // Zugriff auf Pivot-Daten:
    echo $project->pivot->hours_per_week;
}
```

### ⚠️ N+1 Problem verstehen

```php
// ❌ SCHLECHT: 1 Query + N Queries (N+1 Problem)
$employees = Employee::all(); // 1 Query
foreach ($employees as $employee) {
    echo $employee->assignments->count(); // N Queries (einer pro Mitarbeiter!)
}

// ✅ GUT: 2 Queries total (Eager Loading)
$employees = Employee::with('assignments')->get(); // 2 Queries
foreach ($employees as $employee) {
    echo $employee->assignments->count(); // Keine zusätzliche Query!
}
```

## 4. MOCO-Integration verstehen (WICHTIG!)

### Was ist MOCO?

MOCO ist das Haupt-Zeiterfassungssystem von enodia. Day2Day-Manager nutzt es nur zum **Lesen** von Daten!

### 🚨 Die goldene Regel: NIEMALS zu MOCO schreiben!

```php
// ✅ ERLAUBT: Von MOCO lesen
$response = Http::withToken($apiKey)
    ->get('https://enodia.mocoapp.com/api/v1/users');

// 🚫 VERBOTEN: Zu MOCO schreiben
Http::post('https://enodia.mocoapp.com/...'); // NIEMALS TUN!
```

**Warum?**
- MOCO ist Firmeneigentum
- Andere Systeme nutzen MOCO auch
- Day2Day-Manager ist nur ein "Konsument"
- Wir könnten wichtige Daten überschreiben!

### Wie funktioniert der MOCO-Import?

```php
// 1. Daten von MOCO holen (GET)
$mocoEmployees = Http::get('https://enodia.mocoapp.com/api/v1/users')->json();

// 2. In unserer Datenbank speichern
foreach ($mocoEmployees as $mocoEmployee) {
    Employee::updateOrCreate(
        ['moco_id' => $mocoEmployee['id']], // Eindeutige externe ID
        [
            'name' => $mocoEmployee['firstname'] . ' ' . $mocoEmployee['lastname'],
            'email' => $mocoEmployee['email'],
            'weekly_hours' => 40, // Standard
        ]
    );
}

// 3. Ab jetzt arbeiten wir mit unserer Kopie!
```

## 5. Praktische Übungen

### Übung 1: Zeige alle Projekte mit ihren Mitarbeitern

**Aufgabe:** Erstelle eine Seite, die alle Projekte und ihre zugeordneten Mitarbeiter anzeigt.

<details>
<summary>💡 Lösung anzeigen</summary>

```php
// Controller
public function index()
{
    $projects = Project::with('employees')->get();
    return view('projects.index', compact('projects'));
}
```

```blade
@foreach ($projects as $project)
    <h2>{{ $project->name }}</h2>
    <ul>
        @foreach ($project->employees as $employee)
            <li>{{ $employee->name }} ({{ $employee->pivot->hours_per_week }}h/Woche)</li>
        @endforeach
    </ul>
@endforeach
```
</details>

### Übung 2: Berechne die Gesamtauslastung eines Mitarbeiters

**Aufgabe:** Schreibe eine Methode, die berechnet, wie viele Stunden ein Mitarbeiter pro Woche gebucht hat.

<details>
<summary>💡 Lösung anzeigen</summary>

```php
// Im Employee Model
public function getTotalBookedHoursAttribute(): float
{
    return $this->assignments()
        ->where('start_date', '<=', now())
        ->where(function ($query) {
            $query->whereNull('end_date')
                  ->orWhere('end_date', '>=', now());
        })
        ->sum('hours_per_week');
}

// Verwendung:
$employee = Employee::find(1);
echo "Gebucht: " . $employee->total_booked_hours . " von " . $employee->weekly_hours . " Stunden";
```
</details>

## 6. Nützliche Links und Ressourcen

### Offizielle Laravel-Dokumentation
- **Eloquent:** https://laravel.com/docs/eloquent
- **Relationships:** https://laravel.com/docs/eloquent-relationships
- **Blade Templates:** https://laravel.com/docs/blade
- **Validation:** https://laravel.com/docs/validation

### Video-Tutorials (Englisch)
- **Laracasts:** https://laracasts.com (Premium, aber sehr gut!)
- **Laravel Daily:** YouTube-Kanal mit vielen Tipps

### Deutsche Ressourcen
- **Laravel-Handbuch.de:** Gute deutsche Übersetzung
- **PHP-Hilfe.de:** Forum für PHP/Laravel-Fragen

## 7. Debugging-Tipps

### dd() und dump() sind deine Freunde!

```php
// dd() = "dump and die" - zeigt Variable und stoppt
dd($employee);

// dump() = zeigt Variable, aber läuft weiter
dump($employee);
dump($projects);
```

### Log-Dateien checken

```php
// Ins Log schreiben
Log::info('Mitarbeiter geladen', ['count' => $employees->count()]);

// Log lesen:
// storage/logs/laravel.log
```

### Tinker verwenden (Laravel REPL)

```bash
php artisan tinker

# Jetzt kannst du interaktiv PHP-Code ausführen:
>>> $employee = Employee::find(1)
>>> $employee->name
>>> $employee->assignments
```

## Zusammenfassung

**Das Wichtigste:**
1. MVC verstehen: Model-View-Controller
2. Eloquent für Datenbankzugriff nutzen
3. Relationships richtig definieren
4. Niemals zu MOCO schreiben (nur lesen!)
5. Bei Fehlern: dd(), Logs, Tinker nutzen

**Nächste Schritte:**
- Offizielle Laravel-Docs durcharbeiten
- Eigene kleine Features implementieren
- Code von erfahrenen Entwicklern lesen
- Bei Fragen: Nicht scheuen, zu fragen!

**🎯 Lernziel erreicht, wenn du:**
- Eine eigene CRUD-Seite bauen kannst (Create, Read, Update, Delete)
- Eloquent-Relationships verstehst
- Weißt, warum MOCO read-only ist
- Debug-Tools nutzen kannst
