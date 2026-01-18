<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface VotingTypeRepositoryInterface extends BaseRepositoryInterface
{
    public function getActive(): Collection;

    public function getByCategory(string $category): Collection;

    public function findByCode(string $code);
}
