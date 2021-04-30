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
        $classes = [RichText, Audio, Video, Image, H5P];
        return $classes[array_rand($classes)]::factory()->create();
        // TODO: instruduce a abstract TopicContent class
        // all below will extends this calls
        // and the list is from that abstraction
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
