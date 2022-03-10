<?php

namespace EscolaLms\Courses\Rules;

use EscolaLms\Courses\Models\Topic;
use EscolaLms\Files\Rules\FileOrStringRule;

class TopicResourceRule extends FileOrStringRule
{
    public function __construct(int $topicId = null)
    {
        if (is_null($topicId)) {
            return false;
        }
        $topic = Topic::findOrFail($topicId);
        $prefixPath = 'course/' . $topic->course->getKey();

        parent::__construct(['file'], $prefixPath);
    }
}
