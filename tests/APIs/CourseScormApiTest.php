<?php

namespace Tests\APIs;

use EscolaLms\Categories\Models\Category;
use EscolaLms\Tags\Models\Tag;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use EscolaLms\Courses\Tests\TestCase;
//use Tests\ApiTestTrait;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Database\Seeders\CoursesPermissionSeeder;
use EscolaLms\Scorm\Database\Seeders\DatabaseSeeder;
use Laravel\Passport\Passport;
use EscolaLms\Scorm\Database\Seeders;
use Peopleaps\Scorm\Model\ScormModel;

class CourseScormApiTest extends TestCase
{
    use /*ApiTestTrait,*/ DatabaseTransactions;

    /**
     * @test
     */

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_read_scorm()
    {
        $scorm = ScormModel::firstOrFail();
        $course = Course::factory()->create(['base_price' => 0, 'scorm_id' => $scorm->id]);


        $this->response = $this->get(
            '/api/courses/' . $course->id . '/scorm'
        );

        $this->response->assertStatus(200);

        $this->assertStringContainsString('<iframe', $this->response->getContent());
    }
}
