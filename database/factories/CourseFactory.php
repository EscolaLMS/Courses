<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

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
        'image_path' => $this->faker->word,
        'video_path' => $this->faker->word,
        'base_price' => $this->faker->word,
        'duration' => $this->faker->word,
        'active' => $this->faker->word,
        'author_id' => $this->faker->word
        ];
    }
}
