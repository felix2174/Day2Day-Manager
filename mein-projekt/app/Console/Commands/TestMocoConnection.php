<?php

namespace App\Console\Commands;

use App\Services\MocoService;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Console\Command;

class TestMocoConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'moco:test-connection';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test die Verbindung zur MOCO API';

    /**
     * Execute the console command.
     */
    public function handle(MocoService $mocoService): int
    {
        $this->info('Teste MOCO API-Verbindung...');
        $this->newLine();

        try {
            // Versuche die Session-Informationen abzurufen (authentifizierter Benutzer)
            $sessionInfo = $mocoService->getSessionInfo();
            
            if ($sessionInfo !== null) {
                $this->info('✓ MOCO API-Verbindung erfolgreich.');
                $this->newLine();
                
                // Zeige Session-Informationen an, falls verfügbar
                if (isset($sessionInfo['company'])) {
                    $this->line('<fg=gray>Firma: ' . ($sessionInfo['company']['name'] ?? 'N/A') . '</>');
                }
                if (isset($sessionInfo['user'])) {
                    $userName = trim(
                        ($sessionInfo['user']['firstname'] ?? '') . ' ' . 
                        ($sessionInfo['user']['lastname'] ?? '')
                    );
                    $this->line('<fg=gray>Benutzer: ' . ($userName ?: 'N/A') . '</>');
                }
                
                $this->newLine();
                return Command::SUCCESS;
            } else {
                // Fallback zur bestehenden testConnection()-Methode
                $this->line('<fg=yellow>Session-Endpoint nicht verfügbar, verwende Fallback-Test...</>');
                
                if ($mocoService->testConnection()) {
                    $this->info('✓ MOCO API-Verbindung erfolgreich.');
                    return Command::SUCCESS;
                } else {
                    $this->error('✗ MOCO API-Verbindung fehlgeschlagen.');
                    $this->line('<fg=gray>Prüfe die Logs für weitere Details.</>');
                    return Command::FAILURE;
                }
            }
            
        } catch (ClientException $e) {
            // HTTP 4xx Fehler (Client-Fehler)
            $statusCode = $e->getResponse()->getStatusCode();
            $reasonPhrase = $e->getResponse()->getReasonPhrase();
            
            $this->error("✗ Verbindung fehlgeschlagen (Status: {$statusCode}): {$reasonPhrase}");
            $this->newLine();
            
            // Spezifische Fehlerbehandlung
            switch ($statusCode) {
                case 401:
                    $this->line('<fg=yellow>→ Der API-Key ist ungültig oder fehlt.</>');
                    $this->line('<fg=gray>  Prüfe MOCO_API_KEY in deiner .env-Datei.</>');
                    break;
                    
                case 403:
                    $this->line('<fg=yellow>→ Zugriff verweigert. Der API-Key hat keine ausreichenden Berechtigungen.</>');
                    $this->line('<fg=gray>  Stelle sicher, dass der API-Key die erforderlichen Rechte besitzt.</>');
                    break;
                    
                case 404:
                    $this->line('<fg=yellow>→ Endpoint nicht gefunden.</>');
                    $this->line('<fg=gray>  Prüfe MOCO_BASE_URL in deiner .env-Datei.</>');
                    break;
                    
                case 422:
                    $this->line('<fg=yellow>→ Ungültige Anfrage (Validation Error).</>');
                    break;
                    
                case 429:
                    $this->line('<fg=yellow>→ Zu viele Anfragen (Rate Limit erreicht).</>');
                    $this->line('<fg=gray>  Warte einige Minuten und versuche es erneut.</>');
                    break;
                    
                default:
                    $this->line('<fg=yellow>→ Client-Fehler. Prüfe deine API-Konfiguration.</>');
            }
            
            return Command::FAILURE;
            
        } catch (ServerException $e) {
            // HTTP 5xx Fehler (Server-Fehler)
            $statusCode = $e->getResponse()->getStatusCode();
            $reasonPhrase = $e->getResponse()->getReasonPhrase();
            
            $this->error("✗ Verbindung fehlgeschlagen (Status: {$statusCode}): {$reasonPhrase}");
            $this->newLine();
            $this->line('<fg=yellow>→ Server-Fehler auf MOCO-Seite. Versuche es später erneut.</>');
            
            return Command::FAILURE;
            
        } catch (ConnectException $e) {
            // Verbindungsfehler (Netzwerk/DNS)
            $this->error('✗ Verbindung fehlgeschlagen: Netzwerkfehler');
            $this->newLine();
            $this->line('<fg=yellow>→ Kann die MOCO API nicht erreichen.</>');
            $this->line('<fg=gray>  Prüfe deine Internetverbindung und MOCO_BASE_URL.</>');
            $this->line('<fg=gray>  Fehler: ' . $e->getMessage() . '</>');
            
            return Command::FAILURE;
            
        } catch (GuzzleException $e) {
            // Andere Guzzle-Fehler
            $this->error('✗ Verbindung fehlgeschlagen: ' . $e->getMessage());
            $this->newLine();
            $this->line('<fg=gray>Prüfe die Logs für weitere Details.</>');
            
            return Command::FAILURE;
            
        } catch (\Exception $e) {
            // Allgemeine Fehler (z.B. fehlende Config-Werte)
            $this->error('✗ Fehler beim Testen der Verbindung: ' . $e->getMessage());
            $this->newLine();
            
            if (str_contains($e->getMessage(), 'MOCO_API_KEY')) {
                $this->line('<fg=yellow>→ Füge MOCO_API_KEY zu deiner .env-Datei hinzu.</>');
            } elseif (str_contains($e->getMessage(), 'MOCO_BASE_URL')) {
                $this->line('<fg=yellow>→ Füge MOCO_BASE_URL zu deiner .env-Datei hinzu.</>');
            }
            
            return Command::FAILURE;
        }
    }
}
