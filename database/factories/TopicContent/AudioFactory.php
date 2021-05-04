<?php

namespace EscolaLms\Courses\Database\Factories\TopicContent;

use EscolaLms\Courses\Models\TopicContent\Audio;
use Illuminate\Database\Eloquent\Factories\Factory;

class AudioFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Audio::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            //'topic_id' => $this->faker->word,
            'value' => '1.mp3',
            'length' => rand(1000, 2000),
        ];
    }

    public function updatePath($id)
    {
        return $this->state(function (array $attributes) use ($id) {
            $filename = "topic/$id/".$this->faker->word.".mp3";
            $dest = storage_path("app/public/$filename");
            $destDir = dirname($dest);
            if (!is_dir($destDir)) {
                mkdir($destDir, 0777, true);
            }
            copy(realpath(__DIR__."/../../mocks/1.mp3"), $dest);
            return [
                'value' => $filename,
            ];
        });
    }
}
