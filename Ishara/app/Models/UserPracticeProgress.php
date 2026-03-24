<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPracticeProgress extends Model
{
    protected $table = 'user_practice_progress';

    protected $fillable = ['user_id', 'lesson_id', 'is_completed', 'completed_at'];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}
