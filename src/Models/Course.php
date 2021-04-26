<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *      schema="Course",
 *      required={"title", "active"},
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
 *          property="summary",
 *          description="summary",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="image_path",
 *          description="image_path",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="video_path",
 *          description="video_path",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="base_price",
 *          description="base_price",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="duration",
 *          description="duration",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="active",
 *          description="active",
 *          type="boolean"
 *      ),
 *      @OA\Property(
 *          property="author_id",
 *          description="author_id",
 *          type="integer",
 *          format="int32"
 *      )
 * )
 */
class Course extends Model
{
    use HasFactory;

    public $table = 'courses';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'title',
        'summary',
        'image_path',
        'video_path',
        'base_price',
        'duration',
        'active',
        'author_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'title' => 'string',
        'summary' => 'string',
        'image_path' => 'string',
        'video_path' => 'string',
        'base_price' => 'string',
        'duration' => 'string',
        'active' => 'boolean',
        'author_id' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'title' => 'required|string|max:255',
        'summary' => 'nullable|string',
        'image_path' => 'nullable|string|max:255',
        'video_path' => 'nullable|string|max:255',
        'base_price' => 'nullable|string|max:255',
        'duration' => 'nullable|string|max:255',
        'active' => 'required|boolean',
        'author_id' => 'nullable'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function author()
    {
        return $this->belongsTo(\App\Models\User::class, 'author_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function lessons()
    {
        return $this->hasMany(\App\Models\Lesson::class, 'course_id');
    }
}
