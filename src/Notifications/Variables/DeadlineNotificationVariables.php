<?php

namespace EscolaLms\Courses\Notifications\Variables;

use EscolaLms\Core\Models\User;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\ValueObjects\CourseProgressCollection;

class DeadlineNotificationVariables extends CommonUserAndCourseVariables
{
    const VAR_COURSE_DEADLINE = '@VarCourseDeadline';

    public static function getMockVariables(): array
    {
        $faker = \Faker\Factory::create();
        return array_merge(parent::getMockVariables(), [
            self::VAR_COURSE_DEADLINE => $faker->date(),
        ]);
    }

    public static function getVariablesFromContent(User $notifiable = null, ?Course $course = null): array
    {
        if (empty($notifiable) || empty($course)) {
            return [];
        };

        $progress = CourseProgressCollection::make($notifiable, $course);
        return array_merge(parent::getVariablesFromContent($notifiable, $course), [
            self::VAR_COURSE_DEADLINE => $progress->getDeadline(),
        ]);
    }

    public static function getRequiredVariables(): array
    {
        return [
            self::VAR_COURSE_TITLE,
            self::VAR_COURSE_DEADLINE
        ];
    }

    public static function getRequiredTitleVariables(): array
    {
        return [];
    }
}
