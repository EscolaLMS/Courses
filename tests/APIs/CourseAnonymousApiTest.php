<?php

namespace EscolaLms\Courses\Tests\APIs;

use EscolaLms\Categories\Models\Category;
use EscolaLms\Core\Tests\CreatesUsers;
use EscolaLms\Courses\Enum\CourseStatusEnum;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Models\TopicResource;
use EscolaLms\Courses\Tests\Models\User;
use EscolaLms\Courses\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\Fluent\AssertableJson;

class CourseAnonymousApiTest extends TestCase
{
    use DatabaseTransactions, CreatesUsers;

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
    public function test_anonymous_read_published_course()
    {
        $publishedCourse = Course::factory()->create([
            'status' => CourseStatusEnum::PUBLISHED
        ]);

        $this->response = $this->json(
            'GET',
            '/api/admin/courses/' . $publishedCourse->id
        );
        $this->response->assertStatus(401);

        $this->response = $this->json(
            'GET',
            '/api/courses/' . $publishedCourse->id
        );

        $this->assertApiResponse($publishedCourse->toArray());

        $unactivatedCourse = Course::factory()->create([
            'status' => CourseStatusEnum::PUBLISHED_UNACTIVATED
        ]);

        $this->response = $this->json(
            'GET',
            '/api/courses/' . $unactivatedCourse->id
        );

        $this->assertApiResponse($unactivatedCourse->toArray());
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

    public function test_anonymous_categories_course()
    {
        $category = Category::factory()->create();
        $category2 = Category::factory()->create();
        $course = Course::factory()->create();
        $course2 = Course::factory()->create();
        $course->categories()->save($category);
        $course2->categories()->save($category2);

        $this->response = $this->json(
            'GET',
            '/api/admin/courses',
            [
                'categories' => [
                    $category->getKey(),
                    $category2->getKey(),
                ],
            ]
        );

        $this->response->assertStatus(401);

        $this->response = $this->json(
            'GET',
            '/api/courses',
            [
                'categories' => [
                    $category->getKey(),
                    $category2->getKey(),
                ],
            ]
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

    public function test_anonymous_categories_and_category_course_unprocessable()
    {
        $category = Category::factory()->create();
        $category2 = Category::factory()->create();
        $course = Course::factory()->create();
        $course2 = Course::factory()->create();
        $course->categories()->save($category);
        $course2->categories()->save($category2);

        $this->response = $this->json(
            'GET',
            '/api/admin/courses',
            [
                'categories' => [
                    $category->getKey(),
                ],
                'category_id' => $category2->getKey(),
            ]
        )->assertStatus(401);

        $this->response = $this->json(
            'GET',
            '/api/courses',
            [
                'categories' => [
                    $category->getKey(),
                ],
                'category_id' => $category2->getKey(),
            ]
        )->assertUnprocessable();
    }

    /**
     * @test
     */
    public function test_anonymous_read_course_program()
    {
        $course = Course::factory()->create(['status' => CourseStatusEnum::PUBLISHED]);

        $this->response = $this->json(
            'GET',
            '/api/admin/courses/' . $course->id . '/program'
        );

        $this->response->assertStatus(401);

        $user = $this->makeStudent();
        $this->response = $this
            ->actingAs($user, 'api')
            ->json('GET', '/api/courses/' . $course->id . '/program');

        $this->response->assertStatus(403);
    }

    public function test_anonymous_read_free_course_program()
    {
        $course = Course::factory()->create(['status' => CourseStatusEnum::PUBLISHED]);
        Lesson::factory([
            'course_id' => $course->getKey(),
            'active' => true,
        ])
            ->has(Lesson::factory(['course_id' => $course->getKey()])
                ->count(2)
                ->sequence(
                    ['active' => true],
                    ['active' => false],
                )
            )
            ->create();

        $this->response = $this->json(
            'GET',
            '/api/admin/courses/' . $course->id . '/program'
        );

        $this->response->assertStatus(401);

        $user = $this->makeStudent();
        $course->users()->attach($user);
        $this->response = $this
            ->actingAs($user, 'api')
            ->json('GET', '/api/courses/' . $course->id . '/program');

        $this->response->assertStatus(200);
        $this->assertApiResponse($course->toArray());
        $this->response->assertJsonCount(1, 'data.lessons.*');
        $this->response->assertJsonCount(1, 'data.lessons.*.lessons.*');
    }

    public function test_anonymous_can_attend_free_course_program()
    {
        $course = Course::factory()
            ->state(['status' => CourseStatusEnum::PUBLISHED, 'public' => true])
            ->has(Lesson::factory()
                ->count(2)
                ->sequence(
                    ['active' => true],
                    ['active' => false],
                )
                ->has(Topic::factory()->count(2)
                    ->sequence(
                        ['active' => true, 'preview' => true], // return with topicable and resources
                        ['active' => true, 'preview' => false], // return without topicable and resources
                        ['active' => false], // not return
                    )
                )
            )
            ->create();

        $this->response = $this->getJson('/api/courses/' . $course->id . '/program')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data.lessons')
            ->assertJsonCount(2, 'data.lessons.0.topics');

        $this->response->assertJson(
            fn (AssertableJson $json) => $json->has(
                'data.lessons',
                fn ($json) => $json->each(
                    fn (AssertableJson $lesson) => $lesson
                        ->where('active', true)
                        ->has('topics',
                            fn (AssertableJson $topics) => $topics->each(
                            fn (AssertableJson $topic) => $topic
                                ->where('active', true)
                                ->where('preview', function (string $preview) use ($topic) {
                                    $preview
                                        ? $topic->hasAll(['topicable', 'resources'])->etc()
                                        : $topic->missingAll(['topicable', 'resources'])->etc();
                                    return true;
                            })->etc()
                        )->etc()
                    )->etc()
                )
            )->etc()
        );
    }

    public function test_anonymous_sorting()
    {
        $course1 = Course::factory()->create(['status' => CourseStatusEnum::PUBLISHED]);
        $course2 = Course::factory()->create(['status' => CourseStatusEnum::PUBLISHED]);
        $course3 = Course::factory()->create(['status' => CourseStatusEnum::PUBLISHED_UNACTIVATED]);
        $course4 = Course::factory()->create(['status' => CourseStatusEnum::ARCHIVED]);

        $this->response = $this->json(
            'GET',
            '/api/courses/?order_by=id&order=ASC'
        );

        $this->assertEquals($this->response->getData()->data[0]->id, $course1->getKey());
        $this->response->assertStatus(200);

        $this->response = $this->json(
            'GET',
            '/api/courses/?order_by=id&order=DESC'
        );

        $this->assertEquals($this->response->getData()->data[0]->id, $course3->getKey());
        $this->response->assertStatus(200);
    }

    public function test_anonymous_only_with_categories()
    {
        $course1 = Course::factory()->create(['status' => CourseStatusEnum::PUBLISHED]);

        $this->response = $this->json(
            'GET',
            '/api/courses/?only_with_categories=true'
        );
        $responseCourseIds = collect(json_decode($this->response->content(), true)['data'])->pluck('id')->toArray();

        $this->assertTrue(!in_array($course1->getKey(), $responseCourseIds));
        $this->response->assertOk();

        $this->response = $this->json(
            'GET',
            '/api/courses'
        );
        $responseCourseIds = collect(json_decode($this->response->content(), true)['data'])->pluck('id')->toArray();

        $this->assertTrue(in_array($course1->getKey(), $responseCourseIds));
        $this->response->assertOk();
    }

    public function test_admin_only_with_categories()
    {
        $course1 = Course::factory()->create(['status' => CourseStatusEnum::PUBLISHED]);

        $user = config('auth.providers.users.model')::factory()->create();
        $user->guard_name = 'api';
        $user->assignRole('admin');
        $this->response = $this->actingAs($user, 'api')->json(
            'GET',
            '/api/admin/courses/?only_with_categories=true'
        );
        $responseCourseIds = collect(json_decode($this->response->content(), true)['data'])->pluck('id')->toArray();

        $this->assertTrue(!in_array($course1->getKey(), $responseCourseIds));
        $this->response->assertOk();

        $this->response = $this->actingAs($user, 'api')->json(
            'GET',
            '/api/courses'
        );
        $responseCourseIds = collect(json_decode($this->response->content(), true)['data'])->pluck('id')->toArray();

        $this->assertTrue(in_array($course1->getKey(), $responseCourseIds));
        $this->response->assertOk();
    }


    public function test_anonymous_only_published()
    {
        Course::factory()->create(['status' => CourseStatusEnum::PUBLISHED_UNACTIVATED]);
        Course::factory()->create(['status' => CourseStatusEnum::PUBLISHED]);
        Course::factory()->create(['status' => CourseStatusEnum::ARCHIVED]);
        Course::factory()->create(['status' => CourseStatusEnum::DRAFT]);

        $this->response = $this->json(
            'GET',
            '/api/courses'
        );
        $this->response->assertStatus(200);

        $courses = $this->response->getData()->data;

        foreach ($courses as $course) {
            $this->assertContains($course->status, [CourseStatusEnum::PUBLISHED, CourseStatusEnum::PUBLISHED_UNACTIVATED]);
        }
    }

    public function test_anonymous_only_findable()
    {
        Course::factory()->create(['status' => CourseStatusEnum::PUBLISHED, 'findable' => false]);
        Course::factory()->create(['status' => CourseStatusEnum::PUBLISHED_UNACTIVATED, 'findable' => false]);
        Course::factory()->create(['status' => CourseStatusEnum::ARCHIVED, 'findable' => true]);

        $this->response = $this->json(
            'GET',
            '/api/courses'
        );
        $this->response->assertStatus(200);

        $courses = $this->response->getData()->data;

        foreach ($courses as $course) {
            $this->assertContains($course->status, [CourseStatusEnum::PUBLISHED, CourseStatusEnum::PUBLISHED_UNACTIVATED]);
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

    public function test_search_courses_by_course_authors()
    {
        $author1 = User::factory()->create();
        $author2 = User::factory()->create();

        Course::factory()
            ->hasAttached($author1, [], 'authors')
            ->count(2)
            ->create(['status' => CourseStatusEnum::PUBLISHED]);
        Course::factory()
            ->hasAttached($author2, [], 'authors')
            ->count(3)
            ->create(['status' => CourseStatusEnum::PUBLISHED]);
        Course::factory()
            ->hasAttached(User::factory(), [], 'authors')
            ->count(10)
            ->create(['status' => CourseStatusEnum::PUBLISHED]);

        $this->response = $this->json(
            'GET',
            '/api/courses?authors[]=' . $author1->id . '&authors[]=' . $author2->id
        )->assertOk();
        $this->assertCourseAuthorFilterResponse([$author1->id, $author2->id], 5);

        $this->response = $this->json(
            'GET',
            '/api/courses?authors[]=' . $author1->id
        )->assertOk();
        $this->assertCourseAuthorFilterResponse([$author1->id], 2);

        $this->response = $this->json(
            'GET',
            '/api/courses?authors[]=' . $author2->id
        )->assertOk();
        $this->assertCourseAuthorFilterResponse([$author2->id], 3);

        $this->response = $this->json(
            'GET',
            '/api/courses'
        )->assertOk();

        $this->response->assertJsonCount(15, 'data');
    }

    public function test_anonymous_read_course_without_inactive_lessons_and_topics(): void
    {
        $course = Course::factory()
            ->state(['status' => CourseStatusEnum::PUBLISHED, 'findable' => true, 'public' => true])
            ->has(Lesson::factory()
                ->count(2)
                ->sequence(
                    ['active' => true],
                    ['active' => false],
                )
                ->has(Topic::factory()->count(2)
                    ->sequence(
                        ['active' => true],
                        ['active' => false],
                    )
                )
            )
            ->create();

        $this->response = $this->getJson('/api/courses/' . $course->id)
            ->assertOk()
            ->assertJsonCount(1, 'data.lessons')
            ->assertJsonCount(1, 'data.lessons.0.topics');

        $this->response->assertJson(
            fn (AssertableJson $json) => $json->has(
                'data.lessons',
                fn ($json) => $json->each(
                    fn (AssertableJson $lesson) => $lesson
                        ->where('active', true)->etc()
                        ->has('topics',
                            fn (AssertableJson $topics) => $topics->each(
                                fn (AssertableJson $topic) => $topic
                                    ->where('active', true)
                                ->etc()
                            )->etc()
                        )->etc()
                )->etc()
            )->etc()
        );
    }

    private function assertCourseAuthorFilterResponse(array $authorIds, int $count): void
    {
        $this->response->assertJsonCount($count, 'data');
        $this->response->assertJson(
            fn (AssertableJson $json) => $json->has(
                'data',
                fn ($json) => $json->each(
                    fn (AssertableJson $json) =>
                    $json->has(
                        'authors',
                        fn (AssertableJson $json) =>
                        $json->each(
                            fn (AssertableJson $json) =>
                            $json->where('id', fn ($json) => in_array($json, $authorIds))->etc()
                        )->etc()
                    )->etc()
                )
            )->etc()
        );
    }

    public function test_assignable_users(): void
    {
        $this->response = $this
            ->json('GET', '/api/admin/courses/users/assignable')
            ->assertUnauthorized();
    }
}
