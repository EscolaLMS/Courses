<?php

namespace EscolaLms\Courses\Models;

use EscolaLms\Courses\Tests\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\H5PUserProgress
 *
 */
class H5PUserProgress extends Model
{
    protected $table = 'h5p_user_progress';
    protected $guarded = array();

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'json',
    ];

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class, 'topic_id');
    }
}
