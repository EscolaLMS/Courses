<?php

namespace EscolaLms\Courses\Database\Factories;

use EscolaLms\Courses\Models\TopicRichText;
use Illuminate\Database\Eloquent\Factories\Factory;

class TopicRichTextFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TopicRichText::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'topic_id' => $this->faker->word,
        'value' => $this->faker->text
        ];
    }
}
