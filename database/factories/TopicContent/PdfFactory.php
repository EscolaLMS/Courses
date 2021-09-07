<?php

namespace EscolaLms\Courses\Database\Factories\TopicContent;

use EscolaLms\Courses\Models\TopicContent\PDF;
use Illuminate\Database\Eloquent\Factories\Factory;

class PdfFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PDF::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'value' => '1.pdf',
        ];
    }

    public function updatePath($id)
    {
        return $this->state(function (array $attributes) use ($id) {
            $filename = "topic/$id/{$this->faker->word}.pdf";
            $dest = storage_path("app/public/$filename");
            $destDir = dirname($dest);
            if (!is_dir($destDir)) {
                mkdir($destDir, 0777, true);
            }
            copy(realpath(__DIR__ . '/../../mocks/1.pdf'), $dest);
            return [
                'value' => $filename,
            ];
        });
    }
}
