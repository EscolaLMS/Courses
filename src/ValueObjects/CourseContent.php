<?php


namespace EscolaLms\Courses\ValueObjects;


use EscolaLms\Core\Dtos\Contracts\DtoContract;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Services\Contracts\CourseServiceContract;
use EscolaLms\Courses\ValueObjects\Contracts\ValueObjectContract;
use Illuminate\Support\Collection;

class CourseContent extends ValueObject implements DtoContract, ValueObjectContract
{
    private CourseServiceContract $courseService;
    private Course $course;

    public function __construct(CourseServiceContract $courseService)
    {
        $this->courseService = $courseService;
    }

    public function build(Course $course): self
    {
        $this->course = $course;
        $this->topics = [];
//        foreach ($this->getSections() as $section) {
//            $this->topics[$section->getKey()] = $this->courseService->getTopics($section);
//
//            foreach ($this->topics[$section->getKey()] as $topic) {
//                if (!($topic instanceof Topic)) {
//                    continue;
//                }
//            }
//        }

        return $this;
    }

    public function toArray(): array
    {
        return [
            'course' => $this->getCourse()
        ];
    }


    /**
     * @return Course
     */
    public function getCourse(): Course
    {
        return $this->course;
    }

    /**
     * @return Collection
     */
    public function getSections(): Collection
    {
        return $this->course->sections;
    }

    public function getRelated(): Collection
    {
        return $this->courseService->related($this->course);
    }

    public function countCertificates(): int
    {
        return 1;
    }

    public function countFlashcards(): int
    {
        return $this->getCourse()->flashcards()->count();
    }

}