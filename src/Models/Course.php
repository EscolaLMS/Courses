<?php

namespace EscolaLms\Courses\Models;

use Eloquent as Model;
use EscolaLms\Tags\Models\Tag;
use EscolaLms\Categories\Models\Category;
use EscolaLms\Core\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Schema(
 *      schema="Course",
 *      required={"title"},
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
 *          property="image_url",
 *          description="image_url",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="video_url",
 *          description="video_url",
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
 *          property="author_id",
 *          description="author_id",
 *          type="integer",
 *      ),
 *      @OA\Property(
 *          property="image",
 *          description="image",
 *          type="file",
 *      ),
 *      @OA\Property(
 *          property="video",
 *          description="video",
 *          type="file"
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
        'author_id' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'title' => 'string|max:255',
        'summary' => 'nullable|string',
        'image_path' => 'nullable|string|max:255',
        'video_path' => 'nullable|string|max:255',
        'base_price' => 'nullable|string|max:255',
        'duration' => 'nullable|string|max:255',
        'author_id' => 'nullable',
        'image' => 'file|image',
        'video' => 'file|mimes:mp4,ogg,webm',
    ];

    protected $appends = ['image_url', 'video_url'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function lessons()
    {
        return $this->hasMany(Lesson::class, 'course_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function tags()
    {
        return $this->morphMany(Tag::class, 'morphable');
    }

    protected static function newFactory()
    {
        return \EscolaLms\Courses\Database\Factories\CourseFactory::new();
    }

    public function getImageUrlAttribute()
    {
        if (isset($this->attributes['image_path'])) {
            return  url(Storage::url($this->attributes['image_path']));
        }
        return null;
    }

    public function getVideoUrlAttribute()
    {
        if (isset($this->attributes['video_path'])) {
            return  url(Storage::url($this->attributes['video_path']));
        }
        return null;
    }
}
