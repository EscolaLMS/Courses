<?php

namespace EscolaLms\Courses\Tests\APIs;

use EscolaLms\Auth\Models\Group;
use EscolaLms\Courses\Database\Seeders\CoursesPermissionSeeder;
use EscolaLms\Courses\Events\EscolaLmsCourseAccessStartedTemplateEvent;
use EscolaLms\Courses\Events\EscolaLmsCourseFinishedTemplateEvent;
use EscolaLms\Courses\Http\Resources\UserGroupResource;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Tests\Models\User;
use EscolaLms\Courses\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;

class CourseAccessApiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CoursesPermissionSeeder::class);

        $this->user = config('auth.providers.users.model')::factory()->create();
        $this->user->guard_name = 'api';
        $this->user->assignRole('tutor');
        $this->course = Course::factory()->create([
            'base_price' => 1337,
            'active' => true,
        ]);
        $this->course->authors()->sync($this->user);

        Notification::fake();
    }

    public function testAccessList()
    {
        $student = User::factory()->create();
        $group = Group::factory()->create();

        $this->course->users()->sync([$student->getKey()]);
        $this->course->groups()->sync([$group->getKey()]);

        $this->response = $this->actingAs($this->user, 'api')->get('/api/admin/courses/' . $this->course->id . '/access');
        $this->response->assertOk();
        $this->response->assertJsonFragment([
            'users' => [
                [
                    'id' => $student->id,
                    'email' => $student->email,
                    'name' => $student->name,
                ]
            ],
            'groups' => [
                UserGroupResource::make($group)->toArray(null)
            ]
        ]);
    }

    public function testSetAccess()
    {
        $student = User::factory()->create();
        $group = Group::factory()->create();

        $this->course->users()->sync([$student->getKey()]);
        $this->course->groups()->sync([$group->getKey()]);

        $this->response = $this->actingAs($this->user, 'api')->get('/api/admin/courses/' . $this->course->id . '/access');
        $this->response->assertOk();
        $this->response->assertJsonFragment([
            'users' => [
                [
                    'id' => $student->id,
                    'email' => $student->email,
                    'name' => $student->name,
                ]
            ],
            'groups' => [
                UserGroupResource::make($group)->toArray(null)
            ]
        ]);

        $this->response = $this->actingAs($this->user, 'api')->post('/api/admin/courses/' . $this->course->id . '/access/set', [
            'users' => [],
            'groups' => [],
        ]);
        $this->response->assertOk();
        $this->response->assertJsonMissing([
            'id' => $student->id,
            'email' => $student->email,
            'name' => $student->name,
        ]);
        $this->response->assertJsonMissing([
            'id' => $group->id,
            'name' => $group->name,
        ]);
    }

    private function assertUserCanReadProgram(User $user, Course $course)
    {
        /** @var TestResponse $response */
        $response = $this->actingAs($user)->json(
            'GET',
            '/api/courses/' . $course->id . '/program'
        );

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'title',
                'summary',
                'image_path',
                'image_url',
                'video_path',
                'video_url',
                'base_price',
                'duration',
                'author_id',
                'scorm_sco_id',
                'scorm_sco',
                'active',
                'subtitle',
                'language',
                'description',
                'level',
                'lessons',
                'poster_path',
                'poster_url',
            ],
            'message',
        ]);
    }

    private function assertUserCanNotReadProgram(User $user, Course $course)
    {
        /** @var TestResponse $response */
        $response = $this->actingAs($user)->json(
            'GET',
            '/api/courses/' . $course->id . '/program'
        );

        $response->assertStatus(403);
    }

    public function testAddUserAccess()
    {
        Event::fake();
        $student = User::factory()->create();

        $this->assertUserCanNotReadProgram($student, $this->course);

        $this->response = $this->actingAs($this->user, 'api')->post('/api/admin/courses/' . $this->course->id . '/access/add/', [
            'users' => [$student->getKey()]
        ]);
        $this->response->assertOk();
        Event::assertDispatched(EscolaLmsCourseAccessStartedTemplateEvent::class);
        $this->assertUserCanReadProgram($student, $this->course);
    }

    public function testRemoveUserAccess()
    {
        Event::fake();
        $student = User::factory()->create();
        $this->course->users()->sync([$student->getKey()]);

        $this->assertUserCanReadProgram($student, $this->course);

        $this->response = $this->actingAs($this->user, 'api')->post('/api/admin/courses/' . $this->course->id . '/access/remove/', [
            'users' => [$student->getKey()]
        ]);

        $this->response->assertOk();
        Event::assertDispatched(EscolaLmsCourseFinishedTemplateEvent::class);
        $this->assertUserCanNotReadProgram($student, $this->course);
    }

    public function testAddGroupAccess()
    {
        $student = User::factory()->create();
        $group = Group::factory()->create();
        $group->users()->sync([$student->getKey()]);

        $this->assertUserCanNotReadProgram($student, $this->course);

        $this->response = $this->actingAs($this->user, 'api')->post('/api/admin/courses/' . $this->course->id . '/access/add/', [
            'groups' => [$group->getKey()]
        ]);

        $this->response->assertOk();

        $this->assertUserCanReadProgram($student, $this->course);
    }

    public function testRemoveGroupAccess()
    {
        $student = User::factory()->create();
        $group = Group::factory()->create();
        $group->users()->sync([$student->getKey()]);
        $this->course->groups()->sync([$group->getKey()]);

        $this->assertUserCanReadProgram($student, $this->course);

        $this->response = $this->actingAs($this->user, 'api')->post('/api/admin/courses/' . $this->course->id . '/access/remove/', [
            'groups' => [$group->getKey()]
        ]);

        $this->response->assertOk();

        $this->assertUserCanNotReadProgram($student, $this->course);
    }
}
