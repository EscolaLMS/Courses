<?php

namespace EscolaLms\Courses\Models;

use EscolaLms\Categories\Models\Category;
use EscolaLms\Core\Models\User;
use EscolaLms\Courses\Database\Factories\CourseFactory;
use EscolaLms\Tags\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;
use Peopleaps\Scorm\Model\ScormModel;

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
 *          type="integer"
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
 *     @OA\Property(
 *          property="scorm_id",
 *          description="scorm_id",
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
 *      ),
 *      @OA\Property(
 *          property="active",
 *          description="active",
 *          type="boolean",
 *      ),
 *      @OA\Property(
 *          property="subtitle",
 *          description="subtitle",
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="language",
 *          description="language",
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="description",
 *          description="description",
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="level",
 *          description="level",
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="poster",
 *          description="poster",
 *          type="file",
 *      ),
 *      @OA\Property(
 *          property="poster_path",
 *          description="poster_path",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="poster_url",
 *          description="poster_url",
 *          type="string"
 *      ),
 * )
 * 
 * @property bool $active
 * @property-read \Illuminate\Database\Eloquent\Collection|\EscolaLms\Courses\Models\Lesson[] $lessons
 * @property-read \Illuminate\Database\Eloquent\Collection|\EscolaLms\Courses\Models\Topic[] $topics
 */

class Course extends Model
{
    use HasFactory;

    public $table = 'courses';

    public $fillable = [
        'title',
        'summary',
        'image_path',
        'video_path',
        'base_price',
        'duration',
        'author_id',
        'active',
        'subtitle',
        'language',
        'description',
        'level',
        'scorm_id',
        'poster_path',
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
        'base_price' => 'integer',
        'duration' => 'string',
        'author_id' => 'integer',
        'active' => 'boolean',
        'subtitle' => 'string',
        'language' => 'string',
        'description' => 'string',
        'level' => 'string',
        'scorm_id' => 'integer',
        'poster_path' => 'string',
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
        'base_price' => 'nullable|integer|min:0',
        'duration' => 'nullable|string|max:255',
        'author_id' => ['nullable', 'exists:users,id'],
        'image' => 'file|image',
        'video' => 'file|mimes:mp4,ogg,webm',
        'active' => 'boolean',
        'subtitle' => 'nullable|string|max:255',
        'language' => 'nullable|string|max:2',
        'description' => 'nullable|string',
        'level' => 'nullable|string|max:100',
        'scorm_id' => 'nullable|exists:scorm,id',
        'poster_path' => 'nullable|string|max:255',
        'poster' => 'file|image',
    ];

    protected $appends = ['image_url', 'video_url', 'poster_url'];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class, 'course_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function tags(): MorphMany
    {
        return $this->morphMany(Tag::class, 'morphable');
    }

    protected static function newFactory(): CourseFactory
    {
        return \EscolaLms\Courses\Database\Factories\CourseFactory::new();
    }

    public function getImageUrlAttribute(): ?string
    {
        if (isset($this->attributes['image_path'])) {
            return url(Storage::url($this->attributes['image_path']));
        }
        return null;
    }

    public function getVideoUrlAttribute(): ?string
    {
        if (isset($this->attributes['video_path'])) {
            return url(Storage::url($this->attributes['video_path']));
        }
        return null;
    }

    public function getPosterUrlAttribute(): ?string
    {
        if (isset($this->attributes['poster_path'])) {
            return url(Storage::url($this->attributes['poster_path']));
        }
        return null;
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->using(CourseUserPivot::class);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class)->using(CourseGroupPivot::class);
    }

    public function topics(): HasManyThrough
    {
        return $this->hasManyThrough(Topic::class, Lesson::class, 'course_id', 'lesson_id');
    }

    public function scorm(): BelongsTo
    {
        return $this->belongsTo(ScormModel::class, 'scorm_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('courses.active', '=', true);
    }
}
