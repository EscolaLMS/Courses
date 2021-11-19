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

    public function registerContentClass(string $class): array;

    public function registerContentClasses(array $classes): array;

    public function unregisterContentClass(string $class): array;

    public function registerResourceClass(string $topicTypeClass, string $resourceClass, string $type = 'client'): array;

    public function registerResourceClasses(string $topicTypeClass, array $resourceClasses): array;

    public function getResourceClass(string $topicTypeClass = null, string $type = 'client'): string;

    public function deleteModel(Topic $topic): ?bool;
}
