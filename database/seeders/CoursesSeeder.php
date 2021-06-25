<?php

namespace EscolaLms\Courses\Database\Seeders;

use EscolaLms\Categories\Models\Category;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\HeadlessH5P\Models\H5PContent;

use EscolaLms\Courses\Models\TopicContent\RichText;
use EscolaLms\Courses\Models\TopicContent\Audio;
use EscolaLms\Courses\Models\TopicContent\Video;
use EscolaLms\Courses\Models\TopicContent\Image;
use EscolaLms\Courses\Models\TopicContent\H5P;
use EscolaLms\Courses\Models\TopicContent\OEmbed;


use EscolaLms\Tags\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Testing\WithFaker;

class CoursesSeeder extends Seeder
{
    use WithFaker;

    private function getRandomRichContent($withH5P = false)
    {
        $classes = [RichText::factory(), Audio::factory(), Video::factory(), Image::factory(), OEmbed::factory()];

        if ($withH5P) {
            $classes[] = H5P::factory();
        }

        return $classes[array_rand($classes)];
    }

    public function run()
    {
        $this->faker = $this->makeFaker();

        $hasH5P = H5PContent::first() !== null;

        $courses = Course::factory()
        ->count(rand(5, 10))
        ->has(Lesson::factory()
            ->has(
                Topic::factory()->afterCreating(function ($topic) use ($hasH5P) {
                    $content = $this->getRandomRichContent($hasH5P);
                    if (method_exists($content, 'updatePath')) {
                        $content = $content->updatePath($topic->id)->create();
                    } else {
                        $content = $content->create();
                    }

                    $topic->topicable()->associate($content)->save();
                })
            )
            ->count(rand(5, 10)))
            ->create();

        foreach ($courses as $course) {
            $this->seedTags($course);
            $this->seedCategories($course);
        }
    }

    private function seedTags($model)
    {
        for ($i = 0; $i < 3; $i++) {
            Tag::create([
                'morphable_id' => $model->getKey(),
                'morphable_type' => get_class($model),
                'title' => $this->faker->name
            ]);
        }
    }

    private function seedCategories(Course $course)
    {
        $categories = Category::factory(3)->create();
        foreach ($categories as $category) {
            $course->categories()->save($category);
            $this->seedTags($category);
        }
    }
}
