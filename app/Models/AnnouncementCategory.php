<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnnouncementCategory extends Model
{
    protected $guarded = [];

    public function announcements()
    {
        return $this->hasMany(Announcement::class, 'category_id');
    }
}
