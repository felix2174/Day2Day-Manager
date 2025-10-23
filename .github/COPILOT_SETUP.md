# GitHub Copilot Configuration Overview

Dieses Repository enthält eine umfassende GitHub Copilot-Konfiguration für Day2Day-Manager. Diese Dokumentation gibt dir einen Überblick über alle verfügbaren Ressourcen und wie du sie nutzen kannst.

## 📁 Dateistruktur

```
.github/
├── copilot-instructions.md      # Basis-Konfiguration (wird automatisch geladen)
├── AGENTS.md                     # 6 spezialisierte AI-Agenten
├── MCP_ROADMAP.md               # MCP-Integration Roadmap
├── chatmodes/                    # 4 spezialisierte Chatmodes
│   ├── DatabaseSafety.chatmode.md
│   ├── LaravelEnodia.chatmode.md
│   ├── Learning.chatmode.md
│   └── Debugging.chatmode.md
└── mcp/                          # MCP-Dokumentation (zukünftig)
    ├── README.md
    └── examples/
        └── day2day-mcp-server.json

specs/                            # Spec-Driven Development
├── README.md                     # Einführung in Specs
├── templates/                    # Spec-Templates
│   ├── feature-spec.md
│   └── bugfix-spec.md
└── examples/                     # Vollständige Beispiel-Specs
    ├── assignment-management.md
    └── time-entry-sync.md
```

## 🎯 Schnellstart

### 1. Basis-Konfiguration
Die Datei `.github/copilot-instructions.md` wird automatisch von GitHub Copilot geladen. Sie enthält:
- Projekt-Kontext (Day2Day-Manager ist Ressourcenplanungstool)
- MOCO-Integration (READ-ONLY!)
- Code-Richtlinien (deutsche Kommentare für Business-Logik)

**Keine Aktion nötig** - funktioniert automatisch!

### 2. Chatmodes nutzen

Chatmodes sind spezialisierte Kontexte für bestimmte Aufgaben:

| Chatmode | Wann nutzen? | Beispiel |
|----------|--------------|----------|
| **DatabaseSafety** | Datenbank-Operationen, MOCO-Schutz | "Wie importiere ich Daten sicher?" |
| **LaravelEnodia** | Laravel Best Practices, Business-Logik | "Wie berechne ich Kapazität?" |
| **Learning** | Azubi/Junior-freundlich, Erklärungen | "Was ist Eloquent?" |
| **Debugging** | Fehleranalyse, Stack Traces | "Warum bekomme ich 404?" |

**Aktivierung:**
- In VS Code: GitHub Copilot Chat → Chatmode auswählen
- Oder manuell: Kopiere relevante Abschnitte in deine Frage

### 3. Agenten verwenden

Die Datei `AGENTS.md` definiert 6 spezialisierte Agenten:

| Agent | Zweck | Aktivierung |
|-------|-------|-------------|
| **capacity-planner** | Kapazitätsplanung, Überbuchungen | `@capacity-planner` |
| **kpi-analyzer** | KPI-Dashboards, Ist-Soll | `@kpi-analyzer` |
| **gantt-optimizer** | Projektplanung, Zeitlinien | `@gantt-optimizer` |
| **moco-import-helper** | MOCO-Integration (READ-ONLY!) | `@moco-import-helper` |
| **business-logic-helper** | Komplexe Business-Logik | `@business-logic-helper` |
| **time-tracking-analyzer** | Zeitanalyse, Überstunden | `@time-tracking-analyzer` |

**Beispiel:**
```
@capacity-planner Wie viel Kapazität hat das Backend-Team im Q4?
```

### 4. Specs schreiben

Specs helfen dir, Features zu planen bevor du Code schreibst.

**Workflow:**
1. Kopiere Template: `specs/templates/feature-spec.md` oder `bugfix-spec.md`
2. Fülle die Spec aus (30-60 Minuten)
3. Optional: Review von Kollegen
4. Implementiere basierend auf Spec

**Beispiele ansehen:**
- `specs/examples/assignment-management.md` - Komplexe Business-Logik
- `specs/examples/time-entry-sync.md` - MOCO-Integration

**Warum Specs?**
- ✅ Zeitersparnis: 50-70% bei komplexen Features
- ✅ Weniger Rückfragen
- ✅ Alle Edge Cases durchdacht
- ✅ Automatisch testbar

## 📚 Detaillierte Guides

### Für neue Entwickler

1. **Start hier:** `specs/README.md` - Verstehe Spec-Driven Development
2. **Dann:** `.github/chatmodes/Learning.chatmode.md` - Laravel-Basics
3. **Übe:** Erstelle deine erste Spec mit Template

### Für erfahrene Entwickler

1. **Best Practices:** `.github/chatmodes/LaravelEnodia.chatmode.md`
2. **Agenten nutzen:** `.github/AGENTS.md`
3. **Specs für komplexe Features:** `specs/templates/feature-spec.md`

### Für Projektmanager

1. **Kapazitätsplanung:** Nutze `@capacity-planner` Agent
2. **KPI-Dashboards:** Nutze `@kpi-analyzer` Agent
3. **Specs verstehen:** `specs/README.md` - Was sind Specs?

## 🚨 WICHTIGE REGELN

