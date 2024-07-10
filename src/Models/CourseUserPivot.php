<?php

namespace EscolaLms\Courses\Models;

use Carbon\Carbon;
use EscolaLms\Core\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property Carbon|null $deadline
 * @property Carbon|null $end_date
 */
class CourseUserPivot extends Pivot
{
    protected $table = 'course_user';

    protected $casts = [
        'deadline' => 'datetime',
        'end_date' => 'datetime',
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
