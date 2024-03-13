<?php

namespace EscolaLms\Courses\Tests\APIs;

use EscolaLms\Core\Tests\CreatesUsers;
use EscolaLms\Courses\Enum\CourseStatusEnum;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Group;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Tests\Models\TopicContent\ExampleTopicType;
use EscolaLms\Courses\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;

class CourseProgramApiTest extends TestCase
{
    use CreatesUsers, DatabaseTransactions;

    public function testShowProgramForStudentInNestedGroup(): void
    {
        $student = $this->makeStudent();
        $course = Course::factory()->state(['status' => CourseStatusEnum::PUBLISHED])->create();

        $mainGroup = Group::factory()->create();
        $childGroup = Group::factory()->state(['parent_id' => $mainGroup->getKey()])->create();
        $childGroup2 = Group::factory()->state(['parent_id' => $childGroup->getKey()])->create();

        $childGroup2->users()->attach($student);

        $this->actingAs($student, 'api')->getJson('api/courses/' . $course->getKey() . '/program')
            ->assertForbidden();

        $mainGroup->courses()->attach($course->getKey());

        $this->actingAs($student, 'api')->getJson('api/courses/' . $course->getKey() . '/program')
            ->assertStatus(200);
    }

    public function testShowCourseProgramForLessonAvailableBetweenDates(): void
    {
        $student = $this->makeStudent();
        $course = Course::factory()->state(['status' => CourseStatusEnum::PUBLISHED])->create();
        $course->users()->attach($student);

        $parentLesson = Lesson::factory()->state([
            'course_id' => $course->getKey(),
            'active' => true,
        ])
            ->create();

        $lesson = Lesson::factory()->state([
            'course_id' => $course->getKey(),
            'active' => true,
            'parent_lesson_id' => $parentLesson->getKey(),
        ])
            ->create();

        $topicable = ExampleTopicType::factory()->create();
        Topic::factory()->state([
            'active' => true,
            'lesson_id' => $lesson->getKey(),
            'topicable_type' => ExampleTopicType::class,
            'topicable_id' => $topicable->getKey(),
        ])
            ->create();

        $this->actingAs($student, 'api')
            ->getJson('api/courses/' . $course->getKey() . '/program')
            ->assertOk()
            ->assertJsonFragment([
                'topicable' => [
                    'value' => $topicable->value,
                    'created_at' => $topicable->created_at,
                    'updated_at' => $topicable->updated_at,
                    'id' => $topicable->getKey(),
                ],
            ]);

        // update
        $lesson->update([
            'active_from' => Carbon::now()->addMonth(),
            'active_to' => Carbon::now()->addMonth()->addMinutes(),
        ]);

        $this->actingAs($student, 'api')
            ->getJson('api/courses/' . $course->getKey() . '/program')
            ->assertOk()
            ->assertJsonFragment([
                'topicable' => null,
            ]);
    }

    public function testCannotShowCourseProgramWhenEndDateIsOverdue(): void
    {
        $student = $this->makeStudent();
        $course = Course::factory()->state(['status' => CourseStatusEnum::PUBLISHED])->create();
        $course->users()->attach($student, ['end_date' => Carbon::now()->subDay()]);

        $this->actingAs($student, 'api')
            ->getJson('api/courses/' . $course->getKey() . '/program')
            ->assertForbidden();
    }
}
