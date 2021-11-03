<?php

namespace EscolaLms\Courses\Rules;

use EscolaLms\Core\Enums\UserRole;
use EscolaLms\Courses\Models\User;
use Illuminate\Contracts\Validation\Rule;

class ValidAuthor implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (!is_null($value)) {
            /** @var User $user */
            $user = User::find($value);
            if (is_null($user) || !$user->hasRole(UserRole::TUTOR) || !$user->haRole(UserRole::ADMIN)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Author must be a Tutor';
    }
}
