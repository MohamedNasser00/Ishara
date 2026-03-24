<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTestProgress extends Model
{
    protected $fillable = ['user_id', 'test_word_id', 'is_completed', 'completed_at'];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function testWord()
    {
        return $this->belongsTo(TestWord::class);
    }
}
