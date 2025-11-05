# 🔄 MySQL Migration - Schritt-für-Schritt Anleitung

**Status:** Export abgeschlossen ✅  
**Datum:** 03.11.2025  
**Exportierte Daten:** 1 Projekt, 5 Mitarbeiter, 13 Assignments

---

## 📦 Exportierte Daten

| Tabelle | Anzahl | Status |
|---------|--------|--------|
| Projects | 1 | ✅ Exportiert |
| Employees | 5 | ✅ Exportiert |
| Assignments | 13 | ✅ Exportiert |
| TimeEntries | 0 | - |
| Absences | 0 | - |

**Export-Datei:** `database/mysql-import.sql` (8.17 KB)

---

## 🚀 Migration durchführen

### Schritt 1: MySQL-Datenbank in phpMyAdmin erstellen

1. **phpMyAdmin öffnen:**
   ```
   http://localhost/phpmyadmin
   oder
   http://localhost:8080/phpmyadmin
   ```

2. **Neue Datenbank erstellen:**
   - Klick auf "Neu" (links oben)
   - Datenbankname: `day2day`
   - Kollation: `utf8mb4_unicode_ci` (wichtig für Umlaute!)
   - Klick auf "Anlegen"

3. **Benutzer-Rechte prüfen:**
   - User: `root`
   - Passwort: leer (oder dein XAMPP-Passwort)
   - Der User sollte automatisch alle Rechte auf `day2day` haben

---

### Schritt 2: Laravel auf MySQL umstellen

**Deine .env ist bereits konfiguriert! ✅**

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3307           ← Prüfe ob dein MySQL auf 3306 oder 3307 läuft!
DB_DATABASE=day2day
DB_USERNAME=root
DB_PASSWORD=           ← Falls du ein Passwort gesetzt hast, hier eintragen
```

**⚠️ WICHTIG:** Prüfe den MySQL-Port!

```powershell
# In PowerShell ausführen:
netstat -an | findstr "330"
```

- Wenn `3306` erscheint → `.env` auf `DB_PORT=3306` ändern
- Wenn `3307` erscheint → Alles OK (bereits konfiguriert)

---

### Schritt 3: Datenbank-Schema erstellen

**Terminal-Befehl:**

```powershell
cd c:\xampp\htdocs\Day2Day-Manager
php artisan migrate:fresh
```

**Erwartete Ausgabe:**
```
Dropped all tables successfully.
Migration table created successfully.
Migrating: 0001_01_01_000000_create_users_table
Migrated:  0001_01_01_000000_create_users_table (45.23ms)
...
[Alle 23 Migrations]
```

**Bei Fehlern:**

#### Fehler: "Access denied for user 'root'@'localhost'"
```powershell
# Lösung 1: Passwort in .env setzen
DB_PASSWORD=dein_xampp_passwort

# Lösung 2: MySQL-User anlegen in phpMyAdmin
# SQL-Tab → Ausführen:
CREATE USER 'day2day_user'@'localhost' IDENTIFIED BY 'sicheres_passwort';
GRANT ALL PRIVILEGES ON day2day.* TO 'day2day_user'@'localhost';
FLUSH PRIVILEGES;

# Dann .env anpassen:
DB_USERNAME=day2day_user
DB_PASSWORD=sicheres_passwort
```

#### Fehler: "Connection refused"
```powershell
# MySQL-Dienst starten in XAMPP Control Panel
# ODER per PowerShell:
net start MySQL
```

---

### Schritt 4: Daten importieren

**Option A: Via phpMyAdmin (empfohlen für kleine Datenmengen)**

1. phpMyAdmin öffnen
2. Datenbank `day2day` auswählen (links)
3. Tab "Importieren" klicken
4. "Datei auswählen" → Navigiere zu:
   ```
   C:\xampp\htdocs\Day2Day-Manager\database\mysql-import.sql
   ```
5. Format: `SQL`
6. "Importieren" klicken

**Erwartete Ausgabe:**
```
Import erfolgreich abgeschlossen, 13 Abfragen ausgeführt.
```

**Option B: Via Command-Line (schneller bei großen Datenmengen)**

```powershell
cd c:\xampp\mysql\bin
.\mysql.exe -u root -p --port=3307 day2day < C:\xampp\htdocs\Day2Day-Manager\database\mysql-import.sql
# Passwort eingeben (oder Enter wenn leer)
```

---

### Schritt 5: Validierung

**1. Daten prüfen:**

```powershell
cd c:\xampp\htdocs\Day2Day-Manager
php artisan tinker
```

Dann in Tinker:
```php
\App\Models\Project::count()      // Sollte 1 sein
\App\Models\Employee::count()     // Sollte 5 sein
\App\Models\Assignment::count()   // Sollte 13 sein
exit
```

**2. UI testen:**

```powershell
php artisan serve
# Browser: http://127.0.0.1:8000
```

**Checkliste:**
- [ ] Dashboard lädt ohne Fehler
- [ ] Projekte anzeigen funktioniert
- [ ] Mitarbeiter-Liste vollständig
- [ ] Gantt-Chart zeigt Assignments
- [ ] MOCO-Sync-Buttons funktionieren

---

### Schritt 6: MOCO-Sync testen

**Wichtig:** Cache leeren nach Migration!

```powershell
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

