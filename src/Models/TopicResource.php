<?php

namespace EscolaLms\Courses\Models;

use EscolaLms\Core\Models\Traits\QueryCacheable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Schema(
 *      schema="TopicResource",
 *      required={"topic_id", "path", "name"},
 *      @OA\Property(
 *          property="id",
 *          description="id",
 *          type="integer",
 *      ),
 *      @OA\Property(
 *          property="topic_id",
 *          description="topic_id",
 *          type="integer"
 *      ),
 *      @OA\Property(
 *          property="path",
 *          description="path",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="name",
 *          description="name",
 *          type="string"
 *      )
 * )
 *
 * @property \EscolaLms\Courses\Models\Topic|null $topic
 */
class TopicResource extends Model
{
    use HasFactory, QueryCacheable;

    public $table = 'topic_resources';

    /**
     * @var array
     */
    protected $guarded = [
        'id',
    ];

    /**
     * @var array
     */
    protected $casts = [];

    public function topic(): BelongsTo
    {
        return $this->belongsTo(\EscolaLms\Courses\Models\Topic::class, 'topic_id');
    }

    protected static function newFactory()
    {
        return \EscolaLms\Courses\Database\Factories\TopicResourceFactory::new();
    }

    protected static function booted()
    {
        static::retrieved(function (TopicResource $resource) {
            if (!(preg_match('/^course\/([0-9]+)\/topic\/([0-9]+)\/resources?/', $resource->path))) {
                $resource->fixAssetPaths();
            }
        });
    }

    public function getUrlAttribute()
    {
        return Storage::url($this->path);
    }

    private function fixPath($key): array
    {
        $value = $this->$key.$this->name;
        $topic = $this->topic;
        $course = $topic->course;

        $destination = sprintf('course/%d/topic/%d/resources/%s', $course->id, $topic->id, $this->name);

        if (strpos($value, $destination) === false && Storage::exists($value)) {
            $result = [$value, $destination];
            if (!Storage::exists($destination)) {
                Storage::move($value, $destination);
            }
            $this->$key = $destination;
            $this->save();

            return $result;
        }

        return [];
    }

    public function fixAssetPaths(): array
    {
        $results = [];
        $results = $results + $this->fixPath('path');

        return $results;
    }
}
