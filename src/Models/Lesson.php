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
 *      ),
 *      @OA\Property(
 *          property="parent_lesson_id",
 *          description="parent_lesson_id",
 *          type="integer",
 *      )
 * )
 *
 * @property bool $active
 * @property-read \Illuminate\Database\Eloquent\Collection|\EscolaLms\Courses\Models\Topic[] $topics
 * @property-read \Illuminate\Database\Eloquent\Collection|\EscolaLms\Courses\Models\Lesson[] $lessons
 * @property-read \EscolaLms\Courses\Models\Lesson|null $parentLesson
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
        'active',
        'parent_lesson_id',
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
        'active' => 'boolean',
        'parent_lesson_id' => 'integer',
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
        'active' => 'boolean',
        'parent_lesson_id' => 'nullable|exists:lessons,id',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class, 'lesson_id');
    }

    public function parentLesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class, 'parent_lesson_id');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class, 'parent_lesson_id');
    }

    protected static function newFactory(): LessonFactory
    {
        return \EscolaLms\Courses\Database\Factories\LessonFactory::new();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('lessons.active', '=', true);
    }

    public function scopeMain(Builder $query): Builder
    {
        return $query->whereNull('parent_lesson_id');
    }

    protected static function booted()
    {
        static::creating(function (Lesson $lesson) {
            if (!$lesson->order) {
                $lesson->order = 1 + (int)Lesson::when($lesson->course_id, function (Builder $query, int $courseId) {
                        $query->where('course_id', $courseId);
                    })
                    ->when($lesson->parent_lesson_id, function (Builder $query, int $parentId) {
                        $query->where('parent_lesson_id', $parentId);
                    })
                    ->max('order');
            }
        });
    }
}
