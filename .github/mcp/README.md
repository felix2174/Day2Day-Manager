# MCP (Model Context Protocol) für Day2Day-Manager

## Was ist MCP?

**MCP (Model Context Protocol)** ist ein offenes Protokoll von Anthropic (die Firma hinter Claude), das es AI-Assistenten wie GitHub Copilot ermöglicht, direkt mit externen Datenquellen und Tools zu kommunizieren.

### Das Problem ohne MCP

```
Du: "Welche Mitarbeiter sind überbucht?"
Copilot: "Um das herauszufinden, kannst du in deiner Datenbank 
          nachschauen mit: SELECT employee_id, SUM(hours) FROM ..."
          
Du: "Nein, ich will die echten aktuellen Daten!"
Copilot: "Ich kann nicht auf deine Datenbank zugreifen..."
```

### Die Lösung mit MCP

```
Du: "@capacity Welche Mitarbeiter sind überbucht?"
Copilot: *ruft get_overbooked_employees() auf*
         
         "🔴 2 Mitarbeiter sind überbucht:
          - John Doe: 130% (52h bei 40h/Woche)
          - Lisa Müller: 110% (44h bei 40h/Woche)"
```

**Der Unterschied:** Copilot hat **Zugriff auf deine echten Daten** und kann konkrete Antworten geben!

## Warum ist das wichtig für Day2Day-Manager?

Day2Day-Manager ist ein **Ressourcenplanungs-Tool**. Die häufigsten Fragen sind:
- "Wer hat noch freie Kapazität?"
- "Ist Projekt X überbucht?"
- "Wie ist die Team-Auslastung?"

Ohne MCP musst du:
1. Dashboard öffnen
2. Filter setzen
3. Daten exportieren
4. Manuell analysieren

Mit MCP kannst du:
1. Copilot fragen
2. **Fertig!** ✅

## MCP-Architektur

```
┌──────────────────────────────────────────────────┐
│            Deine Entwicklungsumgebung            │
├──────────────────────────────────────────────────┤
│                                                  │
│  ┌─────────────┐         ┌──────────────────┐   │
│  │   VS Code   │         │  GitHub Copilot  │   │
│  │   + Copilot │◄────────┤   Chat/Agent     │   │
│  └─────────────┘         └────────┬─────────┘   │
│                                   │             │
│                         MCP Protocol            │
│                                   │             │
│                          ┌────────▼─────────┐   │
│                          │   MCP Server     │   │
│                          │  (TypeScript/    │   │
│                          │   Node.js)       │   │
│                          └────────┬─────────┘   │
│                                   │             │
│                              HTTP/REST          │
│                                   │             │
│                          ┌────────▼─────────┐   │
│                          │ Day2Day-Manager  │   │
│                          │   REST API       │   │
│                          └────────┬─────────┘   │
│                                   │             │
│                               Database          │
│                                   │             │
│                          ┌────────▼─────────┐   │
│                          │  MySQL/SQLite    │   │
│                          │  (Deine Daten)   │   │
│                          └──────────────────┘   │
│                                                  │
└──────────────────────────────────────────────────┘
       ALLES LÄUFT LOKAL - KEINE CLOUD!
```

### Komponenten

1. **GitHub Copilot** - Der AI-Assistant in deiner IDE
2. **MCP Server** - Übersetzt Copilot-Anfragen in API-Calls (lokal!)
3. **Day2Day-Manager API** - Stellt Daten bereit (Laravel-App)
4. **Datenbank** - Deine echten Projektdaten

## Was MCP NICHT ist

❌ **Kein Cloud-Service** - Läuft komplett lokal auf deinem Rechner  
❌ **Kein Datenbank-Ersatz** - Greift auf deine bestehende DB zu  
❌ **Keine Magie** - Nur eine Schnittstelle zwischen Copilot und deiner App  
❌ **Kein Sicherheitsrisiko** - Du kontrollierst, welche Tools erlaubt sind

## Beispiel-Tools für Day2Day-Manager

Die folgenden Tools könnten via MCP verfügbar sein:

