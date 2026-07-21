<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketValidationLog extends Model
{
    protected $fillable = [
        'ticket_id',
        'scanned_by_type',
        'scanned_by_id',
        'status',
        'ip_address',
        'user_agent',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array'
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}
