# 📋 Day2Day-Manager - Systemdokumentation

**Version:** 1.0 | **Stand:** Nov 2025 | **Technologie:** Laravel 12 + MySQL + MOCO API

---

## 🎯 Gesamtbeschreibung

**Day2Day-Manager** ist ein Web-basiertes Team-Planungstool für Agenturen und Software-Häuser mit MOCO-Zeiterfassung. Es visualisiert Projekt-Zuweisungen in Gantt-Charts und synchronisiert automatisch Zeiterfassungen, Abwesenheiten und Mitarbeiterdaten aus MOCO.

**Kernidee:** MOCO bleibt Master für Zeiterfassung, Day2Day-Manager ergänzt granulare Team-Planung mit visueller Timeline.

---

## ⭐ Wesentliche Funktionen (Überblick)

1. **Projekt-Verwaltung** - Projekte erstellen, bearbeiten, Status verfolgen
2. **Team-Zuweisungen** - Mitarbeiter zu Projekten zuweisen (Stunden, Zeitraum, Aufgaben)
3. **Gantt-Timeline** - Visuelle Projekt-Übersicht mit Mitarbeiter-Balken
4. **MOCO-Synchronisation** - Automatischer Import von Zeiterfassungen, Abwesenheiten, Mitarbeitern
5. **Kapazitäts-Übersicht** - Dashboard mit Auslastung und freien Kapazitäten
6. **Abwesenheits-Management** - Urlaub, Krankheit, Feiertage aus MOCO
7. **Benutzer-Verwaltung** - Login, Rollen, Passwort-Reset

---

## 📊 Bereichs-Details

### 1. Dashboard (Hauptseite)

**Hauptbereich:**
- **Projekt-Kacheln:** Aktive Projekte mit Status, Zeitraum, zugewiesenen Mitarbeitern
- **MOCO-Sync-Status:** Letzte Synchronisation, Warnungen bei veralteten Daten (>24h)
- **Quick-Actions:** Buttons für Projekt erstellen, MOCO-Sync starten
- **Statistiken:** Anzahl aktive Projekte, zugewiesene Mitarbeiter, offene Assignments

**Funktionen:**
- Projekt anklicken → Detailansicht öffnen
- MOCO-Sync-Buttons (AJAX, kein Page-Reload):
  - Zeiterfassungen synchronisieren (letzte 7 Tage)
  - Abwesenheiten synchronisieren (aktuelles Jahr)
  - Verträge/Zuweisungen synchronisieren
- Filter: Aktive/Abgeschlossene/Alle Projekte
- Sortierung: Nach Name, Status, Startdatum

---

### 2. Projekte

#### Hauptbereich (Projektliste):
- **Tabelle:** Name, Kunde, Status, Zeitraum, Budget, Team-Größe
- **Status-Badges:** Farbcodiert (Aktiv=Grün, Abgeschlossen=Grau, Geplant=Blau)
- **Team-Avatars:** Miniatur-Profilbilder der zugewiesenen Mitarbeiter
- **Aktionen:** Bearbeiten, Löschen (mit Bestätigung), Assignments verwalten

**Funktionen:**
- Projekt erstellen (Modal/separate Seite):
  - Name, Beschreibung, Kunde
  - Start-/Enddatum (Datepicker)
  - Budget (optional)
  - Verantwortlicher (Dropdown: Mitarbeiter)
- Projekt bearbeiten (gleiche Felder)
- Projekt löschen (Soft-Delete, Warnung bei aktiven Assignments)
- Suche: Nach Name, Kunde, Verantwortlichem
- Filter: Status, Zeitraum, Team

#### Detailansicht (einzelnes Projekt):
- **Header:** Projektname, Status-Badge, Zeitraum, Budget
- **Info-Sektion:** 
  - Beschreibung (Rich-Text)
  - Kunde (Link zu Kunden-Details falls verfügbar)
  - Verantwortlicher (mit Profilbild)
  - Erstellt/Aktualisiert (Timestamps)
- **Assignments-Tabelle:**
  - Mitarbeiter (Name + Profilbild)
  - Wochenstunden (editierbar inline)
  - Zeitraum (Start-/Enddatum)
  - Aufgabe/Rolle (z.B. "Frontend-Entwicklung")
  - Aktionen: Bearbeiten, Löschen
