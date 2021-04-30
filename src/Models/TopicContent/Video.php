<?php

namespace EscolaLms\Courses\Models\TopicContent;

use Eloquent as Model;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Models\AbstractContent;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *      schema="TopicVideo",
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

class Video extends AbstractContent
{
    use HasFactory;

    public $table = 'topic_videos';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public $fillable = [
        'value',
        'poster'
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
        'value' => 'file|mimes:mp4,ogg,webm',
        'poster' => 'file|image'
    ];


    public function topic()
    {
        return $this->morphOne(Topic::class, 'topicable');
    }

    protected static function newFactory()
    {
        return \EscolaLms\Courses\Database\Factories\TopicContent\VideoFactory::new();
    }

    // TODO: this idea is crazy
    public static function createResourseFromRequest($input, $topicId):array
    {
        $tmpFile = $input['value']->getPathName();
        $path = $input['value']->store("topic/$topicId/videos");

        if (isset($input['poster'])) {
            $poster = $input['poster']->store("topic/$topicId/videos");
        }

        return [
              'value' => $path,
              'width' => 0,
              'height' => 0,
              'poster' => isset($poster) ? $poster : null
          ];
    }
}
