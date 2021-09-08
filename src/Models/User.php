<?php

namespace EscolaLms\Courses\Models;

use EscolaLms\Auth\Models\Traits\HasGroups;
use EscolaLms\Auth\Models\Traits\HasOnboardingStatus;
use EscolaLms\Auth\Models\Traits\UserHasSettings;
use EscolaLms\Core\Models\User as CoreUser;
use EscolaLms\Courses\Models\Traits\HasCourses;
use EscolaLms\Courses\Tests\Database\Factories\UserFactory;

class User extends CoreUser
{
    use HasCourses, HasGroups, HasOnboardingStatus, UserHasSettings;

    protected function getTraitOwner(): self
    {
        return $this;
    }

    public static function newFactory()
    {
        return UserFactory::new();
    }
}