### ✅ ERLAUBT:
- Daten von MOCO **lesen** (GET)
- Daten in Day2Day-Manager **bearbeiten**
- Seeders in dev/testing

### 🚫 VERBOTEN:
- Zu MOCO **schreiben** (POST/PATCH/DELETE) - **NIEMALS!**
- `migrate:fresh` in Produktion
- Seeders in Produktion

**Warum?** MOCO ist Firmeneigentum und wird von anderen Systemen genutzt!

Siehe: `.github/chatmodes/DatabaseSafety.chatmode.md` für Details.

## 🔮 MCP (Model Context Protocol) - Zukunft

MCP ermöglicht es Copilot, direkt auf deine Day2Day-Manager-Daten zuzugreifen.

**Status:** Noch nicht implementiert (nur Planung)

**Was wäre möglich?**
```
Du: "@capacity Wer hat diese Woche 10+ Stunden frei?"
Copilot: *liest aus deiner DB*
         "Max Mustermann: 15h frei, Anna Schmidt: 12h frei"
```

**Mehr erfahren:**
- `.github/MCP_ROADMAP.md` - Detaillierte Roadmap, ROI-Kalkulation
- `.github/mcp/README.md` - Was ist MCP?
- `.github/mcp/examples/day2day-mcp-server.json` - Beispiel-Konfiguration

**Entscheidung:** Noch offen. Evaluiere Aufwand vs. Nutzen!

## 💡 Tipps & Tricks

### Tipp 1: Kombiniere Agenten
```
@kpi-analyzer @capacity-planner 
Zeige KPIs für Projekt X und prüfe ob wir genug Kapazität haben
```

### Tipp 2: Nutze Chatmodes für Kontext
Wechsle den Chatmode je nach Aufgabe:
- Bug fixen? → **Debugging**
- Feature planen? → **LaravelEnodia**
- Lernen? → **Learning**

### Tipp 3: Specs als Dokumentation
Specs sind nicht nur für neue Features - sie dokumentieren auch bestehenden Code!
Beispiel: `specs/examples/assignment-management.md`

### Tipp 4: MOCO-Schutz ist überall
Alle Chatmodes und Agenten kennen die Regel: **MOCO ist READ-ONLY!**
Du wirst automatisch gewarnt, wenn du versuchst zu MOCO zu schreiben.

## 📊 Statistiken

**Gesamt:** 13 Dateien, ~5500 Zeilen Dokumentation

| Kategorie | Dateien | Zweck |
|-----------|---------|-------|
| Chatmodes | 4 | Spezialisierte Kontexte |
| Agenten | 1 (6 Agenten) | Task-spezifische Hilfe |
| MCP | 3 | Zukunfts-Planung |
| Specs | 5 | Feature-Dokumentation |

## 🆘 Support

### Fragen zu GitHub Copilot?
- Offizielle Docs: https://docs.github.com/copilot
- Copilot Chat: https://docs.github.com/copilot/github-copilot-chat

### Fragen zu Day2Day-Manager?
- Projekt-README: `mein-projekt/README.md`
- Issue erstellen auf GitHub

### Feedback zu dieser Konfiguration?
Erstelle ein Issue mit Label `copilot-config`:
- Was fehlt?
- Was ist unklar?
- Was könnte besser sein?

## 🎯 Nächste Schritte

**Als Entwickler:**
1. ✅ Lies `specs/README.md`
2. ✅ Schau dir Beispiel-Specs an
3. ✅ Probiere einen Chatmode aus
4. ✅ Nutze einen Agenten für deine nächste Aufgabe

**Als Team:**
1. ✅ Entscheide: Nutzen wir Specs? (Empfohlen!)
2. ✅ Evaluiere: Lohnt sich MCP? (Siehe `MCP_ROADMAP.md`)
3. ✅ Feedback sammeln: Was hilft? Was nicht?

## 📝 Changelog

| Datum | Änderung |
|-------|----------|
| 2025-10-23 | Initiale Copilot-Konfiguration erstellt |
| | - 4 Chatmodes |
| | - 6 Agenten |
| | - MCP-Roadmap |
| | - Spec-Templates |

---

**Version:** 1.0  
**Erstellt:** 2025-10-23  
**Maintainer:** Day2Day-Manager Team  
**Lizenz:** Projekt-intern (enodia GmbH)

---

## Quick Reference Card

**Häufigste Use Cases:**

| Was willst du tun? | Nutze dies |
|-------------------|------------|
| Feature planen | `specs/templates/feature-spec.md` |
| Bug dokumentieren | `specs/templates/bugfix-spec.md` |
| Kapazität prüfen | `@capacity-planner` Agent |
| MOCO importieren | `@moco-import-helper` + DatabaseSafety Mode |
| Laravel lernen | Learning Chatmode |
| Fehler debuggen | Debugging Chatmode |
| Business-Logik | LaravelEnodia Chatmode |
| KPIs analysieren | `@kpi-analyzer` Agent |

**MOCO-Checkliste:**
- [ ] Nutze nur GET-Requests
- [ ] Schreibe NIE zu MOCO
- [ ] Prüfe `.github/chatmodes/DatabaseSafety.chatmode.md`
- [ ] Siehe `specs/examples/time-entry-sync.md` für Best Practices
