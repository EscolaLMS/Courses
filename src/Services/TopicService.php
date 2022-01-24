<?php

namespace EscolaLms\Courses\Services;

use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Models\TopicResource;
use EscolaLms\Courses\Repositories\Contracts\TopicRepositoryContract;
use EscolaLms\Courses\Repositories\Contracts\TopicResourceRepositoryContract;
use EscolaLms\Courses\Services\Contracts\TopicServiceContract;
use EscolaLms\TopicTypes\Models\TopicContent\AbstractTopicFileContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class TopicService implements TopicServiceContract
{
    protected TopicRepositoryContract $topicRepository;
    protected TopicResourceRepositoryContract $topicResourceRep;

    public function __construct(
        TopicRepositoryContract $topicRepository,
        TopicResourceRepositoryContract $topicResourceRep
    ) {
        $this->topicRepository = $topicRepository;
        $this->topicResourceRep = $topicResourceRep;
    }

    public function cloneTopic(Topic $topic): Model
    {
        $clonedTopicArray = $topic->replicate()->toArray();
        unset($clonedTopicArray['order']);
        $clonedTopic = $this->topicRepository->create($clonedTopicArray);
        $clonedTopicable = $topic->topicable->replicate();

        if ($clonedTopicable instanceof AbstractTopicFileContent) {
            foreach ($clonedTopicable->getFileKeyNames() as $fileKeyName) {
                if (Storage::exists($clonedTopicable->{$fileKeyName})) {
                    $to = $clonedTopic->getStorageDirectoryAttribute() . basename($clonedTopicable->{$fileKeyName});
                    Storage::copy($clonedTopicable->{$fileKeyName}, $to);
                    $clonedTopicable->{$fileKeyName} = $to;
                }
            }
        }

        $clonedTopicable->save();
        $clonedTopic->topicable_id = $clonedTopicable->getKey();
        $clonedTopic->topicable_type = get_class($clonedTopicable);
        $clonedTopic->save();

        foreach ($topic->resources as $resource) {
            if (Storage::exists($resource->path)) {
                $this->cloneTopicResource($clonedTopic, $resource);
            }
        }

        return $clonedTopic;
    }

    private function cloneTopicResource(Model $clonedTopic, TopicResource $topicResource): Model
    {
        $pathFrom = $topicResource->path;
        $pathTo = $clonedTopic->getStorageDirectoryAttribute() . 'resources' . DIRECTORY_SEPARATOR . $topicResource->name;

        Storage::copy($pathFrom, $pathTo);

        $topicResourceData = $topicResource->replicate()->toArray();
        $topicResourceData['topic_id'] = $clonedTopic->getKey();
        $topicResourceData['path'] = $pathTo;

        return $this->topicResourceRep->create($topicResourceData);
    }
}
