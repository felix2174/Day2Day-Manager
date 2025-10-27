# Contributing to Day2Day-Manager

Vielen Dank für dein Interesse an der Mitarbeit am Day2Day-Manager!

## 📋 Hinweis

Dies ist ein **internes Projekt** von enodia IT-Systemhaus. Externe Contributions werden momentan nicht akzeptiert.

## 👥 Für interne Entwickler

### Code-Style

Wir verwenden Laravel's offiziellen Coding-Standard:

```bash
# Code-Style automatisch formatieren
./vendor/bin/pint

# Code-Style prüfen (ohne Änderungen)
./vendor/bin/pint --test
```

### Git Workflow

1. **Branch erstellen**
   ```bash
   git checkout -b feature/deine-feature-beschreibung
   # oder
   git checkout -b fix/bug-beschreibung
   ```

2. **Änderungen committen**
   ```bash
   git add .
   git commit -m "feat: Beschreibung des Features"
   # oder
   git commit -m "fix: Beschreibung des Bugfixes"
   ```

3. **Commit Message Conventions**
   - `feat:` - Neue Features
   - `fix:` - Bugfixes
   - `docs:` - Dokumentations-Änderungen
   - `style:` - Code-Formatierung (keine funktionalen Änderungen)
   - `refactor:` - Code-Refactoring
   - `test:` - Tests hinzufügen oder ändern
   - `chore:` - Build-Prozess oder Hilfswerkzeuge

4. **Tests ausführen**
   ```bash
   php artisan test
   ```

5. **Push und Pull Request**
   ```bash
   git push origin feature/deine-feature-beschreibung
   ```
   Dann erstelle einen Pull Request auf GitHub.

### Entwicklungsumgebung einrichten

```bash
# Repository klonen
git clone https://github.com/felix2174/Day2Day-Manager.git
cd Day2Day-Manager

# Dependencies installieren
composer install
npm install

# Umgebung konfigurieren
cp .env.example .env
php artisan key:generate

# Datenbank einrichten
touch database/database.sqlite
php artisan migrate --seed

# Development Server starten
php artisan serve
npm run dev  # In separatem Terminal
```

### Testing

Alle neuen Features sollten mit Tests abgedeckt werden:

```bash
# Alle Tests ausführen
php artisan test

# Spezifischen Test ausführen
php artisan test --filter TestName

# Mit Coverage-Report
php artisan test --coverage
```

### Code Review Checklist

Vor dem Erstellen eines Pull Requests:

- [ ] Code folgt Laravel Best Practices
- [ ] Alle Tests laufen erfolgreich durch
- [ ] Code-Style wurde mit Pint formatiert
- [ ] Neue Features haben Tests
- [ ] Dokumentation wurde aktualisiert (falls nötig)
- [ ] CHANGELOG.md wurde aktualisiert
- [ ] Keine sensiblen Daten im Code (API-Keys, Passwörter, etc.)
- [ ] Keine `console.log()` oder `dd()` im finalen Code

### Datenbank-Migrationen

Beim Erstellen neuer Migrationen:

```bash
# Migration erstellen
php artisan make:migration create_table_name

# Migration ausführen
php artisan migrate

# Migration rückgängig machen
php artisan migrate:rollback

# Datenbank zurücksetzen und neu aufbauen
php artisan migrate:fresh --seed
```

### MOCO-Integration

Bei Änderungen an der MOCO-Integration:

1. Teste mit echter MOCO-API (Staging-Umgebung)
2. Dokumentiere neue API-Endpoints
3. Update `MOCO_INTEGRATION.md`
4. Prüfe Error-Handling und Logging

### Best Practices

#### Controller
- Halte Controller schlank
- Verwende Service-Klassen für Business-Logik
- Ein Controller-Action sollte nur eine Verantwortung haben

#### Models
- Verwende Eloquent Relationships
- Definiere `$fillable` oder `$guarded`
- Füge Docblocks für IDE-Support hinzu

#### Views
- Nutze Blade-Components für wiederverwendbare UI-Elemente
- Vermeide Business-Logik in Views
- Verwende Blade-Direktiven (@if, @foreach, etc.)

#### Services
- Komplexe Business-Logik gehört in Service-Klassen
- Services sollten testbar sein
- Dependency Injection verwenden

### Fragen?

Bei Fragen wende dich an:
- **Jörg Michno** - Lead Developer
- **Felix** - MOCO Integration & Backend

---

**Hinweis:** Dieses Dokument wird kontinuierlich aktualisiert. Letzte Aktualisierung: Oktober 2024
