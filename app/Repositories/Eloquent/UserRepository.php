<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Collection;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function getActiveUsers(): Collection
    {
        return $this->model
            ->with('role')
            ->where('is_active', true)
            ->orderBy('last_name')
            ->get();
    }

    public function getUsersByRole(int $roleId): Collection
    {
        return $this->model
            ->where('role_id', $roleId)
            ->where('is_active', true)
            ->orderBy('last_name')
            ->get();
    }

    public function getAdministrators(): Collection
    {
        return $this->model
            ->whereHas('role', fn($q) => $q->where('name', 'Administrator'))
            ->where('is_active', true)
            ->get();
    }
}
