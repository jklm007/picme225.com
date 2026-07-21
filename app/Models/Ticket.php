<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'user_request_id',
        'user_id',
        'token',
        'signature',
        'status',
        'expires_at',
        'validated_at',
        'validated_by_type',
        'validated_by_id',
        'qr_code_data'
    ];

    protected $dates = [
        'expires_at',
        'validated_at',
    ];

    public function request()
    {
        return $this->belongsTo(UserRequests::class, 'user_request_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function validationLogs()
    {
        return $this->hasMany(TicketValidationLog::class);
    }

    public function isValid()
    {
        return $this->status === 'PENDING' && $this->expires_at->isFuture();
    }
}
