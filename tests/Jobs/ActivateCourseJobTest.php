<?php

namespace EscolaLms\Courses\Tests\Jobs;

use Carbon\Carbon;
use EscolaLms\Courses\Enum\CourseStatusEnum;
use EscolaLms\Courses\Events\CourseStatusChanged;
use EscolaLms\Courses\Jobs\ActivateCourseJob;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;

class ActivateCourseJobTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    public function testActivatePublishedCourse(): void
    {
        Event::fake([CourseStatusChanged::class]);

        $course = Course::factory()->create([
            'status' => CourseStatusEnum::PUBLISHED_UNACTIVATED,
            'active_from' => Carbon::now()->subHour(),
            'active_to' => Carbon::now()->addDay(),
        ]);

        $course2 = Course::factory()->create([
            'status' => CourseStatusEnum::PUBLISHED_UNACTIVATED,
            'active_from' => Carbon::now()->addDay(),
            'active_to' => Carbon::now()->addDays(2),
        ]);

        $job = new ActivateCourseJob();
        $job->handle();

        $course->refresh();
        $this->assertEquals(CourseStatusEnum::PUBLISHED, $course->status);

        Event::assertDispatched(function (CourseStatusChanged $courseStatusChanged) use ($course) {
            $this->assertEquals($course->getKey(), $courseStatusChanged->getCourse()->getKey());
            return true;
        });

        $course2->refresh();
        $this->assertEquals(CourseStatusEnum::PUBLISHED_UNACTIVATED, $course2->status);
    }
}
