<?php


namespace EscolaLms\Courses\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use EscolaLms\Courses\Models\TopicContent\Video;

class VideoUpdated
{
    use Dispatchable, SerializesModels;

    private Video $video;

    public function __construct(Video $video)
    {
        $this->video = $video;
    }

    public function getVideo(): Video
    {
        return $this->video;
    }
}
