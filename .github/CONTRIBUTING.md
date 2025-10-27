# Contributing to Day2Day-Manager

Vielen Dank für dein Interesse an der Mitarbeit am Day2Day-Manager! Diese Guidelines helfen dir dabei, effektiv zum Projekt beizutragen.

## 📋 Inhaltsverzeichnis

- [Code of Conduct](#code-of-conduct)
- [Entwicklungsumgebung einrichten](#entwicklungsumgebung-einrichten)
- [Git-Workflow](#git-workflow)
- [Issue-Templates verwenden](#issue-templates-verwenden)
- [Pull Requests erstellen](#pull-requests-erstellen)
- [Coding Standards](#coding-standards)
- [Testing](#testing)
- [MOCO-Integration](#moco-integration)
- [Dokumentation](#dokumentation)

---

## 🤝 Code of Conduct

Dies ist ein internes Projekt von **enodia IT-Systemhaus**. Wir erwarten professionelles und respektvolles Verhalten von allen Mitwirkenden.

---

## 🚀 Entwicklungsumgebung einrichten

### Voraussetzungen

- PHP 8.2 oder höher
- Composer
- Node.js & npm
- XAMPP (Apache, MySQL optional)
- Git

### Schritt-für-Schritt Setup

```bash
# 1. Repository klonen
git clone https://github.com/felix2174/Day2Day-Manager.git
cd Day2Day-Manager

# ODER lokal:
cd C:\xampp\htdocs\Day2Day-Manager

# 2. Dependencies installieren
composer install
npm install

# 3. Umgebung konfigurieren
copy .env.example .env
php artisan key:generate

# 4. Datenbank einrichten (SQLite)
# Windows PowerShell:
New-Item -ItemType File database\database.sqlite

# Linux/Mac:
touch database/database.sqlite

# 5. Migrationen und Seeds ausführen
php artisan migrate:fresh --seed

# 6. Development Server starten
php artisan serve

# 7. Frontend Build (in separatem Terminal)
npm run dev
```

### Projekt-Pfad

```
C:\xampp\htdocs\Day2Day-Manager\
├── app/
│   ├── Http/Controllers/
│   ├── Models/
│   └── Services/          # Business Logic (z.B. MocoService)
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── views/             # Blade Templates
│   └── js/
├── routes/
│   └── web.php
├── tests/
└── .github/
    ├── ISSUE_TEMPLATE/
    └── workflows/
```

---

## 🔀 Git-Workflow

### Branch-Strategie

Wir verwenden einen Feature-Branch-Workflow:

```bash
main (production)
  ├─ feature/user-story-123
  ├─ bugfix/fix-moco-sync
  ├─ docs/update-readme
  └─ refactor/optimize-queries
```

### Branch-Naming-Convention

```
feature/kurze-beschreibung    # Neue Features
bugfix/issue-nummer           # Bugfixes
hotfix/kritischer-bug         # Kritische Fixes
docs/dokumentation            # Dokumentation
refactor/code-improvement     # Refactoring
test/test-improvement         # Tests
```

### Workflow

```bash
# 1. Aktuellen Stand holen
git checkout main
git pull origin main

# 2. Neuen Branch erstellen
git checkout -b feature/neue-funktion

# 3. Änderungen committen
git add .
git commit -m "feat: Neue Funktion implementiert"

# 4. Push zum Remote
git push origin feature/neue-funktion

# 5. Pull Request auf GitHub erstellen
```

### Commit Message Convention

Wir verwenden [Conventional Commits](https://www.conventionalcommits.org/):

```
<type>(<scope>): <subject>

<body>

<footer>
```

**Types:**
- `feat:` - Neues Feature
- `fix:` - Bugfix
- `docs:` - Dokumentation
- `style:` - Formatierung (kein Code-Change)
- `refactor:` - Code-Refactoring
- `test:` - Tests hinzufügen/ändern
- `chore:` - Build/Tools/Dependencies
- `perf:` - Performance-Verbesserung

**Beispiele:**

```bash
feat(moco): Add project distribution sync
fix(dashboard): Correct KPI calculation for utilization
docs(readme): Update installation instructions
refactor(services): Optimize MocoService queries
test(unit): Add tests for EmployeeKpiService
```

---

## 📝 Issue-Templates verwenden

Wir haben spezialisierte Issue-Templates für verschiedene Anliegen:

### Verfügbare Templates

1. **🐛 Bug Report** - Für Fehler und Probleme
2. **📝 Feature Request** - Für neue Features
3. **🔗 MOCO Integration** - Für MOCO API-Änderungen
4. **📊 KPI Dashboard** - Für Dashboard-Verbesserungen
5. **🎨 Design Improvement** - Für UI/UX-Änderungen
6. **📚 Documentation** - Für Dokumentations-Updates

### Issue erstellen

1. Gehe zu [Issues](https://github.com/felix2174/Day2Day-Manager/issues/new/choose)
2. Wähle das passende Template
3. Fülle alle erforderlichen Felder aus
4. Weise das Issue zu oder verwende Labels

---

## 🔄 Pull Requests erstellen

### Checklist vor PR

- [ ] Branch ist aktuell mit `main`
- [ ] Code folgt Coding Standards
- [ ] Tests laufen durch
- [ ] Dokumentation aktualisiert
- [ ] Keine `dd()`, `dump()`, `console.log()` im Code

### PR erstellen

1. Pushe deinen Branch zu GitHub
2. Öffne Pull Request gegen `main`
3. Verwende das PR-Template (wird automatisch geladen)
4. Fülle alle relevanten Sektionen aus
5. Weise Reviewer zu (@felix2174)

### PR wird geprüft auf:

- ✅ Code-Qualität
- ✅ Tests
- ✅ Dokumentation
- ✅ Performance
- ✅ Security
- ✅ Design-Richtlinien (bei UI-Änderungen)

---

## 💻 Coding Standards

### PHP / Laravel

Wir folgen [PSR-12](https://www.php-fig.org/psr/psr-12/) und [Laravel Best Practices](https://github.com/alexeymezenin/laravel-best-practices).

#### Code-Style automatisch formatieren

```bash
# Laravel Pint (automatisches Formatting)
./vendor/bin/pint

# Nur prüfen (ohne Änderungen)
./vendor/bin/pint --test
```

#### Laravel Best Practices

**Controller:**
```php
// ✅ Gut - Schlank, delegiert an Service
class ProjectController extends Controller
{
    public function __construct(
        private ProjectService $projectService
    ) {}

    public function index()
    {
        $projects = $this->projectService->getAllProjects();
        return view('projects.index', compact('projects'));
    }
}

// ❌ Schlecht - Zu viel Logik im Controller
class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::with('team')
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($project) {
                // Komplexe Berechnungen...
            });
        return view('projects.index', compact('projects'));
    }
}
```

**Models:**
```php
// ✅ Gut - Mit Relationships, Fillable, Casts
class Employee extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'capacity'
    ];

    protected $casts = [
        'capacity' => 'integer',
        'hire_date' => 'date'
    ];

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'assignments');
    }
}
```

**Services:**
```php
// ✅ Gut - Wiederverwendbare Business Logic
class MocoService
{
    public function __construct(
        private HttpClient $client,
        private MocoSyncLogger $logger
    ) {}

    public function fetchUsers(): Collection
    {
        try {
            $response = $this->client->get('/users');
            $this->logger->log('Users fetched successfully');
            return collect($response['data']);
        } catch (Exception $e) {
            $this->logger->error('Failed to fetch users', $e);
            throw $e;
        }
    }
}
```

### Blade Templates

```blade
{{-- ✅ Gut - Escaped Output --}}
{{ $user->name }}

{{-- ❌ Schlecht - Unescaped (nur wenn nötig!) --}}
{!! $htmlContent !!}

{{-- ✅ Gut - Komponenten verwenden --}}
<x-card title="Projekt">
    <x-slot name="content">
        {{ $project->description }}
    </x-slot>
</x-card>

{{-- ✅ Gut - Blade Direktiven --}}
@if($projects->isNotEmpty())
    @foreach($projects as $project)
        <div>{{ $project->name }}</div>
    @endforeach
@else
    <p>Keine Projekte</p>
@endif
```

### JavaScript / Alpine.js

```javascript
// ✅ Gut - Alpine.js für Interaktivität
<div x-data="{ open: false }">
    <button @click="open = !open">Toggle</button>
    <div x-show="open" x-transition>Content</div>
</div>

// ✅ Gut - Event Listener
document.addEventListener('DOMContentLoaded', function() {
    // Code hier
});
```

### CSS / Tailwind

```html
<!-- ✅ Gut - Utility-First Approach -->
<button class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
    Speichern
</button>

<!-- ✅ Gut - Responsive Design -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    <!-- Content -->
</div>
```

---

## 🧪 Testing

### Tests ausführen

```bash
# Alle Tests
php artisan test

# Spezifischer Test
php artisan test --filter TestName

# Mit Coverage
php artisan test --coverage

# Nur Feature Tests
php artisan test --testsuite=Feature

# Nur Unit Tests
php artisan test --testsuite=Unit
```

### Tests schreiben

#### Unit Test Beispiel

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\EmployeeKpiService;

class EmployeeKpiServiceTest extends TestCase
{
    public function test_calculates_utilization_correctly()
    {
        $service = new EmployeeKpiService();
        
        $utilization = $service->calculateUtilization(
            hoursWorked: 40,
            capacity: 40
        );
        
        $this->assertEquals(100, $utilization);
    }
}
```

#### Feature Test Beispiel

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_projects()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
            ->get('/projects');
        
        $response->assertStatus(200);
        $response->assertViewIs('projects.index');
    }
}
```

---

## 🔗 MOCO-Integration

### MOCO API Testing

```bash
# Manueller Sync
php artisan moco:sync

# Logs anzeigen
php artisan moco:logs

# Reset & Neu-Sync
php artisan moco:reset-and-sync
```

### MOCO Service verwenden

```php
use App\Services\MocoService;

$mocoService = app(MocoService::class);

// Users abrufen
$users = $mocoService->fetchUsers();

// Projekte abrufen
$projects = $mocoService->fetchProjects();

// Mit Error Handling
try {
    $users = $mocoService->fetchUsers();
} catch (MocoApiException $e) {
    Log::error('MOCO API Error: ' . $e->getMessage());
}
```

### Wichtige MOCO-Dokumentation

- [MOCO_INTEGRATION.md](../MOCO_INTEGRATION.md) - Vollständige Integration-Docs
- [MOCO_QUICKSTART.md](../MOCO_QUICKSTART.md) - Schnelleinstieg
- [MOCO_DATA_PRIORITY_RULE.md](../MOCO_DATA_PRIORITY_RULE.md) - Daten-Prioritäten
- [MOCO API Docs](https://github.com/hundertzehn/mocoapp-api-docs) - Offizielle API-Docs

---

## 📚 Dokumentation

### Wann Dokumentation aktualisieren?

- ✅ Neue Features
- ✅ API-Änderungen
- ✅ Breaking Changes
- ✅ Neue ENV-Variablen
- ✅ Deployment-Prozess geändert
- ✅ MOCO-Integration erweitert

### Dokumentations-Dateien

```
Day2Day-Manager/
├── README.md                              # Projekt-Übersicht
├── CHANGELOG.md                           # Versions-Historie
├── CONTRIBUTING.md                        # Diese Datei
├── MOCO_INTEGRATION.md                    # MOCO-Integration
├── KPI_DASHBOARD_DOCUMENTATION.md         # KPI-Dashboard
├── DESIGN_RULES.md                        # Design-System
└── docs/
    └── screenshots/                       # Screenshots
```

### Code-Dokumentation

```php
/**
 * Calculate employee utilization percentage
 *
 * Calculates the utilization based on worked hours and capacity.
 * Includes MOCO sync data and local time entries.
 *
 * @param int $employeeId Employee ID
 * @param Carbon $startDate Start date for calculation
 * @param Carbon $endDate End date for calculation
 * @return float Utilization percentage (0-100)
 * @throws EmployeeNotFoundException
 */
public function calculateUtilization(
    int $employeeId,
    Carbon $startDate,
    Carbon $endDate
): float {
    // Implementation
}
```

---

## 🐛 Debugging

### Laravel Debugging

```bash
# Logs anzeigen
type storage\logs\laravel.log | more    # Windows
tail -f storage/logs/laravel.log        # Linux/Mac

# Cache leeren
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Datenbank zurücksetzen
php artisan migrate:fresh --seed
```

### Debugging in Code

```php
// Development only!
dd($variable);           // Dump and Die
dump($variable);         // Dump
Log::debug($message);    // Log schreiben

// Query-Logging aktivieren
DB::enableQueryLog();
// ... queries ...
dd(DB::getQueryLog());
```

---

## 🔐 Security

### Best Practices

- ✅ Nie API-Keys im Code
- ✅ Immer Eloquent/Query Builder (SQL-Injection-Schutz)
- ✅ Blade Escaping verwenden (`{{ }}` statt `{!! !!}`)
- ✅ CSRF-Protection bei Forms
- ✅ Input-Validierung
- ✅ Authorization-Checks

```php
// ✅ Gut - Mit Validierung
public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
    ]);
    
    User::create($validated);
}

// ❌ Schlecht - Ohne Validierung
public function store(Request $request)
{
    User::create($request->all());
}
```

---

## 🤔 Fragen?

Bei Fragen wende dich an:

- **Jörg Michno** - Lead Developer
- **Felix** (@felix2174) - MOCO Integration & Backend
- Team-Chat oder GitHub Discussions

---

## 📖 Weitere Ressourcen

- [Laravel Documentation](https://laravel.com/docs)
- [Tailwind CSS](https://tailwindcss.com/docs)
- [Alpine.js](https://alpinejs.dev/)
- [MOCO API Docs](https://github.com/hundertzehn/mocoapp-api-docs)
- [Conventional Commits](https://www.conventionalcommits.org/)

---

**Danke für deinen Beitrag zum Day2Day-Manager! 🚀**
