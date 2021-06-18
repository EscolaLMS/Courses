<?php

namespace EscolaLms\Courses\Models;

use Eloquent as Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *      schema="Topic",
 *      required={"lesson_id", "order"},
 *      @OA\Property(
 *          property="id",
 *          description="id",
 *          type="integer",
 *      ),
 *      @OA\Property(
 *          property="title",
 *          description="title",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="lesson_id",
 *          description="lesson_id",
 *          type="integer",
 *      ),
 *      @OA\Property(
 *          property="topicable_id",
 *          description="topicable_id",
 *          type="integer",
 *      ),
 *      @OA\Property(
 *          property="topicable_type",
 *          description="topicable_type",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="order",
 *          description="order",
 *          type="integer",
 *      )
 * )
 */

class Topic extends Model
{
    use HasFactory;

    public $table = 'topics';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';




    public $fillable = [
        'title',
        'lesson_id',
        'topicable_id',
        'topicable_type',
        'order'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'title' => 'string',
        'lesson_id' => 'integer',
        'topicable_id' => 'integer',
        'topicable_type' => 'string',
        'order' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'title' => 'nullable|string|max:255',
        'lesson_id' => 'required|exists:lessons,id',
        'topicable_id' => 'integer',
        'topicable_type' => 'required|string|max:255',
        'order' => 'integer',
        'value' => 'required'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function lesson()
    {
        return $this->belongsTo(\EscolaLms\Courses\Models\Lesson::class, 'lesson_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function topicRichtexts()
    {
        return $this->hasMany(\EscolaLms\Courses\Models\TopicRichtext::class, 'topic_id');
    }

    protected static function newFactory()
    {
        return \EscolaLms\Courses\Database\Factories\TopicFactory::new();
    }

    public function topicable()
    {
        return $this->morphTo();
    }
}
