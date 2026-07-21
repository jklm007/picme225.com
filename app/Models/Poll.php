<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Poll extends Model
{
    protected $fillable = ['post_id', 'question', 'expires_at', 'is_active'];

    public function options()
    {
        return $this->hasMany(PollOption::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
