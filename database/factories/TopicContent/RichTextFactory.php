<?php

namespace EscolaLms\Courses\Database\Factories\TopicContent;

use EscolaLms\Courses\Models\TopicContent\RichText;
use Illuminate\Database\Eloquent\Factories\Factory;

class RichTextFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RichText::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            //'topic_id' => $this->faker->word,
            'value' => $this->faker->text
        ];
    }
}
