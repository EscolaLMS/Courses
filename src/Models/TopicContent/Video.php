<?php

namespace EscolaLms\Courses\Models\TopicContent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use EscolaLms\Courses\Events\VideoUpdated;

/**
 * @OA\Schema(
 *      schema="TopicVideo",
 *      required={"value"},
 *      @OA\Property(
 *          property="id",
 *          description="id",
 *          @OA\Schema(
 *             type="integer",
 *         )
 *      ),
 *      @OA\Property(
 *          property="value",
 *          description="value",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="poster",
 *          description="poster",
 *          type="string"
 *      )
 * )
 */

class Video extends AbstractTopicFileContent
{
    use HasFactory;

    public $table = 'topic_videos';

    protected $dispatchesEvents = [
        'saved' => VideoUpdated::class,
    ];

    public $fillable = [
        'value',
        'poster',
        'width',
        'height',
    ];

    protected $casts = [
        'id' => 'integer',
        'value' => 'string',
        'poster' => 'string',
        'width' => 'integer',
        'height' => 'integer',
    ];

    public static function rules(): array
    {
        return [
            'value' => ['file', 'mimes:mp4,ogg,webm'],
            'poster' => ['file', 'image']
        ];
    }

    protected $appends = ['url', 'poster_url'];

    protected static function newFactory()
    {
        return \EscolaLms\Courses\Database\Factories\TopicContent\VideoFactory::new();
    }

    public function getStoragePathFinalSegment(): string
    {
        return 'video';
    }

    public function getPosterUrlAttribute(): ?string
    {
        if (isset($this->poster)) {
            return Storage::disk(config('courses.topic_content_disk'))->url($this->poster);
        }
        return null;
    }
}
