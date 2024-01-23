<?php

namespace EscolaLms\Courses\Tests\APIs;

use EscolaLms\Core\Tests\CreatesUsers;
use EscolaLms\Courses\Database\Seeders\CoursesPermissionSeeder;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CourseAuthoredApiTest extends TestCase
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
        $this->admin = $this->makeAdmin();
    }

    public function test_get_authored_courses()
    {
        Course::factory()->count(5);
        $course1 = Course::factory()->create();
        $course2 = Course::factory()->create();

        $course1->authors()->save($this->admin);
        $course2->authors()->save($this->admin);

        $this
            ->actingAs($this->admin, 'api')
            ->json(
                'GET',
                '/api/courses/authored',
            )
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_get_authored_courses_with_order()
    {
        $course1 = Course::factory()->create([
            'title' => 'A',
            'created_at' => now(),
        ]);

        $course2 = Course::factory()->create([
            'title' => 'B',
            'created_at' => now()->addDay(),
        ]);

        $course1->authors()->save($this->admin);
        $course2->authors()->save($this->admin);

        $response = $this
            ->actingAs($this->admin, 'api')
            ->json(
                'GET',
                '/api/courses/authored?order_by=title&order=asc',
            );

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->assertEquals($course1->getKey(), $response->json('data.0.id'));
        $this->assertEquals($course2->getKey(), $response->json('data.1.id'));

        $response = $this
            ->actingAs($this->admin, 'api')
            ->json(
                'GET',
                '/api/courses/authored?order_by=title&order=desc',
            );

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->assertEquals($course2->getKey(), $response->json('data.0.id'));
        $this->assertEquals($course1->getKey(), $response->json('data.1.id'));

        $response = $this
            ->actingAs($this->admin, 'api')
            ->json(
                'GET',
                '/api/courses/authored?order_by=created_at&order=asc',
            );

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->assertEquals($course1->getKey(), $response->json('data.0.id'));
        $this->assertEquals($course2->getKey(), $response->json('data.1.id'));

        $response = $this
            ->actingAs($this->admin, 'api')
            ->json(
                'GET',
                '/api/courses/authored?order_by=created_at&order=desc',
            );

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->assertEquals($course2->getKey(), $response->json('data.0.id'));
        $this->assertEquals($course1->getKey(), $response->json('data.1.id'));
    }

    public function test_get_authored_courses_unauthorized(): void
    {
        $this->getJson('/api/courses/authored',)
            ->assertUnauthorized();
    }

    public function test_get_authored_courses_forbidden(): void
    {
        $this->actingAs($this->makeStudent(), 'api')
            ->getJson('/api/courses/authored',)
            ->assertForbidden();
    }
}
