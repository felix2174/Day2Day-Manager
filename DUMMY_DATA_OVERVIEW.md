# Erweiterte Dummy-Daten für das Projektmanagement-Tool

## Übersicht der erstellten Testdaten

### 👥 Mitarbeiter (12 Personen)
- **Thomas Müller** - Backend-Entwicklung (40h)
- **Sarah Weber** - Frontend-Entwicklung (35h)
- **David Klein** - Support (40h)
- **Anna Fischer** - Projektmanagement (32h)
- **Michael Schmidt** - Backend-Entwicklung (40h)
- **Lisa Wagner** - Frontend-Entwicklung (38h)
- **Andreas Becker** - DevOps (40h)
- **Julia Richter** - UX/UI Design (30h)
- **Stefan Hoffmann** - Quality Assurance (40h)
- **Nadine Koch** - Projektmanagement (35h)
- **Marco Bauer** - Backend-Entwicklung (40h) - *inaktiv*
- **Petra Schulz** - Support (25h)

### 🏢 Teams (8 Teams)
- **Entwicklungsteam** - Hauptentwicklungsteam für Webanwendungen
- **Projektmanagement-Team** - Projektplanung und -koordination
- **Support-Team** - Kundensupport und Wartung
- **Frontend-Team** - Spezialisiert auf Benutzeroberflächen und UX/UI
- **Backend-Team** - Server-seitige Entwicklung und APIs
- **DevOps-Team** - Infrastruktur, Deployment und Monitoring
- **QA-Team** - Quality Assurance und Testing
- **Design-Team** - UX/UI Design und Prototyping

### 📋 Projekte (12 Projekte)
- **Projektmanagement enodia** - IHK-Abschlussprojekt (85% Fortschritt)
- **CRM-System Modernisierung** - Kundenverwaltungssystem (45% Fortschritt)
- **Mobile App Kunde XYZ** - Cross-Platform App (0% Fortschritt)
- **API-Integration Projekt** - REST-API (100% abgeschlossen)
- **E-Commerce Plattform** - Online-Shop-Lösung (25% Fortschritt)
- **Datenbank-Migration** - MySQL zu PostgreSQL (60% Fortschritt)
- **Security Audit** - Sicherheitsprüfung (0% Fortschritt)
- **Cloud-Infrastruktur Setup** - AWS-Infrastruktur (70% Fortschritt)
- **Legacy System Wartung** - Wartung veralteter Systeme (40% Fortschritt)
- **KI-Chatbot Integration** - Intelligenter Chatbot (0% Fortschritt)
- **Performance Monitoring** - Monitoring-System (100% abgeschlossen)
- **Microservices Architektur** - Microservices-Umstellung (0% Fortschritt, pausiert)

### 🔗 Zuweisungen (25+ Zuweisungen)
- Verschiedene Auslastungsgrade (0% bis 100%)
- Verschiedene Prioritätsstufen (low, medium, high)
- Realistische Zeiträume und Stundenverteilung
- Überlappende Projekte für realistische Szenarien

### 🏖️ Abwesenheiten (18 Abwesenheiten)
- **Vergangene Abwesenheiten** - Historische Daten
- **Aktuelle Abwesenheiten** - Laufende Urlaube, Krankheiten, Fortbildungen
- **Zukünftige Abwesenheiten** - Geplante Abwesenheiten
- Verschiedene Typen: Urlaub, Krankheit, Fortbildung

### ⏰ Zeiteinträge (30 Tage historische Daten)
- Zeiteinträge für die letzten 30 Arbeitstage
- Realistische Stundenverteilung pro Mitarbeiter
- Detaillierte Beschreibungen der Tätigkeiten
- Verschiedene Projekte pro Mitarbeiter

### 👤 Benutzer (16 Benutzer)
- **Administratoren** - Admin, Marc Hanke, Jörg Michno
- **Mitarbeiter** - Alle Mitarbeiter haben entsprechende Benutzerkonten
- **Management** - Dr. Maria Schmidt (Projektmanager), Robert Weber (Geschäftsführung)
- **HR** - Sabine Müller

### 🎯 Team-Zuweisungen
- Logische Zuordnung von Teams zu Projekten
- Frontend-Team → UI/UX Projekte
- Backend-Team → Server-seitige Projekte
- DevOps-Team → Infrastruktur-Projekte
- QA-Team → Testing für alle Projekte
- Design-Team → UX/UI für Frontend-Projekte

## Vorteile der erweiterten Dummy-Daten

### 🧪 Bessere Tests
- **Verschiedene Auslastungsgrade** - Von 0% bis 100% Auslastung
- **Realistische Szenarien** - Überlappende Projekte und Abwesenheiten
- **Historische Daten** - 30 Tage Zeiteinträge für Reporting-Tests
- **Edge Cases** - Inaktive Mitarbeiter, abgeschlossene Projekte

### 📊 Anschauliche Dashboards
- **Kapazitätsplanung** - Verschiedene Auslastungsgrade sichtbar
- **Projektfortschritt** - Projekte in verschiedenen Phasen
- **Team-Übersicht** - Logische Team-Zuordnungen
- **Abwesenheitsplanung** - Vergangene, aktuelle und zukünftige Abwesenheiten

### 🔍 Funktions-Tests
- **Filterung** - Nach Abteilungen, Teams, Projektstatus
- **Suche** - Viele verschiedene Namen und Projekte
- **Sortierung** - Nach verschiedenen Kriterien
- **Export** - Große Datenmengen für Performance-Tests

## Verwendung

Um die erweiterten Dummy-Daten zu laden, führen Sie folgenden Befehl aus:

```bash
php artisan db:seed
```

Oder für eine frische Installation:

```bash
php artisan migrate:fresh --seed
```

## Anpassungen

Die Seeder können individuell angepasst werden:
- `EmployeeSeeder.php` - Weitere Mitarbeiter hinzufügen
- `ProjectSeeder.php` - Weitere Projekte erstellen
- `AssignmentSeeder.php` - Zuweisungen anpassen
- `AbsenceSeeder.php` - Abwesenheiten modifizieren
- `TimeEntrySeeder.php` - Zeiteinträge erweitern
- `TeamAssignmentSeeder.php` - Team-Zuweisungen ändern









