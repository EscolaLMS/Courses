<?php

namespace EscolaLms\Courses\Repositories;

use Error;
use EscolaLms\Courses\Exceptions\TopicException;
use EscolaLms\Courses\Http\Requests\CreateTopicAPIRequest;
use EscolaLms\Courses\Http\Requests\UpdateTopicAPIRequest;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Repositories\Contracts\TopicRepositoryContract;
use EscolaLms\TopicTypes\Models\Contracts\TopicContentContract;
use EscolaLms\TopicTypes\Models\Contracts\TopicFileContentContract;
use EscolaLms\TopicTypes\Models\TopicContent\AbstractTopicFileContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * Class TopicRepository.
 *
 * @version April 27, 2021, 11:21 am UTC
 */
class TopicRepository extends BaseRepository implements TopicRepositoryContract
{
    /**
     * @var array
     *            All possible classes that can store content
     */
    private array $contentClasses = [];

    /**
     * @var array
     *            All possible classes that can store content
     */
    private array $resourceClasses = [];

    /**
     * @var array
     */
    protected $fieldSearchable = [
        'title',
        'lesson_id',
        'topicable_id',
        'topicable_type',
        'order',
        'active',
        'boolean',
    ];

    /**
     * Return searchable fields.
     *
     * @return array
     */
    public function getFieldsSearchable()
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model.
     **/
    public function model()
    {
        return Topic::class;
    }

    /**
     * @param string $class fullname of a class that can be content
     *
     * @return array list of unique classes
     */
    public function registerContentClass(string $class): array
    {
        if (!in_array($class, $this->contentClasses) && class_exists($class) && (is_a($class, TopicContentContract::class, true))) {
            $this->contentClasses[] = $class;
        }

        return $this->contentClasses;
    }

    public function registerContentClasses(array $classes): array
    {
        foreach ($classes as $class) {
            $this->registerContentClass($class);
        }

        return $this->contentClasses;
    }

    public function registerResourceClass(string $topicTypeClass, string $resourceClass, string $type = 'client'): array
    {
        if (!isset($this->resourceClasses[$type][$topicTypeClass]) && class_exists($resourceClass) && (is_a($resourceClass, JsonResource::class, true))) {
            $this->resourceClasses[$type][$topicTypeClass] = $resourceClass;
        }

        return $this->resourceClasses[$type];
    }

    public function registerResourceClasses(string $topicTypeClass, array $resourceClasses): array
    {
        foreach ($resourceClasses as $type => $resourceClass) {
            $this->registerResourceClass($topicTypeClass, $resourceClass, $type);
        }

        return $this->resourceClasses;
    }

    public function getResourceClass(string $topicTypeClass = null, string $type = 'client'): string
    {
        if (isset($this->resourceClasses[$type][$topicTypeClass])) {
            return $this->resourceClasses[$type][$topicTypeClass];
        }

        return '';
    }

    public function unregisterContentClass(string $class): array
    {
        if (($key = array_search($class, $this->contentClasses)) !== false) {
            unset($this->contentClasses[$key]);
        }

        return $this->contentClasses;
    }

    public function availableContentClasses(): array
    {
        return $this->contentClasses;
    }

    public function getById($id): Topic
    {
        return $this->model->newQuery()->where('id', '=', $id)->first();
    }

    /**
     * Create model record.
     */
    public function create(array $input): Topic
    {
        $input = [
            'title' => $input['title'],
            'lesson_id' => $input['lesson_id'],
            'order' => $input['order'] ?? 0,
            'active' => true,
            'preview' => false,
            'summary' => isset($input['summary']) ? $input['summary'] : null,
            'introduction' => isset($input['introduction']) ? $input['introduction'] : null,
            'description' => isset($input['description']) ? $input['description'] : null,
            'can_skip' => (bool) ($input['can_skip'] ?? false),
        ];

        $model = $this->model->newInstance($input);
        $model->save();

        return $model;
    }

    /**
     * Update model record for given id.
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Model
     */
    public function update(array $input, int $id): Topic
    {
        $query = $this->model->newQuery();

        $model = $query->with('topicable')->findOrFail($id);

        $model->fill($input);

        $model->save();

        return $model;
    }

    public function createFromRequest(CreateTopicAPIRequest $request): Topic
    {
        $validated = $request->validated();

        /** @var Topic $topic */
        $topic = $this->model->newInstance([
            'title' => $validated['title'],
            'lesson_id' => $validated['lesson_id'],
            'order' => $validated['order'] ?? 0,
            'active' => $validated['active'] ?? true,
            'preview' => $validated['preview'] ?? false,
            'summary' => $validated['summary'] ?? null,
            'introduction' => $validated['introduction'] ?? null,
            'description' => $validated['description'] ?? null,
            'can_skip' => $validated['can_skip'] ?? false,
            'json' => empty($validated['json']) ? null : json_decode($validated['json']),
        ]);
        $topic->save();

        $this->createTopicContentModelFromRequest($request, $topic);

        return $topic->loadMissing('topicable');
    }

