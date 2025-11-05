# 🎯 Day2Day-Manager: MOCO-Integration Roadmap

**Projekt:** MOCO-Gantt-Optimierung  
**Erstellt:** 28.10.2025  
**Letzte Aktualisierung:** 29.10.2025 - 12:30 Uhr  
**Status:** � Phase 2 ABGESCHLOSSEN - Automation läuft  
**Fortschritt:** ▓▓▓▓▓▓▓░░░ 70%

---

## 📊 Überblick

### Projektziel
Hybrides Projektmanagement-System mit MOCO-Integration:
- **UI-Verwaltung:** Projekte & Assignments über Day2Day-Manager ✅
- **MOCO-Sync:** Zeiterfassung & Abwesenheiten (Read-Only) ✅
- **Visualisierung:** Gantt-Diagramme mit vollständiger Mitarbeiter-Ansicht ✅

### Erfolgskriterien
- ✅ Alle Projekte zeigen zugewiesene Mitarbeiter im Gantt **→ 173 Assignments aktiv**
- ✅ Sync von MOCO-Zeiterfassung mit Caching (7 Tage partial)
- ✅ Sync von Abwesenheiten mit Future-Lookup (30 Tage + 6 Monate) **→ 165 Absences**
- ✅ Performance-optimiert: Caching (1h TTL), AJAX-Buttons
- ✅ UI ist führendes System für Assignments

---

## 🎉 ERFOLGE

### ✅ Phase 1: Gantt-Chart Fix (ABGESCHLOSSEN - 28.10.2025)

**Problem:** Gantt zeigte "Keine Mitarbeiter zugewiesen" für 64 von 64 Projekten  
**Ursache:** Leere `assignments`-Tabelle, MOCO lieferte keine Contract-Daten

**Lösung:** Hybrid-Strategie mit 3 Datenquellen implementiert

#### 🏆 Erreichte Ergebnisse:
```
✅ 173 Assignments aktiv (Breakdown):
   - 136 via MOCO Contracts Sync (sync:moco-contracts)
   - 36 via Responsible Fallback (sync:responsible-to-assignments)
   - 1 manuelle Zuweisung (UI)

✅ 37 Projekte zeigen jetzt mehrere Mitarbeiter:
   - FISAT: 10 Mitarbeiter
   - Internes: 9 Mitarbeiter
   - Day2Day-Flow: 8 Mitarbeiter
   
✅ Model-Logik mit 3-stufiger Fallback-Hierarchie:
   1. MOCO-Team-Daten (wenn verfügbar)
   2. Lokale Assignments (UI Master)
   3. Responsible als Fallback (Graceful Degradation)
```

**Technische Highlights:**
- Command: `php artisan sync:moco-contracts` (136 neue Assignments)
- Command: `php artisan sync:responsible-to-assignments` (36 Fallback-Zuweisungen)
- Migration: `assignments`-Tabelle erweitert um `source`, `role`, `is_active`
- Model: `Assignment::SOURCE_MOCO_SYNC` / `SOURCE_RESPONSIBLE_FALLBACK` Konstanten

---

### ✅ Phase 2: MOCO Sync Automation (ABGESCHLOSSEN - 29.10.2025)

**Ziel:** Automatisierte Synchronisation ohne manuellen CLI-Aufruf

#### 🏆 Erreichte Ergebnisse:

**1. Zeiterfassungs-Sync** ⏱️
- Command: `sync:moco-time-entries`
- Features: `--days=7` default, `--full` für kompletten Sync, `--no-cache` bypass
- Caching: 1h TTL verhindert redundante API-Calls
- Performance: Partial Sync (nur 7 Tage) statt voller History

**2. Abwesenheiten-Sync** 🏖️
- Command: `sync:moco-absences`
- **BREAKTHROUGH:** Korrekter MOCO-Endpunkt entdeckt: `/schedules` (nicht `/schedules/absences`)
- Filter: Client-seitig nach `assignment.type === "Absence"`
- **165 Absences synchronisiert** (31.07.2025 - 29.04.2026)
- ENUM-Mapping fix: `vacation → urlaub`, `sick → krankheit`, `training → fortbildung`
- Future-Lookup: 30 Tage Vergangenheit + 6 Monate voraus

**3. Contracts-Sync** 👥
- Command: `sync:moco-contracts`
- Nutzt `/projects/{id}` Endpoint mit `contracts` Array
- 174 Zuweisungen (107 bereits aktuell, 0 neu erstellt beim letzten Sync)

