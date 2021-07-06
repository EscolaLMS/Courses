<?php

namespace EscolaLms\Courses\Tests\Models;

use EscolaLms\Core\Models\User as UserCore;
use EscolaLms\Courses\Models\Traits\HasCourses;
use EscolaLms\Courses\Tests\Database\Factories\UserFactory;

use EscolaLms\Auth\Models\Traits\HasOnboardingStatus;
use EscolaLms\Auth\Models\Traits\UserHasSettings;

class User extends UserCore
{
    use HasCourses, HasOnboardingStatus, UserHasSettings;

    protected function getTraitOwner(): self
    {
        return $this;
    }

    public static function newFactory()
    {
        return UserFactory::new();
    }
}
