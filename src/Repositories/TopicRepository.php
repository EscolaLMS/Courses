<?php

namespace EscolaLms\Courses\Repositories;

use Error;
use EscolaLms\Courses\Exceptions\TopicException;
use EscolaLms\Courses\Http\Requests\CreateTopicAPIRequest;
use EscolaLms\Courses\Http\Requests\UpdateTopicAPIRequest;
use EscolaLms\Courses\Models\Contracts\TopicContentContract;
use EscolaLms\Courses\Models\Contracts\TopicFileContentContract;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Repositories\BaseRepository;
use EscolaLms\Courses\Repositories\Contracts\TopicRepositoryContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;

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
        'order',
        'active',
        'boolean'
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
    public static function registerContentClass(string $class): array
    {
        if (!in_array($class, self::$contentClasses) && class_exists($class) && (is_a($class, TopicContentContract::class))) {
            self::$contentClasses[] = $class;
        }
        return self::$contentClasses;
    }

    public static function availableContentClasses(): array
    {
        return self::$contentClasses;
    }

    public function getById($id): Topic
    {
        return $this->model->newQuery()->where('id', '=', $id)->first();
    }

    /**
     * Create model record
     *
     * @return Topic
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
            'can_skip' => (bool) ($input['can_skip'] ?? false),
        ];

        $model = $this->model->newInstance($input);
        $model->save();

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

        $model->fill($input);

        $model->save();

        return $model;
    }
}
