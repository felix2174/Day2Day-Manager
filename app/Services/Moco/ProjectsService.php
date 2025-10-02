<?php
declare(strict_types=1);

namespace App\Services\Moco;

use Carbon\Carbon;

final class ProjectsService
{
    public function __construct(private readonly MocoHttpClient $http) {}

    /** Liest eine Seite Projekte. Optional seit Zeitstempel (ISO-8601). */
    public function list(int $page = 1, ?string $updatedSinceIso = null): array
    {
        $q = [
            'page'     => $page,
            'per_page' => (int) config('moco.page_size', 100),
        ];
        if ($updatedSinceIso) {
            $q['updated_since'] = $updatedSinceIso;
        }
        return $this->http->get('projects', $q);
    }

    /** Streamt alle Seiten, optional seit Zeitpunkt. */
    public function listAll(?Carbon $updatedSince = null): \Generator
    {
        $page = 1;
        $iso  = $updatedSince?->toIso8601ZuluString();
        $size = (int) config('moco.page_size', 100);

        do {
            $items = $this->list($page, $iso);
            foreach ($items as $row) {
                yield $row;
            }
            $count = is_countable($items) ? count($items) : 0;
            $page++;
        } while ($count === $size);
    }

    /** Einzelnes Projekt. */
    public function getById(int|string $id): array
    {
        return $this->http->get("projects/{$id}");
    }
}
