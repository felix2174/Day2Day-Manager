# MOCO Projektverteilung mit Pie Charts

## Übersicht

Die **Mitarbeiter-Detailansicht** zeigt jetzt eine **visuelle Projektverteilung** als kreisförmiges Diagramm (Pie Chart). Die Darstellung basiert auf den tatsächlich gebuchten Stunden der letzten 4 Wochen aus MOCO Activities.

**Wo zu finden:**
- **Mitarbeiter-Übersicht** (`/employees`): Zeigt Gesamtstunden und Anzahl Projekte
- **Mitarbeiter-Details** (`/employees/{id}`): Zeigt vollständiges Pie Chart mit allen Projekten

---

## 📊 Wie funktioniert es?

### Datenerfassung

```
1. Hole alle Activities der letzten 4 Wochen aus MOCO
2. Gruppiere nach Projekt-Namen
3. Summiere Stunden pro Projekt
4. Berechne Prozentanteile
```

### Darstellung

### Mitarbeiter-Übersicht (/employees)

**Kompakte Darstellung:**
- Große Zahl: Gesamtstunden (z.B. "78.5")
- Untertitel: "Stunden (4 Wochen)"
- Kleine Zeile: Anzahl Projekte (z.B. "2 Projekte")

→ **Klick auf Mitarbeiter** öffnet Detail-Ansicht mit vollständigem Pie Chart

### Mitarbeiter-Details (/employees/{id})

**Pie Chart (200×200px):**
- Zeigt die prozentuale Verteilung der Arbeitszeit auf Projekte
- Bis zu 10 verschiedene Farben für unterschiedliche Projekte
- Gesamtstunden zentral in der Mitte
- SVG-basiert für scharfe Darstellung

**Projekt-Liste (rechts vom Chart):**
- Alle Projekte mit Namen, Stunden und Prozentanteil
- Farbcodierte Boxen für jedes Projekt
- Sortiert nach Stunden (höchste zuerst)

---

## 📝 Beispiel: Jörg Michno

**Letzte 4 Wochen (12.09 - 10.10.2025):**

```
KW 39:  15.0h
KW 40:  31.5h  
KW 41:  32.0h
KW 42:   0.0h
─────────────
Total:  78.5h
```

**Projektverteilung:**
```
Day2Day-Flow:         78.0h (99.4%) 🔵
Aufträge auf Zuruf:    0.5h ( 0.6%) 🟢
```

**Pie Chart zeigt:**
- Großer blauer Kreis (99.4%) für Day2Day-Flow
- Kleiner grüner Sliver (0.6%) für Aufträge auf Zuruf
- "78.5h" in der Mitte

**Auslastung:**
```
Kapazität (4 Wochen): 8h/Woche × 4 = 32h
Tatsächlich:          78.5h
Auslastung:           245% (ÜBERLASTET! ❌)
```

→ **Problem:** Kapazität ist mit 8h/Woche zu niedrig eingestellt!

---

## 🎨 UI Komponenten

### Pie Chart (SVG)

```svg
<svg width="80" height="80">
  <!-- Projekt 1 -->
  <circle stroke="#3b82f6" ... />
  <!-- Projekt 2 -->
  <circle stroke="#10b981" ... />
  <!-- Weißer Kreis in der Mitte -->
  <circle fill="white" r="16" />
</svg>
```

### Farben

Die Projekte bekommen automatisch Farben zugewiesen:

| Index | Farbe | Hex |
|-------|-------|-----|
| 1 | 🔵 Blau | #3b82f6 |
| 2 | 🟢 Grün | #10b981 |
| 3 | 🟡 Gelb | #f59e0b |
| 4 | 🔴 Rot | #ef4444 |
| 5 | 🟣 Lila | #8b5cf6 |
| 6 | 🩷 Pink | #ec4899 |
| 7 | 🟦 Türkis | #14b8a6 |
| 8 | 🟠 Orange | #f97316 |
| 9 | 🔵 Indigo | #6366f1 |
| 10 | 🟢 Lime | #84cc16 |

---

## 💻 Implementation

### Controller (`EmployeeController.php`)

```php
// Gruppiere Activities nach Projekten
$projectHours = [];
foreach ($activities as $activity) {
    $projectName = $activity['project']['name'] ?? 'Ohne Projekt';
    $projectHours[$projectName]['hours'] += $activity['hours'];
}

// Berechne Prozentanteile
foreach ($projectHours as $project) {
    $percentage = ($project['hours'] / $totalHours) * 100;
    $projectDistribution[] = [
        'name' => $project['name'],
        'hours' => $project['hours'],
        'percentage' => round($percentage, 1)
    ];
}
```

### View (`employees/index.blade.php`)

**SVG Pie Chart mit stroke-dasharray:**
```php
@foreach($projectDistribution as $index => $project)
    <circle
        cx="40" cy="40" r="32"
        stroke="{{ $colors[$index] }}"
        stroke-width="24"
        stroke-dasharray="{{ $dashArray }}"
        stroke-dashoffset="{{ $dashOffset }}"
    />
@endforeach
```

---

## 🧪 Testing

