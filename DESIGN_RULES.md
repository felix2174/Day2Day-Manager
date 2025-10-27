# Design & Layout Regeln für Laravel-Projekt

## Allgemeine Design-Prinzipien

### 1. Container-Struktur
- **Hauptcontainer**: `<div style="width: 100%; margin: 0; padding: 0;">`
- **Karten**: `<div style="background: white; padding: 20px; margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">`

### 2. Farben & Typography
- **Haupttext**: `color: #111827` (schwarz-grau)
- **Sekundärtext**: `color: #6b7280` (grau)
- **Überschriften**: `font-size: 24px; font-weight: bold; color: #111827` (H1)
- **Unterüberschriften**: `font-size: 18px; font-weight: 600; color: #111827` (H2)

### 3. Buttons & Links
```html
<a href="..." style="background: #ffffff; color: #374151; padding: 10px 20px; border-radius: 12px; text-decoration: none; font-size: 14px; font-weight: 500; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 8px;">
```

### 4. Status-Badges
- **Aktiv**: `background: #dcfce7; color: #166534`
- **Planung**: `background: #dbeafe; color: #1e40af`
- **Abgeschlossen**: `background: #e0e7ff; color: #3730a3`

### 5. Grid-Layouts
```html
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
```

### 6. Info-Karten
```html
<div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px;">
    <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; font-weight: 500;">LABEL</div>
    <div style="font-size: 16px; font-weight: 600; color: #111827;">WERT</div>
</div>
```

### 7. Statistiken-Karten
```html
<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; text-align: center;">
    <div style="font-size: 2rem; font-weight: bold; color: #2563eb; margin-bottom: 4px;">WERT</div>
    <div style="font-size: 14px; color: #6b7280;">LABEL</div>
</div>
```

### 8. Progress Bars
```html
<div style="background: #e5e7eb; height: 8px; border-radius: 4px; overflow: hidden;">
    <div style="background: #2563eb; height: 100%; width: PROZENT%; transition: width 0.3s;"></div>
</div>
```

### 9. Alerts
- **Erfolg**: `background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534`
- **Fehler**: `background: #fef2f2; border: 1px solid #fecaca; color: #dc2626`

### 10. Tabellen
```html
<table style="width: 100%; border-collapse: collapse;">
    <thead style="background: #f9fafb;">
        <tr>
            <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb;">SPALTE</th>
        </tr>
    </thead>
</table>
```

### 11. Avatar/Profile Bilder
```html
<div style="width: 40px; height: 40px; background: linear-gradient(135deg, #3b82f6, #8b5cf6); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px;">
    BUCHSTABE
</div>
```

### 12. Statistik-Farben
- **Alle Zahlen**: `color: #111827` (einheitliche Hauptfarbe)
- **Nur Bottlenecks**: Rot (`#dc2626`) wenn > 0, sonst Hauptfarbe
- **KEINE verschiedenen Farben** für verschiedene Statistik-Typen

## WICHTIG: 
- **KEINE externen CSS-Frameworks** (Bootstrap, Tailwind CSS)
- **NUR Inline-Styles** verwenden
- **Konsistente Farbpalette** einhalten
- **Einheitliche Abstände**: 8px, 12px, 16px, 20px, 24px
- **Border-Radius**: 4px (klein), 8px (mittel), 12px (groß)
- **Schatten**: `box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1)`
- **Transitions**: `transition: all 0.2s ease`
- **KEINE Emojis/Icons** in Überschriften oder Texten
