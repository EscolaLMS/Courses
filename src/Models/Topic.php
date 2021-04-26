<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *      schema="Topic",
 *      required={"topicable_id", "topicable_class"},
 *      @OA\Property(
 *          property="id",
 *          description="id",
 *          type="integer",
 *          format="int32"
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
 *          format="int32"
 *      ),
 *      @OA\Property(
 *          property="topicable_id",
 *          description="topicable_id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @OA\Property(
 *          property="topicable_class",
 *          description="topicable_class",
 *          type="string"
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
        'topicable_class'
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
        'topicable_class' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'title' => 'nullable|string|max:255',
        'lesson_id' => 'nullable',
        'topicable_id' => 'required',
        'topicable_class' => 'required|string|max:255'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function lesson()
    {
        return $this->belongsTo(\App\Models\Lesson::class, 'lesson_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function topicRichtexts()
    {
        return $this->hasMany(\App\Models\TopicRichtext::class, 'topic_id');
    }
}
