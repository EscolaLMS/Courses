<?php

namespace EscolaLms\Courses\Tests\APIs;

use EscolaLms\Core\Tests\CreatesUsers;
use EscolaLms\Courses\Enum\CourseStatusEnum;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Group;
use EscolaLms\Courses\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

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
}
