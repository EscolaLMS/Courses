<?php

namespace EscolaLms\Courses\Tests\APIs;

use EscolaLms\Categories\Models\Category;
use EscolaLms\Core\Tests\CreatesUsers;
use EscolaLms\Courses\Database\Seeders\CoursesPermissionSeeder;
use EscolaLms\Courses\Events\EscolaLmsCoursedPublishedTemplateEvent;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Tests\TestCase;
use EscolaLms\Tags\Models\Tag;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

class CourseAdminApiTest extends TestCase
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
    }

    public function test_create_course()
    {
        $course = Course::factory()->make()->toArray();

        $this->response = $this->actingAs($this->user, 'api')->json(
            'POST',
            '/api/admin/courses',
            $course
        );

        $course['author_id'] = $this->user->id;

        $this->response->assertStatus(201);

        $this->assertApiResponse($course);
    }

    public function test_create_course_active()
    {
        Event::fake();
        $course = Course::factory([
            'active' => true
        ])->make()->toArray();

        $this->response = $this->actingAs($this->user, 'api')->json(
            'POST',
            '/api/admin/courses',
            $course
        );

        $course['author_id'] = $this->user->id;

        $this->response->assertStatus(201);

        $this->assertApiResponse($course);
        Event::assertDispatched(EscolaLmsCoursedPublishedTemplateEvent::class);
    }


    public function test_create_and_update_course_with_deadline()
    {
        $course = Course::factory()->make([
            'active' => true,
            'active_from' => Carbon::now()->subDay(1),
            'active_to' => Carbon::now()->addDay(1),
            'hours_to_complete' => 24,
        ])->toArray();

        $this->response = $this->actingAs($this->user, 'api')->json(
            'POST',
            '/api/admin/courses',
            $course
        );

        $course['author_id'] = $this->user->id;

        $this->response->assertStatus(201);

        $this->assertApiResponse($course);

        $dbCourse = Course::find($this->response->json('data.id'));
        $this->assertTrue($dbCourse->is_active);

        $course['active_from'] = Carbon::now()->subDay(2);
        $course['active_to'] = Carbon::now()->subDay(1);
        $this->response = $this->actingAs($this->user, 'api')->json(
            'PUT',
            '/api/admin/courses/' . $dbCourse->getKey(),
            $course
        );

        $dbCourse->refresh();
        $this->assertFalse($dbCourse->is_active);
    }

    /**
     * @test
     */
    public function test_read_course()
    {
        $course = Course::factory()->create();

        $this->response = $this->actingAs($this->user, 'api')->json(
            'GET',
            '/api/admin/courses/' . $course->id
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

        $this->response = $this->actingAs($this->user, 'api')->json(
            'PUT',
            '/api/admin/courses/' . $course->id,
            $editedCourse
        );

        $this->assertApiResponse($editedCourse);
    }

    public function test_active_course()
    {
        Event::fake();
        $course = Course::factory([
            'active' => false
        ])->create();
        $editedCourse = Course::factory([
            'active' => true
        ])->make()->toArray();

        $this->response = $this->actingAs($this->user, 'api')->json(
            'PUT',
            '/api/admin/courses/' . $course->id,
            $editedCourse
        );

        $this->assertApiResponse($editedCourse);
        Event::assertDispatched(EscolaLmsCoursedPublishedTemplateEvent::class);
    }

    /**
     * @test
     */
    public function test_update_course_with_correct_author()
    {
        $course = Course::factory()->create();
        $editedCourse = Course::factory()->make()->toArray();

        $tutor = $this->makeInstructor();
        $editedCourse['author_id'] = $tutor->getKey();

        $this->response = $this->actingAs($this->user, 'api')->json(
            'PUT',
            '/api/admin/courses/' . $course->id,
            $editedCourse
        );

        $this->response->assertStatus(200);
        $this->response->assertValid('author_id');
    }

    /**
     * @test
     */
    public function test_update_course_with_wrong_author()
    {
        $course = Course::factory()->create();
        $editedCourse = Course::factory()->make()->toArray();

        $student = $this->makeStudent();
        $editedCourse['authors'][] = $student->getKey();

        $this->response = $this->actingAs($this->user, 'api')->json(
            'PUT',
            '/api/admin/courses/' . $course->id,
            $editedCourse
        );

        $this->response->assertStatus(422);
        $this->response->assertInvalid('authors.0');
    }

    /**
     * @test
     */
    public function test_delete_course()
    {
        $course = Course::factory()->create();

        $this->response = $this->actingAs($this->user, 'api')->json(
            'DELETE',
            '/api/admin/courses/' . $course->id
        );

        $this->assertApiSuccess();
        $this->response = $this->actingAs($this->user, 'api')->json(
            'GET',
            '/api/admin/courses/' . $course->id
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
        $this->response = $this->actingAs($this->user, 'api')->json(
            'GET',
            '/api/admin/courses/?category_id=' . $category->getKey()
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
            //$this->assertFalse($data->category_id !== $category->getKey() and $data->category_id !== $category2->getKey());
        }
    }

    public function test_attach_categories_course()
    {
        $course = Course::factory()->create();
        $categoriesIds = Category::factory(5)->create()->pluck('id')->toArray();

        $this->response = $this->actingAs($this->user, 'api')->json(
            'PUT',
            '/api/admin/courses/' . $course->getKey(),
            ['categories' => $categoriesIds]
        );

        $this->response->assertStatus(200);

        $this->response = $this->json(
            'GET',
            '/api/admin/courses/' . $course->id
        );

        foreach ($this->response->getData()->data->categories as $category) {
            $this->assertTrue(in_array($category->id,  $categoriesIds));
        }
    }

    public function test_attach_tags_course()
    {
        $course = Course::factory()->create();

        $tags = ['Lorem', 'Ipsum', "Bla Bla bla"];

        $this->response = $this->actingAs($this->user, 'api')->json(
            'PUT',
            '/api/admin/courses/' . $course->getKey(),
            ['tags' =>  $tags]
        );

        $this->response->assertStatus(200);

        foreach ($this->response->getData()->data->tags as $tag) {
            $this->assertTrue(in_array($tag->title,  $tags));
        }
    }

    public function test_search_course_by_tag()
    {
        $course = Course::factory()->create();

        $tags = ['LoremLorem Lorem', 'Ipsum', "Bla Bla bla"];

        $this->response = $this->actingAs($this->user, 'api')->json(
            'PUT',
            '/api/admin/courses/' . $course->getKey(),
            ['tags' =>  $tags]
        );

        $this->response->assertStatus(200);

        $this->response = $this->actingAs($this->user, 'api')->json(
            'GET',
            '/api/admin/courses/?tag=' . $tags[0],
        );

        $coursesIds = [];

        foreach ($this->response->getData()->data as $course) {
            $coursesIds[] = $course->id;
        }

        $this->assertTrue(in_array($course->id,  $coursesIds));
    }

    /**
     * @test
     */
    public function test_read_course_program()
    {
        $course = Course::factory()->create();

        $this->response = $this->actingAs($this->user, 'api')->json(
            'GET',
            '/api/admin/courses/' . $course->id . '/program'
        );

        $this->response->assertStatus(200);
    }

    /**
     * @test
     */
    public function test_read_course_program_scorm()
    {
        $course = Course::factory()->create();

        $this->response = $this->actingAs($this->user, 'api')->json(
            'GET',
            '/api/admin/courses/' . $course->id . '/program'
        );

        $this->response->assertStatus(200);
    }

    public function test_inactive_for_admins()
    {
        $priceMin = 0;
        $priceMax = 999999;
        $course1 = Course::factory()->create(['base_price' => $priceMin, 'active' => true]);
        $course2 = Course::factory()->create(['base_price' => $priceMax, 'active' => true]);
        $course3 = Course::factory()->create(['base_price' => $priceMax + 1, 'active' => false]);

        $this->response = $this->json(
            'GET',
            '/api/courses/?order_by=base_price&order=DESC'
        );

        $this->assertEquals($this->response->getData()->data[0]->base_price, $priceMax);
        $this->response->assertStatus(200);

        $this->response = $this->actingAs($this->user, 'api')->json(
            'GET',
            '/api/courses/?order_by=base_price&order=DESC'
        );

        $this->assertEquals($this->response->getData()->data[0]->base_price, $priceMax + 1);
        $this->response->assertStatus(200);
    }

    public function test_admin_active_search()
    {

        $this->response = $this->json(
            'GET',
            '/api/courses/?active=true'
        );
        $this->response->assertStatus(200);
        $courses = $this->response->getData()->data;

        foreach ($courses as $course) {
            $this->assertTrue($course->active, true);
        }

        $this->response = $this->json(
            'GET',
            '/api/courses/?active=false'
        );
        $this->response->assertStatus(200);
        $courses = $this->response->getData()->data;

        foreach ($courses as $course) {
            $this->assertTrue($course->active, false);
        }
    }

    public function test_admin_sort_lessons_in_course()
    {
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id
        ]);
        $topic = Topic::factory()->create([
            'lesson_id' => $lesson->id
        ]);
        $lesson2 = Lesson::factory()->create([
            'course_id' => $course->id
        ]);
        $topic2 = Topic::factory()->create([
            'lesson_id' => $lesson->id
        ]);

        $this->response = $this->actingAs($this->user, 'api')->json(
            'POST',
            '/api/admin/courses/sort',
            [
                'class' => 'Lesson',
                'course_id' => $course->getKey(),
                'orders' => [
                    [$lesson2->getKey(), 0],
                    [$lesson->getKey(), 1]
                ]
            ]
        );
        $this->response->assertOk();
    }

    public function test_admin_sort_topics_in_lesson()
    {
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id
        ]);
        $topic = Topic::factory()->create([
            'lesson_id' => $lesson->id
        ]);
        $lesson2 = Lesson::factory()->create([
            'course_id' => $course->id
        ]);
        $topic2 = Topic::factory()->create([
            'lesson_id' => $lesson->id
        ]);

        $this->response = $this->actingAs($this->user, 'api')->json(
            'POST',
            '/api/admin/courses/sort',
            [
                'class' => 'Topic',
                'course_id' => $course->getKey(),
                'orders' => [
                    [$topic2->getKey(), 0],
                    [$topic->getKey(), 1]
                ]
            ]
        );
        $this->response->assertOk();
    }

    /**
     * @test
     */
    public function test_create_admin_course_poster()
    {
        Storage::fake('local');
        $poster = UploadedFile::fake()->image('poster.jpg');

        $this->response = $this->actingAs($this->user, 'api')->post(
            '/api/admin/courses',
            [
                'title' => "Test create course poster",
                'poster' => $poster
            ]
        );

        $this->response->assertStatus(201);

        $data = json_decode($this->response->getContent());
        $path = $data->data->poster_path;

        Storage::disk('local')->assertExists('/' . $path);
        $this->assertDatabaseHas('courses', [
            'poster_path' => $path
        ]);
    }

    public function test_delete_admin_course_poster()
    {
        Storage::fake('local');
        $poster = UploadedFile::fake()->image('poster.jpg');

        $this->response = $this->actingAs($this->user, 'api')->post(
            '/api/admin/courses',
            [
                'title' => "Test create course poster",
                'poster' => $poster
            ]
        );

        $this->response->assertStatus(201);

        $data = $this->response->json();

        $course_id = $data['data']['id'];
        $path = $data['data']['poster_path'];

        Storage::disk('local')->assertExists('/' . $path);
        $this->assertDatabaseHas('courses', [
            'poster_path' => $path
        ]);

        $this->response = $this->actingAs($this->user, 'api')->json(
            'DELETE',
            '/api/admin/courses/' . $course_id
        );

        Storage::disk('local')->assertMissing('/' . $path);
    }

    /**
     * @test
     */
    public function test_update_admin_course_poster()
    {
        $course = Course::factory()->create();

        Storage::fake('local');
        $poster = UploadedFile::fake()->image('poster.jpg');

        $this->response = $this->actingAs($this->user, 'api')->post(
            '/api/admin/courses/' . $course->id,
            [
                'poster' => $poster
            ]
        );

        $this->response->assertStatus(200);

        $data = json_decode($this->response->getContent());
        $path = $data->data->poster_path;

        Storage::disk('local')->assertExists('/' . $path);
        $this->assertDatabaseHas('courses', [
            'poster_path' => $path
        ]);
    }

    public function test_unique_tags_courses() : void
    {
        $courseActive = Course::factory(['active' => true])->create();
        $courseUnActive = Course::factory(['active' => false])->create();
        $tagActiveCourse = Tag::factory([
            'morphable_type' => Course::class,
            'morphable_id' => $courseActive->getKey()
        ])->create();
        $tagUnActiveCourse = Tag::factory([
            'morphable_type' => Course::class,
            'morphable_id' => $courseUnActive->getKey()
        ])->create();

        $response = $this->json('GET', '/api/tags/uniqueTags');
        $response->assertStatus(200);
        $this->assertObjectHasAttribute('data', $response->getData());
        $result = false;
        foreach ($response->getData()->data as $tag) {
            if ($tag->title === $tagActiveCourse->title) {
                $result = true;
            }
            if ($tag->title === $tagUnActiveCourse->title) {
                $result = false;
            }
        }
        $this->assertTrue($result);
    }
}
