<?php

namespace EscolaLms\Courses\Database\Factories;

use EscolaLms\Auth\Models\User;
use EscolaLms\Core\Enums\UserRole;
use EscolaLms\Courses\Database\Factories\FakerMarkdownProvider\FakerProvider;
use EscolaLms\Courses\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

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
        $this->faker->addProvider(new FakerProvider($this->faker));

        $tutor = User::role(UserRole::TUTOR)->inRandomOrder()->first();

        return [
            'title' => $this->faker->sentence,
            'summary' => $this->faker->markdown,
            'image_path' => "1.jpg",
            'video_path' => "1.mp4",
            'base_price' => $this->faker->randomElement([1000, 1999, 0]),
            'duration' => rand(2, 10) . " hours",
            'author_id' => empty($tutor) ? null : $tutor->id,
            'active' => $this->faker->boolean,
            'subtitle' => $this->faker->sentence,
            'language' => $this->faker->randomElement(['en', 'pl']),
            'description' => $this->faker->markdown,
            'level' => $this->faker->randomElement(['beginner', 'regular', 'expert']),
            'poster_path' => "poster.jpg",
            'purchasable' => true,
            'findable' => true,
            'active_from' => null,
            'active_to' => null,
            'hours_to_complete' => null,
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
            $filename_image = "course/$id/" . $word . ".jpg";
            $filename_video = "course/$id/" . $word . ".mp4";
            $filename_poster = "course/$id/" . $word . "poster.jpg";
            $dest_image = Storage::disk('public')->path($filename_image);
            $dest_video = Storage::disk('public')->path($filename_video);
            $dest_poster = Storage::disk('public')->path($filename_poster);
            $destDir = dirname($dest_image);
            if (!is_dir($destDir)) {
                mkdir($destDir, 0777, true);
            }
            copy(realpath(__DIR__ . "/../mocks/1.jpg"), $dest_image);
            copy(realpath(__DIR__ . "/../mocks/1.mp4"), $dest_video);
            copy(realpath(__DIR__ . "/../mocks/poster.jpg"), $dest_poster);

            $course->update([
                'image_path' =>  $filename_image,
                'video_path' => $filename_video,
                'poster_path' => $filename_poster,
            ]);
        });
    }
}
