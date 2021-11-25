<?php

namespace EscolaLms\Courses\Models;

use EscolaLms\Core\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CourseUserPivot extends Pivot
{
    protected $table = 'course_user';

    protected $casts = [
        'deadline' => 'datetime',
        'course_completed_notification' => 'boolean',
        'deadline_notification' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
