<?php

namespace EscolaLms\Courses\Events;

use EscolaLms\Courses\Models\Topic;
use EscolaLms\Core\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EscolaLmsTopicFinishedTemplateEvent
{
    use Dispatchable, SerializesModels;

    private User $user;
    private Topic $topic;

    public function __construct(User $user, Topic $topic)
    {
        $this->user = $user;
        $this->topic = $topic;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getTopic(): Topic
    {
        return $this->topic;
    }
}
