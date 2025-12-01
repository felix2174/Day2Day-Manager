# MOCO Integration

Diese Laravel-Anwendung verfügt über eine vollständige Integration mit der MOCO API zur Synchronisation von Mitarbeitern, Projekten und Zeiterfassungen.

## 🔧 Konfiguration

### 1. Umgebungsvariablen einrichten

Fügen Sie folgende Variablen zu Ihrer `.env` Datei hinzu:

```env
MOCO_API_KEY=ihr_moco_api_key
MOCO_DOMAIN=ihre-firma.mocoapp.com
MOCO_BASE_URL=https://api.mocoapp.com/api/v1
```

**So erhalten Sie Ihren MOCO API-Key:**
1. Melden Sie sich bei MOCO an
2. Gehen Sie zu Einstellungen → Integrationen → API
3. Erstellen Sie einen neuen API-Key
4. Kopieren Sie den Key in Ihre `.env` Datei

### 2. Migration ausführen

Führen Sie die Migration aus, um die `moco_id` Felder zu den Tabellen hinzuzufügen:

```bash
php artisan migrate
```

## 📦 Komponenten

### Services

**`app/Services/MocoService.php`**
- Zentrale API-Klasse für alle MOCO-Anfragen
- Methoden für Projekte, Mitarbeiter, Zeiterfassungen, etc.
- Automatische Fehlerbehandlung und Logging

### Artisan Commands

Die Integration bietet mehrere Artisan-Befehle für die Synchronisation:

#### Mitarbeiter synchronisieren
```bash
php artisan moco:sync-employees
php artisan moco:sync-employees --active  # Nur aktive Mitarbeiter
```

#### Projekte synchronisieren
```bash
php artisan moco:sync-projects
php artisan moco:sync-projects --active  # Nur aktive Projekte
```

#### Zeiterfassungen synchronisieren
```bash
php artisan moco:sync-activities
php artisan moco:sync-activities --days=60  # Letzte 60 Tage
php artisan moco:sync-activities --from=2025-01-01 --to=2025-12-31
```

#### Vollständige Synchronisation
```bash
php artisan moco:sync-all
php artisan moco:sync-all --active --days=90
```

### Web-Interface

Besuchen Sie `/moco` für eine grafische Benutzeroberfläche zur Synchronisation:

- Verbindungstest zur MOCO API
- Einzelne Synchronisation von Mitarbeitern, Projekten oder Zeiterfassungen
- Vollständige Synchronisation aller Daten
- Optionen für "nur aktive" Einträge
- Datumsbereich-Auswahl für Zeiterfassungen

### Controller & Routes

**Controller:** `app/Http/Controllers/MocoController.php`

**Verfügbare Routes:**
- `GET /moco` - Dashboard anzeigen
- `POST /moco/test` - Verbindung testen
- `POST /moco/sync-employees` - Mitarbeiter synchronisieren
- `POST /moco/sync-projects` - Projekte synchronisieren
- `POST /moco/sync-activities` - Zeiterfassungen synchronisieren
- `POST /moco/sync-all` - Alles synchronisieren

## 🗄️ Datenbank-Schema

### Hinzugefügte Felder

**employees**
- `moco_id` - Eindeutige MOCO User-ID

**projects**
- `moco_id` - Eindeutige MOCO Projekt-ID

**time_entries**
- `moco_id` - Eindeutige MOCO Activity-ID
- `billable` - Ob die Zeit abrechenbar ist

## 🔄 Synchronisationslogik

### Mitarbeiter (Users)
- Synchronisiert Vor- und Nachname
- Abteilung wird aus MOCO Unit übernommen
- Wöchentliche Kapazität basiert auf MOCO work_time_per_day
- Aktiv-Status wird synchronisiert

### Projekte
- Synchronisiert Name, Beschreibung, Datumsbereich
- Status wird automatisch gemappt:
  - `abgeschlossen` - wenn inaktiv oder Enddatum in der Vergangenheit
  - `geplant` - wenn Startdatum in der Zukunft
  - `in_bearbeitung` - sonst
- Budget wird als geschätzte Stunden übernommen
- Stundensatz wird synchronisiert
- Projektleiter wird verknüpft (falls als Mitarbeiter vorhanden)

### Zeiterfassungen (Activities)
- Synchronisiert nur wenn Mitarbeiter und Projekt in der DB existieren
- Datum, Stunden und Beschreibung werden übernommen
- Abrechenbar-Status wird gespeichert
- Bestehende Einträge werden aktualisiert (basierend auf moco_id)

## 🔁 Automatische Synchronisation

Sie können die Synchronisation automatisieren, indem Sie einen Cron-Job einrichten:

