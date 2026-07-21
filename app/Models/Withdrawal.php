<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    protected $fillable = [
        'fleet_id',
        'user_id',       // Nouveau système : propriétaire du wallet (User)
        'partner_id',    // Nouveau système : Partner associé
        'amount',
        'status',
        'method',
        'account_number',
        'recipient_name',
        'admin_notes',
        'transaction_id',
    ];

    // ── Relations ──────────────────────────────────────────────────────────────

    /** Fleet legacy */
    public function fleet()
    {
        return $this->belongsTo(Fleet::class);
    }

    /** Utilisateur propriétaire (nouveau système) */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Partenaire associé (nouveau système) */
    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }
}
