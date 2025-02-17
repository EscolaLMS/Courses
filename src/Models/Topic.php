<?php

namespace EscolaLms\Courses\Models;

use EscolaLms\Core\Models\Traits\QueryCacheable;
use EscolaLms\Courses\Database\Factories\TopicFactory;
use EscolaLms\Courses\Facades\Topic as TopicFacade;
use Illuminate\Database\Eloquent\Builder;
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
 *      ),
 *      @OA\Property(
 *          property="duration",
 *          description="duration",
 *          type="string"
 *      ),
 * )
 *
 * @property bool                                  $active
 * @property \EscolaLms\Courses\Models\Lesson|null $lesson
 * @property int $lesson_id
 * @property int $topic_id
 * @property int $order
 * @property int $topicable_id
 * @property string $topicable_type
 * @property bool $preview
 */
class Topic extends Model
{
    use HasFactory, QueryCacheable;

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
        'duration',
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
        'duration' => 'string',
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
            'duration' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(\EscolaLms\Courses\Models\Lesson::class, 'lesson_id');
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

    public function getCourseAttribute(): ?Course
    {
        return $this->lesson ? $this->lesson->course : null;
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->active && ($this->course ? $this->course->is_active : true);
    }

    protected function getCacheBaseTags(): array
    {
        return [
            Topic::class,
        ];
    }
}
