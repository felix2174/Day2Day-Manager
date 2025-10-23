# Spec-Driven Development für Day2Day-Manager

## Was ist Spec-Driven Development?

**Spec-Driven Development** bedeutet: Bevor du Code schreibst, schreibst du eine **Spezifikation** (Spec), die genau beschreibt:
- **Was** soll das Feature machen?
- **Warum** brauchen wir es?
- **Wie** soll es sich verhalten?
- **Was** kann schiefgehen?

### Warum ist das wichtig?

```
Ohne Spec:
❌ "Bau mal eine Mitarbeiter-Verwaltung"
   → Developer rät, was gemeint ist
   → 3 Iterationen bis es passt
   → Keiner weiß, welche Edge Cases vergessen wurden

Mit Spec:
✅ "Siehe specs/examples/employee-management.md"
   → Klare Anforderungen
   → Akzeptanzkriterien definiert
   → Edge Cases dokumentiert
   → 1 Iteration, fertig!
```

**Zeitersparnis: 50-70%** bei komplexen Features!

## Wann eine Spec schreiben?

### ✅ Spec NOTWENDIG für:
- Neue Features (z.B. Gantt-Diagramm)
- Komplexe Business-Logik (z.B. Kapazitätsberechnung)
- API-Endpoints
- Datenmodell-Änderungen
- Kritische Bugfixes (die mehrere Bereiche betreffen)

### ⚠️ Spec OPTIONAL für:
- Kleine UI-Tweaks
- Offensichtliche Bugfixes
- Refactorings ohne Verhaltensänderung
- Typo-Korrekturen

### 🚫 KEINE Spec nötig für:
- Kommentare aktualisieren
- README verbessern
- Dependencies updaten (ohne Breaking Changes)

## Spec-Templates

Wir haben zwei Templates:

### 1. Feature-Spec (`templates/feature-spec.md`)
Für neue Features und Funktionalität.

**Enthält:**
- Feature-Beschreibung
- Akzeptanzkriterien (Given/When/Then)
- Business Rules
- Datenmodell
- API-Verträge
- Edge Cases
- Offene Fragen

**Nutze es für:**
- Neue Seiten/Views
- Neue Business-Logik
- API-Endpoints
- Datenbank-Migrationen

### 2. Bugfix-Spec (`templates/bugfix-spec.md`)
Für Bugfixes, besonders komplexe.

**Enthält:**
- Bug-Beschreibung
- Reproduktionsschritte
- Erwartetes vs. tatsächliches Verhalten
- Root Cause
- Lösung
- Tests

**Nutze es für:**
- Kritische Bugs
- Bugs die schwer zu reproduzieren sind
- Bugs die mehrere Bereiche betreffen

## Wie schreibe ich eine gute Spec?

### Grundprinzipien

1. **Klar und präzise**
   - Keine Mehrdeutigkeiten
   - Konkrete Beispiele statt abstrakte Beschreibungen

2. **Vollständig**
   - Alle Szenarien durchdenken
   - Edge Cases explizit nennen
   - "Was passiert wenn...?" immer beantworten

3. **Testbar**
   - Akzeptanzkriterien müssen überprüfbar sein
   - Konkrete Erwartungen definieren

4. **Business-fokussiert**
   - Warum brauchen wir das Feature?
   - Welches Problem löst es?
   - Welchen Wert bringt es?

### Beispiel: Schlechte vs. Gute Spec

#### ❌ Schlecht:
```markdown
## Feature: Mitarbeiter hinzufügen

Man soll Mitarbeiter hinzufügen können.

### Felder:
- Name
- Email
- Stunden
```

**Probleme:**
- Unklar: Was passiert bei doppelter Email?
- Unklar: Wie werden Stunden validiert?
- Keine Edge Cases
- Keine Business-Rules