### 1. `get_available_capacity`
**Was es tut:** Zeigt verfügbare Kapazität aller Mitarbeiter

**Beispiel:**
```
Du: "@capacity Wer hat nächste Woche 10+ Stunden frei?"

Tool-Call: get_available_capacity({ min_hours: 10, week: "next" })

Response:
✅ Max Mustermann: 15h frei (62% ausgelastet)
✅ Anna Schmidt: 12h frei (70% ausgelastet)
```

### 2. `get_overbooked_employees`
**Was es tut:** Findet überbuchte Mitarbeiter

**Beispiel:**
```
Du: "@capacity Gibt es Überbuchungen?"

Tool-Call: get_overbooked_employees()

Response:
🔴 John Doe: 130% (52h gebucht, 40h verfügbar)
⚠️ Lisa Müller: 105% (42h gebucht, 40h verfügbar)
```

### 3. `get_project_health`
**Was es tut:** Zeigt KPIs für ein Projekt

**Beispiel:**
```
Du: "@kpi Wie steht Projekt Webshop?"

Tool-Call: get_project_health({ project: "Webshop Relaunch" })

Response:
📊 Status: ⚠️ Verzögert
- Fortschritt: 65% (Soll: 80%)
- Budget: +20% überschritten
- Team-Auslastung: 95%
```

### 4. `compare_planned_vs_actual`
**Was es tut:** Vergleicht geplante mit tatsächlichen Stunden

**Beispiel:**
```
Du: "@tracking Ist Projekt X im Plan?"

Tool-Call: compare_planned_vs_actual({ project_id: 15 })

Response:
⏱️ Geplant: 160h
⏱️ Tatsächlich: 185h (+25h, +16% über Plan)
⚠️ Trend: Budget-Überschreitung wahrscheinlich
```

### 5. `moco_import_status`
**Was es tut:** Zeigt Status der MOCO-Synchronisation

**Beispiel:**
```
Du: "@moco Wann lief der letzte Sync?"

Tool-Call: moco_import_status()

Response:
📥 Letzter Sync: Vor 2h (06:00 Uhr)
✅ 342 Zeiteinträge importiert
⏰ Nächster Sync: In 4h (18:00 Uhr)
```

## Sicherheit und Datenschutz

### ✅ Sicher weil:
- **Lokal:** MCP-Server läuft auf deinem Rechner (nicht in der Cloud)
- **Kontrolliert:** Du definierst, welche Tools verfügbar sind
- **Read-Only:** Die meisten Tools lesen nur Daten
- **Authentifiziert:** API-Zugriff nur mit Token/Session

### 🔒 Spezielle Sicherheit für MOCO:
```typescript
// Im MCP-Server: MOCO-Schutz
const FORBIDDEN_TOOLS = [
  'update_moco_entry',
  'delete_moco_project',
  'create_moco_user'
];

// Jeder Tool-Call wird geprüft
if (FORBIDDEN_TOOLS.includes(toolName)) {
  throw new Error('🚫 MOCO write operations are FORBIDDEN!');
}
```

**MOCO ist und bleibt READ-ONLY!**

## Wann ist MCP sinnvoll?

### ✅ MCP lohnt sich, wenn:
- Du **häufig** dieselben Daten-Abfragen machst
- Du **schnelle Antworten** auf Business-Fragen brauchst
- Du **mehrere Systeme** integrieren willst
- Du **Entwicklungszeit** sparen möchtest

### ⚠️ MCP ist Overkill, wenn:
- Du nur 1-2 Mal pro Woche nachschaust
- Ein einfaches Dashboard ausreicht
- Deine Datenbank klein ist (< 100 Datensätze)
- Du die Daten eh manuell prüfen musst

## Aktueller Status für Day2Day-Manager

### ⏸️ Noch nicht implementiert

MCP ist für Day2Day-Manager **noch nicht aktiv**. Diese Dokumentation ist für die **zukünftige Implementierung**.

**Warum dokumentiert, wenn nicht implementiert?**
- Um das Konzept zu verstehen
- Um Use Cases zu sammeln
- Um zu evaluieren, ob es sich lohnt
- Um eine klare Roadmap zu haben

