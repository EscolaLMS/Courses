<?php

namespace EscolaLms\Courses\Database\Factories\TopicContent;

use EscolaLms\Courses\Models\TopicContent\Image;
use Illuminate\Database\Eloquent\Factories\Factory;

class ImageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Image::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            //'topic_id' => $this->faker->word,
            'value' => '1.jpg',
            'width' => 640,
            'height' => 480
        ];
    }

    public function updatePath($id)
    {
        return $this->state(function (array $attributes) use ($id) {
            $filename = "topic/$id/".$this->faker->word.".jpg";
            $dest = storage_path("app/public/$filename");
            $destDir = dirname($dest);
            if (!is_dir($destDir)) {
                mkdir($destDir, 0777, true);
            }
            copy(realpath(__DIR__."/../../mocks/1.jpg"), $dest);
            return [
                'value' => $filename,
            ];
        });
    }
}
