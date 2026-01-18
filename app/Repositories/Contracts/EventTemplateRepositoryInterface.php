<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface EventTemplateRepositoryInterface extends BaseRepositoryInterface
{
    public function getActive(): Collection;

    public function getWithModules(int $id);
}
