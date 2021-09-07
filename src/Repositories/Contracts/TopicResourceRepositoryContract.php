<?php

namespace EscolaLms\Courses\Repositories\Contracts;

use EscolaLms\Core\Repositories\Contracts\BaseRepositoryContract;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Models\TopicResource;
use Illuminate\Http\UploadedFile;

interface TopicResourceRepositoryContract extends BaseRepositoryContract
{
    public function storeUploadedResourceForTopic(Topic $topic, UploadedFile $file): TopicResource;

    public function rename(int $id, string $name): bool;
    public function renameModel(TopicResource $model, string $name): bool;
}
