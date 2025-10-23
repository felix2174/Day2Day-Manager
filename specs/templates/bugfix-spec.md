# Bug Spec: [Bug-Titel]

**Status:** 🔴 Open / 🟡 In Progress / 🟢 Fixed / ✅ Verified  
**Severity:** 🔴 Critical / 🟠 High / 🟡 Medium / 🟢 Low  
**Erstellt:** YYYY-MM-DD  
**Fixed:** YYYY-MM-DD  
**Autor:** [Dein Name]

---

## 1. Bug-Zusammenfassung

[Kurze, prägnante Beschreibung des Bugs in 1-2 Sätzen]

**Beispiel:**
Mitarbeiter-Auslastung zeigt 200% statt 100% wenn mehrere Projekte parallel gebucht sind.

---

## 2. Schweregrad & Priorität

### Severity: [Critical / High / Medium / Low]

**Begründung:**
- **Critical:** System nicht nutzbar / Datenverlust / Sicherheitslücke
- **High:** Major Feature funktioniert nicht / Viele User betroffen
- **Medium:** Minor Feature betroffen / Workaround verfügbar
- **Low:** Kosmetischer Fehler / Einzelfall

**Beispiel:**
**Severity:** High  
**Begründung:** Falsche Auslastungsberechnung führt zu falschen Management-Entscheidungen. Betrifft alle Projektmanager (5 User).

### Priorität: [P0 / P1 / P2 / P3]
- **P0:** Sofort fixen (Production down)
- **P1:** Nächster Sprint
- **P2:** Geplanter Sprint
- **P3:** Backlog

---

## 3. Betroffene Komponenten

- [ ] **Backend:** [Welche Controller/Services/Models?]
- [ ] **Frontend:** [Welche Views/Components?]
- [ ] **Datenbank:** [Welche Tabellen/Queries?]
- [ ] **API:** [Welche Endpoints?]
- [ ] **MOCO-Integration:** [Import/Sync betroffen?]

**Beispiel:**
- [x] Backend: `EmployeeController`, `CapacityService`
- [x] Frontend: `employees/show.blade.php` (Auslastungs-Anzeige)
- [ ] Datenbank: Keine Änderungen
- [ ] API: Nicht betroffen

---

## 4. Umgebung

**Wo tritt der Bug auf?**
- [ ] Production
- [ ] Staging
- [ ] Development / Local
- [ ] Alle Umgebungen

**Browser / Client:**
- Browser: Chrome 118, Firefox 119, Safari 17
- OS: Windows 11, macOS 14
- PHP Version: 8.2.12
- Laravel Version: 12.5.0
- Datenbank: MySQL 8.0.35

---

## 5. Reproduktionsschritte

**Schritt-für-Schritt-Anleitung:**

1. [Schritt 1]
2. [Schritt 2]
3. [Schritt 3]
4. **Beobachtung:** [Was passiert?]

**Beispiel:**

1. Gehe zu `/employees/1` (Max Mustermann)
2. Max hat zwei Projekte:
   - Projekt A: 30h/Woche (75%)
   - Projekt B: 10h/Woche (25%)
3. Erwarte: Auslastung = 100%
4. **Beobachtung:** Auslastung zeigt 200%

**Voraussetzungen:**
- Mindestens 1 Mitarbeiter mit 2+ Projekten
- Wochenarbeitszeit: 40h
- Alle Projekte aktiv (kein end_date)

**Reproduzierbarkeit:**
- [x] Immer (100%)
- [ ] Manchmal (~50%)
- [ ] Selten (<10%)

---

## 6. Erwartetes vs. Tatsächliches Verhalten

### ✅ Erwartetes Verhalten:
[Was sollte passieren?]

**Beispiel:**
Auslastung = (Summe aller hours_per_week) / weekly_hours * 100
           = (30 + 10) / 40 * 100
           = 100%

Display: "🟢 100% ausgelastet"

### ❌ Tatsächliches Verhalten:
[Was passiert stattdessen?]

**Beispiel:**
Auslastung = 200%
Display: "🔴 200% ausgelastet"

### 📸 Screenshots (optional)

[Füge Screenshots ein oder beschreibe visuell]

**Screenshot 1:** Mitarbeiter-Detail-Seite zeigt "200%"  
**Screenshot 2:** Debug-Ausgabe der Calculation

---

## 7. Root Cause Analysis

### 7.1 Ursache
[Was ist die technische Ursache des Bugs?]

**Beispiel:**

**Datei:** `app/Models/Employee.php`  
**Zeile:** 85  
**Problem:**

```php
// ❌ BUG: Multipliziert statt addiert
public function getUtilizationAttribute(): float
{
    return $this->assignments->sum(function ($assignment) {
        return $assignment->hours_per_week * 100 / $this->weekly_hours;
    });
}
```

