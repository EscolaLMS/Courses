<?php

namespace EscolaLms\Courses\Notifications;

use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Notifications\Variables\UserFinishedCourseNotificationVariables;
use EscolaLms\Notifications\Core\NotificationAbstract;
use EscolaLms\Notifications\Core\NotificationContract;
use EscolaLms\Notifications\Core\Traits\NotificationDefaultImplementation;

class UserFinishedCourseNotification extends NotificationAbstract implements NotificationContract
{
    use NotificationDefaultImplementation;

    private Course $course;

    public function __construct(Course $course)
    {
        $this->course = $course;
    }

    public static function availableVia(): array
    {
        return ['mail'];
    }

    public static function defaultContentTemplate(): string
    {
        return __('Congratulations :user_name ! You have finished course ":course".', [
            'user_name' => UserFinishedCourseNotificationVariables::VAR_USER_NAME,
            'course' => UserFinishedCourseNotificationVariables::VAR_COURSE_TITLE,
        ]);
    }

    public static function defaultTitleTemplate(): string
    {
        return __('You finished ":course"', [
            'course' => UserFinishedCourseNotificationVariables::VAR_COURSE_TITLE,
        ]);
    }

    public static function templateVariablesClass(): string
    {
        return DeadlineNotificationVariables::class;
    }

    public function additionalDataForVariables(): array
    {
        return [
            $this->course
        ];
    }
}
