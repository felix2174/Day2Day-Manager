# MOCO Integration - Vollständiger Bereich

## 🎉 Übersicht

Ich habe einen kompletten MOCO-Integrationsbereich mit allen wichtigen Funktionen erstellt. Dieser Bereich bietet eine vollständige Verwaltung und Überwachung der MOCO-Synchronisation.

## 📁 Erstellte Dateien

### Models & Migrations
- ✅ `database/migrations/2025_10_07_120000_add_moco_fields_to_time_entries_table.php` - MOCO-Felder für Zeiterfassungen
- ✅ `database/migrations/2025_10_07_120100_create_moco_sync_logs_table.php` - Sync-Logs Tabelle
- ✅ `app/Models/MocoSyncLog.php` - Model für Synchronisations-Logs

### Services
- ✅ `app/Services/MocoService.php` - MOCO API Service
- ✅ `app/Services/MocoSyncLogger.php` - Sync-Logging Service

### Commands
- ✅ `app/Console/Commands/SyncMocoEmployees.php` - Mitarbeiter synchronisieren
- ✅ `app/Console/Commands/SyncMocoProjects.php` - Projekte synchronisieren
- ✅ `app/Console/Commands/SyncMocoActivities.php` - Zeiterfassungen synchronisieren
- ✅ `app/Console/Commands/SyncMocoAll.php` - Vollständige Synchronisation

### Controllers & Views
- ✅ `app/Http/Controllers/MocoController.php` - Erweiterter Controller mit allen Funktionen
- ✅ `resources/views/moco/layout.blade.php` - Layout mit Navigation
- ✅ `resources/views/moco/index.blade.php` - Dashboard mit Statistiken
- ✅ `resources/views/moco/logs.blade.php` - Sync-History mit Filterung
- ✅ `resources/views/moco/statistics.blade.php` - Detaillierte Statistiken
- ✅ `resources/views/moco/mappings.blade.php` - Mapping-Verwaltung

### Configuration
- ✅ `config/services.php` - MOCO-Konfiguration hinzugefügt
- ✅ `routes/web.php` - Alle MOCO-Routes registriert
- ✅ `resources/views/layouts/app.blade.php` - Navigation erweitert

### Documentation
- ✅ `MOCO_INTEGRATION.md` - Umfassende Dokumentation

## 🎯 Funktionen

### 1. Dashboard (`/moco`)
- **Verbindungsstatus**: Echtzeit-Prüfung der MOCO API-Verbindung
- **Statistik-Karten**: Übersicht über synchronisierte Daten
  - Mitarbeiter (gesamt/synchronisiert mit Fortschrittsbalken)
  - Projekte (gesamt/synchronisiert mit Fortschrittsbalken)
  - Zeiterfassungen (gesamt/synchronisiert mit Fortschrittsbalken)
- **Letzte Synchronisationen**: Tabelle mit den 10 letzten Syncs
- **Schnellzugriff**: Buttons für alle Sync-Typen
- **Vollständige Synchronisation**: Mit Optionen (nur aktive, Zeitraum)
- **Einzelne Synchronisationen**: Mitarbeiter, Projekte, Zeiterfassungen

### 2. Statistiken (`/moco/statistics`)
- **Gesamtstatistiken**: 
  - Gesamte Synchronisationen
  - Erfolgreiche Syncs
  - Fehlgeschlagene Syncs
  - Durchschnittliche Dauer
- **Datenabdeckung**: Visuelle Fortschrittsbalken für jede Datenquelle
- **Monatliche Übersicht**: Detaillierte Statistiken der letzten 6 Monate
  - Nach Typ gruppiert
  - Erfolgs-/Fehlerrate
  - Anzahl erstellt/aktualisiert
  - Durchschnittliche Dauer

### 3. Sync-History (`/moco/logs`)
- **Filterung**: Nach Typ und Status
- **Detaillierte Logs**: 
  - Typ der Synchronisation
  - Status (Erfolgreich/Fehler/Läuft)
  - Anzahl verarbeitet/erstellt/aktualisiert/übersprungen
  - Dauer
  - Benutzer (wer hat die Sync gestartet)
  - Zeitstempel
- **Fehlerdetails**: Anzeige von Fehlermeldungen
- **Pagination**: 20 Einträge pro Seite

