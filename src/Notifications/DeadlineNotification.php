<?php

namespace EscolaLms\Courses\Notifications;

use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Notifications\Variables\DeadlineNotificationVariables;
use EscolaLms\Notifications\Core\NotificationAbstract;
use EscolaLms\Notifications\Core\NotificationContract;
use EscolaLms\Notifications\Core\Traits\NotificationDefaultImplementation;

class DeadlineNotification extends NotificationAbstract implements NotificationContract
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
        return __('Hello :user_name ! Deadline for course ":course" is coming soon. You have time until :deadline to complete this course.', [
            'user_name' => DeadlineNotificationVariables::VAR_USER_NAME,
            'course' => DeadlineNotificationVariables::VAR_COURSE_TITLE,
            'deadline' => DeadlineNotificationVariables::VAR_COURSE_DEADLINE
        ]);
    }

    public static function defaultTitleTemplate(): string
    {
        return __('Deadline for course ":course"', [
            'course' => DeadlineNotificationVariables::VAR_COURSE_TITLE,
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
