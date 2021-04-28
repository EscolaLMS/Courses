<?php

namespace EscolaLms\Courses\Database\Factories;

use EscolaLms\Courses\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;
use EscolaLms\Auth\Models\User;

class CourseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Course::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->word,
            'summary' => $this->faker->text,
            'image_path' => $this->faker->text,
            'video_path' => $this->faker->word,
            'base_price' => $this->faker->word,
            'duration' => $this->faker->word,
            'author_id' => User::factory(),
        ];
    }
}
