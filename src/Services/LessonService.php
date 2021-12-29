<?php

namespace EscolaLms\Courses\Services;

use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Repositories\Contracts\LessonRepositoryContract;
use EscolaLms\Courses\Services\Contracts\LessonServiceContract;
use EscolaLms\Courses\Services\Contracts\TopicServiceContract;
use Illuminate\Database\Eloquent\Model;

class LessonService implements LessonServiceContract
{
    private LessonRepositoryContract $lessonRepository;
    private TopicServiceContract $topicService;

    public function __construct(LessonRepositoryContract $lessonRepository, TopicServiceContract $topicService)
    {
        $this->lessonRepository = $lessonRepository;
        $this->topicService = $topicService;
    }

    public function cloneLesson(Lesson $lesson): Model
    {
       $clonedLesson = $this->lessonRepository->create($lesson->replicate()->toArray());

       foreach ($lesson->topics as $topic) {
           $topic->lesson_id = $clonedLesson->getKey();
           $this->topicService->cloneTopic($topic);
       }

       return $clonedLesson;
    }
}
