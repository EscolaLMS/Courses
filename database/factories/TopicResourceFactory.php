<?php

namespace EscolaLms\Courses\Database\Factories;

use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Models\TopicResource;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;

class TopicResourceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TopicResource::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'path' => '',
            'name' => '1.pdf',
        ];
    }

    public function forTopic(Topic $topic)
    {
        return $this->state(function (array $attributes) use ($topic) {
            $topic_id = $topic->getKey();
            $course_id = $topic->course->getKey();
            $filename = "{$this->faker->word}.pdf";
            $path = "course/{$course_id}/topic/{$topic_id}/resources/{$filename}";
            $dest = Storage::disk('public')->path($path);
            $destDir = dirname($dest);
            Storage::putFileAs("course/{$course_id}/topic/{$topic_id}/resources", new File(__DIR__ . '/../mocks/1.pdf'), $filename);
          
            return [
                'topic_id' => $topic,
                'path'     => $path,
                'name'     => $filename,
            ];
        });
    }
}
