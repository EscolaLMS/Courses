<?php

namespace EscolaLms\Courses\Repositories\Contracts;

use EscolaLms\Core\Repositories\Contracts\BaseRepositoryContract;
use EscolaLms\Courses\Models\H5PUserProgress;
use EscolaLms\Courses\Models\Topic;
use Illuminate\Contracts\Auth\Authenticatable;

interface CourseH5PProgressRepositoryContract extends BaseRepositoryContract
{
    public function store(Topic $topic, Authenticatable $user, string $event, $data): H5PUserProgress;
}
