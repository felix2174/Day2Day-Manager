# Copilot Instructions for Day2Day-Manager

## Project Context

Day2Day-Manager ist ein eigenständiges Ressourcenplanungstool für die enodia GmbH. Es wird MOCO nur für folgende Zwecke verwendet:

- **Initialer Import von Mitarbeitern/Projekten** (selten, manuell)
- **Tägliche Zeiteintrag-Synchronisierung** (automatisch)

### Hauptfunktionen von Day2Day-Manager:

- **MASTER für:** Projekte, Zuordnungen, Kapazitätsplanung, Abwesenheiten, KPIs
- **NEVER write back to MOCO** (lesen nur)
- Fokus auf **Business-Logik:** Kapazitätsplanung, Engpassanalyse, Nutzungstracking, Gantt-Diagramme

### Zeitdaten

Die Zeiteinträge von MOCO werden täglich synchronisiert für:
- Ist- vs. Plan-Vergleich
- Projektfortschritt
- Burndown-Diagramme
- Überstundenanalyse

### Code-Richtlinien

- **Code-Stil:** Deutsche Kommentare für Business-Logik, Laravel Best Practices
- Sicherheitsregeln für die Datenbank in der Produktion
- Vollständige Datenmodell-Erklärung
