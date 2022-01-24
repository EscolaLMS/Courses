<?php

namespace EscolaLms\Courses\Tests\APIs;

use EscolaLms\Courses\Enum\CourseStatusEnum;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Tests\TestCase;
use EscolaLms\Scorm\Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Peopleaps\Scorm\Model\ScormModel;

class CourseScormApiTest extends TestCase
{
    use DatabaseTransactions;

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
        $scorm = ScormModel::with('scos')->firstOrFail();
        $sco = $scorm->scos->first();
        $course = Course::factory()->create([
            'base_price' => 0,
            'scorm_sco_id' => $sco->id,
            'status' => CourseStatusEnum::PUBLISHED]
        );

        $this->response = $this->get(
            '/api/courses/' . $course->id . '/scorm'
        );

        $this->response->assertStatus(200);

        $this->assertStringContainsString('<iframe', $this->response->getContent());
    }
}
