<?php

namespace EscolaLms\Courses\Notifications;

use EscolaLms\Core\Models\User;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Notifications\Variables\UserAssignmentCourseNotificationVariables;
use EscolaLms\Notifications\Core\NotificationAbstract;
use EscolaLms\Notifications\Core\NotificationContract;
use EscolaLms\Notifications\Core\Traits\NotificationDefaultImplementation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserUnassignedFromCourseNotification extends NotificationAbstract implements NotificationContract, ShouldQueue
{
    use NotificationDefaultImplementation;
    use Queueable;

    private Course $course;
    private ?User  $byWho;

    public function __construct(Course $course, ?User $byWho = null)
    {
        $this->course = $course;
        $this->byWho  = $byWho;
    }

    public static function availableVia(): array
    {
        return ['mail'];
    }

    public static function defaultContentTemplate(): string
    {
        return __('Hello :user_name ! You have been unassigned from course ":course".', [
            'user_name' => UserAssignmentCourseNotificationVariables::VAR_USER_NAME,
            'course' => UserAssignmentCourseNotificationVariables::VAR_COURSE_TITLE,
        ]);
    }

    public static function defaultTitleTemplate(): string
    {
        return __('You have been unassigned from ":course"', [
            'course' => UserAssignmentCourseNotificationVariables::VAR_COURSE_TITLE,
        ]);
    }

    public static function templateVariablesClass(): string
    {
        return UserAssignmentCourseNotificationVariables::class;
    }

    public function additionalDataForVariables($notifiable): array
    {
        return [
            $this->course,
            $this->byWho,
        ];
    }
}
