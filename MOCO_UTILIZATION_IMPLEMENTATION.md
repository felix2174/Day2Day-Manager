# MOCO Auslastungsberechnung - Implementation

## Implementierte Features

### 1. **Controller-Anpassungen** (`EmployeeController.php`)

Die `index()` Methode wurde erweitert um:
- MOCO User-Daten zu laden (work_schedule für wöchentliche Kapazität)
- MOCO Projekt-Contracts zu laden (hours_per_week pro Projekt)
- Auslastung basierend auf aktiven Projekten zu berechnen
- Nur Projekte im aktuellen Zeitraum zu berücksichtigen (nicht zukünftig oder bereits beendet)

### 2. **View-Anpassungen** (`employees/index.blade.php`)

- Zeigt MOCO-basierte Kapazität an (statt lokaler Datenbank)
- Zeigt MOCO-basierte Auslastung an (aus Projekt-Contracts)
- Zeigt freie Stunden basierend auf MOCO-Daten
- Hinweis im Header: "Auslastung basierend auf MOCO Projekt-Contracts"

### 3. **MOCO-Service** (`MocoService.php`)

Bereits vorhanden und optimiert:
- `getUser($userId)` - Lädt User-Daten inkl. work_schedule
- `getUserProjects($userId)` - Lädt nur Projekte mit User in Contracts
- Logging für besseres Debugging

---

## Datenquellen aus MOCO

### **User.work_schedule**
```json
{
  "id": 123,
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

### **Project.contracts[].hours_per_week**
```json
{
  "id": 789,
  "project_id": 456,
  "user_id": 123,
  "active": true,
  "hours_per_week": 20
}
```
→ **Geplante Stunden pro Projekt**

---

## Berechnungsformel

```
Wöchentliche Kapazität = Summe aus User.work_schedule
                       ODER Fallback auf lokale DB (Employee.weekly_capacity)
                       ODER Default: 40h

Geplante Stunden = Summe aller hours_per_week aus aktiven Contracts
                   WHERE Project.active = true
                   AND Project.start_date <= heute
                   AND Project.finish_date >= heute (oder NULL)
                   AND Contract.user_id = Mitarbeiter

Auslastung (%) = (Geplante Stunden / Wöchentliche Kapazität) × 100

Freie Stunden = Wöchentliche Kapazität - Geplante Stunden
```

---

## Testing & Debugging

### **Test-Command ausführen:**
```bash
php artisan moco:test-utilization
```

Zeigt für die ersten 3 Mitarbeiter mit MOCO-ID:
- User-Daten (work_schedule)
- Alle zugewiesenen Projekte
- Contract-Details (hours_per_week)
- Berechnete Auslastung

### **Für einen spezifischen Mitarbeiter:**
```bash
php artisan moco:test-utilization {employee_id}
```

### **Logs überprüfen:**
```bash
tail -f storage/logs/laravel.log
```

Suche nach:
- `MOCO: User {id} work_schedule = ...`
- `MOCO: User {id} planned hours = ...`
- `MOCO: Found X assigned projects for user ...`

---

## Fallback-Strategie

1. **Kapazität:**
   - Primär: MOCO `work_schedule` (Summe aller Wochentage)
   - Sekundär: MOCO `custom_properties['Wochenkapazität']`
   - Tertiär: Lokale DB `Employee.weekly_capacity`
   - Default: 40h

2. **Geplante Stunden:**
   - Primär: MOCO Contracts `hours_per_week`
   - Fallback: 0h (keine Zuweisungen)

3. **Bei API-Fehler:**
   - Verwendet lokale Datenbank-Werte
   - Zeigt 0% Auslastung
   - Logged Warnung

---

## Farbcodierung in der UI

| Auslastung | Farbe | Bedeutung |
|-----------|-------|-----------|
| 0-70% | 🟢 Grün | Unterausgelastet |
| 71-90% | 🟡 Gelb | Gut ausgelastet |
| 91-100%+ | 🔴 Rot | Überlastet |

---

## Bekannte Einschränkungen

1. **work_schedule Format:**
   - MOCO kann verschiedene Formate verwenden
   - Aktuell unterstützt: Array mit Wochentagen
   - Falls nicht vorhanden: Fallback auf custom_properties oder lokale DB

2. **Contracts ohne hours_per_week:**
   - Werden als 0h gezählt
   - Projekt wird in der Zuordnung angezeigt, zählt aber nicht zur Auslastung

3. **Performance:**
   - Lädt für jeden Mitarbeiter MOCO-Daten
   - Bei vielen Mitarbeitern kann Ladezeit länger sein
   - Caching könnte implementiert werden (TODO)

---

## Nächste Schritte (Optional)

1. **Caching implementieren:**
   ```php
   Cache::remember("moco_utilization_{$employee->id}", 3600, function() {
       // MOCO API Calls
   });
   ```

2. **Abwesenheiten berücksichtigen:**
   - MOCO Absences API integrieren
   - Verfügbare Stunden reduzieren während Urlaub/Krankheit

3. **Historische Auslastung:**
   - Auslastung über Zeitverlauf darstellen
   - Trendanalyse

4. **Export-Funktion:**
   - CSV/Excel Export mit Auslastungsdaten aus MOCO

---

## Troubleshooting

### Problem: Auslastung zeigt 0% obwohl Projekte zugewiesen sind

**Lösung:**
1. Test-Command ausführen: `php artisan moco:test-utilization {employee_id}`
2. Prüfen ob:
   - Projekt `active = true` ist
   - Projekt im aktuellen Zeitraum liegt (start_date/finish_date)
   - Contract `hours_per_week` gesetzt ist
   - User-ID im Contract korrekt ist

### Problem: Kapazität zeigt 40h obwohl Mitarbeiter Teilzeit ist

**Lösung:**
1. In MOCO prüfen ob `work_schedule` gesetzt ist
2. Alternativ: In lokaler DB `Employee.weekly_capacity` anpassen
3. Oder: MOCO `custom_properties['Wochenkapazität']` setzen

### Problem: Langsame Ladezeit

**Lösung:**
1. Caching implementieren (siehe oben)
2. Nur aktive Mitarbeiter laden
3. MOCO API Rate Limits prüfen

---

## Support

Bei Fragen oder Problemen:
1. Logs prüfen: `storage/logs/laravel.log`
2. Test-Command ausführen
3. MOCO API Dokumentation konsultieren: https://github.com/hundertzehn/mocoapp-api-docs























