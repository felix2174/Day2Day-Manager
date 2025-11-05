@echo off
REM ========================================
REM MySQL Backup Script for Day2Day-Manager
REM ========================================

setlocal enabledelayedexpansion

REM Konfiguration
set MYSQL_BIN=C:\xampp\mysql\bin\mysqldump.exe
set MYSQL_USER=root
set MYSQL_PORT=3307
set DB_NAME=day2day
set BACKUP_DIR=C:\Backups\Day2Day-MySQL

REM Datum & Zeit für Dateinamen
set DATE=%date:~-4,4%-%date:~-10,2%-%date:~-7,2%
set TIME=%time:~0,2%-%time:~3,2%
set TIME=%TIME: =0%
set TIMESTAMP=%DATE%_%TIME%

REM Backup-Verzeichnis erstellen (falls nicht vorhanden)
if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

REM Backup durchführen
echo ========================================
echo Day2Day-Manager MySQL Backup
echo ========================================
echo Start: %date% %time%
echo.

set BACKUP_FILE=%BACKUP_DIR%\day2day_backup_%TIMESTAMP%.sql

echo Erstelle Backup: %BACKUP_FILE%
"%MYSQL_BIN%" -u %MYSQL_USER% --port=%MYSQL_PORT% %DB_NAME% > "%BACKUP_FILE%" 2>&1

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ✅ Backup erfolgreich erstellt!
    echo 📁 Datei: %BACKUP_FILE%
    
    REM Dateigröße anzeigen
    for %%A in ("%BACKUP_FILE%") do set SIZE=%%~zA
    set /A SIZE_KB=!SIZE! / 1024
    echo 📦 Größe: !SIZE_KB! KB
    
    REM Alte Backups löschen (älter als 30 Tage)
    echo.
    echo 🗑️  Lösche alte Backups (älter als 30 Tage)...
    forfiles /P "%BACKUP_DIR%" /M *.sql /D -30 /C "cmd /c del @path" 2>nul
    
) else (
    echo.
    echo ❌ Backup fehlgeschlagen!
    echo Fehlercode: %ERRORLEVEL%
    echo Prüfe MySQL-Verbindung und Zugangsdaten.
)

echo.
echo Ende: %date% %time%
echo ========================================

REM Log-Datei erstellen
echo %date% %time% - Backup: %BACKUP_FILE% >> "%BACKUP_DIR%\backup.log"

pause
