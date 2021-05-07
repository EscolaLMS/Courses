<?php namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use EscolaLms\Courses\Tests\TestCase;
//use Tests\ApiTestTrait;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Course;

use EscolaLms\Courses\Database\Seeders\CoursesPermissionSeeder;

class TopicTutorApiTest extends TestCase
{
    use /*ApiTestTrait,*/ WithoutMiddleware, DatabaseTransactions;


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
    public function test_read_topic()
    {
        $course = Course::factory()->create([
            'author_id' => $this->user->id
        ]);
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id
        ]);
        $topic = Topic::factory()->create([
            'lesson_id' => $lesson->id
        ]);

        

        $this->response = $this->actingAs($this->user, 'api')->json(
            'GET',
            '/api/topics/'.$topic->id
        );

        $this->assertApiResponse($topic->toArray());
    }

   
    /**
     * @test
     */
    public function test_delete_topic()
    {
        $course = Course::factory()->create([
            'author_id' => $this->user->id
        ]);
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id
        ]);
        $topic = Topic::factory()->create([
            'lesson_id' => $lesson->id
        ]);

        $this->response = $this->actingAs($this->user, 'api')->json(
            'DELETE',
            '/api/topics/'.$topic->id
        );

        $this->assertApiSuccess();
        $this->response = $this->actingAs($this->user, 'api')->json(
            'GET',
            '/api/topics/'.$topic->id
        );

        $this->response->assertStatus(404);
    }

    public function test_delete_restrict_topic()
    {
        $topic = Topic::factory()->create();
        $this->response = $this->actingAs($this->user, 'api')->json(
            'DELETE',
            '/api/topics/'.$topic->id
        );

        $this->response->assertStatus(403);
    }
}
