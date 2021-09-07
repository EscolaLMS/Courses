<?php

namespace EscolaLms\Courses\Database\Factories;

use EscolaLms\Courses\Models\TopicResource;
use Illuminate\Database\Eloquent\Factories\Factory;

class TopicResourceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TopicResource::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'value' => '1.pdf',
        ];
    }
}
