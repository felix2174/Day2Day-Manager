<?php

namespace App\Console\Commands;

use App\Services\MocoService;
use Illuminate\Console\Command;

class ProbeMocoUsers extends Command
{
    protected $signature = 'moco:probe-users {--active : Filter active users if supported}';

    protected $description = 'Probe MOCO user endpoints (users/people/staff) and print counts';

    public function handle(MocoService $mocoService): int
    {
        $params = [];
        if ($this->option('active')) { $params['active'] = true; }

        $endpoints = ['users', 'people', 'staff'];
        foreach ($endpoints as $ep) {
            try {
                $ref = new \ReflectionClass($mocoService);
                $clientProp = $ref->getProperty('client');
                $clientProp->setAccessible(true);
                /** @var \GuzzleHttp\Client $client */
                $client = $clientProp->getValue($mocoService);

                $res = $client->get($ep, ['query' => $params + ['limit' => 50]]);
                $status = $res->getStatusCode();
                $data = json_decode($res->getBody()->getContents(), true);
                $count = is_array($data) ? count($data) : 0;
                $this->info("Endpoint /{$ep} -> status {$status}, count {$count}");
            } catch (\Throwable $e) {
                $this->error("Endpoint /{$ep} failed: " . $e->getMessage());
            }
        }
        return Command::SUCCESS;
    }
}





























