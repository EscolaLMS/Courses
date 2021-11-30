<?php

namespace EscolaLms\Courses\Notifications\Variables;

use EscolaLms\Core\Enums\BasicEnum;
use EscolaLms\Core\Models\User;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Notifications\Core\NotificationVariableContract;
use EscolaLms\Notifications\Core\Traits\ContentIsValidIfContainsRequiredVariables;
use EscolaLms\Notifications\Core\Traits\TitleIsValidIfContainsRequiredVariables;

abstract class CommonUserAndCourseVariables extends BasicEnum implements NotificationVariableContract
{
    use TitleIsValidIfContainsRequiredVariables;
    use ContentIsValidIfContainsRequiredVariables;

    const VAR_USER_NAME       = '@VarUserName';
    const VAR_COURSE_TITLE    = '@VarCourseTitle';

    public static function getMockVariables(): array
    {
        $faker = \Faker\Factory::create();
        return [
            self::VAR_USER_NAME       => $faker->name(),
            self::VAR_COURSE_TITLE    => $faker->word(),
        ];
    }

    public static function getVariablesFromContent(User $notifiable = null, ?Course $course = null): array
    {
        if (empty($notifiable) || empty($course)) {
            return [];
        };

        return [
            self::VAR_USER_NAME    => $notifiable->name,
            self::VAR_COURSE_TITLE => $course->title,
        ];
    }

    public static function getRequiredVariables(): array
    {
        return [
            self::VAR_COURSE_TITLE,
        ];
    }

    public static function getRequiredTitleVariables(): array
    {
        return [];
    }
}
