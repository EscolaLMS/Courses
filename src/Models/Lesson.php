<?php

namespace EscolaLms\Courses\Models;

use EscolaLms\Courses\Database\Factories\LessonFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
 *          property="summary",
 *          description="summary",
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
 * 
 * @property bool $active
 * @property-read \Illuminate\Database\Eloquent\Collection|\EscolaLms\Courses\Models\Topic[] $topics
 */
class Lesson extends Model
{
    use HasFactory;

    public $table = 'lessons';

    public $fillable = [
        'title',
        'duration',
        'order',
        'course_id',
        'summary',
        'active'
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
        'course_id' => 'integer',
        'summary' => 'string',
        'active' => 'boolean'
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
        'course_id' => 'required|exists:courses,id',
        'summary' => 'nullable|string',
        'active' => 'boolean'
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class, 'lesson_id');
    }

    protected static function newFactory(): LessonFactory
    {
        return \EscolaLms\Courses\Database\Factories\LessonFactory::new();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('lessons.active', '=', true);
    }

    protected static function booted()
    {
        static::creating(function (Lesson $lesson) {
            if ($lesson->course_id && !$lesson->order) {
                $lesson->order = 1 + (int) Lesson::where('course_id', $lesson->course_id)->max('order');
            }
        });
    }
}
