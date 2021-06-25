<?php

namespace EscolaLms\Courses\Database\Factories;

use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Models\TopicContent\RichText;
use EscolaLms\Courses\Models\Lesson;


use Illuminate\Database\Eloquent\Factories\Factory;

class TopicFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Topic::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->word,
            'lesson_id' => Lesson::factory()->create(),
            'order' => $this->faker->randomDigitNotNull
        ];
    }


}
