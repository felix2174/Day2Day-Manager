<?php
declare(strict_types=1);

namespace App\Services\Moco;

final class EmployeesService
{
    public function __construct(private readonly MocoHttpClient $http) {}

    /** Holt einen MOCO-User per ID */
    public function getById(int|string $id): array
    {
        return $this->http->get("users/{$id}");
    }

    /** Liste mit optionalen Filtern (page, active, search, etc.) */
    public function list(array $query = []): array
    {
        return $this->http->get('users', $query);
    }
}
