<?php

namespace App\Repositories;

use App\Models\Topic;
use App\Repositories\BaseRepository;

/**
 * Class TopicRepository
 * @package App\Repositories
 * @version April 26, 2021, 6:12 pm UTC
*/

class TopicRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'title',
        'lesson_id',
        'topicable_id',
        'topicable_class'
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
}
