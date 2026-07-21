<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnnouncementComment extends Model
{
    protected $guarded = [];

    public function announcement()
    {
        return $this->belongsTo(Announcement::class);
    }
}
