<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappMessage extends Model
{
    protected $table = 'whatsapp_messages';

    protected $fillable = [
        'whatsapp_user_id',
        'group_id',
        'content',
        'medias',
        'status',
        'error_log',
        'batch_processed',
    ];

    protected $casts = [
        'medias' => 'array',
    ];

    /**
     * Get the associated WhatsApp user.
     */
    public function sender()
    {
        return $this->belongsTo(WhatsappUser::class, 'whatsapp_user_id');
    }

    /**
     * Get the marketplace listing created from this message.
     */
    public function listing()
    {
        return $this->hasOne(MarketplaceListing::class, 'whatsapp_message_id');
    }
}
