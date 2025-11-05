# ⚡ MySQL Migration - Quick Start

**5-Minuten-Setup** | Stand: 03.11.2025

---

## ✅ Vorbereitung abgeschlossen

- [x] SQLite-Export erstellt (8.17 KB)
- [x] .env bereits auf MySQL konfiguriert
- [x] config/database.php optimiert
- [x] Backup-Script erstellt

---

## 🚀 Jetzt durchführen (4 Schritte)

### 1️⃣ MySQL-Port prüfen (30 Sekunden)

```powershell
netstat -an | findstr "330"
```

**Falls `3306` erscheint:**
```env
# .env anpassen (Zeile 24):
DB_PORT=3306
```

**Falls `3307` erscheint:** ✅ Nichts tun (schon richtig)

---

### 2️⃣ Datenbank in phpMyAdmin erstellen (1 Minute)

1. Öffne: http://localhost/phpmyadmin
2. Klick "Neu" (links)
3. Name: `day2day`
4. Kollation: `utf8mb4_unicode_ci`
5. Klick "Anlegen"

**Screenshot-Check:** Du siehst jetzt `day2day` in der linken Liste

---

### 3️⃣ Schema erstellen + Daten importieren (2 Minuten)

```powershell
cd c:\xampp\htdocs\Day2Day-Manager

# Schema erstellen
php artisan migrate:fresh

# Daten importieren
php artisan db:import-mysql
```

**Erwartete Ausgabe:**
```
✅ Dropped all tables successfully
✅ Migration table created
✅ Migrating: [23 Migrations]
✅ Import erfolgreich: 13 Assignments, 5 Employees, 1 Project
```

---

### 4️⃣ Testen (1 Minute)

```powershell
# Server starten
php artisan serve

# Browser öffnen
start http://127.0.0.1:8000
```

**Checkliste:**
- [ ] Dashboard lädt
- [ ] Projekte anzeigen
- [ ] Gantt-Chart funktioniert

---

## ❌ Bei Fehlern

### "Access denied for user 'root'"

```powershell
# .env Zeile 26 anpassen:
DB_PASSWORD=dein_xampp_passwort
```

### "Connection refused"

```
XAMPP Control Panel → MySQL → Start
```

### "Table doesn't exist"

```powershell
# Nochmal von vorne:
php artisan migrate:fresh
```

---

## 📚 Detaillierte Anleitung

Siehe: `MYSQL_MIGRATION_GUIDE.md` (vollständige Dokumentation)

---

## 🎯 Fertig!

Nach erfolgreicher Migration:

```powershell
# Cache leeren
php artisan cache:clear
php artisan config:clear

# MOCO-Sync testen
php artisan sync:moco-projects --dry-run
```

**Performance-Gewinn:** 3-5x schnellere Ladezeiten erwartet 🚀

---

**Support:** Siehe `MYSQL_MIGRATION_GUIDE.md` → Troubleshooting-Sektion
