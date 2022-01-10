<?php

namespace EscolaLms\Courses\Tests\APIs;

use EscolaLms\Courses\Database\Seeders\CoursesPermissionSeeder;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TopicResourceTutorApiTest extends TestCase
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
            'author_id' => $this->user->id
        ]);
        $this->lesson = Lesson::factory(['course_id' => $this->course->id])->create();
        $this->topic = Topic::factory()->create(['lesson_id' => $this->lesson->id]);
    }

    public function testCreateResource()
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('test.pdf');

        $this->response = $this->actingAs($this->user, 'api')->post(
            '/api/admin/topics/'.$this->topic->getKey().'/resources',
            [
                'resource' => $file,
            ]
        );

        $this->response->assertStatus(201);

        $data = json_decode($this->response->getContent());

        Storage::disk('local')->assertExists($data->data->path);

        $this->assertDatabaseHas('topic_resources', [
            'id' => $data->data->id,
            'name' => 'test.pdf',
        ]);
    }


    public function testListResource()
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('test.pdf');

        $this->response = $this->actingAs($this->user, 'api')->post(
            '/api/admin/topics/'.$this->topic->getKey().'/resources',
            [
                'resource' => $file,
            ]
        );

        $this->response->assertStatus(201);

        $data = json_decode($this->response->getContent());

        Storage::disk('local')->assertExists($data->data->path);

        $this->assertDatabaseHas('topic_resources', [
            'id' => $data->data->id,
            'name' => 'test.pdf',
        ]);

        $this->response = $this->actingAs($this->user, 'api')->json('GET', '/api/admin/topics/'.$this->topic->getKey().'/resources/');
        $this->response->assertStatus(200);
        $this->response->assertJsonFragment([
            'data' => [
                [
                    'id' => $data->data->id,
                    'name' => $data->data->name,
                    'path' => $data->data->path,
                    'url' => $data->data->url,
                    'topic_id' => $data->data->topic_id,
                ],
            ],
        ]);
    }

    public function testDeleteResource()
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('test.pdf');

        $this->response = $this->actingAs($this->user, 'api')->post(
            '/api/admin/topics/'.$this->topic->getKey().'/resources',
            [
                'resource' => $file,
            ]
        );

        $this->response->assertStatus(201);

        $data = json_decode($this->response->getContent());
        $id = $data->data->id;

        Storage::disk('local')->assertExists($data->data->path);
        $this->assertDatabaseHas('topic_resources', [
            'id' => $id,
            'name' => 'test.pdf',
        ]);

        $this->response = $this->actingAs($this->user, 'api')->delete('/api/admin/topics/'.$this->topic->getKey().'/resources/'.$id);
        $this->response->assertStatus(200);
        Storage::disk('local')->assertMissing($data->data->path);
        $this->assertDatabaseMissing('topic_resources', [
            'id' => $id,
        ]);
    }

    public function testRenameResource()
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('test.pdf');

        $this->response = $this->actingAs($this->user, 'api')->post(
            '/api/admin/topics/'.$this->topic->getKey().'/resources',
            [
                'resource' => $file,
            ]
        );

        $this->response->assertStatus(201);

        $data = json_decode($this->response->getContent());
        $id = $data->data->id;

        Storage::disk('local')->assertExists($data->data->path);
        $this->assertDatabaseHas('topic_resources', [
            'id' => $id,
            'name' => 'test.pdf',
        ]);

        $this->response = $this->actingAs($this->user, 'api')->patch('/api/admin/topics/'.$this->topic->getKey().'/resources/'.$id, [
            'name' => 'test-renamed',
        ]);
        $this->response->assertStatus(200);

        $newpath = Str::replace('test', 'test-renamed', $data->data->path);

        Storage::disk('local')->assertMissing($data->data->path);
        Storage::disk('local')->assertExists($newpath);

        $this->assertDatabaseMissing('topic_resources', [
            'id' => $id,
            'name' => 'test.pdf',
        ]);
        $this->assertDatabaseHas('topic_resources', [
            'id' => $id,
            'name' => 'test-renamed.pdf',
        ]);
    }
}
