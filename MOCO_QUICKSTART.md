# MOCO Integration - Quick Start Guide

## ⚡ In 5 Minuten einsatzbereit!

### Schritt 1: .env-Datei konfigurieren ✅

Ihre `.env` enthält bereits die MOCO-Konfiguration:

```env
MOCO_API_KEY=your_api_key
MOCO_DOMAIN=enodiasoftware.mocoapp.com
MOCO_BASE_URL=https://enodiasoftware.mocoapp.com/api/v1
```

**Wichtig:** Tauschen Sie `your_api_key` gegen Ihren echten MOCO API-Key aus!

**Wo finde ich meinen API-Key?**
1. Bei MOCO anmelden
2. Einstellungen → Integrationen → API
3. Neuen API-Key erstellen
4. Key kopieren und in `.env` einfügen

### Schritt 2: Migrationen ausführen

```bash
php artisan migrate
```

✅ Dies erstellt die notwendigen Datenbank-Tabellen für Sync-Logs.

### Schritt 3: MOCO-Bereich öffnen

Öffnen Sie in Ihrem Browser:
```
http://localhost/moco
```
(oder Ihre Domain: `http://ihre-domain.de/moco`)

### Schritt 4: Verbindung testen

1. Klicken Sie auf den Button **"Verbindung testen"**
2. Sie sollten eine Erfolgsmeldung sehen: ✅ "Verbindung zur MOCO API erfolgreich!"

### Schritt 5: Erste Synchronisation

Klicken Sie auf **"Alles synchronisieren"**

Das wars! 🎉

Die erste Synchronisation läuft jetzt und importiert:
- ✅ Alle Mitarbeiter aus MOCO
- ✅ Alle Projekte aus MOCO
- ✅ Zeiterfassungen der letzten 30 Tage

---

## 🎯 Navigation

Nach der ersten Sync können Sie folgende Bereiche nutzen:

### 📊 **Dashboard** (`/moco`)
- Schnellübersicht über alle synchronisierten Daten
- Buttons für manuelle Synchronisationen
- Letzte Sync-Aktivitäten

### 📈 **Statistiken** (`/moco/statistics`)
- Detaillierte Auswertungen
- Datenabdeckung (wie viel ist synchronisiert?)
- Monatliche Trends

### 📜 **Sync-History** (`/moco/logs`)
- Alle vergangenen Synchronisationen
- Filter nach Typ und Status
- Fehlerdetails bei Problemen

### 🔗 **Mappings** (`/moco/mappings`)
- Lokale Daten ohne MOCO-Verknüpfung
- Hilfreich für Fehlersuche

---

## ⏰ Automatische Synchronisation (Optional)

### Variante 1: Über Cron (empfohlen)

Fügen Sie in Ihre Crontab ein:
```bash
0 2 * * * cd /pfad/zu/projekt && php artisan moco:sync-all --active --days=7 >> /dev/null 2>&1
```

Dies synchronisiert täglich um 2:00 Uhr alle aktiven Daten der letzten 7 Tage.

### Variante 2: Laravel Scheduler

In `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('moco:sync-all --active --days=7')
             ->dailyAt('02:00');
}
```

Dann Cron einrichten:
```bash
* * * * * cd /pfad/zu/projekt && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🎨 Häufige Sync-Szenarien

### Nur neue Zeiterfassungen (täglich)
```bash
php artisan moco:sync-activities --days=1
```

### Alle Mitarbeiter aktualisieren
```bash
php artisan moco:sync-employees
```

### Nur aktive Projekte
```bash
php artisan moco:sync-projects --active
```

### Bestimmter Zeitraum
```bash
php artisan moco:sync-activities --from=2025-10-01 --to=2025-10-31
```

---

## ❓ Probleme?

### "Verbindung fehlgeschlagen"
- ✅ API-Key in `.env` korrekt?
- ✅ MOCO_DOMAIN ohne `https://`
- ✅ MOCO_BASE_URL mit vollständiger URL

### "Keine Daten synchronisiert"
- ✅ Sind Daten in MOCO vorhanden?
- ✅ Sind sie als "aktiv" markiert?
- ✅ Logs prüfen: `storage/logs/laravel.log`

### "Zu langsam"
- ✅ Zeitraum einschränken: `--days=7`
- ✅ Nur aktive Items: `--active`
- ✅ Außerhalb der Stoßzeiten synchronisieren

---

## 🚀 Fertig!

Sie haben jetzt:
- ✅ Eine funktionierende MOCO-Integration
- ✅ Automatische Synchronisation (optional)
- ✅ Vollständiges Dashboard mit Statistiken
- ✅ Überwachung aller Sync-Vorgänge

**Viel Erfolg mit Ihrer MOCO-Integration!** 🎉

---

**Weitere Hilfe benötigt?**
- 📖 `MOCO_BEREICH_README.md` - Vollständige Dokumentation
- 📖 `MOCO_INTEGRATION.md` - Technische Details
- 🌐 MOCO API Docs: https://moco.de/api/

