<?php

namespace EscolaLms\Courses\Repositories;

use EscolaLms\Courses\Models\TopicRichText;
use EscolaLms\Courses\Repositories\BaseRepository;

/**
 * Class TopicRichTextRepository
 * @package EscolaLms\Courses\Repositories
 * @version April 27, 2021, 11:22 am UTC
*/

class TopicRichTextRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'topic_id',
        'value'
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
        return TopicRichText::class;
    }
}
