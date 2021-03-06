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
        $this->faker->addProvider(new \DavidBadura\FakerMarkdownGenerator\FakerProvider($this->faker));

        return [
            'title' => $this->faker->word,
            'active' => $this->faker->boolean,
            'preview' => $this->faker->boolean,
            'lesson_id' => Lesson::factory()->create(),
            'order' => $this->faker->randomDigitNotNull,
            'summary' => $this->faker->markdown,
        ];
    }
}
