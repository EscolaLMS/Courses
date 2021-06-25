<?php namespace Tests\APIs;

use EscolaLms\Categories\Models\Category;
use EscolaLms\Tags\Models\Tag;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use EscolaLms\Courses\Tests\TestCase;
//use Tests\ApiTestTrait;
use EscolaLms\Courses\Models\Course;

class CourseAnonymousApiTest extends TestCase
{
    use /*ApiTestTrait,*/ WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_anonymous_create_course()
    {
        $course = Course::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/courses',
            $course
        );

        $this->response->assertStatus(403);
    }

    /**
     * @test
     */
    public function test_anonymous_read_course()
    {
        $course = Course::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/courses/'.$course->id
        );

        $this->assertApiResponse($course->toArray());
    }

    /**
     * @test
     */
    public function test_anonymous_update_course()
    {
        $course = Course::factory()->create();
        $editedCourse = Course::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/courses/'.$course->id,
            $editedCourse
        );

        $this->response->assertStatus(403);
    }

    /**
     * @test
     */
    public function test_anonymous_delete_course()
    {
        $course = Course::factory()->create();

        $this->response = $this->json(
            'DELETE',
            '/api/courses/'.$course->id
        );
        
        $this->response->assertStatus(403);
    }

    public function test_anonymous_category_course()
    {
        $category = Category::factory()->create();
        $category2 = Category::factory()->create();
        $category->children()->save($category2);
        $course = Course::factory()->create();
        $course2 = Course::factory()->create();
        $course->categories()->save($category);
        $course2->categories()->save($category2);
        $this->response = $this->json(
            'GET',
            '/api/courses?category_id=' . $category->getKey()
        );

        $this->response->assertStatus(200);
        $this->assertObjectHasAttribute('data', $this->response->getData());
        $this->assertObjectHasAttribute('data', $this->response->getData()->data);

        $courses_ids = [$category->getKey(), $category2->getKey()];

        foreach ($this->response->getData()->data->data as $data) {
            foreach ($data->categories as $courseCategory) {
                $this->assertTrue(in_array($courseCategory->id, $courses_ids));
            }
        }
    }

    public function test_anonymous_attach_categories_course()
    {
        $course = Course::factory()->create();
        $categoriesIds = Category::factory(5)->create()->pluck('id')->toArray();
        $this->response = $this->json(
            'POST',
            '/api/courses/attach/'.$course->getKey().'/categories',
            ['categories' => $categoriesIds]
        );

        $this->response->assertStatus(403);
    }

    public function test_anonymous_attach_tags_course()
    {
        $course = Course::factory()->create();
        $this->response = $this->json(
            'POST',
            '/api/courses/attach/'.$course->getKey().'/tags',
            ['tags' => [
                [
                    'title' => 'Nowości'
                ],
                [
                    'title' => 'Promocje'
                ],
                [
                    'title' => 'Owoce'
                ],
            ]]
        );
        $this->response->assertStatus(403);
    }

    public function test_anonymous_attach_course_by_tag()
    {
        $course = Course::factory()->create();
        $this->response = $this->json(
            'POST',
            '/api/courses/attach/'.$course->getKey().'/tags',
            ['tags' => [
                [
                    'title' => 'Fruit'
                ],
            ]]
        );
        $this->response->assertStatus(403);
    }

    /**
     * @test
     */
    public function test_anonymous_read_course_program()
    {
        $course = Course::factory()->create(['base_price'=>9999]);

        $this->response = $this->json(
            'GET',
            '/api/courses/'.$course->id.'/program'
        );

        $this->response->assertStatus(403);
    }

    public function test_anonymous_read_free_course_program()
    {
        $course = Course::factory()->create(['base_price'=>0]);

        $this->response = $this->json(
            'GET',
            '/api/courses/'.$course->id.'/program'
        );

        $this->response->assertStatus(200);
    }
}
