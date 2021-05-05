<?php


namespace EscolaLms\Courses\Tests\APIs;


use EscolaLms\Core\Models\User;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\CourseProgress;
use EscolaLms\Courses\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class CourseProgressApiTest extends TestCase
{
    use WithoutMiddleware, DatabaseTransactions;


    public function test_show_progress_course()
    {
        $user = User::factory()->create();
        $courses = Course::factory(5)->create();
        foreach ($courses as $course) {
            $progress = CourseProgress::create([
                'user_id' => $user->getKey(),
                'course_id' => $course->getKey(),
                'status' => 1
            ]);
            $user->courses()->save($course);
        }

        $this->response = $this->actingAs($user, 'api')->json(
            'GET',
            '/api/progress'
        );
        $this->response->assertStatus(200);
        $this->assertIsArray($this->response->getData());
        foreach ($this->response->getData() as $data) {
            $this->assertObjectHasAttribute('course', $data);
            $this->assertObjectHasAttribute('progress', $data);
            $this->assertNotEmpty($data->progress);
        }
    }
}