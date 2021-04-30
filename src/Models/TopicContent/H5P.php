<?php

namespace EscolaLms\Courses\Models\TopicContent;

use Eloquent as Model;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Models\AbstractContent;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *      schema="TopicH5P",
 *      required={"value"},
 *      @OA\Property(
 *          property="id",
 *          description="id",
*          @OA\Schema(
*             type="integer",
*         )
 *      ),
 *      @OA\Property(
 *          property="value",
 *          description="value",
 *          type="string"
 *      )
 * )
 */

class H5P extends AbstractContent
{
    use HasFactory;

    public $table = 'topic_h5ps';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public $fillable = [
        'value'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'value' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'value' => 'required|integer|exists:hh5p_contents,id'
    ];


    public function topic()
    {
        return $this->morphOne(Topic::class, 'topicable');
    }

    protected static function newFactory()
    {
        return \EscolaLms\Courses\Database\Factories\TopicContent\H5PFactory::new();
    }
}
