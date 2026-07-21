<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecureMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_type',
        'sender_id',
        'receiver_type',
        'receiver_id',
        'announcement_id',
        'message',
        'is_flagged',
        'is_blocked',
    ];

    protected $casts = [
        'is_flagged' => 'boolean',
        'is_blocked' => 'boolean',
    ];

    public function sender()
    {
        return $this->morphTo();
    }

    public function receiver()
    {
        return $this->morphTo();
    }
}
