<?php

namespace EscolaLms\Courses\Notifications;

use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Notifications\Variables\UserFinishedCourseNotificationVariables;
use EscolaLms\Notifications\Core\NotificationAbstract;
use EscolaLms\Notifications\Core\NotificationContract;
use EscolaLms\Notifications\Core\Traits\NotificationDefaultImplementation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserFinishedCourseNotification extends NotificationAbstract implements NotificationContract, ShouldQueue
{
    use NotificationDefaultImplementation;
    use Queueable;

    private Course $course;

    public function __construct(Course $course)
    {
        $this->course = $course;
    }

    public static function availableVia(): array
    {
        return [
            'mail',
            'database'
        ];
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
        return UserFinishedCourseNotificationVariables::class;
    }

    public function additionalDataForVariables($notifiable): array
    {
        return [
            $this->course
        ];
    }

    public function toArray($notifiable, ?string $channel = null): array
    {
        return array_merge(parent::toArray($notifiable, $channel), [
            'course_id' => $this->course->getKey()
        ]);
    }
}