**Erklärung:**
Die Methode berechnet für jedes Assignment separat den Prozentsatz und summiert diese auf. Das führt zu:
- Assignment A: 30/40 * 100 = 75%
- Assignment B: 10/40 * 100 = 25%
- **Summe: 75% + 25% = 100%** ✅ (korrekt)

Aber der Code macht:
- Assignment A: 30 * 100 / 40 = 75
- Assignment B: 10 * 100 / 40 = 25
- **Summe: 100** (als Prozent interpretiert = 100%)

Wait, das wäre korrekt... Lass mich nochmal schauen...

**Korrektur - Der echte Bug:**
```php
// ❌ BUG: Summiert Prozentsätze statt Stunden
public function getUtilizationAttribute(): float
{
    $totalPercentage = $this->assignments->sum(function ($assignment) {
        return ($assignment->hours_per_week / $this->weekly_hours) * 100;
    });
    return $totalPercentage; // Gibt 100 + 100 = 200 zurück
}
```

Das Problem: Jedes Assignment berechnet seine eigene Auslastung als Prozentsatz relativ zur Gesamtkapazität, dann werden diese Prozentsätze addiert.

### 7.2 Warum wurde es nicht früher entdeckt?

- [ ] Keine Tests für diesen Fall
- [ ] Test-Daten hatten immer nur 1 Assignment pro Employee
- [ ] Code Review hat es übersehen
- [ ] Feature war neu

### 7.3 Warum ist es jetzt aufgefallen?

**Beispiel:**
Manager hat erstmals einen Mitarbeiter auf 2 Projekte gleichzeitig gebucht. Vorher hatten alle Mitarbeiter nur 1 Projekt parallel.

---

## 8. Lösung

### 8.1 Lösungsansatz

[Beschreibe die Lösung in Worten]

**Beispiel:**
Statt für jedes Assignment den Prozentsatz zu berechnen und zu addieren, summieren wir erst die Stunden, dann berechnen wir den Prozentsatz.

### 8.2 Code-Änderungen

**Datei:** `app/Models/Employee.php`

**Vorher:**
```php
// ❌ FALSCH
public function getUtilizationAttribute(): float
{
    $totalPercentage = $this->assignments->sum(function ($assignment) {
        return ($assignment->hours_per_week / $this->weekly_hours) * 100;
    });
    return $totalPercentage;
}
```

**Nachher:**
```php
// ✅ RICHTIG
public function getUtilizationAttribute(): float
{
    $totalHours = $this->assignments->sum('hours_per_week');
    return ($totalHours / $this->weekly_hours) * 100;
}
```

**Erklärung:**
1. Summiere erst alle `hours_per_week`: 30 + 10 = 40
2. Dann berechne Prozentsatz: 40 / 40 * 100 = 100%

### 8.3 Weitere Änderungen

**Datei:** [Weitere Dateien, falls nötig]

---

## 9. Tests

### 9.1 Neuer Test (verhindert Regression)

```php
// tests/Unit/EmployeeTest.php

public function test_calculates_utilization_correctly_with_multiple_assignments(): void
{
    // Arrange: Mitarbeiter mit 40h/Woche
    $employee = Employee::factory()->create(['weekly_hours' => 40]);
    
    // Act: 2 Assignments (30h + 10h = 40h total)
    Assignment::factory()->create([
        'employee_id' => $employee->id,
        'hours_per_week' => 30,
    ]);
    
    Assignment::factory()->create([
        'employee_id' => $employee->id,
        'hours_per_week' => 10,
    ]);
    
    // Assert: Auslastung = 100% (nicht 200%!)
    $this->assertEquals(100, $employee->fresh()->utilization);
}

public function test_calculates_overbooking_correctly(): void
{
    $employee = Employee::factory()->create(['weekly_hours' => 40]);
    
    Assignment::factory()->create([
        'employee_id' => $employee->id,
        'hours_per_week' => 30,
    ]);
    
    Assignment::factory()->create([
        'employee_id' => $employee->id,
        'hours_per_week' => 25, // Überbucht!
    ]);
    
    // 55h bei 40h-Woche = 137.5%
    $this->assertEquals(137.5, $employee->fresh()->utilization);
}
```

### 9.2 Manuelle Tests

**Testfall 1: Normaler Fall (100%)**
- [ ] Mitarbeiter mit 40h/Woche
- [ ] 2 Projekte: 30h + 10h
- [ ] **Erwartet:** 100% Auslastung, grüne Ampel

**Testfall 2: Überbuchung (>100%)**
- [ ] Mitarbeiter mit 40h/Woche
- [ ] 2 Projekte: 30h + 25h
- [ ] **Erwartet:** 137.5% Auslastung, rote Ampel

