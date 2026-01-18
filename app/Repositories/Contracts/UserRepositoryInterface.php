<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Support\Collection;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    public function findByEmail(string $email): ?User;

    public function getActiveUsers(): Collection;

    public function getUsersByRole(int $roleId): Collection;

    public function getAdministrators(): Collection;
}
