# Seeder-Dokumentation Day2Day-Manager

## ⚠️ WICHTIG: Datensicherheit

Die Seeder in diesem Projekt sind **NUR für initiale Login-Daten** gedacht.
Alle anderen Daten (Mitarbeiter, Projekte, etc.) kommen über **MOCO-Sync**.

## Aktive Seeder

### DatabaseSeeder.php
Hauptseeder, der beim Ausführen von `php artisan db:seed` gestartet wird.

**Ruft auf:**
- `UserSeeder::class` (immer) - Erstellt Login-Daten
- `GanttTestSeeder::class` (nur in local/testing) - Test-Daten für Entwicklung

### UserSeeder.php
Erstellt initiale Login-Daten für:
- admin@enodia.de (Admin)
- it@enodia.de (Marc Hanke)
- j.michno@enodia.de (Jörg Michno)
- Weitere Test-User für Entwicklung

**Passwort für alle:** `Test1234`

### GanttTestSeeder.php
Nur in local/testing Umgebung aktiv.
Erstellt ein Test-Projekt "Gantt-Testprojekt" mit 5 Test-Mitarbeitern und Aufgaben.

⚠️ **Achtung:** Bereinigt bei jedem Lauf alte Gantt-Testdaten!

## Platzhalter-Seeder (leer)

- `EmployeeSeeder.php` - Mitarbeiter kommen aus MOCO
- `TestAbsenceSeeder.php` - Nicht verwendet
- `TestAssignmentSeeder.php` - Nicht verwendet

## Seeder ausführen

```bash
# Alle Seeder ausführen (normal nur UserSeeder)
php artisan db:seed

# Nur einen bestimmten Seeder ausführen
php artisan db:seed --class=UserSeeder

# In local/testing Umgebung: UserSeeder + GanttTestSeeder
php artisan db:seed
```

## 🚫 NIEMALS in Produktion

- `php artisan migrate:fresh --seed` - Löscht ALLE Daten!
- Seeder mit `truncate()` oder `delete()` Befehlen

## Daten wiederherstellen

Falls Daten versehentlich gelöscht wurden:

1. **Backup wiederherstellen** (falls vorhanden)
2. **MOCO-Sync neu ausführen** um Mitarbeiter & Projekte zu synchronisieren
3. **Nur UserSeeder ausführen** für Login-Daten: `php artisan db:seed --class=UserSeeder`