- **MOCO-Sync-Status:**
  - Letzte Synchronisation
  - Anzahl Zeiterfassungen aus MOCO
  - Link zu MOCO-Projekt (externe Seite)
- **Gantt-Preview:** Mini-Gantt nur für dieses Projekt

**Funktionen:**
- Assignment hinzufügen:
  - Mitarbeiter auswählen (Multi-Select Dropdown)
  - Wochenstunden (Input: 0-40, 2 Dezimalstellen)
  - Zeitraum (Datepicker mit Validierung: Start < Ende)
  - Aufgabenbeschreibung (Text)
  - **Validierung:** Keine Überbuchung (>40h/Woche), Zeitraum innerhalb Projektlaufzeit
- Assignment bearbeiten (inline oder Modal)
- Assignment löschen (mit Bestätigung)
- Projekt-Timeline anzeigen (Gantt-Chart springen)

---

### 3. Gantt-Chart

**Hauptbereich:**
- **Timeline-Achse:** Wochenweise/Monatlich (umschaltbar), scrollbar horizontal
- **Projekt-Blöcke:** Vertikale Sections pro Projekt
  - Projektname (links, fixiert)
  - Mitarbeiter-Zeilen (farbcodiert pro Person)
  - Balken: Start bis Ende, Breite = Zeitraum
- **Hover-Tooltips:** Name, Wochenstunden, Aufgabe, Zeitraum
- **Farblegende:** Mitarbeiter-Farben (konsistent über alle Projekte)

**Funktionen:**
- Zoom: Woche/Monat/Quartal (Buttons oben)
- Filter:
  - Nach Projekt (Multi-Select)
  - Nach Mitarbeiter (Multi-Select)
  - Nach Zeitraum (Date-Range-Picker)
- Sortierung: Projekte nach Start, Name, Team-Größe
- Scroll-Sync: Horizontales Scrollen synchronisiert alle Projekt-Zeilen
- Export (geplant): PNG/PDF-Download der sichtbaren Timeline
- Konflikt-Markierung: Überlappende Assignments rot hervorheben (>40h/Woche)

**Interaktionen:**
- Klick auf Balken → Assignment-Details-Modal
- Drag & Drop (geplant): Assignment verschieben
- Resize (geplant): Zeitraum anpassen durch Ziehen an Enden

---

### 4. Mitarbeiter

#### Hauptbereich (Mitarbeiterliste):
- **Tabelle:** Name, Position, Skills, Wochenstunden, Status (Aktiv/Inaktiv)
- **Profilbilder:** Initialen-Avatar oder Foto (falls vorhanden)
- **Status-Badge:** Aktiv (Grün), Inaktiv (Grau)
- **Quick-Info:** Anzahl aktuelle Projekte, nächste Abwesenheit

**Funktionen:**
- Mitarbeiter erstellen:
  - Vorname, Nachname (Pflicht)
  - E-Mail (unique Validierung)
  - Position/Rolle (z.B. "Senior Developer")
  - Wochenstunden (Standard: 40)
  - Skills (Tags/Multi-Select: PHP, Laravel, React, etc.)
  - Status (Aktiv/Inaktiv Toggle)
- Mitarbeiter bearbeiten (gleiche Felder)
- Mitarbeiter deaktivieren (Soft-Delete, bleibt in Historie)
- Suche: Nach Name, E-Mail, Position, Skills
- Filter: Status, Skill-Tags, Verfügbarkeit

#### Detailansicht (einzelner Mitarbeiter):
- **Header:** Name, Position, Profilbild, Status
- **Kontakt:** E-Mail, Telefon (optional)
- **Skills:** Tag-Cloud (klickbar für Suche)
- **Kapazität:**
  - Wochenstunden (Vertraglich)
  - Aktuell zugewiesen (Summe aller Assignments)
  - Frei verfügbar (Differenz, farbcodiert: >10h=Grün, <5h=Rot)
- **Projekt-Historie:**
  - Tabelle: Projekt, Zeitraum, Rolle, Stunden
  - Filter: Aktive/Vergangene/Alle
