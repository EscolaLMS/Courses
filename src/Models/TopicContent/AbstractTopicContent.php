<?php

namespace EscolaLms\Courses\Models\TopicContent;

use EscolaLms\Core\Models\Traits\QueryCacheable;
use EscolaLms\Courses\Models\Contracts\TopicContentContract;
use EscolaLms\Courses\Models\Topic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

abstract class AbstractTopicContent extends Model implements TopicContentContract
{
    use QueryCacheable;

    protected $fillable = [
        'value',
    ];

    protected $casts = [
        'id' => 'integer',
        'value' => 'string',
    ];

    public static function rules(): array
    {
        return [
            'value' => ['required'],
        ];
    }

    public function topic(): MorphOne
    {
        return $this->morphOne(Topic::class, 'topicable');
    }

    public function fixAssetPaths(): array
    {
        return [];
    }
}
