<?php

namespace EscolaLms\Courses\Models;

use Eloquent as Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *      schema="Lesson",
 *      required={"title", "order", "course_id"},
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
 *          property="duration",
 *          description="duration",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="order",
 *          description="order",
 *          type="integer",
 *      ),
 *      @OA\Property(
 *          property="course_id",
 *          description="course_id",
 *          type="integer",
 *      )
 * )
 */

class Lesson extends Model
{
    use HasFactory;

    public $table = 'lessons';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';




    public $fillable = [
        'title',
        'duration',
        'order',
        'course_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'title' => 'string',
        'duration' => 'string',
        'order' => 'integer',
        'course_id' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'title' => 'required|string|max:255',
        'duration' => 'nullable|string|max:255',
        'order' => 'required|integer',
        'course_id' => 'required'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function course()
    {
        return $this->belongsTo(\App\Models\Course::class, 'course_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function topics()
    {
        return $this->hasMany(\App\Models\Topic::class, 'lesson_id');
    }

    protected static function newFactory()
    {
        return \EscolaLms\Courses\Database\Factories\LessonFactory::new();
    }
}
