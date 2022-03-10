<?php

namespace EscolaLms\Courses\Tests\APIs;

use EscolaLms\Auth\Models\User;
use EscolaLms\Categories\Models\Category;
use EscolaLms\Core\Tests\CreatesUsers;
use EscolaLms\Courses\Database\Seeders\CoursesPermissionSeeder;
use EscolaLms\Courses\Enum\CourseStatusEnum;
use EscolaLms\Courses\Events\CourseStatusChanged;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Tests\TestCase;
use EscolaLms\ModelFields\Facades\ModelFields;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;

class CourseTutorApiTest extends TestCase
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
        $this->user->assignRole('tutor');
    }

    public function test_create_course()
    {
        $course = Course::factory()->make([
            'status' => CourseStatusEnum::PUBLISHED
        ])->toArray();

        $this->response = $this->actingAs($this->user, 'api')->json(
            'POST',
            '/api/admin/courses',
            $course
        );

        $this->response->assertStatus(201);

        $content = json_decode($this->response->getContent());

        $this->assertTrue($this->user->id == $content->data->author_id);

        $course['author_id'] = $this->user->id;
        $this->assertApiResponse($course);

        $this->response = $this->json(
            'GET',
            '/api/tutors'
        );

        $this->response->assertStatus(200);

        $userId = $this->user->id;
        $collection  = collect($this->response->getData()->data);
        $contains = $collection->contains(function ($value, $key) use ($userId) {
            return $value->id === $userId;
        });

        $this->assertTrue($contains);

        $this->response = $this->json(
            'GET',
            '/api/tutors/' . $userId
        );

        $this->response->assertStatus(200);

        $this->assertTrue($this->response->getData()->data->id === $userId);
    }


    /**
     * @test
     */
    public function test_read_course()
    {
        $course = Course::factory()->create([
            'status' => CourseStatusEnum::PUBLISHED,
        ]);

        $this->response = $this->actingAs($this->user, 'api')->json(
            'GET',
            '/api/admin/courses/' . $course->id
        );

        $this->assertApiResponse($course->toArray());
    }

    public function test_read_owned_inactive_course()
    {
        $course = Course::factory()->create([
            'status' => CourseStatusEnum::ARCHIVED,
            'author_id' => $this->user->getKey(),
        ]);

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
        $course = Course::factory()->create([
            'author_id' => $this->user->id
        ]);

        $editedCourse = Course::factory()->make()->toArray();

        $this->response = $this->actingAs($this->user, 'api')->json(
            'PUT',
            '/api/admin/courses/' . $course->id,
            $editedCourse
        );

        $this->assertApiResponse($editedCourse);
    }

    /**
     * @test
     */
    public function test_delete_course()
    {
        $course = Course::factory()->create([
            'author_id' => $this->user->id
        ]);

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
        }
    }



    /**
     * @test
     */
    public function test_read_course_program()
    {
        $course = Course::factory()->create([
            'author_id' => $this->user->id
        ]);

        $this->response = $this->actingAs($this->user, 'api')->json(
            'GET',
            '/api/admin/courses/' . $course->id . '/program'
        );

        $this->response->assertStatus(200);
    }

    public function test_assign_tutor()
    {
        $admin = $this->makeAdmin();

        /** @var Course $course */
        $course = Course::factory()->create([
            'author_id' => null
        ]);
        $this->assertFalse($course->hasAuthor($this->user));

        $this->response = $this->actingAs($admin, 'api')->json(
            'POST',
            '/api/admin/tutors/' . $this->user->id . '/assign/' . $course->id
        );
        $this->response->assertOk();

        $course->refresh();
        $this->assertTrue($course->hasAuthor($this->user));
    }

    public function test_unassign_tutor()
    {
        $admin = $this->makeAdmin();

        /** @var Course $course */
        $course = Course::factory()->create([
            'author_id' => $this->user->id
        ]);
        $this->assertTrue($course->hasAuthor($this->user));

        $this->response = $this->actingAs($admin, 'api')->json(
            'POST',
            '/api/admin/tutors/' . $this->user->id . '/unassign/' . $course->id
        );
        $this->response->assertOk();

        $course->refresh();
        $this->assertFalse($course->hasAuthor($this->user));
    }

    public function test_update_course_status()
    {
        Event::fake([CourseStatusChanged::class]);

        $course = Course::factory()->create([
            'status' => CourseStatusEnum::PUBLISHED,
            'author_id' => $this->user->id,
        ]);

        $course->title = 'Update course title';
        $this->response = $this->actingAs($this->user, 'api')->putJson('/api/admin/courses/' . $course->id,
            $course->toArray(),
        )->assertOk();

        Event::assertNotDispatched(CourseStatusChanged::class);

        $course->status = CourseStatusEnum::DRAFT;
        $this->response = $this->actingAs($this->user, 'api')->putJson('/api/admin/courses/' . $course->id,
            $course->toArray(),
        )->assertOk();

        Event::assertDispatched(CourseStatusChanged::class, function (CourseStatusChanged $event) {
            $this->assertEquals(CourseStatusEnum::DRAFT, $event->getCourse()->status);
            return true;
        });
    }

    public function test_read_tutors_interests(): void
    {
        $tutor = $this->makeInstructor();
        $tutor2 = $this->makeInstructor();
        $category = Category::factory()->create();
        $tutor->interests()->sync($category->getKey());
        $course = Course::factory()->create();
        $course->authors()->sync([$tutor->getKey(), $tutor2->getKey()]);

        $this->response = $this->getJson('/api/tutors')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $category->getKey(),
                'name' => $category->name,
            ]);

        $this->response = $this->getJson('/api/tutors/' . $tutor->getKey())
            ->assertOk()
            ->assertJsonFragment([
                'id' => $category->getKey(),
                'name' => $category->name,
            ]);

        $this->response = $this->getJson('/api/tutors/' . $tutor2->getKey())
            ->assertOk()
            ->assertJsonFragment(['interests' => []]);
    }

    public function test_read_tutor_additional_field(): void
    {
        ModelFields::addOrUpdateMetadataField(
            User::class,
            'additional_field_a',
            'varchar',
        );

        $tutor = $this->makeInstructor([
            'additional_field_a' => 'public string',
        ]);
        $course = Course::factory()->create();
        $course->authors()->sync([$tutor->getKey()]);

        $this->response = $this->getJson('/api/tutors/' . $tutor->getKey())
            ->assertOk()
            ->assertJsonFragment([
                'id' => $tutor->getKey(),
                'additional_field_a' => 'public string',
                ]);
    }
}
