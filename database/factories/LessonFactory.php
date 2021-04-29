<?php

namespace EscolaLms\Courses\Database\Factories;

use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Course;

use Illuminate\Database\Eloquent\Factories\Factory;

class LessonFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Lesson::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->word,
            'duration' => rand(10, 50)." minutes",
            'order' => $this->faker->randomDigitNotNull,
            'course_id' => Course::factory(),
        ];
    }
}
