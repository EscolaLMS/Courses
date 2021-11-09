<?php

namespace EscolaLms\Courses\Http\Resources;

use EscolaLms\TopicTypes\Http\Resources\TopicType\Contacts\TopicTypeResourceContract;
use EscolaLms\TopicTypes\Models\Contracts\TopicContentContract;
use Illuminate\Http\Resources\Json\JsonResource;

class TopicAdminResource extends JsonResource
{
    /**
     * @var array
     *            All possible classes that can store content
     */
    private static array $contentClasses = [
    ];

    /**
     * @param string $class fullname of a class that can be content
     *
     * @return array list of unique classes
     */
    public static function registerContentClass(string $modelClass, string $resourceClass): array
    {
        if (
            class_exists($modelClass)
            && (is_a($modelClass, TopicContentContract::class, true))
            && class_exists($resourceClass)
            && (is_a($resourceClass, TopicTypeResourceContract::class, true))
            ) {
            self::$contentClasses[$modelClass] = $resourceClass;
        }

        return self::$contentClasses;
    }

    public static function unregisterContentClass(string $class): array
    {
        unset(self::$contentClasses[$class]);

        return self::$contentClasses;
    }

    public static function availableContentClasses(): array
    {
        return self::$contentClasses;
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        $topicable = $this->topicable;

        if (array_key_exists($this->topicable_type, self::$contentClasses)) {
            $resource = new self::$contentClasses[$this->topicable_type]($this->topicable);
            $topicable = $resource->toArray($request);
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'lesson_id' => $this->lesson_id,
            'active' => $this->active,
            'preview' => $this->preview,
            'topicable_id' => $this->topicable_id,
            'topicable_type' => $this->topicable_type,
            'topicable' => $topicable,
            'summary' => $this->summary,
            'introduction' => $this->introduction,
            'description' => $this->description,
            'resources' => TopicResourceResource::collection($this->resources),
            'order' => $this->order,
            'json' => $this->json,
            'can_skip' => $this->can_skip,
        ];
    }
}
