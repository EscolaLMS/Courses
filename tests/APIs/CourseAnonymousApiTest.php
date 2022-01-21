<?php

namespace EscolaLms\Courses\Tests\APIs;

use EscolaLms\Categories\Models\Category;
use EscolaLms\Courses\Enum\CourseStatusEnum;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CourseAnonymousApiTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function test_anonymous_create_course()
    {
        $course = Course::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/admin/courses',
            $course
        );

        $this->response->assertStatus(401);
    }

    /**
     * @test
     */
    public function test_anonymous_read_course()
    {
        $course = Course::factory()->create([
            'status' => CourseStatusEnum::PUBLISHED
        ]);

        $this->response = $this->json(
            'GET',
            '/api/admin/courses/' . $course->id
        );
        $this->response->assertStatus(401);

        $this->response = $this->json(
            'GET',
            '/api/courses/' . $course->id
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
            '/api/admin/courses/' . $course->id,
            $editedCourse
        );

        $this->response->assertStatus(401);
    }

    /**
     * @test
     */
    public function test_anonymous_delete_course()
    {
        $course = Course::factory()->create();

        $this->response = $this->json(
            'DELETE',
            '/api/admin/courses/' . $course->id
        );

        $this->response->assertStatus(401);
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
            '/api/admin/courses?category_id=' . $category->getKey()
        );

        $this->response->assertStatus(401);

        $this->response = $this->json(
            'GET',
            '/api/courses?category_id=' . $category->getKey()
        );

        $this->response->assertStatus(200);
        $this->response->assertJsonStructure([
            'data'
        ]);

        $courses_ids = [$category->getKey(), $category2->getKey()];

        foreach ($this->response->getData()->data as $data) {
            foreach ($data->categories as $courseCategory) {
                $this->assertTrue(in_array($courseCategory->id, $courses_ids));
            }
        }
    }

    /**
     * @test
     */
    public function test_anonymous_read_course_program()
    {
        $course = Course::factory()->create(['base_price' => 9999, 'status' => CourseStatusEnum::PUBLISHED]);

        $this->response = $this->json(
            'GET',
            '/api/admin/courses/' . $course->id . '/program'
        );

        $this->response->assertStatus(401);

        $this->response = $this->json(
            'GET',
            '/api/courses/' . $course->id . '/program'
        );

        $this->response->assertStatus(403);
    }

    public function test_anonymous_read_free_course_program()
    {
        $course = Course::factory()->create(['base_price' => 0, 'status' => CourseStatusEnum::PUBLISHED]);

        $this->response = $this->json(
            'GET',
            '/api/admin/courses/' . $course->id . '/program'
        );

        $this->response->assertStatus(401);

        $this->response = $this->json(
            'GET',
            '/api/courses/' . $course->id . '/program'
        );

        $this->response->assertStatus(200);
        $this->assertApiResponse($course->toArray());
    }

    public function test_anonymous_sorting()
    {
        $priceMin = 0;
        $priceMax = 9999999;
        $course1 = Course::factory()->create(['base_price' => $priceMin, 'status' => CourseStatusEnum::PUBLISHED]);
        $course2 = Course::factory()->create(['base_price' => $priceMax, 'status' => CourseStatusEnum::PUBLISHED]);
        $course3 = Course::factory()->create(['base_price' => $priceMax + 1, 'status' => CourseStatusEnum::PUBLISHED]);

        $this->response = $this->json(
            'GET',
            '/api/courses/?order_by=base_price&order=ASC'
        );

        $this->assertEquals($this->response->getData()->data[0]->base_price, 0);
        $this->response->assertStatus(200);

        $this->response = $this->json(
            'GET',
            '/api/courses/?order_by=base_price&order=DESC'
        );

        $this->assertEquals($this->response->getData()->data[0]->base_price, $priceMax);
        $this->response->assertStatus(200);
    }


    public function test_anonymous_only_active()
    {
        $priceMin = 0;
        $priceMax = 9999999;
        $course1 = Course::factory()->create(['base_price' => $priceMin, 'status' => CourseStatusEnum::PUBLISHED]);
        $course2 = Course::factory()->create(['base_price' => $priceMax, 'status' => CourseStatusEnum::DRAFT]);

        $this->response = $this->json(
            'GET',
            '/api/courses/?order_by=base_price&order=ASC'
        );
        $this->response->assertStatus(200);

        $courses = $this->response->getData()->data;

        foreach ($courses as $course) {
            $this->assertEquals( CourseStatusEnum::PUBLISHED, $course->status);
        }

        $this->response = $this->json(
            'GET',
            '/api/courses/?order_by=base_price&order=DESC'
        );

        $this->response->assertStatus(200);

        $courses = $this->response->getData()->data;

        foreach ($courses as $course) {
            $this->assertEquals(CourseStatusEnum::PUBLISHED, $course->status);
        }
    }

    public function test_anonymous_only_findable()
    {
        $priceMin = 0;
        $priceMax = 9999999;
        $course1 = Course::factory()->create(['base_price' => $priceMin, 'status' => CourseStatusEnum::PUBLISHED, 'findable' => false]);
        $course2 = Course::factory()->create(['base_price' => $priceMax, 'status' => CourseStatusEnum::ARCHIVED, 'findable' => true]);

        $this->response = $this->json(
            'GET',
            '/api/courses/?order_by=base_price&order=ASC'
        );
        $this->response->assertStatus(200);

        $courses = $this->response->getData()->data;

        foreach ($courses as $course) {
            $this->assertEquals(CourseStatusEnum::PUBLISHED, $course->status);
            $this->assertTrue($course->findable);
        }

        $this->response = $this->json(
            'GET',
            '/api/courses/?order_by=base_price&order=DESC'
        );

        $this->response->assertStatus(200);

        $courses = $this->response->getData()->data;

        foreach ($courses as $course) {
            $this->assertEquals(CourseStatusEnum::PUBLISHED, $course->status);
            $this->assertTrue($course->findable);
        }
    }

    /**
     * @test
     */
    public function test_search_courses_by_ids()
    {
        $firstCourse = Course::factory()->create(['status' => CourseStatusEnum::PUBLISHED]);
        $secondCourse = Course::factory()->create(['status' => CourseStatusEnum::PUBLISHED]);

        $this->response = $this->json(
            'GET',
            '/api/courses?ids[]=' . $firstCourse->id . '&ids[]=' . $secondCourse->id
        );

        $this->response->assertStatus(200);

        $courses = $this->response->getData()->data;
        $this->assertCount(2, $courses);
        $this->response->assertJsonFragment([
            'id' => $firstCourse->id,
        ]);
        $this->response->assertJsonFragment([
            'id' => $secondCourse->id,
        ]);

        $this->assertEquals(2, $this->response->getData()->meta->total);
    }
}
