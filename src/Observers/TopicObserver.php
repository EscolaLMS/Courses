<?php

namespace EscolaLms\Courses\Observers;

use EscolaLms\Courses\Models\Topic;
use EscolaLms\TopicTypes\Events\VideoUpdated;

class TopicObserver
{
    /**
     * Handle the Topic "created" event.
     *
     * @param Topic $topic
     * @return void
     */
    public function created(Topic $topic)
    {
        if ($topic->lesson_id && !$topic->order) {
            $topic->order = 1 + (int) Topic::where('lesson_id', $topic->lesson_id)->max('order');
        }

        $topic->save();
    }

    /**
     * Handle the Topic "updated" event.
     *
     * @param Topic $topic
     * @return void
     */
    public function updated(Topic $topic)
    {
        if (
            $topic->wasChanged('topicable_id')
            && $topic->topicable_type === \EscolaLms\TopicTypes\Models\TopicContent\Video::class
        ) {
            $topic->load('topicable');
            VideoUpdated::dispatch($topic->topicable);
        }
    }
}