#### ✅ Gut:
```markdown
## Feature: Mitarbeiter hinzufügen

### Business Context
Neue Mitarbeiter müssen im System erfasst werden, um sie Projekten zuordnen zu können.

### Akzeptanzkriterien

**AC1: Erfolgreicher Mitarbeiter-Anlage**
- **Given:** Ich bin auf der Mitarbeiter-Seite
- **When:** Ich fülle alle Pflichtfelder aus und klicke "Speichern"
- **Then:** Mitarbeiter wird erstellt und in der Liste angezeigt

**AC2: Email-Duplikat wird verhindert**
- **Given:** Ein Mitarbeiter mit max@example.com existiert bereits
- **When:** Ich versuche einen neuen Mitarbeiter mit max@example.com zu erstellen
- **Then:** Fehlermeldung "Email bereits vergeben" wird angezeigt

### Business Rules
- BR1: Email muss eindeutig sein (Unique Constraint)
- BR2: Wochenstunden: 1-60 (realistischer Bereich)
- BR3: Name ist Pflichtfeld
- BR4: Standardwert Wochenstunden: 40

### Edge Cases
- EC1: Name mit Umlauten (Max Müller) → Erlaubt
- EC2: Sehr langer Name (>100 Zeichen) → Validierung: Max 255 Zeichen
- EC3: Email ohne TLD (max@localhost) → Erlaubt (für Tests)
```

**Vorteile:**
- Jeder weiß, was zu tun ist
- Alle Szenarien durchdacht
- Testbar (für QA/Tests)
- Keine Rückfragen nötig

## Workflow: Von Spec zu Code

### Schritt 1: Spec schreiben (vor dem Code!)
```bash
# Kopiere Template
cp specs/templates/feature-spec.md specs/features/my-new-feature.md

# Fülle die Spec aus
# Investiere 30-60 Minuten in gute Planung!
```

### Schritt 2: Spec reviewen (optional, aber empfohlen)
- Zeige die Spec einem Kollegen
- Oder: Frage GitHub Copilot: "@workspace Review diese Spec"
- Ziel: Lücken und Mehrdeutigkeiten finden

### Schritt 3: Spec committen
```bash
git add specs/features/my-new-feature.md
git commit -m "Add spec: My New Feature"
```

### Schritt 4: Code implementieren
Jetzt weißt du genau, was zu tun ist!

### Schritt 5: Tests schreiben (basierend auf Akzeptanzkriterien)
```php
// Aus AC1 der Spec:
public function test_creates_employee_successfully(): void
{
    $this->post('/employees', [
        'name' => 'Max Mustermann',
        'email' => 'max@example.com',
        'weekly_hours' => 40,
    ]);
    
    $this->assertDatabaseHas('employees', [
        'email' => 'max@example.com'
    ]);
}
```

### Schritt 6: Spec updaten (wenn sich was ändert)
Spec ist **Living Document**! Wenn du während der Implementierung merkst, dass etwas anders gemacht werden muss, update die Spec!

## Beispiele

Wir haben zwei vollständige Beispiel-Specs:

### 1. `examples/assignment-management.md`
Zeigt wie eine Feature-Spec für **Projektbuchungen** aussieht:
- Komplexe Business-Logik (Überbuchung, Überschneidungen)
- Datenmodell mit Relationships
- Validierungsregeln
- Edge Cases (Was wenn Projekt endet? Was bei Überbuchung?)

**Ideal als Vorlage für:** Features mit komplexer Business-Logik

### 2. `examples/time-entry-sync.md`
Zeigt wie eine Spec für **MOCO-Integration** aussieht:
- Externe API-Integration
- Sync-Strategie
- Error Handling
- Sicherheits-Anforderungen (READ-ONLY!)

**Ideal als Vorlage für:** Integration mit externen Systemen

## Tipps für Day2Day-Manager-Specs

### Deutsche Kommentare für Business-Logik
```markdown
### Business Rule: Überbuchungs-Erkennung

Ein Mitarbeiter ist überbucht, wenn:
- Summe aller Projektbuchungen > Wochenarbeitszeit
- Beispiel: 30h + 25h = 55h bei 40h/Woche → Überbucht!

**Behandlung:**
- System ERLAUBT Überbuchung (technisch)
- Zeigt aber WARNUNG an (gelbe/rote Ampel)
- Manager kann bewusst überbuchen (z.B. bei Teilzeit-Krankheit)
```