### 📋 Nächste Schritte (falls implementiert wird):

1. **Phase 1: API-Endpoints bauen** (in Laravel)
   - `/api/capacity/available`
   - `/api/capacity/overbooked`
   - `/api/projects/{id}/health`

2. **Phase 2: MCP-Server erstellen** (TypeScript/Node.js)
   - Basis-Setup
   - 3-5 wichtigste Tools
   - Lokale Tests

3. **Phase 3: Copilot-Integration**
   - MCP-Server Config in Copilot
   - User-Tests
   - Feedback sammeln

4. **Phase 4: Erweitern oder beenden**
   - War es hilfreich? → Mehr Tools
   - War es nutzlos? → MCP entfernen

**Geschätzte Zeit:** 2-4 Wochen für Phase 1-3

## Ressourcen

### Offizielle MCP-Dokumentation:
- **Website:** https://modelcontextprotocol.io
- **Spezifikation:** https://spec.modelcontextprotocol.io
- **GitHub:** https://github.com/modelcontextprotocol

### Day2Day-Manager-spezifisch:
- **Roadmap:** `.github/MCP_ROADMAP.md`
- **Beispiel-Config:** `.github/mcp/examples/day2day-mcp-server.json`
- **Use Cases:** Siehe MCP_ROADMAP.md

### Tutorials & Examples:
- **MCP Quickstart:** https://modelcontextprotocol.io/quickstart
- **Example Servers:** https://github.com/modelcontextprotocol/servers
- **Community Servers:** https://github.com/topics/mcp-server

## Häufige Fragen

### Q: Muss ich MCP nutzen?
**A:** Nein! Day2Day-Manager funktioniert auch ohne MCP perfekt. MCP ist ein **Optional Add-on** für Power-User.

### Q: Kostet MCP etwas?
**A:** Nein, das Protokoll ist Open Source und kostenlos. Du brauchst nur:
- GitHub Copilot (bereits vorhanden)
- Eigenen MCP-Server (kostenlos, selbst gehostet)

### Q: Kann Copilot dann in meine Datenbank schreiben?
**A:** Nur wenn du das explizit erlaubst! Du kontrollierst, welche Tools verfügbar sind. Für Day2Day-Manager wären 95% der Tools read-only.

### Q: Was ist mit MOCO-Schutz?
**A:** MCP würde **niemals** zu MOCO schreiben. Das ist in der Architektur fest verankert. Selbst mit MCP bleibt MOCO read-only!

### Q: Läuft MCP in der Cloud?
**A:** Nein! Der MCP-Server läuft **lokal auf deinem Rechner**. Deine Daten verlassen niemals dein System.

### Q: Ist das kompliziert einzurichten?
**A:** Mittelschwer. Du brauchst:
1. API-Endpoints in Day2Day-Manager (Laravel-Kenntnisse)
2. MCP-Server (TypeScript/Node.js-Kenntnisse)
3. Copilot-Konfiguration (JSON-Datei)

**Empfehlung:** Erst evaluieren, ob es sich lohnt (siehe MCP_ROADMAP.md)

## Zusammenfassung

**MCP für Day2Day-Manager:**

✅ **Potenzial:** Enorme Zeitersparnis bei Planungs-Fragen  
✅ **Sicherheit:** Lokal, kontrolliert, MOCO-geschützt  
✅ **Flexibel:** Von 3 Tools bis 20+ erweiterbar  
⚠️ **Aufwand:** 2-4 Wochen Entwicklungszeit  
⚠️ **Status:** Noch nicht implementiert (nur Planung)

**Empfehlung:** Lies die `MCP_ROADMAP.md` für Details und ROI-Kalkulation!

---

**Hinweis:** Diese Dokumentation beschreibt eine **mögliche zukünftige Implementierung**. MCP ist aktuell nicht aktiv in Day2Day-Manager. Der Zweck dieser Docs ist, das Konzept zu verstehen und zu evaluieren, ob eine Implementierung sinnvoll ist.
