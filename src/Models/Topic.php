<?php

namespace EscolaLms\Courses\Models;

use EscolaLms\Courses\Database\Factories\TopicFactory;
use EscolaLms\Courses\Facades\Topic as TopicFacade;
use EscolaLms\Courses\Models\Traits\ClearsResponseCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Validation\Rule;

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
 *          property="active",
 *          description="active",
 *          type="boolean"
 *      ),
 *      @OA\Property(
 *          property="can_skip",
 *          description="can skip",
 *          type="boolean"
 *      ),
 *      @OA\Property(
 *          property="preview",
 *          description="preview",
 *          type="boolean"
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
 *          property="value",
 *          description="value",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="summary",
 *          description="summary",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="introduction",
 *          description="introduction",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="description",
 *          description="description",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="order",
 *          description="order",
 *          type="integer",
 *      ),
 *      @OA\Property(
 *          property="json",
 *          description="json",
 *          type="object",
 *      )
 * )
 *
 * @property bool                                  $active
 * @property \EscolaLms\Courses\Models\Lesson|null $lesson
 */
class Topic extends Model
{
    use HasFactory, ClearsResponseCache;

    public $table = 'topics';

    public $fillable = [
        'title',
        'lesson_id',
        'topicable_id',
        'topicable_type',
        'order',
        'active',
        'preview',
        'summary',
        'introduction',
        'description',
        'json',
        'can_skip',
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
        'order' => 'integer',
        'active' => 'boolean',
        'preview' => 'boolean',
        'summary' => 'string',
        'introduction' => 'string',
        'description' => 'string',
        'json' => 'array',
        'can_skip' => 'boolean',
    ];

    public static function rules(): array
    {
        return [
            'title' => ['string', 'max:255'],
            'summary' => ['string'],
            'introduction' => ['string'],
            'description' => ['string'],
            'lesson_id' => ['integer', 'exists:lessons,id'],
            'topicable_type' => ['string', Rule::in(TopicFacade::availableContentClasses())],
            'order' => ['integer'],
            'active' => ['boolean'],
            'preview' => ['boolean'],
            'can_skip' => ['boolean'],
            'json' => ['json'],
        ];
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(\EscolaLms\Courses\Models\Lesson::class, 'lesson_id');
    }

    public function topicRichtexts(): HasMany
    {
        return $this->hasMany(\EscolaLms\Courses\Models\TopicRichtext::class, 'topic_id');
    }

    protected static function newFactory(): TopicFactory
    {
        return \EscolaLms\Courses\Database\Factories\TopicFactory::new();
    }

    public function topicable(): MorphTo
    {
        return $this->morphTo();
    }

    public function progress(): HasMany
    {
        return $this->hasMany(CourseProgress::class, 'topic_id');
    }

    public function resources(): HasMany
    {
        return $this->hasMany(TopicResource::class, 'topic_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('topics.active', '=', true);
    }

    public function getStorageDirectoryAttribute(): string
    {
        if ($this->lesson && $this->lesson->course_id) {
            return 'course/' . $this->lesson->course_id . '/topic/' . $this->getKey() . '/';
        }

        return 'topic/' . $this->getKey() . '/';
    }

    protected static function booted()
    {
        static::creating(function (Topic $topic) {
            if ($topic->lesson_id && !$topic->order) {
                $topic->order = 1 + (int) Topic::where('lesson_id', $topic->lesson_id)->max('order');
            }
        });
    }

    public function getCourseAttribute(): ?Course
    {
        return $this->lesson ? $this->lesson->course : null;
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->active && ($this->course ? $this->course->is_active : true);
    }
}
