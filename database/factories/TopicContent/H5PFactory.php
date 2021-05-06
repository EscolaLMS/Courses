<?php

namespace EscolaLms\Courses\Database\Factories\TopicContent;

use EscolaLms\Courses\Models\TopicContent\H5P;
use Illuminate\Database\Eloquent\Factories\Factory;

class H5PFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = H5P::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        if (class_exists('EscolaLms\HeadlessH5P\Models\H5PContent')) {
            $h5p = 'EscolaLms\HeadlessH5P\Models\H5PContent';
        }
        
        return [
            //'topic_id' => $this->faker->word,
            'value' => isset($h5p) ? $h5p ::inRandomOrder()->first()->id : 0
            // ID to h5p content
        ];
    }
}
