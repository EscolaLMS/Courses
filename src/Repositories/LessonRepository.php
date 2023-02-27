<?php

namespace EscolaLms\Courses\Repositories;

use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Repositories\Contracts\LessonRepositoryContract;
use EscolaLms\Courses\Repositories\Contracts\TopicRepositoryContract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Application;

/**
 * Class LessonRepository.
 *
 * @version April 27, 2021, 11:20 am UTC
 */
class LessonRepository extends BaseRepository implements LessonRepositoryContract
{
    private TopicRepositoryContract $topicRepository;

    /**
     * @var array
     */
    protected $fieldSearchable = [
        'title',
        'duration',
        'order',
        'course_id',
        'parent_lesson_id',
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
        return Lesson::class;
    }

    public function __construct(Application $app)
    {
        parent::__construct($app);
        $this->topicRepository = $app->make(TopicRepositoryContract::class);
    }

    public function delete(int $id): ?bool
    {
        $lesson = $this->findWith($id, ['*'], ['topics']);

        return !is_null($lesson) && $this->deleteModel($lesson);
    }

    public function deleteModel(Lesson $lesson): ?bool
    {
        foreach ($lesson->topics as $topic) {
            $this->topicRepository->deleteModel($topic);
        }

        foreach ($lesson->lessons as $child) {
            $this->deleteModel($child);
        }

        $lesson->delete();

        return true;
    }

    public function allMain(
        array $search = [],
        ?int $skip = null,
        ?int $limit = null,
        array $columns = ['*'],
        string $orderDirection = 'asc',
        string $orderColumn = 'id'
    ): Collection {
        return $this->allQuery($search, $skip, $limit)
            ->whereNull('parent_lesson_id')
            ->orderBy($orderColumn, $orderDirection)
            ->get($columns);
    }
}
