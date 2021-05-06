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
use EscolaLms\Courses\Models\TopicContent\OEmbed;



use Illuminate\Database\Seeder;

class CoursesSeeder extends Seeder
{
    private function getRandomRichContent()
    {
        $classes = [RichText::factory(), Audio::factory(), Video::factory(), Image::factory(), H5P::factory(), OEmbed::factory()];
        
        return $classes[array_rand($classes)];
    }

    public function run()
    {
        Course::factory()
        ->count(rand(5, 10))
        ->count(1)
        ->has(Lesson::factory()
            ->has(
                Topic::factory()->afterCreating(function ($topic) {
                    $content = $this->getRandomRichContent();
                    if (method_exists($content, 'updatePath')) {
                        $content = $content->updatePath($topic->id)->create();
                    } else {
                        $content = $content->create();
                    }
                    
                    $topic->topicable()->associate($content)->save();
                })->count(10)
            )
            ->count(rand(5, 10)))

        ->create();
    }
}