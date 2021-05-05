<?php namespace Tests\APIs;

use EscolaLms\Categories\Models\Category;
use EscolaLms\Tags\Models\Tag;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use EscolaLms\Courses\Tests\TestCase;
//use Tests\ApiTestTrait;
use EscolaLms\Courses\Models\Course;

class CourseApiTest extends TestCase
{
    use /*ApiTestTrait,*/ WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_course()
    {
        $course = Course::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/courses',
            $course
        );

        $this->assertApiResponse($course);
    }

    /**
     * @test
     */
    public function test_read_course()
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
    public function test_update_course()
    {
        $course = Course::factory()->create();
        $editedCourse = Course::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/courses/'.$course->id,
            $editedCourse
        );

        $this->assertApiResponse($editedCourse);
    }

    /**
     * @test
     */
    public function test_delete_course()
    {
        $course = Course::factory()->create();

        $this->response = $this->json(
            'DELETE',
            '/api/courses/'.$course->id
        );
        

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/courses/'.$course->id
        );

        $this->response->assertStatus(404);
    }

    public function test_category_course()
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
            '/api/courses/search/' . $category->getKey()
        );
        $this->response->assertStatus(200);
        $this->assertObjectHasAttribute('data', $this->response->getData());
        $this->assertObjectHasAttribute('data', $this->response->getData()->data);
        foreach ($this->response->getData()->data->data as $data) {
            $this->assertFalse($data->category_id !== $category->getKey() and $data->category_id !== $category2->getKey());
        }
    }

    public function test_attach_categories_course()
    {
        $course = Course::factory()->create();
        $categoriesIds = Category::factory(5)->create()->pluck('id')->toArray();
        $this->response = $this->json(
            'POST',
            '/api/courses/attach/'.$course->getKey().'/categories',
            ['categories' => $categoriesIds]
        );
        $this->response->assertStatus(200);
    }

    public function test_attach_tags_course()
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
        $this->response->assertStatus(200);
    }

    public function test_search_course_by_tag()
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
        $this->response->assertStatus(200);

        $this->response = $this->json(
            'GET',
            '/api/courses/search/tags',
            ['tag' => 'Fruit']
        );
        $this->response->assertStatus(200);
        $this->assertObjectHasAttribute('data', $this->response->getData());
        $this->assertObjectHasAttribute('data', $this->response->getData()->data);
        foreach ($this->response->getData()->data->data as $data) {
            $this->assertFalse(empty($data->tags));
            foreach ($data->tags as $tag) {
                $this->assertTrue($tag->title === 'Fruit');
            }
        }
    }

    /**
     * @test
     */
    public function test_read_course_program()
    {
        $course = Course::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/courses/'.$course->id.'/program'
        );

        $this->response->assertStatus(200);
    }
}
