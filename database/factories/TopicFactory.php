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
            'lesson_id' => Lesson::factory(),
            //'topicable_id' => $this->faker->word,
            //'topicable_type' => $this->faker->word,
            'order' => $this->faker->randomDigitNotNull
        ];
    }

    /*
    public function configure()
    {
        return $this->afterMaking(function (Topic $topic) {
            //
        })->afterCreating(function (Topic $topic) {
            // TODO, randome time
            $topicText = TopicRichText::factory()->make([
                'topic_id' => $topic->id
            ]);
        });
    }
    */
}
