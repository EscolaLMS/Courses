<?php

namespace EscolaLms\Courses\Observers;

use EscolaLms\Courses\Models\Topic;
use Spatie\ResponseCache\Facades\ResponseCache;

class TopicObserver
{
    public function creating(Topic $topic)
    {
        if ($topic->lesson_id && !$topic->order) {
            $topic->order = 1 + (int) Topic::where('lesson_id', $topic->lesson_id)->max('order');
        }

        ResponseCache::clear();
    }

    public function updated(Topic $topic)
    {
        ResponseCache::clear();
    }
}
