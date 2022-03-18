<?php

namespace EscolaLms\Courses\Tests\APIs;

use EscolaLms\Core\Tests\CreatesUsers;
use EscolaLms\Courses\Enum\CourseStatusEnum;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Tests\TestCase;
use EscolaLms\Scorm\Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Peopleaps\Scorm\Model\ScormModel;

class CourseScormApiTest extends TestCase
{
    use DatabaseTransactions, CreatesUsers;

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
        $user = $this->makeStudent();

        $scorm = ScormModel::with('scos')->firstOrFail();
        $sco = $scorm->scos->first();

        $course = Course::factory()->create([
            'scorm_sco_id' => $sco->id,
            'status' => CourseStatusEnum::PUBLISHED
        ]);
        $course->users()->attach($user);

        $this->response = $this
            ->actingAs($user, 'api')
            ->get('/api/courses/' . $course->id . '/scorm');

        $this->response->assertStatus(200);

        $this->assertStringContainsString('<iframe', $this->response->getContent());
    }
}
