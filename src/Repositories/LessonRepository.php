<?php

namespace EscolaLms\Courses\Repositories;

use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Repositories\BaseRepository;

/**
 * Class LessonRepository
 * @package EscolaLms\Courses\Repositories
 * @version April 27, 2021, 11:20 am UTC
*/

class LessonRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'title',
        'duration',
        'order',
        'course_id'
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
        return Lesson::class;
    }

    public function delete(int $id): ?bool
    {
        $query = $this->model->newQuery()->with(['topics']);
        $lesson = $query->find($id);

        foreach ($lesson->topics as $topic) {
            $topic->topicable()->delete();
            $topic->delete();
        }
        $lesson->topics()->delete();
        $lesson->delete();
        return true;
    }
}