**Testfall 3: Unterlast (<100%)**
- [ ] Mitarbeiter mit 40h/Woche
- [ ] 1 Projekt: 30h
- [ ] **Erwartet:** 75% Auslastung, grüne Ampel

**Testfall 4: Edge Case (0 Projekte)**
- [ ] Mitarbeiter ohne Assignments
- [ ] **Erwartet:** 0% Auslastung

---

## 10. Betroffene Nutzer & Kommunikation

### Wer ist betroffen?
- Alle Projektmanager (5 User)
- Betrifft ~15 Mitarbeiter mit mehreren Projekten

### Workaround (falls nicht sofort gefixt)
[Gibt es einen temporären Workaround?]

**Beispiel:**
**Workaround:** Manuell berechnen:
1. Schaue in "Zuordnungen"-Tab
2. Addiere alle Stunden händisch
3. Teile durch Wochenarbeitszeit

### Kommunikation
- [ ] Slack-Nachricht an #day2day-manager
- [ ] Email an betroffene User
- [ ] Update im Changelog
- [ ] Post-Mortem (bei kritischen Bugs)

---

## 11. Prävention

**Was können wir tun, damit das nicht wieder passiert?**

- [ ] **Test hinzufügen:** ✅ Siehe Abschnitt 9.1
- [ ] **Code Review verbessern:** Checkliste für Berechnungen
- [ ] **Edge Cases dokumentieren:** In Business-Logic-Comments
- [ ] **Monitoring:** Alert bei unplausiblen Auslastungen (>200%)

**Beispiel:**
```php
// Zukünftig: Business-Logik mit Kommentar

/**
 * Berechnet die Auslastung eines Mitarbeiters.
 * 
 * WICHTIG: Erst Stunden summieren, dann Prozentsatz berechnen!
 * Nicht pro Assignment Prozentsatz berechnen und summieren (Bug #123).
 * 
 * Beispiel:
 * - Mitarbeiter: 40h/Woche
 * - Projekt A: 30h
 * - Projekt B: 10h
 * - Auslastung: (30+10)/40*100 = 100% ✅
 * 
 * FALSCH wäre: 30/40*100 + 10/40*100 = 75% + 25% = 100% 
 *              (zufällig richtig, aber bei 3+ Projekten falsch!)
 */
public function getUtilizationAttribute(): float
{
    // ...
}
```

---

## 12. Rollback-Plan

**Was wenn der Fix weitere Probleme verursacht?**

### Rollback-Schritte:
1. `git revert <commit-hash>`
2. Deploy Rollback
3. Monitoring prüfen

### Risiken des Fixes:
- [ ] Keine (nur Calculation-Fix, keine DB-Änderungen)
- [ ] Niedrig
- [ ] Mittel
- [ ] Hoch

---

## 13. Related Bugs / Tickets

- **GitHub Issue:** #123
- **Ähnliche Bugs:** #100 (Auslastung bei Teilzeit falsch)
- **Related Features:** Kapazitätsplanung (Spec: `specs/features/capacity-planning.md`)

---

## 14. Timeline

| Datum | Ereignis |
|-------|----------|
| 2025-10-20 | Bug entdeckt durch Projektmanager |
| 2025-10-20 | Bug-Report erstellt |
| 2025-10-21 | Root Cause gefunden |
| 2025-10-21 | Fix implementiert & getestet |
| 2025-10-22 | Code Review |
| 2025-10-22 | Deployed to Staging |
| 2025-10-23 | Deployed to Production |
| 2025-10-23 | Verifiziert: Bug fixed ✅ |

---

## 15. Lessons Learned

**Was haben wir gelernt?**

1. **Test-Daten realistischer gestalten**
   - Unsere Seeders hatten immer nur 1 Assignment pro Employee
   - Real-World: Viele Mitarbeiter haben 2-3 Projekte parallel

2. **Berechnungen immer mit Kommentaren versehen**
   - Business-Logik ist nicht selbsterklärend
   - Formeln explizit aufschreiben

3. **Edge Cases durchdenken**
   - Vor Implementierung fragen: "Was bei 0/1/2/viele?"

4. **Tests für alle Szenarien**
   - Nicht nur Happy Path testen
   - Auch: Überbuchung, 0 Projekte, viele Projekte

---

## 16. Attachments

- Screenshot: [Link oder eingebettet]
- Log Files: [Auszug aus laravel.log]
- Database Query: [Problematischer SQL-Query]

---

## Status: ✅ Fixed & Verified

**Verifiziert von:** [Name]  
**Datum:** 2025-10-23  
**Notizen:** Alle manuellen Tests bestanden. Monitoring zeigt keine Anomalien.
