<?php

namespace App\Services\Moco;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class MocoHttpClient
{
    private string $base;
    private string $token;
    private int $timeout;
    private int $ttl;

    public function __construct()
    {
        $this->base    = sprintf('https://%s.mocoapp.com/api/v1', config('moco.subdomain'));
        $this->token   = (string) config('moco.token');
        $this->timeout = (int) config('moco.timeout', 10);
        $this->ttl     = (int) config('moco.cache_ttl', 120);
    }

    public function get(string $path, array $query = [], bool $cached = true): array
    {
        $url = rtrim($this->base, '/') . '/' . ltrim($path, '/');
        $key = 'moco:GET:' . md5($url . '|' . json_encode($query));

        $fetch = function () use ($url, $query) {
            $resp = Http::withToken($this->token)
                ->timeout($this->timeout)
                ->retry(3, 500)   // 3 Versuche, 500 ms Backoff
                ->get($url, $query);

            $this->logRate($resp);
            $resp->throw();
            return $resp->json();
        };

        return $cached ? Cache::remember($key, now()->addSeconds($this->ttl), $fetch) : $fetch();
    }

    private function logRate($resp): void
    {
        $remaining = $resp->header('X-RateLimit-Remaining');
        if ($remaining !== null && (int) $remaining <= 5) {
            Log::channel('moco')->warning('MOCO rate low', [
                'remaining' => (int) $remaining,
                'limit'     => $resp->header('X-RateLimit-Limit'),
                'reset'     => $resp->header('X-RateLimit-Reset'),
            ]);
        }
    }
}