- **Abwesenheiten:**
  - Anstehende Urlaube/Krankheiten
  - Kalender-Ansicht (Mini-Kalender)
- **MOCO-Sync:**
  - MOCO-ID (falls synchronisiert)
  - Letzte Sync-Zeit
  - Link zu MOCO-Profil

**Funktionen:**
- Zu Projekt zuweisen (Quick-Action Button)
- Abwesenheit eintragen (Redirect zu Abwesenheits-Modul)
- Zeiterfassungen anzeigen (aus MOCO, Read-Only)
- Kapazitäts-Report exportieren (CSV/Excel, geplant)

---

### 5. Abwesenheiten

**Hauptbereich:**
- **Kalender-Ansicht:** Monatlicher Kalender mit farbcodierten Abwesenheiten
  - Urlaub: Blau
  - Krankheit: Rot
  - Elternzeit: Lila
  - Sonderurlaub: Orange
- **Listen-Ansicht (umschaltbar):** Tabelle mit Mitarbeiter, Typ, Zeitraum, Status
- **Team-Filter:** Dropdown zur Auswahl Team/Abteilung

**Funktionen:**
- Abwesenheit eintragen (manuell):
  - Mitarbeiter auswählen
  - Typ (Dropdown: Urlaub, Krankheit, etc.)
  - Zeitraum (Start-/Enddatum)
  - Ganztags/Halbtags (Checkbox)
  - Notiz (optional, z.B. "Arzttermin")
- Abwesenheit bearbeiten (nur eigene oder Admin)
- Abwesenheit löschen (mit Bestätigung)
- MOCO-Sync: Automatischer Import aus MOCO (überschreibt lokale Daten)
- Filter:
  - Nach Typ
  - Nach Mitarbeiter
  - Nach Zeitraum (Date-Range)
- Export: iCal-Download (Kalender-Import), CSV

**Detailansicht:**
- Mitarbeiter-Name + Profilbild
- Abwesenheitstyp (Icon + Text)
- Zeitraum (formatiert: "01. - 05. Jan 2026")
- Ganztags/Halbtags
- Notiz (falls vorhanden)
- Erstellt von (User + Timestamp)
- MOCO-Sync-Status (falls aus MOCO importiert)

---

### 6. MOCO-Integration

**Hauptbereich (Dashboard):**
- **Verbindungsstatus:**
  - ✅ Verbunden (mit MOCO-API-URL)
  - ❌ Nicht verbunden (Fehlermeldung + Anleitung)
  - Health-Check Button (testet API-Erreichbarkeit)
- **Sync-Sections:**
  1. **Zeiterfassungen:**
     - Button "Synchronisieren" (AJAX)
     - Einstellungen: Zeitraum (Standard: 7 Tage)
     - Status: Letzte Sync-Zeit, Anzahl neue/aktualisierte Einträge
     - Progress-Bar während Sync
  2. **Abwesenheiten:**
     - Button "Synchronisieren"
     - Einstellungen: Zeitraum (Standard: aktuelles Jahr)
     - Status: Letzte Sync-Zeit, Anzahl Abwesenheiten
  3. **Verträge/Zuweisungen:**
     - Button "Synchronisieren"
     - Info: "Ergänzt lokale Assignments, überschreibt nicht"
     - Status: Anzahl importierte Assignments aus MOCO
- **Sync-Empfehlungen:**
  - Warnungen bei veralteten Daten (>24h)
  - "Empfohlen: Täglich synchronisieren"
- **Logs:**
  - Liste der letzten Sync-Vorgänge (Timestamp, Typ, Status, Errors)
  - Filter: Erfolg/Fehler, Zeitraum

**Funktionen:**
- MOCO-Verbindung testen (Health-Check Command)
- Sync manuell starten (per Button, AJAX)
- Dry-Run aktivieren (Checkbox "Nur Vorschau")
- Automatischen Sync konfigurieren (Scheduler-Einstellungen, falls implementiert)
- Konflikt-Management:
  - Bei Daten-Konflikten: Modal mit Optionen (MOCO übernehmen / Lokal behalten / Ignorieren)
- Cache leeren (Button "MOCO-Cache zurücksetzen")

