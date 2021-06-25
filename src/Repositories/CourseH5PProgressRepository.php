<?php

namespace EscolaLms\Courses\Repositories;

use EscolaLms\Courses\Models\H5PUserProgress;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Repositories\Contracts\CourseH5PProgressRepositoryContract;
use Illuminate\Contracts\Auth\Authenticatable;

class CourseH5PProgressRepository extends BaseRepository implements CourseH5PProgressRepositoryContract
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'topic_id',
        'user_id',
        'event',
    ];

    public function getFieldsSearchable()
    {
        return $this->fieldSearchable;
    }

    public function model()
    {
        return H5PUserProgress::class;
    }

    public function store(Topic $topic, Authenticatable $user, string $event, $data) : H5PUserProgress
    {
        return $this->model->updateOrCreate([
            'topic_id' => $topic->getKey(),
            'user_id' => $user->getKey(),
            'event' => $event,
        ], [
            'data' => $data,
        ]);
    }

}