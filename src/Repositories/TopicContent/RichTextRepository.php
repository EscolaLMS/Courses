<?php

namespace EscolaLms\Courses\Repositories\TopicContent;

use EscolaLms\Courses\Models\TopicContent\RichText;
use EscolaLms\Courses\Repositories\BaseRepository;

/**
 * Class RichTextRepository
 * @package EscolaLms\Courses\Repositories\TopicContent
 * @version April 27, 2021, 11:22 am UTC
*/

class RichTextRepository extends BaseRepository
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
        return RichText::class;
    }
}
