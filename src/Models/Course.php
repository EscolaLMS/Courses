<?php

namespace EscolaLms\Courses\Models;

use EscolaLms\Categories\Models\Category;
use EscolaLms\Core\Models\Traits\QueryCacheable;
use EscolaLms\Core\Models\User as CoreUser;
use EscolaLms\Courses\Database\Factories\CourseFactory;
use EscolaLms\Courses\Enum\CourseStatusEnum;
use EscolaLms\Courses\Enum\PlatformVisibility;
use EscolaLms\Courses\Events\CourseStatusChanged;
use EscolaLms\Tags\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Peopleaps\Scorm\Model\ScormScoModel;

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
 *          property="duration",
 *          description="duration",
 *          type="string"
 *      ),
 *     @OA\Property(
 *          property="scorm_sco_id",
 *          description="scorm_sco_id",
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
 *          property="status",
 *          description="status",
 *          type="string",
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
 *      @OA\Property(
 *          property="active_from",
 *          description="active_from",
 *          type="datetime"
 *      ),
 *      @OA\Property(
 *          property="active_to",
 *          description="active_to",
 *          type="datetime"
 *      ),
 *      @OA\Property(
 *          property="hours_to_complete",
 *          description="hours_to_complete",
 *          type="integer"
 *      ),
 *      @OA\Property(
 *          property="findable",
 *          description="findable",
 *          type="boolean"
 *      ),
 *      @OA\Property(
 *          property="target_group",
 *          description="target group",
 *          type="string",
 *      ),
 *     @OA\Property(
 *          property="teaser_url",
 *          description="teaser url",
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="public",
 *          description="free access, public audience ",
 *          type="boolean",
 *      ),
 * )
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\EscolaLms\Courses\Models\Lesson[] $lessons
 * @property-read \Illuminate\Database\Eloquent\Collection|\EscolaLms\Courses\Models\Topic[] $topics
 */

class Course extends Model
{
    use HasFactory, QueryCacheable;

    public $table = 'courses';

    /** Backwards compatibility */
    protected ?int $author_id = null;

    public $fillable = [
        'title',
        'summary',
        'image_path',
        'video_path',
        'duration',
        'author_id',
        'status',
        'subtitle',
        'language',
        'description',
        'level',
        'scorm_sco_id',
        'poster_path',
        'active_from',
        'active_to',
        'hours_to_complete',
        'findable',
        'target_group',
        'teaser_url',
        'public'
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
        'duration' => 'string',
        'status' => 'string',
        'subtitle' => 'string',
        'language' => 'string',
        'description' => 'string',
        'level' => 'string',
        'scorm_sco_id' => 'integer',
        'poster_path' => 'string',
        'active_from' => 'datetime',
        'active_to' => 'datetime',
        'hours_to_complete' => 'integer',
        'findable' => 'boolean',
        'target_group' => 'string',
        'teaser_url' => 'string',
        'public' => 'boolean',

    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static function rules(): array
    {
        return [
            'title' => 'string|max:255',
            'summary' => 'nullable|string',
            'image_path' => 'nullable|string|max:255',
            'video_path' => 'nullable|string|max:255',
            'duration' => 'nullable|string|max:255',
            'authors' => ['nullable', 'array'],
            'authors.*' => ['integer'],
            'image' => 'file|image',
            'video' => 'file|mimes:mp4,ogg,webm',
            'status' => ['string', Rule::in(CourseStatusEnum::getValues())],
            'subtitle' => 'nullable|string|max:255',
            'language' => 'nullable|string|max:2',
            'description' => 'nullable|string',
            'level' => 'nullable|string|max:100',
            'scorm_sco_id' => 'nullable|exists:scorm_sco,id',
            'poster_path' => 'nullable|string|max:255',
            'poster' => 'file|image',
            'active_from' => 'date|nullable',
            'active_to' => 'date|nullable',
            'hours_to_complete' => 'integer|nullable',
            'findable' => 'boolean',
            'target_group' => 'nullable|string|max:100',
            'teaser_url' => 'nullable|string',
            'public' => 'nullable|boolean',
        ];
    }

    protected $appends = ['image_url', 'video_url', 'poster_url'];

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_author', 'course_id', 'author_id')->using(CourseAuthorPivot::class);
    }

    /** Backwards compatibility */
    public function getAuthorAttribute(): ?User
    {
        return $this->authors->first();
    }

    /** Backwards compatibility */
    public function getAuthorIdAttribute(): ?int
    {
        $author = $this->author;
        if ($author) {
            return $author->id;
        }
        return $this->author_id;
    }

