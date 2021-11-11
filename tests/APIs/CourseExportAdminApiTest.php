<?php

namespace Tests\APIs;

use EscolaLms\Core\Tests\CreatesUsers;
use EscolaLms\Courses\Database\Seeders\CoursesPermissionSeeder;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;

class CourseExportAdminApiTest extends TestCase
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
        $this->user = config('auth.providers.users.model')::factory()->create();
        $this->user->guard_name = 'api';
        $this->user->assignRole('admin');

        Storage::fake(config('filesystems.default'));
    }

    /**
     * @test
     */
    public function testExportiCreated()
    {
        $course = Course::factory()->create();

        $this->response = $this->actingAs($this->user, 'api')->json(
            'GET',
            '/api/admin/courses/'.$course->id.'/export/'
        );

        $this->response->assertOk();

        $data = $this->response->getData();

        $filename = basename($data->data);

        $filepath = sprintf('exports/courses/%d/%s', $course->id, $filename);

        Storage::assertExists($filepath);
    }
}
