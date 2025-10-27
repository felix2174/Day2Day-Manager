# 🏢 Day2Day-Manager

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php)](https://php.net)
[![License](https://img.shields.io/badge/License-Proprietary-red)](LICENSE)

> **Projektmanagement-Tool für enodia IT-Systemhaus**  
> Laravel-basierte Webanwendung mit MOCO-Integration zur Verwaltung von Projekten, Aufgaben, Zeiterfassung und Mitarbeitern.

**Status:** ✅ **PRODUKTIV** - In aktiver Nutzung  
**Entwickler:** Jörg Michno, Felix  
**Entwicklungszeitraum:** September 2025 - laufend

## ✨ Features

- 📊 **Dashboard** mit Projektstatus und laufenden Aktivitäten
- 📁 **Projektmanagement** mit Status-Tracking und Teamzuweisung
- ✅ **Aufgabenverwaltung** mit Prioritäten und Zuweisungen
- ⏱️ **Zeiterfassung** mit manueller und automatischer Erfassung
- 👥 **Mitarbeiterverwaltung** mit Kapazitätsübersicht und Auslastungsampel
- � **MOCO-Integration** für nahtlose API-Anbindung
- 📅 **Abwesenheitsverwaltung** (Urlaub, Krankheit, Fortbildung)
- 🚦 **Ampelsystem** für Ressourcenauslastung (Grün/Gelb/Rot)
- 📈 **CSV-Export** für Reporting und Analyse
- 🔐 **Benutzerauthentifizierung** mit Laravel Breeze
- ⚡ **Echtzeit-Auslastungsberechnung** mit visueller Darstellung

## � Quick Start

### Voraussetzungen

- PHP 8.2 oder höher
- Composer
- Node.js & npm
- SQLite (oder MySQL/PostgreSQL)
- XAMPP (optional für lokale Entwicklung)

### Installation

```bash
# 1. Repository klonen
git clone https://github.com/felix2174/Day2Day-Manager.git
cd Day2Day-Manager

# 2. Dependencies installieren
composer install
npm install && npm run build

# 3. Umgebung einrichten
cp .env.example .env
php artisan key:generate

# 4. Datenbank vorbereiten
touch database/database.sqlite  # Linux/Mac
# Oder: New-Item database/database.sqlite  # Windows PowerShell
php artisan migrate --seed

# 5. Server starten
php artisan serve
```

Öffne http://localhost:8000 im Browser.

### Demo-Login (nach Seeding)

```
E-Mail: admin@enodia.de
Passwort: Test1234
```

## ⚙️ Konfiguration

### Umgebungsvariablen (.env)

```env
# Datenbank
DB_CONNECTION=sqlite
# DB_DATABASE=/absolute/path/to/database.sqlite

# MOCO Integration
MOCO_API_KEY=your_moco_api_key_here
MOCO_DOMAIN=your-company.mocoapp.com

# Mail (optional)
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
```

### MOCO-Integration einrichten

1. Logge dich in dein MOCO-Konto ein
2. Gehe zu Einstellungen → API
3. Erstelle einen neuen API-Key
4. Kopiere den Key in deine `.env` Datei unter `MOCO_API_KEY`
5. Setze deine MOCO-Domain unter `MOCO_DOMAIN`

## 🛠️ Technischer Stack

- **Backend:** Laravel 12.x, PHP 8.2+
- **Frontend:** Blade Templates, Alpine.js, Tailwind CSS
- **Datenbank:** SQLite (dev), MySQL/PostgreSQL (prod)
- **APIs:** MOCO API Integration
- **Server:** Apache 2.4 (XAMPP) / Nginx
- **Tools:** Composer, NPM, Vite

## 📁 Projektstruktur

```
Day2Day-Manager/
├── app/
│   ├── Http/Controllers/     # Controller für Routen
│   ├── Models/               # Eloquent Models (Employee, Project, etc.)
│   └── Services/             # Business Logic (MocoService, etc.)
├── database/
│   ├── migrations/           # Datenbank-Migrationen
│   └── seeders/              # Test- und Demo-Daten
├── resources/
│   ├── views/                # Blade Templates
│   └── js/                   # Frontend JavaScript
├── routes/
│   └── web.php               # Webrouten
└── public/                   # Öffentliche Assets
```

## 🔧 Entwicklung

### XAMPP Schnellstart nach Neustart

```bash
# 1. XAMPP Control Panel öffnen
# Apache → Start
# MySQL → Start (optional, wenn nicht SQLite verwendet wird)

# 2. Terminal/PowerShell öffnen und zum Projekt navigieren
cd C:\xampp\htdocs\Day2Day-Manager

# 3. Laravel Server starten
php artisan serve

# 4. Browser öffnen
# http://127.0.0.1:8000
```

### Entwicklungsserver starten

```bash
# Backend
php artisan serve

# Frontend (in separatem Terminal für Hot-Reload)
npm run dev
```

### Tests ausführen

```bash
php artisan test
```

### Code-Style prüfen

```bash
./vendor/bin/pint
```

## 🔄 Git-Workflow

```bash
# Status prüfen
git status

# Änderungen speichern
git add .
git commit -m "Beschreibung der Änderungen"
git push

# Historie anzeigen
git log --oneline
```

## 🐛 Troubleshooting

### Falls Server nicht startet

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Falls Datenbank-Probleme

```bash
# Datenbank zurücksetzen und neu aufbauen
php artisan migrate:fresh --seed

# Nur Migrationen ausführen
php artisan migrate
```

### MOCO-Sync-Probleme

```bash
# Sync-Logs prüfen
php artisan moco:logs

# Manueller Sync
php artisan moco:sync

# Reset und vollständiger Neu-Sync
php artisan moco:reset-and-sync
```

## 🔐 Sicherheit

- ✅ CSRF-Protection aktiviert
- ✅ XSS-Protection durch Blade Escaping
- ✅ Passwort-Hashing mit bcrypt
- ✅ API-Keys in Umgebungsvariablen
- ⚠️ `.env` Datei nie committen!
- ⚠️ SQLite-Datenbank nie ins Repository hochladen

## 🤝 Contributing

Da dies ein internes Projekt ist, sind Contributions momentan nicht öffentlich möglich.

## 📄 License

Proprietary - © 2024-2025 enodia IT-Systemhaus. Alle Rechte vorbehalten.

## 👤 Autoren

- **Jörg Michno** - Hauptentwicklung
- **Felix** - [GitHub](https://github.com/felix2174) - MOCO-Integration & Erweiterungen

## 📞 Support

Bei Fragen oder Problemen:

- Erstelle ein Issue im Repository
- Kontaktiere das Entwicklungsteam von enodia IT-Systemhaus

## 📚 Weitere Dokumentation

- [MOCO Integration Guide](MOCO_INTEGRATION.md)
- [KPI Dashboard Documentation](KPI_DASHBOARD_DOCUMENTATION.md)
- [Gantt MVP Improvements](GANTT_MVP_IMPROVEMENTS.md)
- [Design Rules](DESIGN_RULES.md)

---

**Hinweis:** Dies ist ein internes Tool für enodia IT-Systemhaus. Für die vollständige Nutzung wird ein MOCO-Account benötigt.
