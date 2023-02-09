<?php

namespace EscolaLms\Courses\Tests\APIs;

use EscolaLms\Core\Tests\CreatesUsers;
use EscolaLms\Courses\Database\Seeders\CoursesPermissionSeeder;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CourseSortAdminApiTest extends TestCase
{
    use CreatesUsers;
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CoursesPermissionSeeder::class);
        $this->user = config('auth.providers.users.model')::factory()->create();
        $this->user->guard_name = 'api';
        $this->user->assignRole('admin');
    }

    public function test_admin_sort_lessons_in_course(): void
    {
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
        ]);
        $lesson2 = Lesson::factory()->create([
            'course_id' => $course->id,
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

    public function test_admin_sort_topics_in_lesson(): void
    {
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id
        ]);
        $topic = Topic::factory()->create([
            'lesson_id' => $lesson->id
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

    public function test_admin_sort_lessons_in_lesson(): void
    {
        $course = Course::factory()->create();
        $mainLesson = Lesson::factory()->create([
            'course_id' => $course->getKey(),
        ]);

        $childLesson1 = Lesson::factory()->create([
            'course_id' => $course->getKey(),
            'parent_lesson_id' => $mainLesson->getKey(),
        ]);

        $childLesson2 = Lesson::factory()->create([
            'course_id' => $course->getKey(),
            'parent_lesson_id' => $mainLesson->getKey(),
        ]);

        $this->response = $this->actingAs($this->user, 'api')->json(
            'POST',
            '/api/admin/courses/sort',
            [
                'class' => 'Lesson',
                'course_id' => $course->getKey(),
                'orders' => [
                    [$childLesson1->getKey(), 4],
                    [$childLesson2->getKey(), 5],
                ]
            ]
        )->assertOk();

        $this->assertEquals($childLesson1->refresh()->order, 4);
        $this->assertEquals($childLesson2->refresh()->order, 5);
    }

    public function test_admin_sort_topics_in_nested_lesson_structure(): void
    {
        $course = Course::factory()->create();
        $mainLesson = Lesson::factory()->create([
            'course_id' => $course->getKey(),
        ]);

        $childLesson = Lesson::factory()->create([
            'course_id' => $course->getKey(),
            'parent_lesson_id' => $mainLesson->getKey(),
        ]);

        $topic = Topic::factory()->create([
            'lesson_id' => $childLesson->getKey(),
        ]);

        $topic2 = Topic::factory()->create([
            'lesson_id' => $childLesson->getKey(),
        ]);

        $this->response = $this->actingAs($this->user, 'api')->json(
            'POST',
            '/api/admin/courses/sort',
            [
                'class' => 'Topic',
                'course_id' => $course->getKey(),
                'orders' => [
                    [$topic->getKey(), 2],
                    [$topic2->getKey(), 3],
                ]
            ]
        )->assertOk();

        $this->assertEquals($topic->refresh()->order, 2);
        $this->assertEquals($topic2->refresh()->order, 3);
    }

    public function test_admin_cannot_sort_topics_from_different_lessons(): void
    {
        $course = Course::factory()->create();
        $lesson1 = Lesson::factory()->create();
        $lesson2 = Lesson::factory()->create();

        $topic1 = Topic::factory()->create([
            'lesson_id' => $lesson1->getKey(),
        ]);

        $topic2 = Topic::factory()->create([
            'lesson_id' => $lesson2->getKey(),
        ]);

        $this->response = $this->actingAs($this->user, 'api')->json(
            'POST',
            '/api/admin/courses/sort',
            [
                'class' => 'Topic',
                'course_id' => $course->getKey(),
                'orders' => [
                    [$topic1->getKey(), 2],
                    [$topic2->getKey(), 3],
                ]
            ]
        )->assertForbidden();
    }
}
