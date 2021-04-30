<?php

namespace EscolaLms\Courses\Database\Seeders;

use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Models\TopicContent\RichText;
use EscolaLms\Courses\Models\TopicContent\Audio;
use EscolaLms\Courses\Models\TopicContent\Video;
use EscolaLms\Courses\Models\TopicContent\Image;
use EscolaLms\Courses\Models\TopicContent\H5P;


use Illuminate\Database\Seeder;

class CoursesSeeder extends Seeder
{
    private function getRandomRichContent()
    {
        $classes = [RichText::factory(), Audio::factory(), Video::factory(), Image::factory(), H5P::factory()];
        return $classes[array_rand($classes)]->create();
    }

    public function run()
    {
        Course::factory()
        ->count(1)
        ->has(Lesson::factory()
            ->has(
                Topic::factory()->afterCreating(function ($topic) {
                    $content = $this->getRandomRichContent();
                    $topic->topicable()->associate($content)->save();
                })
            )
            ->count(rand(1, 10)))

        ->create();
    }
}
