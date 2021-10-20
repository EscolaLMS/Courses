<?php

namespace EscolaLms\Courses\Database\Factories;

use EscolaLms\Courses\Database\Factories\FakerMarkdownProvider\FakerProvider;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Topic;
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
        $this->faker->addProvider(new FakerProvider($this->faker));

        $lesson = Lesson::inRandomOrder()->first();
        return [
            'title' => $this->faker->word,
            'active' => $this->faker->boolean,
            'preview' => $this->faker->boolean,
            'lesson_id' => $lesson ? $lesson->id : null,
            'order' => $this->faker->randomDigitNotNull,
            'summary' => $this->faker->markdown,
        ];
    }
}
