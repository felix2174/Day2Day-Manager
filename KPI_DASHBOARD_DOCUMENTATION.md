# KPI Dashboard Dokumentation

## Übersicht

Das neue KPI Dashboard bietet eine umfassende Echtzeit-Übersicht über alle wichtigen Unternehmenskennzahlen Ihres Projektverwaltungssystems.

## Features

### 🎯 Executive View (Standard-Ansicht)

#### Top 4 KPI-Metriken
1. **Geschätzter Projektumsatz**
   - Gesamtumsatz aller aktiven Projekte
   - Realisierter Umsatz basierend auf Projektfortschritt
   - Berechnung: `SUM(estimated_hours × hourly_rate)` × Fortschritt

2. **Projekt Performance Score**
   - Prozentsatz der Projekte, die im Plan sind
   - Berechnung: `(On-Track-Projekte / Aktive Projekte) × 100`
   - Status: On Track, At Risk, Delayed

3. **Team Auslastung**
   - Durchschnittliche Auslastung aller aktiven Mitarbeiter
   - Status-Indikator:
     - 🟢 Optimal (70-85%)
     - 🟡 Hoch (85-100%)
     - 🔴 Niedrig (<70%)

4. **Budget Effizienz**
   - Verhältnis von geschätzten zu tatsächlichen Stunden
   - Berechnung: `(Geschätzte Stunden / Tatsächliche Stunden) × 100`
   - ✓ Im Budget: ≥95%
   - ⚠ Beobachten: <95%

#### Visualisierungen

**Umsatzentwicklung (Line Chart)**
- Monatlicher Umsatz der letzten 6 Monate
- Basiert auf gebuchten Stunden × durchschnittlichem Stundensatz
- Interaktive Hover-Effekte

**Projekt Status (Donut Chart)**
- Verteilung: On Track, At Risk, Delayed
- Projekt-Status-Berechnung:
  - `On Track`: Fortschritt ≥ erwarteter Fortschritt - 5%
  - `At Risk`: Fortschritt zwischen erwarteter Fortschritt - 15% und - 5%
  - `Delayed`: Fortschritt < erwarteter Fortschritt - 15%

**Ressourcen-Auslastung (Heatmap)**
- Top 10 Mitarbeiter nach Auslastung
- Farbcodierung:
  - 🔴 Rot: Überlastet (>100%)
  - 🟡 Orange: Hoch (85-100%)
  - 🟢 Grün: Optimal (70-85%)
  - ⚫ Grau: Niedrig (<70%)

**Alerts & Warnungen**
- Überlastete Mitarbeiter (>100% Auslastung)
- Verzögerte Projekte
- Unterausgelastete Mitarbeiter (<70%)

**Top Projekte Tabelle**
- Sortiert nach geschätztem Umsatz (höchste zuerst)
- Zeigt: Name, Fortschritt, Status, Umsatz, Deadline
- Direkte Links zur Projekt-Detailseite

### 📊 Project Manager View

#### Alle aktiven Projekte
- Vollständige Tabelle mit allen aktiven Projekten
- Spalten:
  - Projekt-Name
  - Aktueller Fortschritt
  - Erwarteter Fortschritt (zeitbasiert)
  - Status (On Track / At Risk / Delayed)
  - Geschätzter Umsatz
  - Deadline
  - Aktions-Link zu Projekt-Details

#### Detaillierte Ressourcen-Allocation
- Grid-Layout mit allen Mitarbeitern
- Für jeden Mitarbeiter:
  - Name
  - Auslastung in %
  - Zugewiesene Stunden
  - Wöchentliche Kapazität
  - Status-Badge
  - Visuelle Fortschrittsbalken

## Technische Details

### Backend (DashboardController)

**Neue KPI-Berechnungen:**
- Projekt-Statistiken (total, aktiv, abgeschlossen, in Planung)
- Umsatz-Metriken (geschätzt, realisiert)
- Budget-Effizienz (geschätzte vs. tatsächliche Stunden)
- Team-Auslastung mit Status-Klassifizierung
- Projekt-Health-Status mit Zeitplanung
- Zeit-Tracking-Insights
- Umsatz-Trend über 6 Monate
- Abwesenheiten & Verfügbarkeit
- Alert-System

**Daten-Priorisierung:**
- Alle Berechnungen basieren auf Datenbank-Daten
- Vorbereitet für MOCO-Integration (MOCO-Daten haben Vorrang)

### Frontend (dashboard.blade.php)

**Design-System:**
- Nur Inline-Styles (wie in den Projekt-Regeln definiert)
- Konsistente Farbpalette:
  - `#111827` - Primärtext
  - `#6b7280` - Sekundärtext
  - `#e5e7eb` - Borders
  - Gradient-Buttons: `linear-gradient(135deg, #3b82f6, #8b5cf6)`
- Border-Radius: 8px, 12px
- Abstände: 8px, 12px, 16px, 20px, 24px

**Tab-System:**
- JavaScript-basiertes Switching zwischen Views
- Keine Seiten-Neuladen
- Aktiver Tab mit Gradient-Hintergrund

**Charts:**
- Chart.js für interaktive Visualisierungen
- Responsive und animiert
- Custom Styling passend zum Design-System

## Nutzung

1. **Dashboard aufrufen**: Navigieren Sie zu `/dashboard` oder klicken Sie im Hauptmenü auf "Dashboard"

2. **View wechseln**: 
   - Klicken Sie auf "Executive View" für die Übersichts-Ansicht
   - Klicken Sie auf "Project Manager View" für detaillierte Projekt-Informationen

3. **Projekt-Details**: Klicken Sie in den Tabellen auf einen Projektnamen oder "Details", um zur Projekt-Detailseite zu gelangen

## Datenquellen

Alle KPIs werden in Echtzeit aus folgenden Datenbank-Tabellen berechnet:
- `projects` - Projekt-Informationen
- `employees` - Mitarbeiter-Daten
- `assignments` - Projekt-Zuweisungen
- `time_entries` - Zeitbuchungen
- `absences` - Abwesenheiten

## Zukünftige Erweiterungen

### Geplante Features:
- [ ] MOCO-Integration für Live-Daten
- [ ] Export-Funktion (PDF/Excel)
- [ ] Anpassbare Zeiträume (Woche, Monat, Quartal, Jahr)
- [ ] Favoriten-Projekte markieren
- [ ] Dashboard-Widgets per Drag & Drop verschieben
- [ ] E-Mail-Benachrichtigungen bei kritischen Alerts
- [ ] Team-spezifische Dashboards
- [ ] Vergleich mit Vorperioden
- [ ] Forecast & Predictive Analytics

## Performance

Das Dashboard ist optimiert für schnelle Ladezeiten:
- Effiziente Datenbankabfragen
- Minimale externe Abhängigkeiten (nur Chart.js)
- Keine unnötigen API-Calls
- Inline-Styles für schnelles Rendering

## Support

Bei Fragen oder Problemen:
1. Überprüfen Sie die Browser-Konsole auf JavaScript-Fehler
2. Stellen Sie sicher, dass alle Datenbank-Tabellen korrekt migriert sind
3. Prüfen Sie, ob ausreichend Testdaten vorhanden sind

---

**Version:** 1.0  
**Stand:** Oktober 2025  
**Erstellt von:** Cursor AI Assistant





















