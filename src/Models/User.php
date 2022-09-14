<?php

namespace EscolaLms\Courses\Models;

use EscolaLms\Auth\Models\Traits\HasGroups;
use EscolaLms\Auth\Models\Traits\HasOnboardingStatus;
use EscolaLms\Auth\Models\Traits\UserHasSettings;
use EscolaLms\Auth\Models\User as AuthUser;
use EscolaLms\Categories\Models\Category;
use EscolaLms\Courses\Models\Traits\HasAuthoredCourses;
use EscolaLms\Courses\Models\Traits\HasCourses;
use EscolaLms\Courses\Tests\Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends AuthUser
{
    use HasCourses, HasAuthoredCourses, HasGroups, HasOnboardingStatus, UserHasSettings;

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_user');
    }

    protected function getTraitOwner(): self
    {
        return $this;
    }

    public static function newFactory()
    {
        return UserFactory::new();
    }
}
