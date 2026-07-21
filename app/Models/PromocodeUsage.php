<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromocodeUsage extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id' ,'promocode_id','status'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'updated_at'
    ];

    /**
     * The services that belong to the user.
     */
    public function promocode()
    {
        return $this->belongsTo('App\Models\Promocode')->withTrashed();
    }

   	public function scopeActive($query)
    {
        return $query->where('status', 'ADDED');
    }
}
