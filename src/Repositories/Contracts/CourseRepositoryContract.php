<?php

namespace EscolaLms\Courses\Repositories\Contracts;

use EscolaLms\Core\Repositories\Contracts\BaseRepositoryContract;
use Illuminate\Database\Eloquent\Builder;

interface CourseRepositoryContract extends BaseRepositoryContract
{
    public function allQueryBuilder(array $search = [], ?int $skip = null, ?int $limit = null, array $criteria = []): Builder;

    public function queryAll(): Builder;
}