**Detailansicht (Sync-Logs):**
- Timestamp (Wann wurde synchronisiert?)
- Typ (Zeiterfassungen/Abwesenheiten/Verträge)
- Status (Erfolg/Fehler/Teilweise)
- Details:
  - Anzahl erstellt/aktualisiert/gelöscht
  - Fehlermeldungen (bei Errors)
  - API-Response-Zeit
  - User (wer hat Sync gestartet)
- Aktionen: Sync wiederholen, Details exportieren (JSON)

---

### 7. Benutzer-Verwaltung

**Hauptbereich (nur für Admins):**
- **Tabelle:** Name, E-Mail, Rolle, Letzter Login, Status
- **Rollen-Badges:** Admin (Rot), Projektleiter (Blau), Mitarbeiter (Grau)
- **Status:** Aktiv/Gesperrt

**Funktionen:**
- Benutzer erstellen:
  - Name, E-Mail (unique)
  - Passwort (min. 8 Zeichen, Validierung)
  - Rolle zuweisen (Admin/Projektleiter/Mitarbeiter)
  - Status (Aktiv/Gesperrt Toggle)
- Benutzer bearbeiten (gleiche Felder, Passwort optional)
- Benutzer sperren (Soft-Lock, kann reaktiviert werden)
- Benutzer löschen (Hard-Delete, nur bei 0 Aktivitäten)
- Passwort zurücksetzen:
  - Admin kann neues Passwort setzen
  - Oder: Reset-Link per E-Mail senden (geplant)
- Suche: Nach Name, E-Mail
- Filter: Rolle, Status, Letzter Login

**Detailansicht:**
- Profil-Header: Name, E-Mail, Rolle
- Aktivitäts-Log:
  - Letzte Logins (Timestamp, IP, Browser)
  - Letzte Aktionen (Projekt erstellt, Assignment geändert)
- Berechtigungen:
  - Liste der Permissions (falls RBAC implementiert)
  - Zugewiesene Projekte (bei Projektleiter-Rolle)
- Sessions: Aktive Sessions anzeigen + beenden (Force-Logout)

---

### 8. Einstellungen (geplant)

**Bereiche:**
- **Allgemein:** Firmenname, Logo, Zeitzone
- **MOCO-Integration:** API-Key, Base-URL, Sync-Intervalle
- **Benachrichtigungen:** E-Mail bei Konflikten, Warnungen
- **Backup:** Automatische Backups aktivieren, Intervall, Speicherort

---

## 🔄 Datenfluss (Hybrid-Modell)

```
MOCO (Master)                Day2Day-Manager (Master)
─────────────                ────────────────────────
Projekte      →  Sync  →     Projekte (Read-Only)
Mitarbeiter   →  Sync  →     Mitarbeiter (Read-Only)
Zeiterfassung →  Sync  →     TimeEntries (Read-Only)
Abwesenheiten →  Sync  →     Absences (Read-Only)
Verträge      →  Supplement → Assignments (Ergänzung)
                              
                              Assignments (UI) → Master
                              Workflows (UI) → Master
                              Status (UI) → Master
```

**Regel:** MOCO-Sync überschreibt NIEMALS manuelle UI-Assignments.

---

## 🎯 Alleinstellungsmerkmale

| Problem | Lösung Day2Day |
|---------|----------------|
| **MOCO fehlt Team-Planung** | Granulare Zuweisungen + Timeline |
| **Excel-Chaos** | Zentrale Datenbank, keine Duplikate |
| **Manuelle Sync** | Automatisch aus MOCO (1 Klick) |
| **Keine Übersicht** | Gantt-Chart zeigt ALLE Projekte |
| **Vendor-Lock-in** | Hybrid: MOCO + eigene Daten |

---

## 💡 Use Cases

### Typischer Workflow:
```
1. Neues Projekt in MOCO anlegen
2. MOCO-Sync → Projekt erscheint in Day2Day
3. Team zuweisen (+ Stunden, Aufgaben)
4. Gantt prüfen → Konflikte erkennen
5. Wöchentlicher Auto-Sync für Ist-Stunden
```