### MOCO ist READ-ONLY!
Jede Spec die MOCO betrifft MUSS explizit sagen:
```markdown
### MOCO-Integration

⚠️ **KRITISCH:** MOCO ist READ-ONLY!

Day2Day-Manager darf:
✅ Daten von MOCO lesen (GET)
✅ In lokaler DB speichern

Day2Day-Manager darf NICHT:
🚫 Zu MOCO schreiben (POST/PATCH/DELETE)
🚫 MOCO-Daten ändern
🚫 Zeiteinträge zurücksynchronisieren
```

### Edge Cases immer durchdenken

Frage dich bei jedem Feature:
- Was passiert wenn das Projekt gelöscht wird?
- Was bei doppelten Einträgen?
- Was wenn die API nicht antwortet?
- Was bei ungültigen Daten?
- Was in der Produktion vs. Development?

## GitHub Copilot + Specs

### Specs mit Copilot schreiben

```
Du: "@workspace Erstelle eine Spec für Feature: Urlaubs-Kalender"

Copilot: *nutzt template und erstellt vollständige Spec*
```

### Code aus Specs generieren

```
Du: "@workspace Implementiere specs/features/vacation-calendar.md"

Copilot: *liest Spec und generiert passenden Code*
```

### Specs reviewen

```
Du: "@workspace Review specs/features/my-feature.md - fehlt etwas?"

Copilot: *analysiert Spec und zeigt fehlende Edge Cases*
```

## Häufige Fragen

### Q: Muss ich für JEDES Feature eine Spec schreiben?
**A:** Nein! Kleine Features/Bugfixes brauchen keine Spec. Aber bei komplexer Business-Logik spart eine Spec enorm Zeit!

### Q: Wie lang sollte eine Spec sein?
**A:** So kurz wie möglich, so lang wie nötig. Durchschnittlich: 1-3 Seiten. Bei sehr komplexen Features auch mal 5-10 Seiten.

### Q: Was wenn sich Anforderungen während der Implementierung ändern?
**A:** Update die Spec! Sie ist ein Living Document. Besser eine aktualisierte Spec als veraltete Doku.

### Q: Schreibt man Specs auf Deutsch oder Englisch?
**A:** Für Day2Day-Manager: **Deutsch** für Business-Logik (weil enodia-intern), **Englisch** für technische Specs (z.B. API-Docs).

### Q: Wer schreibt die Specs?
**A:** 
- **Ideal:** Product Owner / Fachbereichsverantwortlicher
- **Praxis:** Oft Developer + PO zusammen
- **Day2Day-Manager:** Du (Developer) schreibst es, basierend auf Anforderungen

## Zusammenfassung

**Specs sind wie eine Landkarte:**
- Ohne Karte: Du irrst umher und hoffst, dass du ankommst
- Mit Karte: Du weißt genau, wo du bist und wohin du musst

**Vorteile:**
✅ Klarheit: Jeder weiß, was zu tun ist  
✅ Zeitersparnis: Weniger Rückfragen und Iterationen  
✅ Qualität: Edge Cases vergisst du nicht  
✅ Dokumentation: Für später nachvollziehbar  
✅ Tests: Akzeptanzkriterien = Testfälle

**Investment:**
- 30-60 Min für Spec schreiben
- Spart 2-5 Stunden bei Implementierung
- **ROI: 300-500%!**

---

**Nächste Schritte:**
1. Lies die Beispiele in `specs/examples/`
2. Kopiere ein Template aus `specs/templates/`
3. Schreibe deine erste Spec!
4. Zeige sie jemandem → Feedback
5. Implementiere basierend auf der Spec

**Und denk dran:** Eine gute Spec ist wie ein guter Commit-Message - dein zukünftiges Ich wird dir dankbar sein! 🎯
