<?php

namespace EscolaLms\Courses\Repositories\Contracts;

use EscolaLms\Core\Repositories\Contracts\BaseRepositoryContract;
use EscolaLms\Courses\Models\Lesson;

interface LessonRepositoryContract extends BaseRepositoryContract
{
    public function deleteModel(Lesson $lesson): ?bool;
}
