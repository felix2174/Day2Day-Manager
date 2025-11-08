# 🚀 Deployment-Anleitung: Day2Day-Manager

Diese Anleitung erklärt, wie du Änderungen vom lokalen Laptop über GitLab auf den Plesk-Server deployst.

---

## 📋 Inhaltsverzeichnis

1. [Lokale Entwicklung → GitLab](#1-lokale-entwicklung--gitlab)
2. [GitLab → Plesk Server](#2-gitlab--plesk-server)
3. [MOCO Synchronisation](#3-moco-synchronisation)
4. [Deployment-Checkliste](#deployment-checkliste)
5. [Troubleshooting](#troubleshooting)
6. [Best Practices](#best-practices)

---

## 1. Lokale Entwicklung → GitLab

### Schritt 1: Änderungen prüfen

```bash
# Zeige alle geänderten Dateien
git status

# Zeige Unterschiede (optional)
git diff
```

### Schritt 2: Änderungen stagen

```bash
# Alle Änderungen hinzufügen
git add -A

# Oder einzelne Dateien
git add app/Models/User.php
git add resources/views/users/index.blade.php
```

### Schritt 3: Commit erstellen

```bash
# Mit aussagekräftiger Nachricht
git commit -m "Beschreibung der Änderungen"

# Beispiel:
git commit -m "Add user management system with RBAC"
```

**Wichtig:** 
- Verwende aussagekräftige Commit-Messages
- Beschreibe WAS geändert wurde und WARUM (falls relevant)

### Schritt 4: Zu GitLab pushen

```bash
# Push zum GitLab Repository
git push gitlab main

# Falls es Konflikte gibt:
git pull gitlab main
# Konflikte lösen, dann:
git push gitlab main
```

---

## 2. GitLab → Plesk Server

### Schritt 1: SSH-Verbindung zum Server

```bash
# Verbinde dich mit dem Server
ssh enodia@192.168.228.30
# MB6g5f!TK2grz!xq

# Oder falls du direkt als root einloggst:
ssh root@192.168.228.30
```

### Schritt 2: Zu Plesk-User wechseln

```bash
# WICHTIG: Als Plesk-User arbeiten, nicht als root!
su daytoday.enodia-soft_2z8v0lj6aa7

# Ins Projektverzeichnis wechseln
cd ~/httpdocs
```

**⚠️ Wichtig:** Arbeite immer als Plesk-User (`daytoday.enodia-soft_2z8v0lj6aa7`), nicht als root! Sonst können Dateiberechtigungen kaputt gehen.

### Schritt 3: Code aktualisieren

```bash
# Hole die neuesten Änderungen von GitLab
git pull origin main
```

**Falls es Fehler gibt:**
- Konflikte: `git stash` → `git pull` → `git stash pop`
- Oder manuell lösen

### Schritt 4: Composer Dependencies aktualisieren

```bash
# Prüfe ob composer.json geändert wurde
git diff HEAD~1 composer.json

# Falls ja, installiere/aktualisiere Dependencies
composer install --no-dev --optimize-autoloader
```

**Hinweis:** `--no-dev` installiert nur Production-Dependencies (schneller, sicherer)

### Schritt 5: Datenbank-Migrationen ausführen

```bash
# WICHTIG: Führe Migrationen aus für neue Datenbank-Änderungen
php artisan migrate --force
```

**⚠️ Achtung:** 
- `--force` ist nötig in Production
- Prüfe vorher, ob neue Migrationen vorhanden sind: `php artisan migrate:status`

### Schritt 6: Caches leeren

```bash
# Alle Caches auf einmal leeren
php artisan optimize:clear

# Oder einzeln:
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

**Warum wichtig?** Laravel cached Konfiguration, Routes und Views. Nach Code-Änderungen müssen diese geleert werden!

### Schritt 7: Frontend-Assets bauen

```bash
# Baue die Frontend-Assets (Vite)
npm run build
```

**Hinweis:** Falls `npm run build` fehlschlägt:
- Prüfe ob `node_modules` existiert: `ls -la node_modules`
- Falls nicht: `npm install` → `npm run build`

### Schritt 8: Caches neu aufbauen (Production)

```bash
# Für bessere Performance in Production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Hinweis:** Diese Caches verbessern die Performance. Nach Code-Änderungen aber erst `optimize:clear` ausführen!

### Schritt 9: Testen

```bash
# Öffne die Website im Browser
# https://daytoday.enodia-software.de

# Prüfe Logs bei Fehlern
tail -f storage/logs/laravel.log
```

---

## 3. MOCO Synchronisation

### 🏗️ Architektur

**WICHTIG:** MOCO ist die Single Source of Truth!

- **MySQL-Datenbank** = Performance-Cache für schnellen Zugriff
- **MOCO API** = Primäre Datenquelle (immer aktuell)

### Datentypen und Sync-Häufigkeit

#### Stammdaten (1x täglich um 2:00 Uhr nachts)
```bash
php artisan moco:sync-employees   # Mitarbeiter
php artisan moco:sync-projects    # Projekte  
php artisan moco:sync-contracts   # Verträge
```

#### Bewegungsdaten (mehrmals täglich)
```bash
php artisan moco:sync-time-entries  # Zeiterfassungen (stündlich 8-18 Uhr)
php artisan moco:sync-absences      # Abwesenheiten (stündlich 8-18 Uhr)
php artisan moco:sync-assignments   # Zuweisungen (alle 4 Stunden)
```

#### Manuelle Vollsynchronisation
```bash
php artisan moco:sync-all  # Synchronisiert alle Daten
```

### Cron-Job auf Plesk einrichten

**1. Plesk Admin-Panel öffnen**
- Gehe zu "Websites & Domains" → daytoday.enodia-software.de
- Klicke auf "Scheduled Tasks" (Geplante Aufgaben)

**2. Neuen Cron-Job erstellen**

**Einstellungen:**
- **Task Type:** Befehl ausführen
- **Schedule:** Jede Minute (`* * * * *`)
- **Befehl:**
```bash
cd /var/www/vhosts/daytoday.enodia-software.de/httpdocs && php artisan schedule:run >> /dev/null 2>&1
```

**Wichtig:** Dieser eine Cron-Job führt automatisch alle geplanten Tasks aus (siehe `routes/console.php`)

### Status prüfen

```bash
# Zeige alle geplanten Tasks
php artisan schedule:list

# Zeige Datenbankinhalt
php artisan tinker --execute="echo 'Mitarbeiter: ' . App\Models\Employee::count();"
php artisan tinker --execute="echo 'Projekte: ' . App\Models\Project::count();"
```

### Troubleshooting

**Problem:** Keine Daten in der Datenbank

```bash
# Manuell synchronisieren
php artisan moco:sync-employees
php artisan moco:sync-projects
```

**Problem:** MOCO API antwortet nicht

```bash
# Verbindung testen
php artisan moco:test-connection

# Cache leeren
php artisan cache:clear
```

**Problem:** Cron-Job läuft nicht

```bash
# Prüfe ob Schedule funktioniert
php artisan schedule:run

# Prüfe Logs
tail -f storage/logs/laravel.log
```

---

## 📝 Deployment-Checkliste

### Vor dem Deployment

- [ ] Alle lokalen Änderungen getestet
- [ ] Code committed und zu GitLab gepusht
- [ ] Keine uncommitted Änderungen mehr lokal

### Auf dem Server

- [ ] SSH-Verbindung hergestellt
- [ ] Als Plesk-User eingeloggt (`su daytoday.enodia-soft_2z8v0lj6aa7`)
- [ ] Im richtigen Verzeichnis (`cd ~/httpdocs`)
- [ ] Code aktualisiert (`git pull origin main`)
- [ ] Composer Dependencies aktualisiert (`composer install`)
- [ ] Datenbank-Migrationen ausgeführt (`php artisan migrate --force`)
- [ ] **MOCO Daten synchronisiert** (`php artisan moco:sync-all` beim ersten Deployment)
- [ ] **Cron-Job eingerichtet** (siehe MOCO Synchronisation Abschnitt)
- [ ] Caches geleert (`php artisan optimize:clear`)
- [ ] Frontend-Assets gebaut (`npm run build`)
- [ ] Caches neu aufgebaut (`php artisan config:cache` etc.)
- [ ] Website im Browser getestet
- [ ] Keine Fehler in den Logs

---

## 🔧 Automatisches Deployment-Script

Für schnelleres Deployment kannst du das Script `deploy.sh` verwenden:

```bash
# Script ausführbar machen (einmalig)
chmod +x deploy.sh

# Script ausführen
./deploy.sh
```

Das Script führt automatisch alle Schritte aus (siehe `deploy.sh` Datei).

---

## 🐛 Troubleshooting

### Problem: "Permission denied"

**Lösung:**
```bash
# Prüfe ob du als Plesk-User arbeitest
whoami
# Sollte ausgeben: daytoday.enodia-soft_2z8v0lj6aa7

# Falls nicht:
su daytoday.enodia-soft_2z8v0lj6aa7
```

### Problem: "Migration failed"

**Lösung:**
```bash
# Prüfe Migration-Status
php artisan migrate:status

# Prüfe Logs
tail -50 storage/logs/laravel.log

# Falls nötig, Migration manuell ausführen
php artisan migrate --path=/database/migrations/2025_XX_XX_XXXXXX_migration_name.php --force
```

### Problem: "Vite manifest not found"

**Lösung:**
```bash
# Frontend-Assets neu bauen
npm run build

# Prüfe ob public/build existiert
ls -la public/build/
```

### Problem: "Class not found" oder "Method does not exist"

**Lösung:**
```bash
# Composer Autoload neu generieren
composer dump-autoload

# Caches leeren
php artisan optimize:clear
```

### Problem: "500 Internal Server Error"

**Lösung:**
```bash
# Debug-Modus temporär aktivieren
nano .env
# Ändere: APP_DEBUG=true

# Caches leeren
php artisan optimize:clear

# Logs prüfen
tail -100 storage/logs/laravel.log
```

### Problem: Git-Konflikte

**Lösung:**
```bash
# Lokale Änderungen temporär speichern
git stash

# Code aktualisieren
git pull origin main

# Lokale Änderungen wieder anwenden
git stash pop

# Konflikte manuell lösen, dann:
git add .
git commit -m "Resolve merge conflicts"
git push origin main
```

---

## 💡 Best Practices

### 1. Immer als Plesk-User arbeiten
- ❌ **NICHT** als root arbeiten
- ✅ **IMMER** als `daytoday.enodia-soft_2z8v0lj6aa7` arbeiten

### 2. Regelmäßig deployen
- Kleine, häufige Deployments sind besser als große, seltene
- Reduziert das Risiko von Konflikten

### 3. Vor Deployment testen
- Immer lokal testen bevor du pusht
- Prüfe ob Migrationen funktionieren

### 4. Backup vor großen Änderungen
```bash
# Datenbank-Backup (falls möglich)
mysqldump -u user -p database_name > backup_$(date +%Y%m%d).sql
```

### 5. Logs im Auge behalten
```bash
# Logs live mitverfolgen
tail -f storage/logs/laravel.log
```

### 6. Git-Best Practices
- Aussagekräftige Commit-Messages
- Nicht direkt auf `main` pushen (falls möglich)
- Regelmäßig committen

---

## 📞 Bei Problemen

1. **Logs prüfen:** `tail -100 storage/logs/laravel.log`
2. **Cache leeren:** `php artisan optimize:clear`
3. **Debug-Modus aktivieren:** `.env` → `APP_DEBUG=true`
4. **Git-Status prüfen:** `git status`
5. **Migration-Status prüfen:** `php artisan migrate:status`

---

## 🔄 Schnell-Referenz

### Kompletter Deployment-Workflow (Copy & Paste)

```bash
# 1. Als Plesk-User einloggen
su daytoday.enodia-soft_2z8v0lj6aa7
cd ~/httpdocs

# 2. Code aktualisieren
git pull origin main

# 3. Dependencies & Migrationen
composer install --no-dev --optimize-autoloader
php artisan migrate --force

# 4. Caches & Assets
php artisan optimize:clear
npm run build

# 5. Production-Caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Fertig! Website testen
```

---

**Viel Erfolg beim Deployment! 🚀**


