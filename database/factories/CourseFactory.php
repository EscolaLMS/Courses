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

    
    public function configure()
    {
        return $this->afterMaking(function (Course $course) {
            //
        })->afterCreating(function (Course $course) {
            //
            $id = $course->id;
            $word = $this->faker->word;
            $filename_image = "course/$id/".$word.".jpg";
            $filename_video = "course/$id/".$word.".mp4";
            $dest_image = storage_path("app/public/$filename_image");
            $dest_video = storage_path("app/public/$filename_video");
            $destDir = dirname($dest_image);
            if (!is_dir($destDir)) {
                mkdir($destDir, 0777, true);
            }
            copy(realpath(__DIR__."/../mocks/1.jpg"), $dest_image);
            copy(realpath(__DIR__."/../mocks/1.mp4"), $dest_video);

            $course->update([
                'image_path' =>  $filename_image,
                'video_path' => $filename_video
            ]);
        });
    }
}
