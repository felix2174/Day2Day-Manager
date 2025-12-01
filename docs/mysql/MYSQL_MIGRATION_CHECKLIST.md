# ✅ MySQL Migration Checkliste

**Datum:** 03.11.2025  
**Projekt:** Day2Day-Manager  
**Migration:** SQLite → MySQL

---

## 📋 Vor der Migration

- [x] SQLite-Backup erstellt (database/database.sqlite)
- [x] Export-Command erstellt (`db:export-mysql`)
- [x] Import-Command erstellt (`db:import-mysql`)
- [x] Backup-Script erstellt (`backup-mysql.bat`)
- [x] config/database.php optimiert (InnoDB, Connection Pooling)
- [x] .env bereits auf MySQL konfiguriert
- [x] Dokumentation erstellt (MYSQL_MIGRATION_GUIDE.md)

---

## 🚀 Migration durchführen

### Schritt 1: MySQL-Port prüfen
```powershell
netstat -an | findstr "330"
```
- [ ] Port identifiziert (3306 oder 3307)
- [ ] .env DB_PORT angepasst (falls nötig)

### Schritt 2: Datenbank erstellen
- [ ] phpMyAdmin geöffnet (http://localhost/phpmyadmin)
- [ ] Neue DB erstellt: `day2day`
- [ ] Kollation gesetzt: `utf8mb4_unicode_ci`

### Schritt 3: Schema erstellen
```powershell
php artisan migrate:fresh
```
- [ ] Alle 23 Migrations erfolgreich
- [ ] Keine Fehler in der Ausgabe

### Schritt 4: Daten importieren
```powershell
php artisan db:import-mysql
```
- [ ] Import erfolgreich (13 Statements)
- [ ] Validierung zeigt Daten:
  - [ ] 1 Project
  - [ ] 5 Employees
  - [ ] 13 Assignments

### Schritt 5: Cache leeren
```powershell
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```
- [ ] Cache erfolgreich geleert

---

## 🧪 Testing

### Basis-Funktionalität
```powershell
php artisan serve
```
- [ ] Server startet ohne Fehler
- [ ] Dashboard lädt (<2 Sekunden)
- [ ] Keine Fehlermeldungen im Browser

### UI-Tests
- [ ] **Dashboard:** Projektstatus sichtbar
- [ ] **Projekte:** Liste zeigt 1 Projekt
- [ ] **Mitarbeiter:** Liste zeigt 5 Mitarbeiter
- [ ] **Gantt-Chart:** Assignments werden angezeigt
- [ ] **Assignments:** 13 Zuweisungen sichtbar

### MOCO-Integration
```powershell
php artisan sync:moco-projects --dry-run
```
- [ ] Dry-Run funktioniert ohne Fehler
- [ ] API-Verbindung erfolgreich

### Performance-Check
- [ ] Dashboard lädt in <2 Sekunden (vorher: >5 Sekunden)
- [ ] Gantt-Chart lädt in <3 Sekunden
- [ ] Keine Locking-Warnungen mehr

---

## 🔧 Backup-Setup

### Automatisches Backup konfigurieren
- [ ] Backup-Script getestet (`backup-mysql.bat`)
- [ ] Backup-Verzeichnis erstellt (`C:\Backups\Day2Day-MySQL`)
- [ ] Windows Task-Scheduler eingerichtet:
  - [ ] Name: "Day2Day MySQL Backup"
  - [ ] Zeitplan: Täglich 2:00 Uhr
  - [ ] Aktion: `backup-mysql.bat` ausführen
  - [ ] Aktiviert: Ja

### Erstes manuelles Backup
```batch
backup-mysql.bat
```
- [ ] Backup erfolgreich erstellt
- [ ] Dateigröße plausibel (>5 KB)
- [ ] Log-Datei vorhanden

---

## 📊 Performance-Monitoring

### Erste Woche nach Migration
- [ ] Tag 1: Performance-Check (Ladezeiten notieren)
- [ ] Tag 3: Error-Logs prüfen (`storage/logs/laravel.log`)
- [ ] Tag 7: Backup-Routine validieren

### Metriken tracken
- [ ] Dashboard-Ladezeit: _____ Sekunden
- [ ] Gantt-Chart-Ladezeit: _____ Sekunden
- [ ] MOCO-Sync-Dauer: _____ Sekunden
- [ ] DB-Größe: _____ MB

---

## 🐛 Troubleshooting

### Bei Problemen getestet:

#### Rollback zu SQLite
```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite
```
- [ ] Rollback funktioniert
- [ ] Alte Daten noch verfügbar

#### MySQL-Connection-Test
```powershell
php artisan tinker
DB::connection()->getPdo();
```
- [ ] Verbindung erfolgreich

#### Datenbank-Integrität
```sql
-- In phpMyAdmin SQL-Tab
SELECT COUNT(*) FROM projects;
SELECT COUNT(*) FROM employees;
SELECT COUNT(*) FROM assignments;
```
- [ ] Counts stimmen überein mit Export

---

## ✅ Migration abgeschlossen

**Bestätigt durch:**
- [ ] Alle UI-Tests erfolgreich
- [ ] MOCO-Sync funktioniert
- [ ] Performance verbessert
- [ ] Backup-Routine aktiv
- [ ] Team informiert

**Abgeschlossen am:** ______________  
**Abgeschlossen von:** ______________  

---

## 📚 Dokumentation

**Erstellt:**
- [x] `MYSQL_MIGRATION_GUIDE.md` (detailliert)
- [x] `MYSQL_QUICKSTART.md` (5-Minuten-Setup)
- [x] `MYSQL_MIGRATION_CHECKLIST.md` (diese Datei)
- [x] `backup-mysql.bat` (Backup-Script)
- [x] Commands: `db:export-mysql`, `db:import-mysql`

**Nächste Schritte:**
- [ ] PROJECT_ROADMAP.md aktualisieren
- [ ] CHANGELOG.md erweitern
- [ ] Team-Meeting: Migration-Review

---

**Version:** 1.0  
**Letztes Update:** 03.11.2025