**4. UI-Integration** 🎨
- 3 Sync-Buttons im MOCO-Dashboard
- AJAX-basiert (keine Page-Reloads)
- Progress-Indicators mit Spinner-Animation
- Grüne Erfolgs-/Rote Fehlermeldungen
- Last-Sync Timestamps mit Cache

**5. Sync-Empfehlungen** 💡
- Blaue Info-Box (nicht Grün!) für bessere UX
- Warnung nur bei Syncs >24h alt
- Tracking via `MocoSyncLog`-Tabelle

#### 🐛 Gefixte Bugs:
1. **STDIN-Fehler:** Commands crashten im Web-Context → `app()->runningInConsole()` Check
2. **JavaScript undefined:** Funktionen nach Forms geladen → Verschoben an Anfang
3. **Status-Mismatch:** `status='success'` vs. `scopeSuccessful()` → Korrigiert zu `'completed'`
4. **Feldnamen:** `records_*` vs. `items_*` → Migration-konforme Namen

---

## 🔄 Aktueller Stand

### Datenbank-Status (29.10.2025 - 12:30 Uhr)
```sql
assignments:    173 Einträge (136 MOCO, 36 Fallback, 1 Manual)
absences:       165 Einträge (Urlaub, Krankheit, Fortbildung)
moco_sync_logs: 30+ Einträge (employees, projects, activities, absences, contracts)
```

### MOCO API Endpoints (Validiert)
✅ `/projects` - Projekt-Liste
✅ `/projects/{id}` - Projekt-Details mit Contracts
✅ `/users` - Mitarbeiter-Liste  
✅ `/schedules` - Alle Schedules (inkl. Absences mit Type-Filter)
✅ `/activities` - Zeiterfassungen
❌ `/schedules/absences` - Existiert NICHT (404)
❌ `/users/absences` - Existiert NICHT (404)

---

## 🚀 Nächste Schritte

### **Phase 3: Abwesenheiten-Verwaltung** ⏳ NÄCHSTE PRIO

**Ziel:** Dedizierter Bereich für Abwesenheiten mit Übersicht und Filter

**Status:** 🟡 In Planung  
**Start:** 29.10.2025 - 12:30 Uhr  
**Geschätzte Dauer:** ~1.5h

#### 3.1 Navigation anpassen
**Dateien:**
- `resources/views/layouts/app.blade.php` - Hauptnavigation erweitern
- `resources/views/employees/show.blade.php` - Abwesenheiten-Sidebar entfernen

**Änderungen:**
```blade
<!-- Neuer Hauptmenü-Eintrag -->
<a href="{{ route('absences.index') }}" class="nav-link">
    📅 Abwesenheiten
</a>
```

#### 3.2 Controller & Routes erstellen
**Dateien:**
- `app/Http/Controllers/AbsenceController.php` (neu)
- `routes/web.php`

**Features:**
- `index()` - Übersicht mit Pagination (50/Seite)
- Filter: Mitarbeiter, Typ (Urlaub/Krankheit/Fortbildung), Datums-Range
- Statistiken: Gesamt-Count, Breakdown nach Typ

**Route:**
```php
Route::resource('absences', AbsenceController::class)->only(['index', 'show']);
```

#### 3.3 Übersichtsseite erstellen
**Datei:** `resources/views/absences/index.blade.php` (neu)

**UI-Komponenten:**
1. **Filter-Sektion:**
   - Dropdown: Mitarbeiter-Auswahl
   - Dropdown: Typ-Filter (Alle, Urlaub, Krankheit, Fortbildung)
   - Date-Range-Picker (Von - Bis)
   - "Filter zurücksetzen" Button

2. **Statistik-Cards:**
   - Gesamt: 165 Abwesenheiten
   - 🏖️ Urlaub: XX Tage
   - 🤒 Krankheit: XX Tage
   - 📚 Fortbildung: XX Tage

3. **Tabelle:**
   | Mitarbeiter | Typ | Von | Bis | Tage | Grund |
   |-------------|-----|-----|-----|------|-------|
   | Jörg Michno | 🏖️ Urlaub | 01.11. | 15.11. | 15 | Jahresurlaub |
   
   - Sortierbar nach Datum
   - Farbcodierte Badges (Grün=Urlaub, Rot=Krankheit, Blau=Fortbildung)
   - Hover-Effekt
   - Pagination

