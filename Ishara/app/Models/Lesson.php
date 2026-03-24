<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $fillable = ['level_id', 'letter', 'order'];

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function userProgress()
    {
        return $this->hasOne(UserLesson::class, 'lesson_id');
    }
}
