<?php

namespace EscolaLms\Courses\Models;

use EscolaLms\Core\Models\User;
use EscolaLms\Courses\Models\Traits\ClearsResponseCache;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CourseUserPivot extends Pivot
{
    use ClearsResponseCache;

    protected $table = 'course_user';

    protected $casts = [
        'deadline' => 'datetime',
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
