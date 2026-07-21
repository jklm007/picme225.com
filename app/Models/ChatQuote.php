<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatQuote extends Model
{
    protected $table = 'chat_quotes';

    protected $fillable = [
        'message_id',
        'provider_id',
        'user_id',
        'listing_id',
        'amount',
        'status',
        'completion_images',
        'dispute_images',
        'dispute_reason',
    ];

    protected $casts = [
        'completion_images' => 'array',
        'dispute_images' => 'array',
    ];

    public function message()
    {
        return $this->belongsTo(SecureMessage::class, 'message_id');
    }

    public function provider()
    {
        return $this->belongsTo(\App\Models\Provider::class, 'provider_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