4. **Leerzustand:**
   ```blade
   @empty
       <div class="text-center py-8 text-gray-500">
           📭 Keine Abwesenheiten gefunden
           <p class="text-sm mt-2">Ändere die Filter oder führe eine Synchronisation durch</p>
       </div>
   @endforelse
   ```

#### 3.4 Test-Kriterien
- ✅ Navigation: Abwesenheiten-Link funktioniert
- ✅ Übersicht: 165 Absences werden angezeigt
- ✅ Filter: Nach Mitarbeiter filtern funktioniert
- ✅ Filter: Nach Typ filtern funktioniert
- ✅ Statistiken: Counts sind korrekt
- ✅ Pagination: 50 Einträge pro Seite

---

### **Phase 4: Bottleneck-Visualisierung** 💤 OPTIONAL

**Start:** Nach Phase 2  
**Dauer:** ~1 Woche

#### 3.1 Kapazitäts-Berechnung

**Backend:**
- Service: `app/Services/CapacityService.php` (neu)
- Berechnet wöchentliche Auslastung pro Mitarbeiter
- Berücksichtigt Abwesenheiten
- Erkennt Überlastung

**UI-Indikatoren:**
- 🔴 Rot: >100% Auslastung (kritisch)
- 🟡 Gelb: 80-100% (Warnung)
- 🟢 Grün: <80% (normal)

**Test-Kriterium:**
✅ Überlastete Mitarbeiter farblich markiert  
✅ Hover zeigt Auslastungs-Details  
✅ Dashboard-Widget mit Überlastungs-Statistik

---

## 📊 Datenmodell & Sync-Strategie

### Sync-Strategie

| Entität           | Quelle      | Richtung    | Frequenz  | Verwaltung |
|-------------------|-------------|-------------|-----------|------------|
| **Projekte**      | MOCO        | → Lokal     | Täglich   | MOCO       |
| **Mitarbeiter**   | MOCO        | → Lokal     | Täglich   | MOCO       |
| **Assignments**   | **UI**      | **Lokal**   | **Manuell**| **UI**    |
| Zeiterfassung     | MOCO        | → Lokal     | Stündlich | MOCO       |
| Abwesenheiten     | MOCO        | → Lokal     | Täglich   | MOCO       |

### Datenfluss

```
┌─────────────────────────────────────────────────────────┐
│                    MOCO API (Quelle)                     │
│  - Projekte      - Zeiterfassung                        │
│  - Mitarbeiter   - Abwesenheiten                        │
└────────────┬────────────────────────────────────────────┘
             │ Sync (Read-Only)
             ▼
┌─────────────────────────────────────────────────────────┐
│              Day2Day-Manager (Lokal)                     │
│                                                          │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐ │
│  │  Projects    │  │  Employees   │  │  TimeEntries │ │
│  │  (Synced)    │  │  (Synced)    │  │  (Synced)    │ │
│  └──────┬───────┘  └──────┬───────┘  └──────────────┘ │
│         │                  │                            │
│         └─────────┬────────┘                            │
│                   ▼                                     │
│         ┌──────────────────┐                           │
│         │   Assignments    │ ◄─── UI-Verwaltung       │
│         │  (Lokal Master)  │      (Manuell)           │
│         └──────────────────┘                           │
│                   │                                     │
│                   ▼                                     │
│         ┌──────────────────┐                           │
│         │  Gantt-Diagramm  │                           │
│         │  + Bottlenecks   │                           │
│         └──────────────────┘                           │
└─────────────────────────────────────────────────────────┘
```

---

## 🎯 Meilensteine

### Milestone 1: UI-Zuweisung funktionsfähig ✅ 20%
**Zieldatum:** 30.10.2025  
**Status:** 🔄 In Arbeit

- [x] Problem analysiert
- [x] Lösungsansatz definiert
- [ ] UI-Modal implementiert
- [ ] Assignment-Route erstellt
- [ ] Funktionstest erfolgreich

### Milestone 2: MOCO-Abwesenheiten-Sync
**Zieldatum:** 01.11.2025  
**Status:** ⏳ Geplant

- [ ] `getAbsences()` Methode implementiert
- [ ] Sync-Command erweitert
- [ ] Scheduler konfiguriert
- [ ] Gantt berücksichtigt Abwesenheiten

### Milestone 3: Bottleneck-Visualisierung
**Zieldatum:** 08.11.2025  
**Status:** ⏳ Geplant

