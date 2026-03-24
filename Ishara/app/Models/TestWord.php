<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestWord extends Model
{
    protected $fillable = ['level_id', 'word', 'order'];

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function userProgress()
    {
        return $this->hasOne(UserTestProgress::class, 'test_word_id');
    }
}
