<?php

namespace EscolaLms\Courses\Database\Factories;

use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Lesson;
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
        $this->faker->addProvider(new \DavidBadura\FakerMarkdownGenerator\FakerProvider($this->faker));

        $course = Course::inRandomOrder()->first();
        return [
            'title' => $this->faker->word,
            'duration' => random_int(10, 50) . " minutes",
            'order' => $this->faker->randomDigitNotNull,
            'active' => $this->faker->boolean,
            'course_id' => $course ? $course->id : null,
            'summary' => $this->faker->markdown,
        ];
    }
}
