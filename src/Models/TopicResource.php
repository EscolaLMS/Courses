<?php

namespace EscolaLms\Courses\Models;

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
 * @property-read null|\EscolaLms\Courses\Models\Topic $topic
 */
class TopicResource extends Model
{
    use HasFactory;

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

    public function getUrlAttribute()
    {
        return Storage::url($this->path . $this->name);
    }
}
