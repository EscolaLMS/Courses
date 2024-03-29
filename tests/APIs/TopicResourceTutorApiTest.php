<?php

namespace EscolaLms\Courses\Tests\APIs;

use EscolaLms\Courses\Database\Seeders\CoursesPermissionSeeder;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
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

    public function excludedFileExtensionProvider(): array
    {
        return [
            ['html'],
            ['php'],
            ['css'],
            ['java'],
            ['h5p'],
            ['m3u8'],
            ['heic'],
            ['xlsx'],
        ];
    }

    public function allowedFileExtensionProvider(): array
    {
        $this->createApplication();
        return array_map(fn ($item) => [$item], explode(',', config('escolalms_courses.topic_resource_mimes')));
    }

    /**
     * @dataProvider allowedFileExtensionProvider
     */
    public function testCreateResourceAllowedFileExtensions(string $ext)
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('file.' . $ext);

        $this->response = $this->actingAs($this->user, 'api')->postJson(
            '/api/admin/topics/' . $this->topic->getKey() . '/resources',
            [
                'resource' => $file,
            ]
        );

        $this->response->assertStatus(201);

        $data = json_decode($this->response->getContent());

        Storage::disk('local')->assertExists($data->data->path);

        $this->assertDatabaseHas('topic_resources', [
            'id' => $data->data->id,
            'name' => 'file.' . $ext,
        ]);
    }

    /**
     * @dataProvider excludedFileExtensionProvider
     */
    public function testCreateResourceExcludedFileExtensions(string $ext): void
    {
        $this->response = $this->actingAs($this->user, 'api')->postJson(
            '/api/admin/topics/' . $this->topic->getKey() . '/resources',
            [
                'resource' =>  UploadedFile::fake()->create('file.' . $ext),
            ]
        );

        $this->response->assertStatus(422);
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
            'name' => 'test',
        ]);
        $this->response->assertStatus(422);

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

    public function testCreateResourceFileAsPathNotExist()
    {
        $this->response = $this->actingAs($this->user, 'api')->postJson(
            '/api/admin/topics/' . $this->topic->getKey() . '/resources',
            [
                'resource' => 'file.pdf',
            ]
        )->assertStatus(422);
    }

    public function testCreateResourceFileAsWrongPath()
    {
        Storage::fake('local');

        $newCourse = Course::factory()->create([
            'author_id' => $this->user->id
        ]);

        $pdfPath = "course/{$newCourse->getKey()}/topic/1/resources/document.pdf";
        Storage::makeDirectory("course/{$newCourse->getKey()}/topic/1/resources");
        copy(__DIR__ . '/../mocks/document.pdf', Storage::path($pdfPath));

        $this->response = $this->actingAs($this->user, 'api')->postJson(
            '/api/admin/topics/' . $this->topic->getKey() . '/resources',
            [
                'resource' => 'file.pdf',
            ]
        )->assertStatus(422);
    }

    public function testCreateResourceFileAsPath()
    {
        Storage::fake('local');

        $pdfPath = "course/{$this->course->getKey()}/topic/1/resources/document.pdf";
        Storage::makeDirectory("course/{$this->course->getKey()}/topic/1/resources");
        copy(__DIR__ . '/../mocks/document.pdf', Storage::path($pdfPath));

        $this->response = $this->actingAs($this->user, 'api')->postJson(
            '/api/admin/topics/' . $this->topic->getKey() . '/resources',
            [
                'resource' => $pdfPath,
            ]
        )->assertStatus(201);

        $data = json_decode($this->response->getContent());

        Storage::assertExists($data->data->path);

        $this->assertDatabaseHas('topic_resources', [
            'id' => $data->data->id,
            'name' => 'document.pdf',
        ]);
    }
}
