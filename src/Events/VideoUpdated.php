<?php

namespace EscolaLms\Courses\Events;

use EscolaLms\TopicTypes\Models\TopicContent\Video;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VideoUpdated
{
    use Dispatchable, SerializesModels;

    private Video $video;

    /**
     * @param Video $video
     */
    public function __construct(Video $video)
    {
        $this->video = $video;
    }

    /**
     * @return Video
     */
    public function getVideo(): Video
    {
        return $this->video;
    }
}
