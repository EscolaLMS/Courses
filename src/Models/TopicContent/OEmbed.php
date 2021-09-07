<?php

namespace EscolaLms\Courses\Models\TopicContent;

use EscolaLms\Courses\Models\AbstractContent;
use EscolaLms\Courses\Models\Topic;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *      schema="TopicOEmbed",
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

class OEmbed extends AbstractContent
{
    use HasFactory;

    public $table = 'topic_oembeds';

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
        'value' => 'required|string'
    ];


    public function topic()
    {
        return $this->morphOne(Topic::class, 'topicable');
    }

    protected static function newFactory()
    {
        return \EscolaLms\Courses\Database\Factories\TopicContent\OEmbedFactory::new();
    }
}
