# 🏢 Projektmanagement - enodia IT-Systemhaus

## 📋 Firmenprojekt: Ressourcen- und Kapazitätsverwaltung
**Projekttyp:** Individualisierte Webapplikation für internes Projektmanagement  
**Entwicklungszeitraum:** September 2025 - laufend  
**Entwickler:** Jörg Michno  
**Status:** ✅ **PRODUKTIV** - In aktiver Nutzung

## 🚀 Features
- 👥 **Mitarbeiterverwaltung** mit Kapazitätsübersicht und Auslastungsampel
- 📊 **Projektverwaltung** mit Teamzuweisung und Fortschrittsverfolgung
- ⚡ **Echtzeit-Auslastungsberechnung** mit visueller Darstellung
- 📈 **CSV-Export** für Reporting und Analyse
- 🚦 **Ampelsystem** für Ressourcenauslastung (Grün/Gelb/Rot)
- 📅 **Abwesenheitsverwaltung** mit Urlaub, Krankheit und Fortbildung
- 🔐 **Benutzerauthentifizierung** mit Laravel Breeze

## 🛠️ Technischer Stack
- **Framework:** Laravel 12.x (PHP 8.2+)
- **Datenbank:** SQLite (Development) / MySQL (Production)
- **Frontend:** Blade Templates mit modernem CSS
- **Server:** Apache 2.4 (XAMPP)
- **Styling:** Custom CSS mit professionellem Design
- **Export:** CSV-Export für Excel-Kompatibilität

## 🎯 Projektziele (ERREICHT)
- ✅ Vollständige Funktionsfähigkeit aller Module
- ✅ Sauberer, wartbarer Code nach Laravel-Standards
- ✅ Professionelle Benutzeroberfläche
- ✅ SQLite-Kompatibilität für lokale Entwicklung
- ✅ Responsive Design für verschiedene Bildschirmgrößen

 Schnellstart nach Neustart
1. XAMPP starten

XAMPP Control Panel öffnen
Apache → Start
MySQL → Start

2. Terminal/PowerShell öffnen und zum Projekt navigieren
cd C:\xampp\htdocs\mein-projekt

3. Laravel Server starten
php artisan serve

4. Browser öffnen
http://127.0.0.1:8000

5. Login
Email: admin@enodia.de
Passwort: Test1234

Git-Befehle (im Projektordner)

Status prüfen
git status

Änderungen speichern
git add .
git commit -m "Beschreibung der Änderungen"

Historie anzeigen
git log --oneline

Troubleshooting

Falls Server nicht startet
php artisan cache:clear
php artisan config:clear
php artisan route:clear

Falls Datenbank-Probleme
php artisan migrate:fresh --seed

Wichtige Pfade

Projekt: C:\xampp\htdocs\mein-projekt
phpMyAdmin: http://localhost/phpmyadmin
Datenbank: terminplanungstool