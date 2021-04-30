<?php

namespace EscolaLms\Courses\Repositories;

use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Repositories\BaseRepository;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use EscolaLms\Courses\Exceptions\TopicException;
use Error;

/**
 * Class TopicRepository
 * @package EscolaLms\Courses\Repositories
 * @version April 27, 2021, 11:21 am UTC
*/

class TopicRepository extends BaseRepository
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
        'topicable_class',
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

    /**
     * Create model record
     *
     * @param array $input
     *
     * @return Model
     */
    public function create($input)
    {
        // initialise mode from allowed list string
        $classType = $input["topicable_class"];

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
            $contentInput = $classType::createResourseFromRequest($contentInput, $model->id);
        }
       
        // create related 1:1 content and associate with topic
        $contentModel->fill($contentInput);
        $contentModel->save();
        
        $model->topicable()->associate($contentModel)->save();
        $model->load('topicable');

        return $model;
    }
}
