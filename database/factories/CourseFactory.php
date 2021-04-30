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
            'title' => $this->faker->sentence,
            'summary' => $this->faker->text,
            'image_path' => "1.jpg",
            'video_path' => "1.mp4",
            'base_price' => 11.99,
            'duration' => rand(2, 10)." hours",
            'author_id' => User::factory(),
        ];
    }

    /*
    public function configure()
    {

        return $this->afterMaking(function (Course $topic) {
            //
        })->afterCreating(function (Course $topic) {
            //
            $topicText = TopicRichText::factory()->make([
                'topic_id' => $topic->id
            ]);
        });
    }
    */
}