    /** Backwards compatibility */
    public function setAuthorAttribute(User $author)
    {
        $this->setAuthorIdAttribute($author->getKey());
    }

    /** Backwards compatibility */
    public function setAuthorIdAttribute(?int $author_id)
    {
        if ($this->exists && !is_null($author_id)) {
            $this->authors()->syncWithoutDetaching([$author_id]);
        }
        $this->author_id = $author_id;
    }

    public function hasAuthor(CoreUser|User $author): bool
    {
        return !is_null($this->authors()->where('author_id', $author->id)->first());
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
        if ($this->attributes['image_path'] ?? null) {
            $path = trim(trim($this->attributes['image_path'], '/'));
            if ($path) {
                $value = Storage::url($path);
                return preg_match('/^(http|https):.*$/', $value, $oa) ?
                    $value :
                    url($value);
            }
        }
        return null;
    }

    public function getVideoUrlAttribute(): ?string
    {
        if ($this->attributes['video_path'] ?? null) {
            $path = trim(trim($this->attributes['video_path'], '/'));
            if ($path) {
                $value = Storage::url($path);
                return preg_match('/^(http|https):.*$/', $value, $oa) ?
                    $value :
                    url($value);
            }
        }
        return null;
    }

    public function getPosterUrlAttribute(): ?string
    {
        if ($this->attributes['poster_path'] ?? null) {
            $path = trim(trim($this->attributes['poster_path'], '/'));
            if ($path) {
                $value = Storage::url($path);
                return preg_match('/^(http|https):.*$/', $value, $oa) ?
                    $value :
                    url($value);
            }
        }
        return null;
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->using(CourseUserPivot::class)->withTimestamps();
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class)->using(CourseGroupPivot::class)->withTimestamps();
    }

    public function topics(): HasManyThrough
    {
        return $this->hasManyThrough(Topic::class, Lesson::class, 'course_id', 'lesson_id');
    }

    public function scormSco(): BelongsTo
    {
        return $this->belongsTo(ScormScoModel::class, 'scorm_sco_id');
    }

    public function getIsPublishedAttribute(): bool
    {
        return in_array($this->status, [CourseStatusEnum::PUBLISHED, CourseStatusEnum::PUBLISHED_UNACTIVATED]);
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === CourseStatusEnum::PUBLISHED
            && (is_null($this->active_from) || Carbon::now()->greaterThanOrEqualTo($this->active_from))
            && (is_null($this->active_to) || Carbon::now()->lessThanOrEqualTo($this->active_to));
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('courses.status', '=', CourseStatusEnum::PUBLISHED)
            ->where(function (Builder $query) {
                return $query->whereDate('active_from', '>=', Carbon::now())->orWhereNull('active_from');
            })
            ->where(function (Builder $query) {
                return $query->whereDate('active_to', '<=', Carbon::now())->orWhereNull('active_to');
            });
    }

    public function hasUser(CoreUser|User $user): bool
    {
        $groupIds = $this->groups->pluck('id')->toArray();
        $childGroups = $this->getChildGroups($groupIds);
        $allGroups = array_merge($groupIds, $childGroups);

        $inGroup = DB::table('group_user')
            ->whereIn('group_id', $allGroups)
            ->where('user_id', $user->getKey())
            ->exists();

        return $this->users()
                ->where('users.id', $user->getKey())
                ->where(fn(Builder $query) => $query->whereNull('end_date')->orWhereDate('end_date', '>=', Carbon::now()))
                ->exists()
            || $inGroup;
    }

    private function getChildGroups(array $groupIds): array
    {
        $childGroups = DB::table('groups')->whereIn('parent_id', $groupIds)->pluck('id')->toArray();
        if (count($childGroups) > 0) {
            $childGroups = array_merge($childGroups, $this->getChildGroups($childGroups));
        }
        return $childGroups;
    }

    protected static function booted()
    {
        self::creating(function (Course $course) {
            if (is_null($course->findable)) {
                $course->findable = config('escolalms_courses.platform_visibility', PlatformVisibility::VISIBILITY_PUBLIC) === PlatformVisibility::VISIBILITY_PUBLIC;
            }
        });
        /** Backwards compatibility */
        self::saved(function (Course $course) {
            if ($course->author_id) {
                $course->authors()->syncWithoutDetaching([$course->author_id]);
            }
        });

        self::updated(function (Course $course) {
            if ($course->wasChanged('status')) {
                event(new CourseStatusChanged($course));
            }
        });
    }

    public function getMorphClass()
    {
        return self::class;
    }
}