- [ ] CapacityService erstellt
- [ ] UI-Indikatoren implementiert
- [ ] Dashboard-Widget
- [ ] Performance-Optimierung

### Milestone 4: Produktiv-Rollout
**Zieldatum:** 15.11.2025  
**Status:** ⏳ Geplant

- [ ] Alle Tests bestanden
- [ ] User-Dokumentation
- [ ] Schulung durchgeführt
- [ ] Produktiv-Deployment

---

## 📝 Change Log

### 28.10.2025 - 23:45 Uhr
**Phase 1 gestartet - Roadmap erstellt**
- ✅ Analyse abgeschlossen
- ✅ Problem identifiziert (leere assignments-Tabelle)
- ✅ MOCO-API getestet (keine Contract-Daten verfügbar)
- ✅ Entscheidung: UI-First statt Command-basiert
- ✅ Bulk-Assignment Backend implementiert (Controller + Route)
- ✅ Modal-Code bereitgestellt (inaktiv - Drei-Punkte-Menü hat Priorität)
- 📋 PROJECT_ROADMAP.md erstellt

**Hinweis:** Bestehende UI-Funktionalität im Drei-Punkte-Menü wird bevorzugt genutzt.

### 29.10.2025 - 02:15 Uhr
**Phase 1 abgeschlossen - Dauerhafte Lösung implementiert** ✅

**Änderungen:**
- ✅ `.github/copilot-instructions.md` erstellt (Development Rules v2.0)
- ✅ `Project::getAssignedPersonsList()` erweitert mit Fallback auf `responsible_id`
- ✅ Bestehende UI-Zuweisung getestet und funktionsfähig

**Technische Details:**
- **Dateien geändert:** 
  - `app/Models/Project.php` (Zeile 61-101: Fallback-Logik)
  - `.github/copilot-instructions.md` (neu erstellt)
- **Breaking Changes:** Keine
- **Migration nötig:** Nein

**Lösung:**
Statt Datenbank mit Assignments zu füllen, nutzt das Model jetzt eine **intelligente Fallback-Kette**:
1. MOCO-Daten (falls übergeben)
2. Lokale Assignments (aus DB)
3. **NEU:** Verantwortlicher (`responsible_id`)
4. Leer (Graceful Degradation)

**Ergebnis:**
- ✅ Projekte ohne Assignments zeigen jetzt den Verantwortlichen
- ✅ Manuelle Zuweisungen über UI funktionieren
- ✅ Keine Daten-Migration nötig
- ✅ Dauerhafte, wartbare Lösung

**Nächster Schritt:** Gantt-Validierung durch User

### Nächster Update
**Geplant:** 29.10.2025 nach Gantt-Validierung → Phase 2 Start

---

## 🔗 Relevante Dateien

### Models
- `app/Models/Project.php` - Relationship `employees()` (Zeile 50-54)
- `app/Models/Assignment.php` - Pivot-Model
- `app/Models/Employee.php` - Mitarbeiter-Model
- `app/Models/Absence.php` - Abwesenheiten-Model

### Controllers
- `app/Http/Controllers/GanttController.php` - Gantt-Logik + neue Methode `bulkAssignEmployees()`
- `app/Http/Controllers/ProjectController.php` - Projekt-Verwaltung

### Services
- `app/Services/MocoService.php` - API-Integration
- `app/Services/CapacityService.php` - ⏳ Geplant (Bottleneck-Logik)

### Views
- `resources/views/gantt/partials/timeline-projects.blade.php` - Gantt-Template + Modal

### Commands
- `app/Console/Commands/SyncMoco.php` - Bestehender Sync (wird erweitert)

---

## 📞 Kontakt & Verantwortlichkeiten

**Entwicklung:** Jörg Michno, Felix  
**Produktverantwortung:** enodia IT-Systemhaus  
**GitHub Copilot:** Code-Unterstützung & Dokumentation

---

## 📚 Offene Fragen

- [ ] Sollen MOCO-Projekt-Updates auch lokal überschrieben werden?
- [ ] Standard-Stundenzahl bei Zuweisung: 20h oder 40h/Woche?
- [ ] Wie gehen wir mit gelöschten MOCO-Projekten um?
- [ ] Benachrichtigung bei Überlastung >100%?

---

**Nächster Schritt:** UI-Modal für Mitarbeiter-Zuweisung implementieren  
**Geschätzter Zeitaufwand bis Milestone 1:** ~2-3 Stunden  
**Test bereit:** Nach Controller + Blade-Änderungen
