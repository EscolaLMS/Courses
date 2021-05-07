<?php

namespace EscolaLms\Courses\Database\Factories;

use EscolaLms\Courses\Models\CourseProgress;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseProgressFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CourseProgress::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [];
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
