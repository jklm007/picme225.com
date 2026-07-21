<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsOutbox extends Model
{
    protected $table = 'sms_outbox';

    protected $fillable = [
        'phone_number',
        'message',
        'network',
        'status',
        'attempts',
        'gateway_node_id',
        'sent_at'
    ];

    protected $dates = [
        'sent_at',
        'created_at',
        'updated_at'
    ];
}