    public function updateFromRequest(UpdateTopicAPIRequest $request): Topic
    {
        $topic = $request->getTopic()->loadMissing('topicable');

        if ($request->has('topicable_type')) {
            $class = $request->input('topicable_type');

            if (!in_array($class, $this->contentClasses)) {
                throw new Error("Type '$class' is not allowed");
            }

            if ($topic->topicable && $class === get_class($topic->topicable) && $request->hasAny(array_keys($class::rules()))) {
                $this->updateTopicContentModelFromRequest($request, $topic->topicable);
            } else {
                $topicContent = $this->createTopicContentModelFromRequest($request, $topic);
                $topic->topicable()->associate($topicContent);
            }
        }

        $validated = $request->validated();
        if (!empty($validated['json'])) {
            $validated['json'] = json_decode($validated['json'], true);
        }
        $topic->fill($validated);
        $topic->save();

        return $topic;
    }

    /**
     * @return TopicContentContract|TopicFileContentContract|Model
     *
     * @throws TopicException
     */
    private function createTopicContentModelFromRequest(FormRequest $request, Topic $topic): Model
    {
        $class = $request->input('topicable_type');

        if (!in_array($class, $this->contentClasses)) {
            throw new Error("Type '$class' is not allowed");
        }

        $model = new $class();
        assert($model instanceof TopicContentContract);
        assert($model instanceof Model);

        $validator = Validator::make($request->all(), $model::rules());
        if ($validator->fails()) {
            throw new TopicException(TopicException::CONTENT_VALIDATION, $validator->errors()->toArray());
        }

        $attributes = $validator->validated();
        if ($model instanceof TopicFileContentContract) {
            $attributes = array_filter($attributes, fn ($attribute_key) => !in_array($attribute_key, $model->getFileKeyNames()), ARRAY_FILTER_USE_KEY);
            $model->storeUploadsFromRequest($request, $topic->storage_directory);
        }
        $model->fill($attributes);
        $model->save();
        $model->topic()->save($topic);

        return $model;
    }

    /**
     * @return TopicContentContract|TopicFileContentContract|Model
     *
     * @throws TopicException
     */
    private function updateTopicContentModelFromRequest(FormRequest $request, TopicContentContract $topicContent): Model
    {
        assert($topicContent instanceof Model);

        $rules = $this->getRulesForTopicContentUpdate($request, $topicContent);

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            throw new TopicException(TopicException::CONTENT_VALIDATION, $validator->errors()->toArray());
        }

        $attributes = $validator->validated();
        if ($topicContent instanceof TopicFileContentContract) {
            Arr::forget($attributes, $topicContent->getFileKeyNames());
            $topicContent->storeUploadsFromRequest($request);
        }
        // we only update validated attributes and we removed validations for fields that would cause problems :)
        $topicContent->fill($attributes);
        $topicContent->save();

        return $topicContent;
    }

    private function getRulesForTopicContentUpdate(FormRequest $request, TopicContentContract $topicContent)
    {
        // we want to do partial update, so we add 'sometimes' to all rules
        $partialRules = array_map(fn ($field_rules) => is_array($field_rules) ? array_merge(['sometimes'], $field_rules) : 'sometimes' . $field_rules, $topicContent::rules());

        // don't try to validate file keys in request if they don't contain file during topic / topic content update
        if ($topicContent instanceof TopicFileContentContract) {
            foreach ($topicContent->getFileKeyNames() as $fileKeyName) {
                if (!$request->hasFile($fileKeyName)) {
                    unset($partialRules[$fileKeyName]);
                }
            }
        }

        return $partialRules;
    }

    public function delete(int $id): ?bool
    {
        $topic = $this->findWith($id, ['*'], ['topicable']);

        return !is_null($topic) && $this->deleteModel($topic);
    }

    public function deleteModel(Topic $topic): ?bool
    {
        if ($topic->delete()) {
            $topicable = $topic->topicable;
            if (is_a($topicable, AbstractTopicFileContent::class)) {
                /** @var AbstractTopicFileContent $topicable */
                $path = Storage::path($topicable->generateStoragePath());
                try {
                    File::cleanDirectory($path);
                    Storage::deleteDirectory($path);
                } catch (\Throwable $th) {
                }
            }
        }

        return true;
    }
}
