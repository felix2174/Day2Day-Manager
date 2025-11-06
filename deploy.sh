#!/bin/bash

# ============================================================================
# Day2Day-Manager Deployment Script
# ============================================================================
# Dieses Script führt alle notwendigen Schritte für ein Deployment aus:
# - Code aktualisieren
# - Dependencies installieren
# - Migrationen ausführen
# - Caches leeren
# - Frontend bauen
# ============================================================================

set -e  # Stoppe bei Fehlern

# Farben für Output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Funktionen
print_step() {
    echo -e "${BLUE}▶${NC} $1"
}

print_success() {
    echo -e "${GREEN}✓${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

# Header
echo ""
echo "============================================================================"
echo "  Day2Day-Manager - Deployment Script"
echo "============================================================================"
echo ""

# Prüfe ob wir im richtigen Verzeichnis sind
if [ ! -f "artisan" ]; then
    print_error "artisan Datei nicht gefunden! Bist du im Projektverzeichnis?"
    exit 1
fi

# Prüfe ob Git Repository vorhanden
if [ ! -d ".git" ]; then
    print_error "Git Repository nicht gefunden!"
    exit 1
fi

# Schritt 1: Code aktualisieren
print_step "Code von GitLab aktualisieren..."
if git pull origin main; then
    print_success "Code erfolgreich aktualisiert"
else
    print_error "Git Pull fehlgeschlagen!"
    print_warning "Prüfe ob es Konflikte gibt: git status"
    exit 1
fi

echo ""

# Schritt 2: Composer Dependencies
print_step "Composer Dependencies prüfen..."
if [ -f "composer.json" ]; then
    # Prüfe ob composer.json geändert wurde
    if git diff HEAD~1 composer.json > /dev/null 2>&1 || [ ! -d "vendor" ]; then
        print_step "  Dependencies installieren..."
        if composer install --no-dev --optimize-autoloader; then
            print_success "Composer Dependencies installiert"
        else
            print_error "Composer Install fehlgeschlagen!"
            exit 1
        fi
    else
        print_success "Composer Dependencies bereits aktuell"
    fi
else
    print_warning "composer.json nicht gefunden, überspringe Composer"
fi

echo ""

# Schritt 3: Datenbank-Migrationen
print_step "Datenbank-Migrationen prüfen..."
if php artisan migrate:status > /dev/null 2>&1; then
    PENDING=$(php artisan migrate:status | grep -c "Pending" || true)
    if [ "$PENDING" -gt 0 ]; then
        print_step "  Migrationen ausführen ($PENDING ausstehend)..."
        if php artisan migrate --force; then
            print_success "Migrationen erfolgreich ausgeführt"
        else
            print_error "Migrationen fehlgeschlagen!"
            print_warning "Prüfe Logs: tail -50 storage/logs/laravel.log"
            exit 1
        fi
    else
        print_success "Keine ausstehenden Migrationen"
    fi
else
    print_warning "Migration-Status konnte nicht geprüft werden"
fi

echo ""

# Schritt 4: Caches leeren
print_step "Caches leeren..."
if php artisan optimize:clear; then
    print_success "Caches erfolgreich geleert"
else
    print_error "Cache leeren fehlgeschlagen!"
    exit 1
fi

echo ""

# Schritt 5: Frontend-Assets bauen
print_step "Frontend-Assets bauen..."
if [ -f "package.json" ]; then
    if [ -d "node_modules" ]; then
        if npm run build; then
            print_success "Frontend-Assets erfolgreich gebaut"
        else
            print_error "Frontend-Build fehlgeschlagen!"
            print_warning "Versuche: npm install && npm run build"
            exit 1
        fi
    else
        print_warning "node_modules nicht gefunden, überspringe Frontend-Build"
        print_warning "Führe manuell aus: npm install && npm run build"
    fi
else
    print_warning "package.json nicht gefunden, überspringe Frontend-Build"
fi

echo ""

# Schritt 6: Production-Caches aufbauen
print_step "Production-Caches aufbauen..."
if php artisan config:cache && php artisan route:cache && php artisan view:cache; then
    print_success "Production-Caches erfolgreich aufgebaut"
else
    print_error "Cache-Aufbau fehlgeschlagen!"
    exit 1
fi

echo ""

# Zusammenfassung
echo "============================================================================"
echo -e "${GREEN}✓ Deployment erfolgreich abgeschlossen!${NC}"
echo "============================================================================"
echo ""
echo "Nächste Schritte:"
echo "  1. Website im Browser testen: https://daytoday.enodia-software.de"
echo "  2. Logs prüfen bei Problemen: tail -f storage/logs/laravel.log"
echo ""
echo "Bei Fehlern:"
echo "  - Cache leeren: php artisan optimize:clear"
echo "  - Debug-Modus: .env → APP_DEBUG=true"
echo "  - Logs prüfen: tail -100 storage/logs/laravel.log"
echo ""

