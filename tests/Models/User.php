<?php


namespace EscolaLms\Courses\Tests\Models;


use EscolaLms\Core\Models\User as UserCore;
use EscolaLms\Courses\Models\Traits\HasCourses;

class User extends UserCore
{
    use HasCourses;
}