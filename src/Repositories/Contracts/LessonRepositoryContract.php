<?php

namespace EscolaLms\Courses\Repositories\Contracts;

use EscolaLms\Core\Repositories\Contracts\BaseRepositoryContract;
use EscolaLms\Courses\Models\Lesson;
use Illuminate\Database\Eloquent\Collection;

interface LessonRepositoryContract extends BaseRepositoryContract
{
    public function deleteModel(Lesson $lesson): ?bool;

    public function allMain(
        array $search = [],
        ?int $skip = null,
        ?int $limit = null,
        array $columns = ['*'],
        string $orderDirection = 'asc',
        string $orderColumn = 'id'
    ): Collection;
}
