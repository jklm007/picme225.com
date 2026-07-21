<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletPassbook extends Model
{
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'partner_id', 'amount', 'status', 'via',
        'transaction_id', 'reference_id', 'description',
    ];


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'updated_at'
    ];
}
