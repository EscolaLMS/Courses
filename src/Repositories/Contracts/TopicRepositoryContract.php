<?php

namespace EscolaLms\Courses\Repositories\Contracts;

use EscolaLms\Core\Repositories\Contracts\BaseRepositoryContract;
use EscolaLms\Courses\Http\Requests\CreateTopicAPIRequest;
use EscolaLms\Courses\Http\Requests\UpdateTopicAPIRequest;
use EscolaLms\Courses\Models\Topic;

interface TopicRepositoryContract extends BaseRepositoryContract
{
    public function getById($id): Topic;

    public function createFromRequest(CreateTopicAPIRequest $request): Topic;

    public function updateFromRequest(UpdateTopicAPIRequest $request): Topic;

    public static function registerContentClass(string $class): array;
    public static function unregisterContentClass(string $class): array;
}
