<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MocoService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MocoTestConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'moco:test-connection {--store : Store the successful check timestamp in cache}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testet die Verbindung zur MOCO API und liefert einen konsistenten Status.';

    /**
     * Execute the console command.
     */
    public function handle(MocoService $mocoService): int
    {
        $this->info('Starte MOCO-Verbindungstest ...');

        try {
            $connected = $mocoService->testConnection();

            if ($connected) {
                $this->info('✅ Verbindung zur MOCO API erfolgreich.');
                Log::info('MOCO test-connection successful');

                if ($this->option('store')) {
                    Cache::put('moco:last_connection_check', now(), now()->addHours(24));
                    $this->line('Timestamp wurde in Cache gespeichert.');
                }

                return Command::SUCCESS;
            }

            $this->error('❌ Verbindung zur MOCO API fehlgeschlagen.');
            Log::warning('MOCO test-connection failed: API nicht erreichbar');
            return Command::FAILURE;
        } catch (\Throwable $e) {
            $this->error('⚠️ Fehler beim Verbindungstest: ' . $e->getMessage());
            Log::error('MOCO test-connection exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return Command::FAILURE;
        }
    }
}