```bash
# In Ihrer crontab oder Scheduler
# Täglich um 2 Uhr morgens alle Daten der letzten 7 Tage synchronisieren
0 2 * * * cd /pfad/zu/projekt && php artisan moco:sync-all --active --days=7
```

Oder in Laravel's `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Täglich um 2 Uhr alle aktiven Daten synchronisieren
    $schedule->command('moco:sync-all --active --days=7')
             ->dailyAt('02:00')
             ->timezone('Europe/Berlin');
}
```

## 🧪 API-Verbindung testen

```bash
# Via Artisan Command (impliziter Test bei jeder Sync)
php artisan moco:sync-employees

# Via Web-Interface
# Besuchen Sie /moco und klicken Sie auf "Verbindung testen"
```

## ⚠️ Wichtige Hinweise

1. **API-Limits:** MOCO hat API-Rate-Limits. Bei großen Datenmengen kann die Synchronisation länger dauern.

2. **Daten-Mapping:** 
   - Bestehende Daten mit `moco_id` werden aktualisiert
   - Neue Daten werden erstellt
   - Lokale Daten ohne `moco_id` werden nicht verändert

3. **Abhängigkeiten:**
   - Mitarbeiter sollten vor Projekten synchronisiert werden
   - Projekte sollten vor Zeiterfassungen synchronisiert werden
   - Der Befehl `moco:sync-all` führt dies in der richtigen Reihenfolge aus

4. **Fehlerbehandlung:**
   - Alle Fehler werden in `storage/logs/laravel.log` protokolliert
   - Bei Netzwerkfehlern schlägt die Synchronisation fehl
   - Fehlende Referenzen (z.B. Projekt nicht gefunden) werden übersprungen

## 📚 MOCO API Dokumentation

Weitere Informationen zur MOCO API finden Sie unter:
https://moco.de/api/

## 🚀 Verwendungsbeispiele

### Szenario 1: Erstmalige Einrichtung
```bash
# 1. Zuerst Mitarbeiter importieren
php artisan moco:sync-employees

# 2. Dann Projekte importieren
php artisan moco:sync-projects

# 3. Schließlich Zeiterfassungen der letzten 30 Tage
php artisan moco:sync-activities --days=30
```

### Szenario 2: Tägliche Aktualisierung
```bash
# Nur aktive Projekte und Mitarbeiter, Zeiterfassungen von gestern
php artisan moco:sync-all --active --days=1
```

### Szenario 3: Bestimmter Zeitraum
```bash
# Zeiterfassungen für einen spezifischen Monat
php artisan moco:sync-activities --from=2025-09-01 --to=2025-09-30
```

## 🐛 Troubleshooting

### Verbindungsfehler
- Prüfen Sie MOCO_API_KEY und MOCO_DOMAIN in `.env`
- Stellen Sie sicher, dass der API-Key in MOCO aktiv ist
- Prüfen Sie Ihre Internetverbindung

### Fehlende Daten
- Mitarbeiter und Projekte müssen vor Zeiterfassungen synchronisiert werden
- Prüfen Sie die Logs in `storage/logs/laravel.log`
- Verwenden Sie `moco:sync-all` für die richtige Reihenfolge

### Performance-Probleme
- Begrenzen Sie den Datumsbereich mit `--days` oder `--from`/`--to`
- Verwenden Sie `--active` um nur aktive Einträge zu synchronisieren
- Führen Sie die Synchronisation außerhalb der Hauptgeschäftszeiten durch

## 📁 Erstellte Dateien

### Neue Dateien:
- `app/Services/MocoService.php` - API Service
- `app/Console/Commands/SyncMocoEmployees.php` - Employee Sync Command
- `app/Console/Commands/SyncMocoProjects.php` - Project Sync Command
- `app/Console/Commands/SyncMocoActivities.php` - Activities Sync Command
- `app/Console/Commands/SyncMocoAll.php` - Full Sync Command
- `app/Http/Controllers/MocoController.php` - Web Controller
- `resources/views/moco/index.blade.php` - Dashboard View
- `database/migrations/2025_10_07_120000_add_moco_fields_to_time_entries_table.php` - Migration

### Geänderte Dateien:
- `config/services.php` - MOCO Konfiguration hinzugefügt
- `routes/web.php` - MOCO Routes hinzugefügt
- `app/Models/Employee.php` - moco_id zu fillable hinzugefügt
- `app/Models/Project.php` - moco_id zu fillable hinzugefügt
- `app/Models/TimeEntry.php` - moco_id und billable zu fillable hinzugefügt, Relationships hinzugefügt

---

Bei Fragen oder Problemen, konsultieren Sie die Laravel-Logs oder die MOCO API-Dokumentation.

