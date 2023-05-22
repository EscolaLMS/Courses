<?php

namespace EscolaLms\Courses\Tests\APIs;

use EscolaLms\Core\Tests\CreatesUsers;
use EscolaLms\Courses\Database\Seeders\CoursesPermissionSeeder;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CourseAuthoredApiTest extends TestCase
{
    use CreatesUsers;
    use DatabaseTransactions;

    /**
     * @test
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CoursesPermissionSeeder::class);
        $this->admin = $this->makeAdmin();
    }

    public function test_get_authored_courses()
    {
        Course::factory()->count(5);
        $course1 = Course::factory()->create();
        $course2 = Course::factory()->create();

        $course1->authors()->save($this->admin);
        $course2->authors()->save($this->admin);

        $this
            ->actingAs($this->admin, 'api')
            ->json(
                'GET',
                '/api/courses/authored',
            )
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }
}
