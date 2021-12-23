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

    public function test_create_resource()
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('test.pdf');

        $this->response = $this->actingAs($this->user, 'api')->post(
            '/api/admin/topics/' . $this->topic->getKey() . '/resources',
            [
                'resource' => $file
            ]
        );

        $this->response->assertStatus(201);

        $data = json_decode($this->response->getContent());

        $fullpath = $data->data->path . $data->data->name;

        Storage::disk('local')->assertExists($fullpath);

        $this->assertDatabaseHas('topic_resources', [
            'id' => $data->data->id,
            'name' => 'test.pdf',
        ]);
    }

    public function test_list_resource()
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('test.pdf');

        $this->response = $this->actingAs($this->user, 'api')->post(
            '/api/admin/topics/' . $this->topic->getKey() . '/resources',
            [
                'resource' => $file
            ]
        );

        $this->response->assertStatus(201);

        $data = json_decode($this->response->getContent());

        $fullpath = $data->data->path . $data->data->name;

        Storage::disk('local')->assertExists($fullpath);

        $this->assertDatabaseHas('topic_resources', [
            'id' => $data->data->id,
            'name' => 'test.pdf',
        ]);

        $this->response = $this->actingAs($this->user, 'api')->json('GET', '/api/admin/topics/' . $this->topic->getKey() . '/resources/');
        $this->response->assertStatus(200);
        $this->response->assertJsonFragment([
            'data' => [
                [
                    'id' => $data->data->id,
                    'name' => $data->data->name,
                    'path' => $data->data->path,
                    'url' => $data->data->url,
                    'topic_id' => $data->data->topic_id,
                ]
            ]
        ]);
    }

    public function test_delete_resource()
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('test.pdf');

        $this->response = $this->actingAs($this->user, 'api')->post(
            '/api/admin/topics/' . $this->topic->getKey() . '/resources',
            [
                'resource' => $file
            ]
        );

        $this->response->assertStatus(201);

        $data = json_decode($this->response->getContent());
        $id = $data->data->id;
        $fullpath = $data->data->path . $data->data->name;

        Storage::disk('local')->assertExists($fullpath);
        $this->assertDatabaseHas('topic_resources', [
            'id' => $id,
            'name' => 'test.pdf',
        ]);

        $this->response = $this->actingAs($this->user, 'api')->delete('/api/admin/topics/' . $this->topic->getKey() . '/resources/' . $id);
        $this->response->assertStatus(200);
        Storage::disk('local')->assertMissing($fullpath);
        $this->assertDatabaseMissing('topic_resources', [
            'id' => $id
        ]);
    }

    public function test_rename_resource()
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('test.pdf');

        $this->response = $this->actingAs($this->user, 'api')->post(
            '/api/admin/topics/' . $this->topic->getKey() . '/resources',
            [
                'resource' => $file
            ]
        );

        $this->response->assertStatus(201);

        $data = json_decode($this->response->getContent());
        $id = $data->data->id;
        $fullpath = $data->data->path . $data->data->name;

        Storage::disk('local')->assertExists($fullpath);
        $this->assertDatabaseHas('topic_resources', [
            'id' => $id,
            'name' => 'test.pdf',
        ]);

        $this->response = $this->actingAs($this->user, 'api')->patch('/api/admin/topics/' . $this->topic->getKey() . '/resources/' . $id, [
            'name' => 'test-renamed'
        ]);
        $this->response->assertStatus(200);

        $newpath = $data->data->path . 'test-renamed.pdf';

        Storage::disk('local')->assertMissing($fullpath);
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