**Test-Sync:**
```powershell
# Projekte synchronisieren
php artisan sync:moco-projects --dry-run

# Falls OK:
php artisan sync:moco-projects
```

---

## ⚠️ Troubleshooting

### Problem: "Class 'PDO' not found"

**Lösung:** PHP-MySQL-Extension aktivieren

1. Öffne: `C:\xampp\php\php.ini`
2. Suche nach: `;extension=pdo_mysql`
3. Entferne das `;` (Semikolon): `extension=pdo_mysql`
4. Speichern & Apache neu starten

### Problem: Migration schlägt fehl bei bestimmten Tabellen

```powershell
# Einzelne Migration ausführen:
php artisan migrate:refresh --path=database/migrations/2025_09_09_113655_create_assignments_table.php

# Oder alles von vorne:
php artisan migrate:fresh --force
```

### Problem: Importierte Daten fehlen

```sql
-- In phpMyAdmin SQL-Tab prüfen:
SELECT * FROM projects;
SELECT * FROM employees;
SELECT * FROM assignments;

-- Falls leer: Import wiederholen
-- Vorher Tabellen leeren:
TRUNCATE TABLE assignments;
TRUNCATE TABLE projects;
TRUNCATE TABLE employees;
```

---

## 🎯 Performance-Vergleich (vorher/nachher)

### SQLite (vorher)
```
Query-Zeit: ~50-150ms (Laptop-Festplatte)
Concurrent Reads: Langsam bei >3 Users
Datei-Locking: Häufig bei Schreibzugriffen
```

### MySQL (nachher - erwartet)
```
Query-Zeit: ~10-30ms (dedizierter Server)
Concurrent Reads: Schnell bis 50+ Users
Connection Pool: Keine Locking-Probleme
Indexierung: Automatisch optimiert
```

**Erwartete Verbesserung:** 3-5x schnellere Ladezeiten

---

## 📋 Backup-Strategie (neu mit MySQL)

**Automatisches Backup einrichten:**

```batch
REM backup-mysql.bat (speichern im Projekt-Root)
@echo off
set BACKUP_DIR=C:\Backups\Day2Day-MySQL
set DATE=%date:~-4,4%%date:~-10,2%%date:~-7,2%
set TIME=%time:~0,2%%time:~3,2%

C:\xampp\mysql\bin\mysqldump.exe -u root --port=3307 day2day > %BACKUP_DIR%\day2day-%DATE%-%TIME%.sql
echo Backup erstellt: %BACKUP_DIR%\day2day-%DATE%-%TIME%.sql
```

**Windows Task-Scheduler:**
- Aufgabe: Täglich 2:00 Uhr nachts
- Programm: `C:\xampp\htdocs\Day2Day-Manager\backup-mysql.bat`

---

## ✅ Erfolgs-Kriterien

**Migration ist erfolgreich wenn:**

1. ✅ Alle Migrations laufen ohne Fehler
2. ✅ Import zeigt "13 Abfragen ausgeführt"
3. ✅ `Project::count()` = 1
4. ✅ `Employee::count()` = 5
5. ✅ `Assignment::count()` = 13
6. ✅ Dashboard lädt in <2 Sekunden
7. ✅ Gantt-Chart zeigt alle Assignments
8. ✅ MOCO-Sync funktioniert ohne Fehler

---

## 🔄 Rollback (falls nötig)

**Falls MySQL-Probleme auftreten:**

```env
# .env zurücksetzen auf SQLite:
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite
```

```powershell
# Cache leeren
php artisan config:clear
php artisan cache:clear

# Testen
php artisan serve
```

Deine alte SQLite-Datei ist noch vorhanden in:
```
database/database.sqlite
```

---

## 📞 Support

**Bei Problemen:**

1. Logs prüfen: `storage/logs/laravel.log`
2. MySQL-Logs: `C:\xampp\mysql\data\mysql_error.log`
3. PHP-Fehler: `C:\xampp\php\logs\php_error_log`

**Häufige Fehler & Lösungen:** Siehe Troubleshooting-Sektion oben

---

**Erstellt:** 03.11.2025  
**Command:** `php artisan db:export-mysql`  
**Export-Datei:** `database/mysql-import.sql`