### Szenarien:
- ✅ **Projektleiter:** "Wer kann noch 10h/Woche?"
- ✅ **Geschäftsführung:** "Welche Projekte laufen parallel?"
- ✅ **HR:** "Wer ist nächste Woche im Urlaub?"
- ✅ **Controlling:** "Soll/Ist-Stunden-Vergleich"

---

## 📊 Technische Details

### **Technologie-Stack:**
- **Framework:** Laravel 12 (PHP 8.2)
- **Datenbank:** MySQL 8.0 (InnoDB, Foreign Keys)
- **Frontend:** Blade Templates + TailwindCSS + Alpine.js
- **API:** Guzzle HTTP Client für MOCO
- **Authentication:** Laravel Breeze
- **Charts:** Chart.js (Gantt-Visualisierung)
- **Deployment:** XAMPP (lokal), Apache 2.4

### **Performance:**
- ✅ Eager Loading (keine N+1 Queries)
- ✅ Caching (MOCO-Daten: 1h TTL)
- ✅ Pagination (>50 Einträge)
- ✅ Database Indexes (Foreign Keys, Search-Felder)

### **Sicherheit:**
- ✅ CSRF-Protection (alle Forms)
- ✅ SQL-Injection-Schutz (Prepared Statements)
- ✅ XSS-Prevention (Blade Escaping)
- ✅ Bcrypt-Passwort-Hashing
- ✅ DSGVO-konform (Soft-Deletes, Backups)

---

## 🎓 Zielgruppe

**Perfekt für:**
- Agenturen (5-50 Mitarbeiter)
- Software-Häuser mit Projektgeschäft
- Beratungen mit MOCO-Zeiterfassung
- Teams mit parallelen Projekten

**Nicht geeignet für:**
- Einzelpersonen (Overkill)
- Unternehmen ohne MOCO (möglich, aber weniger Features)

---

## 📈 ROI (Return on Investment)

**Vorher (Excel + MOCO):**
- 2-3h/Woche manuelle Planung
- Fehlerquote: ~15% (Überbuchungen)
- Keine Team-Übersicht

**Nachher (Day2Day-Manager):**
- **30 Min/Woche** für Planung
- **Fehlerquote: <2%** (automatische Warnungen)
- **Live-Übersicht** für alle

**Ersparnis:** ~10h/Monat = ~120h/Jahr

---

## 🎯 Marketing-Pitch

### 1-Liner:
> "Gantt-Charts für MOCO – Visualisiere dein Team, nicht nur Stunden."

### Elevator Pitch (30 Sekunden):
> Day2Day-Manager verbindet MOCO-Zeiterfassung mit intelligenter Team-Planung. Sieh auf einen Blick, wer wann an welchem Projekt arbeitet. Automatische Synchronisation, visuelle Gantt-Charts, Überbuchungs-Warnungen. Keine Excel-Tabellen mehr – nur noch 1 System.

### Key Benefits:
- ⚡ **3x schnellere** Projektplanung
- 👁️ **Komplette Transparenz** über Team-Auslastung
- 🔄 **MOCO bleibt Master** für Zeiterfassung (keine Dopplungen)
- 📊 **Gantt-Charts** die MOCO nicht hat

---

## 📱 Browser-Support

| Browser | Version | Status |
|---------|---------|--------|
| Chrome | 90+ | ✅ Voll unterstützt |
| Firefox | 88+ | ✅ Voll unterstützt |
| Edge | 90+ | ✅ Voll unterstützt |
| Safari | 14+ | ✅ Voll unterstützt |
| Mobile | iOS 14+, Android 10+ | 🔄 In Arbeit |

---

## 📞 Support & Wartung

**Entwickler:** Jörg Michno, Felix  
**Firma:** Enodia Software  
**Dokumentation:** 
- `PROJECT_ROADMAP.md` - Entwicklungs-Historie
- `MYSQL_MIGRATION_GUIDE.md` - Migration SQLite → MySQL
- `.github/copilot-instructions.md` - Entwicklungs-Regeln
- `FEATURE_DOCUMENTATION.md` - Diese Datei

**Issue-Tracking:** GitHub Issues (geplant)

---

**Erstellt:** 03.11.2025  
**Version:** 1.0-MySQL  
**Letzte Aktualisierung:** 03.11.2025  
**Nächstes Update:** Nach Phase 3-Abschluss
