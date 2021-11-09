<?php

namespace EscolaLms\Courses\Database\Seeders;

use EscolaLms\TopicTypes\Database\Seeders\CoursesWithTopicSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(CoursesWithTopicSeeder::class);
    }
}
