<?php

namespace EscolaLms\Courses\Rules;

use EscolaLms\Courses\Models\Course;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;

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
            $user = Auth::user()->find($value);
            if (is_null($user) || !$user->can('create', Course::class)) {
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
        return __('Author must be a Tutor or Admin');
    }
}