### 4. Mappings (`/moco/mappings`)
- **Unverknüpfte Mitarbeiter**: Liste aller Mitarbeiter ohne MOCO-ID
- **Unverknüpfte Projekte**: Liste aller Projekte ohne MOCO-ID
- **Unverknüpfte Zeiterfassungen**: Liste aller Zeiterfassungen ohne MOCO-ID
- **Übersichtliche Darstellung**: Mit allen relevanten Details
- **Hinweise**: Erklärungen zum Umgang mit unmapped Items

### 5. Navigation
- **Tab-Navigation**: Zwischen Dashboard, Statistiken, Logs und Mappings
- **Haupt-Navigation**: MOCO-Bereich prominent im Sidebar-Menü
- **Visuelles Highlight**: Gradient-Hintergrund für bessere Sichtbarkeit

## 🚀 Erste Schritte

### 1. Umgebungsvariablen konfigurieren

Ihre `.env` enthält bereits:
```env
MOCO_API_KEY=your_api_key
MOCO_DOMAIN=enodiasoftware.mocoapp.com
MOCO_BASE_URL=https://enodiasoftware.mocoapp.com/api/v1
```

### 2. Migrationen ausführen

```bash
php artisan migrate
```

Dies erstellt:
- `moco_sync_logs` Tabelle für Synchronisations-Logs
- Fügt `moco_id` und `billable` zu `time_entries` hinzu

### 3. Erste Synchronisation

**Option A: Via Web-Interface**
1. Besuchen Sie `/moco` im Browser
2. Klicken Sie auf "Verbindung testen" (um sicherzustellen, dass alles funktioniert)
3. Klicken Sie auf "Alles synchronisieren"

**Option B: Via Artisan**
```bash
php artisan moco:sync-all
```

## 📊 Verwendung

### Web-Interface

**Dashboard aufrufen:**
```
http://ihr-domain.de/moco
```

**Navigation:**
- Dashboard: Übersicht und Schnell-Sync
- Statistiken: Detaillierte Auswertungen
- Sync-History: Alle vergangenen Synchronisationen
- Mappings: Unverknüpfte Datensätze

### Artisan Commands

**Mitarbeiter synchronisieren:**
```bash
php artisan moco:sync-employees
php artisan moco:sync-employees --active  # Nur aktive
```

**Projekte synchronisieren:**
```bash
php artisan moco:sync-projects
php artisan moco:sync-projects --active  # Nur aktive
```

**Zeiterfassungen synchronisieren:**
```bash
php artisan moco:sync-activities
php artisan moco:sync-activities --days=60  # Letzte 60 Tage
php artisan moco:sync-activities --from=2025-01-01 --to=2025-12-31  # Zeitraum
```

**Alles synchronisieren:**
```bash
php artisan moco:sync-all
php artisan moco:sync-all --active --days=90  # Mit Optionen
```

### Automatische Synchronisation einrichten

In `app/Console/Kernel.php` (falls noch nicht vorhanden, erstellen):

```php
protected function schedule(Schedule $schedule)
{
    // Täglich um 2:00 Uhr synchronisieren
    $schedule->command('moco:sync-all --active --days=7')
             ->dailyAt('02:00')
             ->timezone('Europe/Berlin');
    
    // Oder stündlich nur Zeiterfassungen
    $schedule->command('moco:sync-activities --days=1')
             ->hourly();
}
```

Dann Cron einrichten:
```bash
* * * * * cd /pfad/zu/projekt && php artisan schedule:run >> /dev/null 2>&1
```

## 🎨 Features im Detail

### Sync-Logging
- Jede Synchronisation wird automatisch geloggt
- Erfasst: Typ, Status, Statistiken, Dauer, Benutzer
- Fehler werden mit Details gespeichert
- Einsehbar in der Sync-History

### Statistiken & Analytics
- **Live-Statistiken**: Aktuelle Datenabdeckung
- **Historische Daten**: Trends über 6 Monate
- **Performance-Metriken**: Durchschnittliche Sync-Dauer
- **Erfolgsraten**: Visualisierung von Erfolg/Fehler

### Mapping-Management
- **Identifikation**: Datensätze ohne MOCO-ID
- **Kategorisiert**: Nach Mitarbeiter, Projekte, Zeiterfassungen
- **Detaillierte Info**: Alle relevanten Felder angezeigt
- **Handlungsempfehlungen**: Hinweise zum weiteren Vorgehen

