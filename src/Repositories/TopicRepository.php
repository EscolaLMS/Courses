<?php

namespace EscolaLms\Courses\Repositories;

use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Repositories\BaseRepository;

/**
 * Class TopicRepository
 * @package EscolaLms\Courses\Repositories
 * @version April 27, 2021, 11:21 am UTC
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
}
