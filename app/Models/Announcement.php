<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $guarded = [];

    // L'utilisateur ou le chauffeur qui publie
    public function creator()
    {
        return $this->morphTo();
    }

    // La catégorie
    public function category()
    {
        return $this->belongsTo(AnnouncementCategory::class);
    }
    
    // Les commentaires
    public function comments()
    {
        return $this->hasMany(AnnouncementComment::class);
    }

    // Les likes
    public function likes()
    {
        return $this->hasMany(AnnouncementLike::class);
    }

    // Les réservations (Bookings P2P)
    public function bookings()
    {
        return $this->hasMany(AnnouncementBooking::class);
    }
}
