<?php

namespace EscolaLms\Courses\Repositories\Contracts;

use EscolaLms\Core\Repositories\Contracts\BaseRepositoryContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface CourseRepositoryContract extends BaseRepositoryContract
{
    public function allQueryBuilder(array $search = [], array $criteria = []): Builder;

    public function queryAll(): Builder;

    public function findTutors(): Collection;
}
