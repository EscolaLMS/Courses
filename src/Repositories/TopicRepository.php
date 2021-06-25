<?php

namespace EscolaLms\Courses\Repositories;

use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Repositories\BaseRepository;
use EscolaLms\Courses\Repositories\Contracts\TopicRepositoryContract;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use EscolaLms\Courses\Exceptions\TopicException;
use Error;

/**
 * Class TopicRepository
 * @package EscolaLms\Courses\Repositories
 * @version April 27, 2021, 11:21 am UTC
*/

class TopicRepository extends BaseRepository implements TopicRepositoryContract
{

    /**
     * @var array
     * All possible classes that can store content
     */
    private static array $contentClasses = [];

    /**
     * @var array
     */
    protected $fieldSearchable = [
        'title',
        'lesson_id',
        'topicable_id',
        'topicable_type',
        'order'
    ];

    /**
     * Return searchable fields
     *
     * @return array
     */
    public function getFieldsSearchable()
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return Topic::class;
    }

    /**
     * @param string $class fullname of a class that can be content
     * @return array list of unique classes
     */
    public static function registerContentClass(string $class):array
    {
        if (!in_array($class, self::$contentClasses)) {
            self::$contentClasses[] = $class;
        }
        return self::$contentClasses;
    }

    public function availableContentClasses(): array
    {
        return self::$contentClasses;
    }

    private function getContentModel($classType, $input)
    {
        if (!in_array($classType, self::$contentClasses)) {
            throw new Error("Type '$classType' is not allowed");
        }

        $contentModel = App::make($classType);
        $contentFillable = $contentModel->fillable;

        // get only input by Models fillable attribute
        $contentInput = array_filter($input, function ($key) use ($contentFillable) {
            return in_array($key, $contentFillable);
        }, ARRAY_FILTER_USE_KEY);

        // Validate against Models `rules` array
        $validator = Validator::make($contentInput, $classType::$rules);

        if ($validator->fails()) {
            throw new TopicException(TopicException::CONTENT_VALIDATION, $validator->errors()->toArray());
        }

        return [
            'model' => $contentModel,
            'input' => $contentInput
        ];
    }

    public function getById($id) : Topic
    {
        return $this->model->newQuery()->where('id', '=', $id)->first();
    }

    /**
     * Create model record
     *
     * @param array $input
     *
     * @return Topic
     */
    public function create(array $input): Topic
    {
        // initialise mode from allowed list string
        $classType = $input["topicable_type"];
        $content = $this->getContentModel($classType, $input);

        // saves topic to gets its ID, for later referncing
        $input = [
            'title' => $input['title'],
            'lesson_id' => $input['lesson_id'],
            'order' => $input['order'] ?? 0,
        ];

        $model = $this->model->newInstance($input);
        $model->save();

        // check if `createResourseFromRequest` exisits on `Model` if so then convert input
        if (method_exists($classType, 'createResourseFromRequest')) {
            $content['input'] = $classType::createResourseFromRequest($content['input'], $model->id);
        }

        // create related 1:1 content and associate with topic
        $content['model']->fill($content['input']);
        $content['model']->save();
        $model->topicable()->associate($content['model'])->save();
        $model->load('topicable');
        return $model;
    }


    /**
     * Update model record for given id
     *
     * @param array $input
     * @param int $id
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Model
     */
    public function update(array $input, int $id): Topic
    {
        $query = $this->model->newQuery();

        $model = $query->with('topicable')->findOrFail($id);

        if (isset($input['topicable_type']) && $model->topicable_type != $input['topicable_type']) {
            $classType = $input['topicable_type'];
            $content = $this->getContentModel($classType, $input);

            if (method_exists($classType, 'createResourseFromRequest')) {
                $content['input'] = $classType::createResourseFromRequest($content['input'], $id);
            }

            $content['model']->fill($content['input']);
            $content['model']->save();

            //$model->topicable()->delete();
            $model->topicable()->associate($content['model'])->save();
        } elseif (isset($input['topicable_type']) && $model->topicable_type == $input['topicable_type']) {
            $classType = $input['topicable_type'];

            if (method_exists($classType, 'createResourseFromRequest')) {
                $input = $classType::createResourseFromRequest($input, $id);
            }

            $model->topicable->fill($input);
            $model->topicable->save();
        }

        $modelFillable = $model->fillable;

        $input = array_filter($input, function ($key) use ($modelFillable) {
            return in_array($key, $modelFillable);
        }, ARRAY_FILTER_USE_KEY);

        $model->fill($input);

        $model->save();

        return $model;
    }
}
