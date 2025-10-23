# MOCO Auslastungsberechnung basierend auf Activities

## Implementierung

Da die MOCO Contracts **kein `hours_per_week` Feld** haben, berechnen wir die Auslastung jetzt basierend auf **tatsächlich gebuchten Stunden** aus MOCO Activities.

---

## 📊 Berechnungsformel

```
Wöchentliche Kapazität = MOCO User.work_schedule (z.B. 40h)
                       ODER lokale DB (Fallback)

Gebuchte Stunden = Summe aller Activity.hours der letzten 4 Wochen

Durchschnittliche Wochenstunden = Gebuchte Stunden / 4

Auslastung (%) = (Durchschnittliche Wochenstunden / Kapazität) × 100

Freie Stunden = Kapazität - Durchschnittliche Wochenstunden
```

---

## ✅ Vorteile dieser Methode

1. **Realistisch**: Zeigt die TATSÄCHLICHE Auslastung (nicht nur geplante Stunden)
2. **Automatisch aktualisiert**: Basiert auf realer Zeiterfassung
3. **Kein manueller Aufwand**: Keine Pflege von hours_per_week nötig
4. **Genau**: 4-Wochen-Durchschnitt gleicht Schwankungen aus

---

## 📝 Beispiel

**Mitarbeiter: Steffen Armgart**
```
Letzte 4 Wochen:
- KW 37: 0h
- KW 38: 0h  
- KW 39: 0h
- KW 41: 18h
─────────────
Total: 18h

Berechnung:
- Durchschnitt: 18h / 4 = 4.5h/Woche
- Kapazität: 8h/Woche
- Auslastung: (4.5 / 8) × 100 = 56%
- Freie Stunden: 3.5h/Woche
```

---

## 🛠️ Verwendete MOCO Daten

### 1. **User.work_schedule** → Kapazität
```json
{
  "id": 933722010,
  "work_schedule": {
    "monday": 8,
    "tuesday": 8,
    "wednesday": 8,
    "thursday": 8,
    "friday": 8,
    "saturday": 0,
    "sunday": 0
  }
}
```
→ **Total: 40h/Woche**

### 2. **Activities** → Tatsächliche Stunden
```json
{
  "id": 123456,
  "user_id": 933722010,
  "date": "2025-10-09",
  "hours": 6.5,
  "project": {
    "id": 947495290,
    "name": "Projekt XYZ"
  }
}
```

API Call:
```php
$activities = $mocoService->getUserActivities($userId, [
    'from' => Carbon::now()->subWeeks(4)->format('Y-m-d'),
    'to' => Carbon::now()->format('Y-m-d')
]);
```

---

## 🧪 Testing

### Debug-Command ausführen:
```bash
php artisan moco:debug-utilization
```

Zeigt detailliert:
- Wöchentliche Kapazität (aus MOCO oder DB)
- Alle Activities der letzten 4 Wochen
- Gruppierung nach Projekt
- Gruppierung nach Kalenderwoche
- Berechnete Auslastung

### Für spezifischen Mitarbeiter:
```bash
php artisan moco:debug-utilization {employee_id}
```

---

## 🎨 UI Darstellung

**Mitarbeiter-Übersicht** (`/employees`)

| Mitarbeiter | Kapazität | Auslastung | Status |
|-------------|-----------|------------|--------|
| Steffen A. | 8h/Woche | 56% (4.5h) 🟢 | 3.5h frei |
| Marc H. | 40h/Woche | 85% (34h) 🟡 | 6h frei |
| Tim H. | 40h/Woche | 95% (38h) 🔴 | 2h frei |

**Farbcodierung:**
- 🟢 Grün (0-70%): Unterausgelastet
- 🟡 Gelb (71-90%): Gut ausgelastet
- 🔴 Rot (91%+): Überlastet

**Hinweis im Header:**
> "Auslastung basierend auf MOCO Activities (Ø letzte 4 Wochen)"

---

## ⚙️ Implementation Details

### Controller (`EmployeeController.php`)

```php
// Hole Activities der letzten 4 Wochen
$fourWeeksAgo = Carbon::now()->subWeeks(4);
$activities = $mocoService->getUserActivities($employee->moco_id, [
    'from' => $fourWeeksAgo->format('Y-m-d'),
    'to' => Carbon::now()->format('Y-m-d')
]);

// Summiere alle Stunden
$totalHours = 0;
foreach ($activities as $activity) {
    $totalHours += $activity['hours'] ?? 0;
}

// Berechne Durchschnitt pro Woche
$averageWeeklyHours = $totalHours / 4;

// Auslastung
$utilization = round(($averageWeeklyHours / $weeklyCapacity) * 100);
$freeHours = max(0, $weeklyCapacity - $averageWeeklyHours);
```

---

## 📌 Wichtige Hinweise

### Zeitraum: 4 Wochen
- Wir nutzen einen 4-Wochen-Durchschnitt
- Gleicht wochenweise Schwankungen aus
- Aktuelle genug für realistische Einschätzung

### Kapazität-Fallback
1. Primär: MOCO `work_schedule`
2. Sekundär: Lokale DB `weekly_capacity`
3. Default: 40h

### Bei Neueinstellungen
- Neue Mitarbeiter ohne Activities zeigen 0% Auslastung
- Nach 4 Wochen normalisiert sich die Anzeige
- Alternative: Ersten Monat ausblenden oder anders kennzeichnen

---

## 🔄 Aktualisierung

Die Daten werden **bei jedem Seitenaufruf** frisch aus MOCO geladen.

Optional: Caching implementieren (5-15 Minuten)
```php
Cache::remember("moco_utilization_{$employee->id}", 900, function() {
    // MOCO API Calls
});
```

---

## 📊 Vergleich: Alte vs. Neue Methode

| Aspekt | Alt (Contract.hours_per_week) | Neu (Activities) |
|--------|------------------------------|------------------|
| Datenquelle | Geplante Stunden | Tatsächliche Stunden |
| Verfügbarkeit | ❌ Feld fehlt in MOCO | ✅ Immer vorhanden |
| Genauigkeit | Theoretisch | Realistisch |
| Aktualisierung | Manuell in MOCO | Automatisch durch Zeiterfassung |
| Aussagekraft | "Sollte arbeiten" | "Arbeitet tatsächlich" |

---

## 🚀 Nächste Schritte (Optional)

1. **Trend-Analyse**: Auslastung über mehrere Monate visualisieren
2. **Prognose**: Basierend auf letzten 12 Wochen zukünftige Auslastung schätzen
3. **Team-Vergleich**: Durchschnittliche Auslastung pro Abteilung
4. **Alerts**: Benachrichtigung bei Über-/Unterauslastung
5. **Export**: Auslastungsreport als Excel/PDF

---

## 💡 Best Practices

1. **Regelmäßige Zeiterfassung**: Mitarbeiter sollten täglich/wöchentlich buchen
2. **Konsistente Erfassung**: Alle Tätigkeiten in MOCO erfassen
3. **4-Wochen-Fenster**: Gibt gutes Bild ohne zu weit zurückzuschauen
4. **Urlaubszeiten beachten**: Bei langen Abwesenheiten Auslastung niedriger

---

## 📞 Support

Bei Fragen:
- Logs prüfen: `storage/logs/laravel.log`
- Debug-Command: `php artisan moco:debug-utilization`
- MOCO API Docs: https://github.com/hundertzehn/mocoapp-api-docs























