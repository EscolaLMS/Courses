<?php

namespace EscolaLms\Courses\Repositories\Contracts;

use EscolaLms\Core\Repositories\Contracts\BaseRepositoryContract;
use EscolaLms\Courses\Models\Topic;

interface TopicRepositoryContract extends BaseRepositoryContract
{
    public function getById($id): Topic;
}
