<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappUser extends Model
{
    protected $table = 'whatsapp_users';

    protected $fillable = [
        'phone_number',
        'whatsapp_id',
        'user_id',
        'name',
    ];

    /**
     * Get the associated standard user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the messages sent by this user.
     */
    public function messages()
    {
        return $this->hasMany(WhatsappMessage::class);
    }
}
