<?php

namespace EscolaLms\Courses\Database\Factories;

use EscolaLms\Auth\Models\User;
use EscolaLms\Core\Enums\UserRole;
use EscolaLms\Courses\Database\Factories\FakerMarkdownProvider\FakerProvider;
use EscolaLms\Courses\Enum\CourseStatusEnum;
use EscolaLms\Courses\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;

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
            'duration' => rand(2, 10) . " hours",
            'author_id' => empty($tutor) ? null : $tutor->id,
            'status' => $this->faker->randomElement(CourseStatusEnum::getValues()),
            'subtitle' => $this->faker->sentence,
            'language' => $this->faker->randomElement(['en', 'pl']),
            'description' => $this->faker->markdown,
            'level' => $this->faker->randomElement(['beginner', 'regular', 'expert']),
            'poster_path' => "poster.jpg",
            'findable' => true,
            'active_from' => null,
            'active_to' => null,
            'hours_to_complete' => null,
            'target_group' => $this->faker->randomElement(['top-level managers', 'mid-level managers', 'workers']),
            'teaser_url' => null,
            'public' => false,
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

            Storage::putFileAs("course/{$id}", new File(__DIR__ . '/../mocks/1.jpg'), $filename_image);
            Storage::putFileAs("course/{$id}", new File(__DIR__ . '/../mocks/1.mp4'), $filename_video);
            Storage::putFileAs("course/{$id}", new File(__DIR__ . '/../mocks/poster.jpg'), $filename_poster);        

            $course->update([
                'image_path' =>  $filename_image,
                'video_path' => $filename_video,
                'poster_path' => $filename_poster,
            ]);
        });
    }
}
