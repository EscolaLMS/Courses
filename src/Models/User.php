<?php

namespace EscolaLms\Courses\Models;

use EscolaLms\Auth\Models\Traits\HasGroups;
use EscolaLms\Auth\Models\Traits\HasOnboardingStatus;
use EscolaLms\Auth\Models\Traits\UserHasSettings;
use EscolaLms\Auth\Models\User as AuthUser;
use EscolaLms\Courses\Models\Traits\HasAuthoredCourses;
use EscolaLms\Courses\Models\Traits\HasCourses;
use EscolaLms\Courses\Tests\Database\Factories\UserFactory;

/**
 * @OA\Schema(
 *     description="User model",
 *     title="User",
 *     required={},
 *     @OA\Xml(
 *         name="User"
 *     ),
 *     @OA\Property(
 *          type="integer",
 *          format="int64",
 *          property="id",
 *     ),
 *     @OA\Property(
 *          property="email",
 *          type="string"
 *     )
 * )
 */
class User extends AuthUser
{
    use HasCourses, HasAuthoredCourses, HasGroups, HasOnboardingStatus, UserHasSettings;

    protected function getTraitOwner(): self
    {
        return $this;
    }

    public static function newFactory()
    {
        return UserFactory::new();
    }
}
