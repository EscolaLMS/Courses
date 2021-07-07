<?php namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use EscolaLms\Courses\Tests\TestCase;
//use Tests\ApiTestTrait;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Course;

use EscolaLms\Courses\Database\Seeders\CoursesPermissionSeeder;

class LessonTutorApiTest extends TestCase
{
    use /*ApiTestTrait,*/ DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CoursesPermissionSeeder::class);

        $this->user = config('auth.providers.users.model')::factory()->create();
        $this->user->guard_name = 'api';
        $this->user->assignRole('tutor');
    }

    /**
     * @test
     */
    public function test_create_lesson()
    {
        $course = Course::factory()->create([
            'author_id' => $this->user->id
        ]);
        $lesson = Lesson::factory()->make(['course_id' => $course->id])->toArray();

        $this->response = $this->actingAs($this->user, 'api')->json(
            'POST',
            '/api/admin/lessons',
            $lesson
        );

        $this->assertApiResponse($lesson);
    }

    /**
     * @test
     */
    public function test_read_lesson()
    {
        $lesson = Lesson::factory()->create();

        $this->response = $this->actingAs($this->user, 'api')->json(
            'GET',
            '/api/admin/lessons/'.$lesson->id
        );

        $this->assertApiResponse($lesson->toArray());
    }

    /**
     * @test
     */
    public function test_update_lesson()
    {
        $course = Course::factory()->create([
            'author_id' => $this->user->id
        ]);
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        $editedLesson = Lesson::factory()->make()->toArray();

        $this->response = $this->actingAs($this->user, 'api')->json(
            'PUT',
            '/api/admin/lessons/'.$lesson->id,
            $editedLesson
        );

        $this->assertApiResponse($editedLesson);
    }

    /**
     * @test
     */
    public function test_delete_lesson()
    {
        $course = Course::factory()->create([
            'author_id' => $this->user->id
        ]);
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        $this->response = $this->actingAs($this->user, 'api')->json(
            'DELETE',
            '/api/admin/lessons/'.$lesson->id
        );

        $this->assertApiSuccess();
        $this->response = $this->actingAs($this->user, 'api')->json(
            'GET',
            '/api/admin/lessons/'.$lesson->id
        );

        $this->response->assertStatus(404);
    }
}