### Debug-Command:
```bash
php artisan moco:debug-utilization {employee_id}
```

**Ausgabe:**
```
By Project (Top 10):
  - Day2Day-Flow: 78h
  - Aufträge auf Zuruf: 0.5h

Project Distribution:
  Day2Day-Flow:
    ████████████████████ 99.4% (78h)
  Aufträge auf Zuruf:
     0.6% (0.5h)

Overall Utilization: 245% (ÜBERLASTET)
```

---

## ⚙️ Auslastungsberechnung

### Neue Formel

```
Maximale Kapazität (4 Wochen) = weekly_capacity × 4

Auslastung (%) = (Total Hours / Max Capacity) × 100
```

### Beispiele

**Mitarbeiter A: Vollzeit (40h/Woche)**
```
Max Capacity: 40h × 4 = 160h
Gearbeitet:   120h
Auslastung:   75% ✅
```

**Mitarbeiter B: Teilzeit (20h/Woche)**
```
Max Capacity: 20h × 4 = 80h
Gearbeitet:   85h
Auslastung:   106% ⚠️
```

**Jörg Michno: Falsche Kapazität (8h/Woche)**
```
Max Capacity: 8h × 4 = 32h  ← FALSCH!
Gearbeitet:   78.5h
Auslastung:   245% ❌
```

→ **Lösung:** Kapazität auf 40h/Woche setzen

---

## 🔧 Kapazität korrigieren

Falls die Kapazität falsch ist:

```sql
-- Für einen Mitarbeiter
UPDATE employees 
SET weekly_capacity = 40 
WHERE id = 8;

-- Für alle auf 40h setzen
UPDATE employees 
SET weekly_capacity = 40 
WHERE weekly_capacity = 8;
```

---

## 📊 Vorteile dieser Darstellung

✅ **Visuell intuitiv** - Sofort erkennbar welche Projekte dominant sind  
✅ **Detailliert** - Zeigt exakte Prozentanteile und Stunden  
✅ **Aktuell** - Basiert auf tatsächlichen Activities der letzten 4 Wochen  
✅ **Projektspezifisch** - Nicht nur Gesamtauslastung, sondern Verteilung  
✅ **Keine Pflege nötig** - Automatisch aus Zeiterfassung generiert  

---

## 🎯 Use Cases

### 1. Projekt-Fokus erkennen
> "Mitarbeiter X arbeitet zu 80% an Projekt A → Ist das gewollt?"

### 2. Überlastung identifizieren
> "Mitarbeiter Y hat 245% Auslastung → Kapazität falsch oder Überstunden?"

### 3. Projektressourcen optimieren
> "3 Mitarbeiter arbeiten alle >50% am selben Projekt → Bottleneck?"

### 4. Untauslastung erkennen
> "Mitarbeiter Z hat nur 20% Auslastung → Mehr Projekte zuweisen?"

---

## 🚀 Erweitungsmöglichkeiten

### 1. Zeitraum wählbar
```php
// 4 Wochen / 3 Monate / 6 Monate
$period = request('period', 4);
```

### 2. Hover-Effekt
```js
// Bei Mouse-Over: Vollständiger Projektname + Details
```

### 3. Drill-Down
```php
// Klick auf Projekt → Zeigt alle Activities dieses Projekts
```

### 4. Team-Ansicht
```php
// Zeige Projektverteilung für ganzes Team aggregiert
```

### 5. Export
```php
// CSV/PDF Export der Projektverteilung
```

---

## 📌 Wichtige Hinweise

### Kapazität muss korrekt sein!
Die Auslastung ist nur aussagekräftig, wenn `weekly_capacity` richtig eingestellt ist:
- Vollzeit: 40h/Woche
- Teilzeit (50%): 20h/Woche
- Teilzeit (75%): 30h/Woche

### "Ohne Projekt" Activities
Activities ohne zugeordnetes Projekt werden als "Ohne Projekt" gruppiert.

### Farbzuweisung
Farben werden nach Reihenfolge (höchste Stunden zuerst) zugewiesen, nicht nach Projektname.

### Performance
Bei vielen Mitarbeitern (>50) kann die Seite langsam laden.
→ Lösung: Pagination oder Caching implementieren

---

## 🐛 Troubleshooting

### Problem: Pie Chart wird nicht angezeigt

**Ursache:** Keine Activities in den letzten 4 Wochen

**Lösung:** Zeigt "Keine Aktivitäten" Text an

---

### Problem: Auslastung über 100%

**Ursache 1:** `weekly_capacity` zu niedrig (z.B. 8h statt 40h)

**Lösung:** Kapazität korrigieren

**Ursache 2:** Tatsächliche Überstunden

**Lösung:** Normal, wenn Mitarbeiter mehr als Kapazität arbeitet

---

### Problem: Nur ein Projekt trotz mehrerer

**Ursache:** Ein Projekt dominiert (>95% der Zeit)

**Lösung:** Normal, wenn Mitarbeiter fokussiert an einem Projekt arbeitet

---

## 📞 Support

- Debug-Command: `php artisan moco:debug-utilization {id}`
- Logs: `storage/logs/laravel.log`
- Suche nach: `"MOCO: User"` für Details