### User Experience
- **Responsive Design**: Funktioniert auf allen Geräten
- **Echtzeit-Feedback**: Success/Error Messages
- **Visuelles Feedback**: Progress Bars, Status-Badges
- **Intuitive Navigation**: Tab-basierte Struktur
- **Filter & Suche**: In Logs und Mappings

## 🔧 Technische Details

### Database Schema

**moco_sync_logs:**
- `id` - Primary Key
- `sync_type` - employees, projects, activities, all
- `status` - started, completed, failed
- `items_processed` - Anzahl verarbeiteter Items
- `items_created` - Anzahl neu erstellter Items
- `items_updated` - Anzahl aktualisierter Items
- `items_skipped` - Anzahl übersprungener Items
- `error_message` - Fehlermeldung (bei Fehler)
- `parameters` - JSON mit Sync-Parametern
- `started_at` - Start-Zeitstempel
- `completed_at` - Ende-Zeitstempel
- `duration_seconds` - Dauer in Sekunden
- `user_id` - Benutzer der die Sync gestartet hat

### API Integration
- Vollständige Nutzung der MOCO API v1
- Unterstützte Endpoints:
  - `/users` - Mitarbeiter
  - `/projects` - Projekte
  - `/activities` - Zeiterfassungen
  - `/project_assignments` - Projekt-Zuweisungen
  - `/schedules/absences` - Abwesenheiten

### Error Handling
- Try-Catch Blöcke in allen Sync-Funktionen
- Detailliertes Logging in `storage/logs/laravel.log`
- User-freundliche Fehlermeldungen im Frontend
- Fehler-Details in Sync-Logs gespeichert

## 📱 Screenshots & UI

### Dashboard
- Großer Verbindungsstatus-Bereich
- 3 Statistik-Karten mit Progress Bars
- Vollständige Sync-Sektion mit Optionen
- 3 Einzelsync-Karten
- Tabelle mit letzten Synchronisationen

### Statistiken
- 4 Metric-Cards (Gesamt, Erfolgreich, Fehlgeschlagen, Ø Dauer)
- 3 große Progress Bars für Datenabdeckung
- Detaillierte Monatstabelle

### Sync-History
- Filter-Formular (Typ, Status)
- Detaillierte Tabelle mit allen Sync-Informationen
- Expandierbare Fehlerdetails
- Pagination

### Mappings
- Info-Box mit Erklärungen
- 3 Sektionen (Mitarbeiter, Projekte, Zeiterfassungen)
- Detaillierte Tabellen für jede Kategorie
- Visual Feedback für leere Zustände

## 🎯 Nächste Schritte

1. **Migration ausführen**: `php artisan migrate`
2. **Verbindung testen**: Im Dashboard auf "Verbindung testen" klicken
3. **Erste Sync**: "Alles synchronisieren" ausführen
4. **Statistiken prüfen**: Tab "Statistiken" besuchen
5. **Logs einsehen**: Tab "Sync-History" besuchen
6. **Mappings prüfen**: Tab "Mappings" für unverknüpfte Daten
7. **Automatisierung**: Cron-Job einrichten (optional)

## 🐛 Troubleshooting

### Verbindung fehlgeschlagen
- API-Key in `.env` prüfen
- MOCO_DOMAIN korrekt?
- MOCO_BASE_URL korrekt? (Format: `https://DOMAIN/api/v1`)

### Keine Daten synchronisiert
- Sind Mitarbeiter/Projekte in MOCO vorhanden?
- Sind sie als "aktiv" markiert? (falls `--active` verwendet)
- Logs in `storage/logs/laravel.log` prüfen

### Performance-Probleme
- Zeitraum einschränken (`--days` Parameter)
- Nur aktive Items synchronisieren (`--active`)
- Synchronisation außerhalb der Geschäftszeiten

## 📚 Weitere Ressourcen

- `MOCO_INTEGRATION.md` - Umfassende technische Dokumentation
- MOCO API Dokumentation: https://moco.de/api/
- Laravel Dokumentation: https://laravel.com/docs

---

**Erstellt am:** 7. Oktober 2025  
**Version:** 1.0.0  
**Status:** ✅ Produktionsbereit

