<?php

namespace EscolaLms\Courses\Tests\Models;

use EscolaLms\Core\Models\User as UserCore;
use EscolaLms\Courses\Models\Traits\HasCourses;
use EscolaLms\Courses\Tests\Database\Factories\UserFactory;

class User extends UserCore
{
    use HasCourses;

    public static function newFactory()
    {
        return UserFactory::new();
    }
}