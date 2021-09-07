<?php

namespace EscolaLms\Courses\Models;

use EscolaLms\Courses\Database\Factories\TopicFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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
 *          property="order",
 *          description="order",
 *          type="integer",
 *      )
 * )
 * 
 * @property bool $active
 * @property-read null|\EscolaLms\Courses\Models\Lesson $lesson
 */
class Topic extends Model
{
    use HasFactory;

    public $table = 'topics';

    public $fillable = [
        'title',
        'lesson_id',
        'topicable_id',
        'topicable_type',
        'order',
        'active',
        'preview',
        'summary'
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
        'summary' => 'string'
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
        'value' => 'required',
        'active' => 'boolean',
        'preview' => 'boolean',
        'summary' => 'string'
    ];

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

    public function progress(): HasOne
    {
        return $this->hasOne(CourseProgress::class, 'topic_id');
    }

    public function resources(): HasMany
    {
        return $this->hasMany(TopicResource::class, 'topic_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('topics.active', '=', true);
    }
}
