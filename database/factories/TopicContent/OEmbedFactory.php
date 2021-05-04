<?php

namespace EscolaLms\Courses\Database\Factories\TopicContent;

use EscolaLms\Courses\Models\TopicContent\RichText;
use Illuminate\Database\Eloquent\Factories\Factory;

class OEmbedFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RichText::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $links = ['https://youtu.be/b-mGA4V2LK0','https://youtu.be/mRfSM-lv55I', 'https://youtu.be/uvRBUw_Ls2o',
                    'https://vimeo.com/539333005', 'https://vimeo.com/542174820', 'https://vimeo.com/451165366',
                'https://soundcloud.com/howardstern/davidletterman', 'https://soundcloud.com/jamescarterpresents/shape-of-you',
            'https://www.mixcloud.com/quietmusic/quietmusic-may-2-hour-1-excerpt/'];
        return [
            //'topic_id' => $this->faker->word,
            'value' => $links[array_rand($links)]
        ];
    }
}
